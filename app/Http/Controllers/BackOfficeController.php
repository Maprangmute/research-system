<?php

namespace App\Http\Controllers;

use App\Models\BackOfficeEntry;
use App\Models\BackOfficeTask;
use App\Models\User;
use Carbon\Carbon;
use PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BackOfficeController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $fullName = trim($user->prefix . ' ' . $user->name);

        $visibleTaskIds = $this->visibleTaskQuery($user, $fullName)->select('back_office_tasks.id');

        $personList = BackOfficeEntry::query()
            ->whereIn('task_id', clone $visibleTaskIds)
            ->whereNotNull('person_name')
            ->where('person_name', '!=', '')
            ->distinct()
            ->orderBy('person_name')
            ->pluck('person_name');

        $categoryOptions = BackOfficeEntry::query()
            ->whereIn('task_id', clone $visibleTaskIds)
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        $creatorOptions = User::query()
            ->whereIn('id', $this->visibleTaskQuery($user, $fullName)->select('created_by')->distinct())
            ->select('id', 'prefix', 'name')
            ->orderBy('name')
            ->get();

        $currentSort = $request->input('sort', 'latest');

        $summaryQuery = BackOfficeTask::query();
        $this->applyVisibilityScope($summaryQuery, $user, $fullName);
        $this->applyIndexFilters($summaryQuery, $request);

        $summaryTaskIds = (clone $summaryQuery)->select('back_office_tasks.id');
        $entrySummary = BackOfficeEntry::query()
            ->whereIn('task_id', $summaryTaskIds)
            ->selectRaw('COUNT(*) as entries_count, COALESCE(SUM(hours), 0) as hours_sum, COALESCE(SUM(fte_value), 0) as fte_sum')
            ->first();

        $query = BackOfficeTask::query()
            ->with(['creator'])
            ->withCount('entries')
            ->withCount('outputs')
            ->withSum('entries', 'hours')
            ->withSum('entries', 'fte_value');

        $this->applyVisibilityScope($query, $user, $fullName);
        $this->applyIndexFilters($query, $request);
        $this->applyIndexSorting($query, $currentSort);

        $summary = [
            'tasks' => (clone $summaryQuery)->count('back_office_tasks.id'),
            'entries' => (int) ($entrySummary->entries_count ?? 0),
            'hours' => (float) ($entrySummary->hours_sum ?? 0),
            'fte' => (float) ($entrySummary->fte_sum ?? 0),
        ];

        $tasks = $query
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $viewData = [
            'categoryOptions' => $categoryOptions,
            'creatorOptions' => $creatorOptions,
            'currentSort' => $currentSort,
            'currentSortLabel' => $this->backOfficeSortLabel($currentSort),
            'personList' => $personList,
            'tasks' => $tasks,
            'summary' => $summary,
        ];

        if ($request->ajax()) {
            return view('back-office.partials.index-content', $viewData);
        }

        return view('back-office.index', $viewData);
    }

    private function visibleTaskQuery($user, string $fullName)
    {
        $query = BackOfficeTask::query();
        $this->applyVisibilityScope($query, $user, $fullName);

        return $query;
    }

    private function applyVisibilityScope($query, $user, string $fullName): void
    {
        if ($user->role === 'admin') {
            return;
        }

        $query->where(function ($q) use ($fullName, $user) {
            $q->where('created_by', $user->id)
                ->orWhereHas('entries', function ($entryQ) use ($fullName) {
                    $entryQ->where('person_name', $fullName);
                });
        });
    }

    private function applyIndexFilters($query, Request $request): void
    {
        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhere('purpose', 'like', '%' . $search . '%')
                    ->orWhere('leaders', 'like', '%' . $search . '%')
                    ->orWhereHas('creator', function ($creatorQ) use ($search) {
                        $creatorQ->where('name', 'like', '%' . $search . '%')
                            ->orWhere('prefix', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('entries', function ($entryQ) use ($search) {
                        $entryQ->where('person_name', 'like', '%' . $search . '%')
                            ->orWhere('job_name', 'like', '%' . $search . '%')
                            ->orWhere('job_detail', 'like', '%' . $search . '%')
                            ->orWhere('category', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('outputs', function ($outputQ) use ($search) {
                        $outputQ->where('title', 'like', '%' . $search . '%')
                            ->orWhere('type', 'like', '%' . $search . '%');
                    });
            });
        }

        if ($creator = $request->input('creator')) {
            $query->where('created_by', $creator);
        }

        if ($person = $request->input('person')) {
            $query->whereHas('entries', function ($entryQ) use ($person) {
                $entryQ->where('person_name', $person);
            });
        }

        if ($category = $request->input('category')) {
            $query->whereHas('entries', function ($entryQ) use ($category) {
                $entryQ->where('category', $category);
            });
        }

        if ($outputState = $request->input('with_output')) {
            if ($outputState === 'yes') {
                $query->has('outputs');
            }

            if ($outputState === 'no') {
                $query->whereDoesntHave('outputs');
            }
        }

        $rangeStart = $request->input('start');
        $rangeEnd = $request->input('end');
        if (!$rangeStart && !$rangeEnd) {
            return;
        }

        try {
            $from = $rangeStart ? Carbon::createFromFormat('d-m-Y', $rangeStart)->startOfDay() : null;
            $to = $rangeEnd ? Carbon::createFromFormat('d-m-Y', $rangeEnd)->endOfDay() : null;

            $query->whereHas('entries', function ($entryQ) use ($from, $to) {
                if ($from) {
                    $entryQ->whereRaw("STR_TO_DATE(end_date, '%d-%m-%Y') >= ?", [$from->format('Y-m-d H:i:s')]);
                }
                if ($to) {
                    $entryQ->whereRaw("STR_TO_DATE(start_date, '%d-%m-%Y') <= ?", [$to->format('Y-m-d H:i:s')]);
                }
            });
        } catch (\Exception $e) {
        }
    }

    private function applyIndexSorting($query, string $sort): void
    {
        switch ($sort) {
            case 'oldest':
                $query->orderBy('created_at');
                break;
            case 'title':
                $query->orderBy('title')->orderByDesc('created_at');
                break;
            case 'hours':
                $query->orderByDesc('entries_sum_hours')->orderByDesc('created_at');
                break;
            case 'fte':
                $query->orderByDesc('entries_sum_fte_value')->orderByDesc('created_at');
                break;
            case 'outputs':
                $query->orderByDesc('outputs_count')->orderByDesc('created_at');
                break;
            case 'latest':
            default:
                $query->orderByDesc('created_at');
                break;
        }
    }

    private function backOfficeSortLabel(string $sort): string
    {
        return match ($sort) {
            'oldest' => 'เก่าสุด',
            'title' => 'ชื่องาน A-Z',
            'hours' => 'ชั่วโมงมากที่สุด',
            'fte' => 'FTE มากที่สุด',
            'outputs' => 'ผลงานมากที่สุด',
            default => 'บันทึกล่าสุด',
        };
    }

    public function create()
    {
        $users = User::select('prefix', 'name')->orderBy('name')->get();
        $personList = $users->map(fn($u) => trim($u->prefix . ' ' . $u->name));

        return view('back-office.create', [
            'personList' => $personList,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date_format:d-m-Y'],
            'end_date' => ['required', 'date_format:d-m-Y', 'after_or_equal:start_date'],
            'description' => ['nullable', 'string'],
            'purpose' => ['nullable', 'string'],
            'leaders' => ['nullable', 'array'],
            'leaders.*' => ['nullable', 'string', 'max:255'],
            'outputs' => ['nullable', 'array'],
            'outputs.*.title' => ['required_with:outputs', 'string', 'max:255'],
            'outputs.*.type' => ['nullable', 'string', 'max:255'],
            'outputs.*.project_output' => ['nullable', 'string', 'max:255'],
            'outputs.*.year' => ['nullable', 'string', 'max:255'],
            'outputs.*.journal' => ['nullable', 'string', 'max:255'],
            'outputs.*.quartile' => ['nullable', 'in:Q1,Q2,Q3,Q4'],
            'outputs.*.impact_factor' => ['nullable', 'numeric'],
            'outputs.*.book_title' => ['nullable', 'string', 'max:1000'],
            'outputs.*.publisher_name' => ['nullable', 'string', 'max:1000'],
            'outputs.*.chapter_pages' => ['nullable', 'string', 'max:1000'],
            'outputs.*.book_authors' => ['nullable', 'array'],
            'outputs.*.book_authors.*.name' => ['nullable', 'string', 'max:255'],
            'outputs.*.authors' => ['nullable', 'array'],
            'outputs.*.authors.*.name' => ['nullable', 'string', 'max:255'],
            'outputs.*.authors.*.role' => ['nullable', 'string', 'max:50'],
            'outputs.*.conferences' => ['nullable', 'array'],
            'outputs.*.conferences.*.name' => ['nullable', 'string', 'max:255'],
            'outputs.*.conferences.*.presentation_type' => ['nullable', 'string', 'max:100'],
            'outputs.*.conferences.*.location' => ['nullable', 'string', 'max:255'],
            'outputs.*.conferences.*.note' => ['nullable', 'string', 'max:255'],
            'entries' => ['required', 'array', 'min:1'],
            'entries.*.person_name' => ['required', 'string', 'max:255'],
            'entries.*.category' => ['required', 'in:งานบริการด้านเอกสารงานวิจัย,งานบริการด้านการขอคำปรึกษางานวิจัย,งานบริการหน่วยงาน,งานบริการด้านทรัพยากรบุคคล,งานจัดประชุมที่ไม่เกี่ยวข้องกับงานวิจัย,งานบริการขนส่งสินค้าเเละเอกสาร,งานด้านติดต่อประสานงาน,งานด้านการลงตารางนัดหมายประชุม,งานด้านการออกแบบ เเละ สื่อสิ่งพิมพ์,งานด้านการสื่อสาร,งานด้านการพัฒนาเทคโนโลยีเเละสารสนเทศ,งานด้านการออกแบบเเละผลิตสื่อวิดิทัศน์,การจัดงานหรือเข้าร่วมงานนิทรรศการ,การออกแบบหลักสูตร เเละ การเรียนการสอน'],
            'entries.*.job_name' => ['required', 'string', 'max:255'],
            'entries.*.job_detail' => ['required', 'string', 'max:255'],
            'entries.*.start_date' => ['required', 'date_format:d-m-Y'],
            'entries.*.end_date' => ['required', 'date_format:d-m-Y'],
            'entries.*.hours' => ['required', 'numeric', 'min:0'],
        ]);

        $startDate = Carbon::createFromFormat('d-m-Y', $data['start_date'])->format('d-m-Y');
        $endDate = Carbon::createFromFormat('d-m-Y', $data['end_date'])->format('d-m-Y');
        $task = null;

        foreach ($data['entries'] as $idx => $entry) {
            $entryStart = Carbon::createFromFormat('d-m-Y', $entry['start_date']);
            $entryEnd = Carbon::createFromFormat('d-m-Y', $entry['end_date']);
            if ($entryEnd->lt($entryStart)) {
                return back()->withErrors([
                    "entries.$idx.end_date" => 'วันที่สิ้นสุดต้องไม่ก่อนวันที่เริ่มต้น',
                ])->withInput();
            }
        }

        DB::transaction(function () use ($data, $startDate, $endDate, &$task) {
            $task = BackOfficeTask::create([
                'title' => $data['title'],
                'start_date' => $startDate,
                'end_date' => $endDate,
                'description' => $data['description'] ?? null,
                'purpose' => $data['purpose'] ?? null,
                'leaders' => $data['leaders'] ?? null,
                'created_by' => Auth::id(),
            ]);

            if (!empty($data['outputs'])) {
                foreach ($data['outputs'] as $output) {
                    $task->outputs()->create([
                        'title' => $output['title'],
                        'type' => $output['type'] ?? null,
                        'project_output' => $output['project_output'] ?? null,
                        'year' => $output['year'] ?? null,
                        'journal' => $output['journal'] ?? null,
                        'quartile' => $output['quartile'] ?? null,
                        'impact_factor' => $output['impact_factor'] ?? null,
                        'book_title' => $output['book_title'] ?? null,
                        'publisher_name' => $output['publisher_name'] ?? null,
                        'chapter_pages' => $output['chapter_pages'] ?? null,
                        'book_authors' => collect($output['book_authors'] ?? [])
                            ->map(fn ($author) => ['name' => trim((string) ($author['name'] ?? ''))])
                            ->filter(fn ($author) => $author['name'] !== '')
                            ->values()
                            ->all(),
                        'authors' => $output['authors'] ?? null,
                        'conferences' => $output['conferences'] ?? null,
                    ]);
                }
            }

            foreach ($data['entries'] as $entry) {
                $hours = (float) $entry['hours'];
                $fteValue = round($hours * 0.000621, 8);

                $createdEntry = BackOfficeEntry::create([
                    'task_id' => $task->id,
                    'person_name' => $entry['person_name'],
                    'category' => $entry['category'],
                    'job_name' => $entry['job_name'],
                    'job_detail' => $entry['job_detail'],
                    'start_date' => $entry['start_date'],
                    'end_date' => $entry['end_date'],
                    'hours' => $hours,
                    'fte_value' => $fteValue,
                ]);

            }
        });

        return redirect()
            ->route('back_office.show', $task?->id)
            ->with('success', 'บันทึกงานนอกโครงการเรียบร้อย');
    }

    public function show(Request $request, BackOfficeTask $task)
    {
        $task->load(['entries', 'outputs', 'creator']);

        $summary = [
            'entries' => $task->entries->count(),
            'hours' => $task->entries->sum('hours'),
            'fte' => $task->entries->sum('fte_value'),
        ];

        if ($request->boolean('drawer')) {
            return view('back-office.partials.detail-content', [
                'task' => $task,
                'summary' => $summary,
                'drawerMode' => true,
            ]);
        }

        return view('back-office.show', [
            'task' => $task,
            'summary' => $summary,
        ]);
    }

    public function edit(BackOfficeTask $task)
    {
        $task->load(['entries', 'outputs', 'creator']);

        $users = User::select('prefix', 'name')->orderBy('name')->get();
        $personList = $users->map(fn($u) => trim($u->prefix . ' ' . $u->name));

        return view('back-office.edit', [
            'task' => $task,
            'personList' => $personList,
        ]);
    }

    public function update(Request $request, BackOfficeTask $task)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date_format:d-m-Y'],
            'end_date' => ['required', 'date_format:d-m-Y', 'after_or_equal:start_date'],
            'description' => ['nullable', 'string'],
            'purpose' => ['nullable', 'string'],
            'leaders' => ['nullable', 'array'],
            'leaders.*' => ['nullable', 'string', 'max:255'],
            'outputs' => ['nullable', 'array'],
            'outputs.*.title' => ['required_with:outputs', 'string', 'max:255'],
            'outputs.*.type' => ['nullable', 'string', 'max:255'],
            'outputs.*.project_output' => ['nullable', 'string', 'max:255'],
            'outputs.*.year' => ['nullable', 'string', 'max:255'],
            'outputs.*.journal' => ['nullable', 'string', 'max:255'],
            'outputs.*.quartile' => ['nullable', 'in:Q1,Q2,Q3,Q4'],
            'outputs.*.impact_factor' => ['nullable', 'numeric'],
            'outputs.*.book_title' => ['nullable', 'string', 'max:1000'],
            'outputs.*.publisher_name' => ['nullable', 'string', 'max:1000'],
            'outputs.*.chapter_pages' => ['nullable', 'string', 'max:1000'],
            'outputs.*.book_authors' => ['nullable', 'array'],
            'outputs.*.book_authors.*.name' => ['nullable', 'string', 'max:255'],
            'outputs.*.authors' => ['nullable', 'array'],
            'outputs.*.authors.*.name' => ['nullable', 'string', 'max:255'],
            'outputs.*.authors.*.role' => ['nullable', 'string', 'max:50'],
            'outputs.*.conferences' => ['nullable', 'array'],
            'outputs.*.conferences.*.name' => ['nullable', 'string', 'max:255'],
            'outputs.*.conferences.*.presentation_type' => ['nullable', 'string', 'max:100'],
            'outputs.*.conferences.*.location' => ['nullable', 'string', 'max:255'],
            'outputs.*.conferences.*.note' => ['nullable', 'string', 'max:255'],
            'entries' => ['required', 'array', 'min:1'],
            'entries.*.person_name' => ['required', 'string', 'max:255'],
            'entries.*.category' => ['required', 'in:งานบริการด้านเอกสารงานวิจัย,งานบริการด้านการขอคำปรึกษางานวิจัย,งานบริการหน่วยงาน,งานบริการด้านทรัพยากรบุคคล,งานจัดประชุมที่ไม่เกี่ยวข้องกับงานวิจัย,งานบริการขนส่งสินค้าเเละเอกสาร,งานด้านติดต่อประสานงาน,งานด้านการลงตารางนัดหมายประชุม,งานด้านการออกแบบ เเละ สื่อสิ่งพิมพ์,งานด้านการสื่อสาร,งานด้านการพัฒนาเทคโนโลยีเเละสารสนเทศ,งานด้านการออกแบบเเละผลิตสื่อวิดิทัศน์,การจัดงานหรือเข้าร่วมงานนิทรรศการ,การออกแบบหลักสูตร เเละ การเรียนการสอน'],
            'entries.*.job_name' => ['required', 'string', 'max:255'],
            'entries.*.job_detail' => ['required', 'string', 'max:255'],
            'entries.*.start_date' => ['required', 'date_format:d-m-Y'],
            'entries.*.end_date' => ['required', 'date_format:d-m-Y'],
            'entries.*.hours' => ['required', 'numeric', 'min:0'],
        ]);

        $startDate = Carbon::createFromFormat('d-m-Y', $data['start_date'])->format('d-m-Y');
        $endDate = Carbon::createFromFormat('d-m-Y', $data['end_date'])->format('d-m-Y');

        foreach ($data['entries'] as $idx => $entry) {
            $entryStart = Carbon::createFromFormat('d-m-Y', $entry['start_date']);
            $entryEnd = Carbon::createFromFormat('d-m-Y', $entry['end_date']);
            if ($entryEnd->lt($entryStart)) {
                return back()->withErrors([
                    "entries.$idx.end_date" => 'วันที่สิ้นสุดต้องไม่ก่อนวันที่เริ่มต้น',
                ])->withInput();
            }
        }

        DB::transaction(function () use ($data, $task, $startDate, $endDate) {
            $task->update([
                'title' => $data['title'],
                'start_date' => $startDate,
                'end_date' => $endDate,
                'description' => $data['description'] ?? null,
                'purpose' => $data['purpose'] ?? null,
                'leaders' => $data['leaders'] ?? null,
            ]);

            $task->outputs()->delete();
            if (!empty($data['outputs'])) {
                foreach ($data['outputs'] as $output) {
                    $task->outputs()->create([
                        'title' => $output['title'],
                        'type' => $output['type'] ?? null,
                        'project_output' => $output['project_output'] ?? null,
                        'year' => $output['year'] ?? null,
                        'journal' => $output['journal'] ?? null,
                        'quartile' => $output['quartile'] ?? null,
                        'impact_factor' => $output['impact_factor'] ?? null,
                        'book_title' => $output['book_title'] ?? null,
                        'publisher_name' => $output['publisher_name'] ?? null,
                        'chapter_pages' => $output['chapter_pages'] ?? null,
                        'book_authors' => collect($output['book_authors'] ?? [])
                            ->map(fn ($author) => ['name' => trim((string) ($author['name'] ?? ''))])
                            ->filter(fn ($author) => $author['name'] !== '')
                            ->values()
                            ->all(),
                        'authors' => $output['authors'] ?? null,
                        'conferences' => $output['conferences'] ?? null,
                    ]);
                }
            }

            $task->entries()->delete();

            foreach ($data['entries'] as $entry) {
                $hours = (float) $entry['hours'];
                $fteValue = round($hours * 0.000621, 8);

                $createdEntry = $task->entries()->create([
                    'person_name' => $entry['person_name'],
                    'category' => $entry['category'],
                    'job_name' => $entry['job_name'],
                    'job_detail' => $entry['job_detail'],
                    'start_date' => $entry['start_date'],
                    'end_date' => $entry['end_date'],
                    'hours' => $hours,
                    'fte_value' => $fteValue,
                ]);

            }
        });

        return redirect()
            ->route('back_office.show', $task->id)
            ->with('success', 'อัปเดตงานนอกโครงการเรียบร้อย');
    }

    public function pdf(BackOfficeTask $task)
    {
        $task->load(['entries', 'creator']);

        $summary = [
            'entries' => $task->entries->count(),
            'hours' => $task->entries->sum('hours'),
            'fte' => $task->entries->sum('fte_value'),
        ];

        $user = auth()->user();
        $dateStr = now()->setTimezone('Asia/Bangkok')->format('Ymd_Hi');
        $filename = "BACK_OFFICE_{$dateStr}_{$task->id}.pdf";

        $pdf = PDF::loadView('back-office.pdf', [
            'task' => $task,
            'summary' => $summary,
            'user' => $user,
        ]);

        return $pdf->stream($filename);
    }
}
