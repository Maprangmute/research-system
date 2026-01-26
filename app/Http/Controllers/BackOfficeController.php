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

        $users = User::select('prefix', 'name')->orderBy('name')->get();
        $personList = $users->map(fn($u) => trim($u->prefix . ' ' . $u->name));

        $query = BackOfficeTask::query()
            ->with(['creator'])
            ->withCount('entries')
            ->withCount('outputs')
            ->withSum('entries', 'hours')
            ->withSum('entries', 'fte_value');

        if ($user->role !== 'admin') {
            $query->where(function ($q) use ($fullName, $user) {
                $q->where('created_by', $user->id)
                    ->orWhereHas('entries', function ($entryQ) use ($fullName) {
                        $entryQ->where('person_name', $fullName);
                    });
            });
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
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

        $rangeStart = $request->input('start');
        $rangeEnd = $request->input('end');
        if ($rangeStart || $rangeEnd) {
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
            } catch (\Exception $e) {}
        }

        $summaryTasks = (clone $query)->get();
        $summary = [
            'tasks' => $summaryTasks->count(),
            'entries' => $summaryTasks->sum('entries_count'),
            'hours' => $summaryTasks->sum('entries_sum_hours'),
            'fte' => $summaryTasks->sum('entries_sum_fte_value'),
        ];

        $tasks = $query
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('back-office.index', [
            'personList' => $personList,
            'tasks' => $tasks,
            'summary' => $summary,
        ]);
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
            'outputs.*.delivered_at' => ['nullable', 'date_format:d-m-Y'],
            'outputs.*.description' => ['nullable', 'string', 'max:255'],
            'outputs.*.document_link' => ['nullable', 'string', 'max:255'],
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
            'entries.*.category' => ['required', 'in:งานบริการด้านเอกสารงานวิจัย,งานบริการด้านการขอคำปรึกษางานวิจัย,งานบริการหน่วยงาน,งานบริการด้านทรัพยากรบุคคล,งานจัดประชุมที่ไม่เกี่ยวข้องกับงานวิจัย,งานบริการขนส่งสินค้าเเละเอกสาร,งานด้านการออกแบบ เเละ สื่อสิ่งพิมพ์,งานด้านการสื่อสาร,งานด้านการพัฒนาเทคโนโลยีเเละสารสนเทศ,งานด้านการออกแบบเเละผลิตสื่อวิดิทัศน์,การจัดงานหรือเข้าร่วมงานนิทรรศการ,การออกแบบหลักสูตร เเละ การเรียนการสอน'],
            'entries.*.job_detail' => ['required', 'string', 'max:255'],
            'entries.*.start_date' => ['required', 'date_format:d-m-Y'],
            'entries.*.end_date' => ['required', 'date_format:d-m-Y'],
            'entries.*.document_link' => ['nullable', 'string', 'max:255'],
            'entries.*.hours' => ['required', 'numeric', 'min:0'],
            'entries.*.outputs' => ['nullable', 'array'],
            'entries.*.outputs.*.title' => ['required_with:entries.*.outputs', 'string', 'max:255'],
            'entries.*.outputs.*.project_output' => ['nullable', 'string', 'max:255'],
            'entries.*.outputs.*.year' => ['nullable', 'string', 'max:255'],
            'entries.*.outputs.*.journal' => ['nullable', 'string', 'max:255'],
            'entries.*.outputs.*.quartile' => ['nullable', 'in:Q1,Q2,Q3,Q4'],
            'entries.*.outputs.*.impact_factor' => ['nullable', 'numeric'],
            'entries.*.outputs.*.delivered_at' => ['nullable', 'date_format:d-m-Y'],
            'entries.*.outputs.*.description' => ['nullable', 'string', 'max:255'],
            'entries.*.outputs.*.document_link' => ['nullable', 'string', 'max:255'],
            'entries.*.outputs.*.authors' => ['nullable', 'array'],
            'entries.*.outputs.*.authors.*.name' => ['nullable', 'string', 'max:255'],
            'entries.*.outputs.*.authors.*.role' => ['nullable', 'string', 'max:50'],
            'entries.*.outputs.*.conferences' => ['nullable', 'array'],
            'entries.*.outputs.*.conferences.*.name' => ['nullable', 'string', 'max:255'],
            'entries.*.outputs.*.conferences.*.presentation_type' => ['nullable', 'string', 'max:100'],
            'entries.*.outputs.*.conferences.*.location' => ['nullable', 'string', 'max:255'],
            'entries.*.outputs.*.conferences.*.note' => ['nullable', 'string', 'max:255'],
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
                        'delivered_at' => $output['delivered_at'] ?? null,
                        'description' => $output['description'] ?? null,
                        'document_link' => $output['document_link'] ?? null,
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
                    'job_detail' => $entry['job_detail'],
                    'start_date' => $entry['start_date'],
                    'end_date' => $entry['end_date'],
                    'document_link' => $entry['document_link'] ?? null,
                    'hours' => $hours,
                    'fte_value' => $fteValue,
                ]);

                if (!empty($entry['outputs'])) {
                    foreach ($entry['outputs'] as $output) {
                        $createdEntry->outputs()->create([
                            'title' => $output['title'],
                            'project_output' => $output['project_output'] ?? null,
                            'year' => $output['year'] ?? null,
                            'journal' => $output['journal'] ?? null,
                            'quartile' => $output['quartile'] ?? null,
                            'impact_factor' => $output['impact_factor'] ?? null,
                            'delivered_at' => $output['delivered_at'] ?? null,
                            'description' => $output['description'] ?? null,
                            'document_link' => $output['document_link'] ?? null,
                            'authors' => $output['authors'] ?? null,
                            'conferences' => $output['conferences'] ?? null,
                        ]);
                    }
                }
            }
        });

        return redirect()
            ->route('back_office.show', $task?->id)
            ->with('success', 'บันทึกงานนอกโครงการเรียบร้อย');
    }

    public function show(BackOfficeTask $task)
    {
        $task->load(['entries.outputs', 'outputs', 'creator']);

        $summary = [
            'entries' => $task->entries->count(),
            'hours' => $task->entries->sum('hours'),
            'fte' => $task->entries->sum('fte_value'),
        ];

        return view('back-office.show', [
            'task' => $task,
            'summary' => $summary,
        ]);
    }

    public function edit(BackOfficeTask $task)
    {
        $task->load(['entries.outputs', 'outputs', 'creator']);

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
            'outputs.*.delivered_at' => ['nullable', 'date_format:d-m-Y'],
            'outputs.*.description' => ['nullable', 'string', 'max:255'],
            'outputs.*.document_link' => ['nullable', 'string', 'max:255'],
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
            'entries.*.category' => ['required', 'in:งานบริการด้านเอกสารงานวิจัย,งานบริการด้านการขอคำปรึกษางานวิจัย,งานบริการหน่วยงาน,งานบริการด้านทรัพยากรบุคคล,งานจัดประชุมที่ไม่เกี่ยวข้องกับงานวิจัย,งานบริการขนส่งสินค้าเเละเอกสาร,งานด้านการออกแบบ เเละ สื่อสิ่งพิมพ์,งานด้านการสื่อสาร,งานด้านการพัฒนาเทคโนโลยีเเละสารสนเทศ,งานด้านการออกแบบเเละผลิตสื่อวิดิทัศน์,การจัดงานหรือเข้าร่วมงานนิทรรศการ,การออกแบบหลักสูตร เเละ การเรียนการสอน'],
            'entries.*.job_detail' => ['required', 'string', 'max:255'],
            'entries.*.start_date' => ['required', 'date_format:d-m-Y'],
            'entries.*.end_date' => ['required', 'date_format:d-m-Y'],
            'entries.*.document_link' => ['nullable', 'string', 'max:255'],
            'entries.*.hours' => ['required', 'numeric', 'min:0'],
            'entries.*.outputs' => ['nullable', 'array'],
            'entries.*.outputs.*.title' => ['required_with:entries.*.outputs', 'string', 'max:255'],
            'entries.*.outputs.*.project_output' => ['nullable', 'string', 'max:255'],
            'entries.*.outputs.*.year' => ['nullable', 'string', 'max:255'],
            'entries.*.outputs.*.journal' => ['nullable', 'string', 'max:255'],
            'entries.*.outputs.*.quartile' => ['nullable', 'in:Q1,Q2,Q3,Q4'],
            'entries.*.outputs.*.impact_factor' => ['nullable', 'numeric'],
            'entries.*.outputs.*.delivered_at' => ['nullable', 'date_format:d-m-Y'],
            'entries.*.outputs.*.description' => ['nullable', 'string', 'max:255'],
            'entries.*.outputs.*.document_link' => ['nullable', 'string', 'max:255'],
            'entries.*.outputs.*.authors' => ['nullable', 'array'],
            'entries.*.outputs.*.authors.*.name' => ['nullable', 'string', 'max:255'],
            'entries.*.outputs.*.authors.*.role' => ['nullable', 'string', 'max:50'],
            'entries.*.outputs.*.conferences' => ['nullable', 'array'],
            'entries.*.outputs.*.conferences.*.name' => ['nullable', 'string', 'max:255'],
            'entries.*.outputs.*.conferences.*.presentation_type' => ['nullable', 'string', 'max:100'],
            'entries.*.outputs.*.conferences.*.location' => ['nullable', 'string', 'max:255'],
            'entries.*.outputs.*.conferences.*.note' => ['nullable', 'string', 'max:255'],
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
                        'delivered_at' => $output['delivered_at'] ?? null,
                        'description' => $output['description'] ?? null,
                        'document_link' => $output['document_link'] ?? null,
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
                    'job_detail' => $entry['job_detail'],
                    'start_date' => $entry['start_date'],
                    'end_date' => $entry['end_date'],
                    'document_link' => $entry['document_link'] ?? null,
                    'hours' => $hours,
                    'fte_value' => $fteValue,
                ]);

                if (!empty($entry['outputs'])) {
                    foreach ($entry['outputs'] as $output) {
                        $createdEntry->outputs()->create([
                            'title' => $output['title'],
                            'project_output' => $output['project_output'] ?? null,
                            'year' => $output['year'] ?? null,
                            'journal' => $output['journal'] ?? null,
                            'quartile' => $output['quartile'] ?? null,
                            'impact_factor' => $output['impact_factor'] ?? null,
                            'delivered_at' => $output['delivered_at'] ?? null,
                            'description' => $output['description'] ?? null,
                            'document_link' => $output['document_link'] ?? null,
                            'authors' => $output['authors'] ?? null,
                            'conferences' => $output['conferences'] ?? null,
                        ]);
                    }
                }
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
