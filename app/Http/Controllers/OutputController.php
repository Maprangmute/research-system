<?php

namespace App\Http\Controllers;

use App\Models\BackOfficeTaskOutput;
use App\Models\ExternalOutput;
use App\Models\Output;
use App\Models\OutputAuthor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use PDF;

class OutputController extends Controller
{
    private const EXTERNAL_JOB_CATEGORIES = [
        'TBA xxxxxx by Aj. V',
    ];

    public function externalAcademic(Request $request)
    {
        $query = ExternalOutput::query()
            ->with(['authors', 'conferences', 'creator'])
            ->latest();

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('output_code', 'like', '%' . $search . '%')
                    ->orWhere('title', 'like', '%' . $search . '%')
                    ->orWhere('note', 'like', '%' . $search . '%')
                    ->orWhere('journal', 'like', '%' . $search . '%')
                    ->orWhereHas('authors', function ($authorQ) use ($search) {
                        $authorQ->where('name', 'like', '%' . $search . '%')
                            ->orWhere('role', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('conferences', function ($conferenceQ) use ($search) {
                        $conferenceQ->where('name', 'like', '%' . $search . '%')
                            ->orWhere('presentation_type', 'like', '%' . $search . '%')
                            ->orWhere('location', 'like', '%' . $search . '%')
                            ->orWhere('presenter_name', 'like', '%' . $search . '%');
                    });
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $outputs = $query->paginate(12)->withQueryString();

        $statsQuery = ExternalOutput::query();
        $allCount = (clone $statsQuery)->count();
        $paperCount = (clone $statsQuery)->where('type', 'EA101')->count();
        $presentationCount = (clone $statsQuery)->where('type', 'EA103')->count();
        $textbookCount = (clone $statsQuery)->where('type', 'EA119')->count();
        $manualCount = (clone $statsQuery)->where('type', 'EA120')->count();

        $authorOptions = OutputAuthor::query()
            ->whereNotNull('name')
            ->whereRaw("TRIM(name) <> ''")
            ->selectRaw('TRIM(name) as name')
            ->distinct()
            ->orderBy('name')
            ->pluck('name')
            ->values();

        $presenterOptions = User::query()
            ->selectRaw("TRIM(CONCAT(COALESCE(prefix,''), ' ', COALESCE(name,''))) as name")
            ->whereRaw("TRIM(CONCAT(COALESCE(prefix,''), ' ', COALESCE(name,''))) <> ''")
            ->orderBy('name')
            ->pluck('name')
            ->unique()
            ->values();

        return view('outputs.external-academic-index', [
            'outputs' => $outputs,
            'allCount' => $allCount,
            'paperCount' => $paperCount,
            'presentationCount' => $presentationCount,
            'textbookCount' => $textbookCount,
            'manualCount' => $manualCount,
            'search' => $request->string('search')->toString(),
            'type' => $request->string('type')->toString(),
            'jobCategoryOptions' => self::EXTERNAL_JOB_CATEGORIES,
        ]);
    }

    public function externalAcademicCreate()
    {
        $authorOptions = OutputAuthor::query()
            ->whereNotNull('name')
            ->whereRaw("TRIM(name) <> ''")
            ->selectRaw('TRIM(name) as name')
            ->distinct()
            ->orderBy('name')
            ->pluck('name')
            ->values();

        $presenterOptions = User::query()
            ->selectRaw("TRIM(CONCAT(COALESCE(prefix,''), ' ', COALESCE(name,''))) as name")
            ->whereRaw("TRIM(CONCAT(COALESCE(prefix,''), ' ', COALESCE(name,''))) <> ''")
            ->orderBy('name')
            ->pluck('name')
            ->unique()
            ->values();

        return view('outputs.external-academic-create', [
            'authorOptions' => $authorOptions,
            'presenterOptions' => $presenterOptions,
            'jobCategoryOptions' => self::EXTERNAL_JOB_CATEGORIES,
        ]);
    }

    public function externalAcademicShow(ExternalOutput $externalOutput)
    {
        $externalOutput->load(['authors', 'conferences', 'creator']);

        return view('outputs.external-academic-show', [
            'externalOutput' => $externalOutput,
        ]);
    }

    public function externalAcademicEdit(ExternalOutput $externalOutput)
    {
        $authorOptions = OutputAuthor::query()
            ->whereNotNull('name')
            ->whereRaw("TRIM(name) <> ''")
            ->selectRaw('TRIM(name) as name')
            ->distinct()
            ->orderBy('name')
            ->pluck('name')
            ->values();

        $presenterOptions = User::query()
            ->selectRaw("TRIM(CONCAT(COALESCE(prefix,''), ' ', COALESCE(name,''))) as name")
            ->whereRaw("TRIM(CONCAT(COALESCE(prefix,''), ' ', COALESCE(name,''))) <> ''")
            ->orderBy('name')
            ->pluck('name')
            ->unique()
            ->values();

        $externalOutput->load(['authors', 'conferences']);

        return view('outputs.external-academic-edit', [
            'externalOutput' => $externalOutput,
            'authorOptions' => $authorOptions,
            'presenterOptions' => $presenterOptions,
            'jobCategoryOptions' => self::EXTERNAL_JOB_CATEGORIES,
        ]);
    }

    public function storeExternalAcademic(Request $request): RedirectResponse
    {
        $normalizedOutputs = collect($request->input('outputs', []))
            ->map(function ($item) {
                $item['publish_date'] = $this->normalizeThaiDateInput($item['publish_date'] ?? null);
                return $item;
            })
            ->values()
            ->all();

        $request->merge([
            'outputs' => $normalizedOutputs,
        ]);

        $validated = $request->validate([
            'outputs' => ['required', 'array', 'min:1'],
            'outputs.*.title' => ['required', 'string', 'max:5000'],
            'outputs.*.type' => ['required', 'in:EA101,EA103,EA119,EA120'],
            'outputs.*.job_category' => ['required', 'in:' . implode(',', self::EXTERNAL_JOB_CATEGORIES)],
            'outputs.*.note' => ['nullable', 'string', 'max:5000'],
            'outputs.*.publish_date' => ['required', 'date'],
            'outputs.*.journal' => ['required_if:outputs.*.type,EA101', 'nullable', 'string', 'max:255'],
            'outputs.*.quartile' => ['nullable', 'in:Q1,Q2,Q3,Q4'],
            'outputs.*.impact_factor' => ['nullable', 'numeric', 'min:0', 'max:99999.999'],
            'outputs.*.book_title' => ['required_if:outputs.*.type,EA119', 'nullable', 'string', 'max:1000'],
            'outputs.*.publisher_name' => ['required_if:outputs.*.type,EA119', 'nullable', 'string', 'max:1000'],
            'outputs.*.chapter_pages' => ['required_if:outputs.*.type,EA119', 'nullable', 'string', 'max:1000'],
            'outputs.*.authors' => ['nullable', 'array'],
            'outputs.*.authors.*.role' => ['nullable', 'in:first,co,corresponding'],
            'outputs.*.authors.*.name' => ['nullable', 'string', 'max:255'],
            'outputs.*.conferences' => ['nullable', 'array'],
            'outputs.*.conferences.*.name' => ['required_if:outputs.*.type,EA103', 'nullable', 'string', 'max:255'],
            'outputs.*.conferences.*.presentation_type' => ['nullable', 'in:Oral Presentation,Poster Presentation,Abstract Presentation,Panel Discussion,Keynote Speech,World café'],
            'outputs.*.conferences.*.location' => ['nullable', 'string', 'max:255'],
            'outputs.*.conferences.*.presenter_name' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['outputs'] as $item) {
                $type = (string) ($item['type'] ?? 'EA101');
                $output = ExternalOutput::create([
                    'type' => $type,
                    'output_code' => null,
                    'job_category' => $this->nullableTrim($item['job_category'] ?? null),
                    'title' => trim((string) ($item['title'] ?? '')),
                    'note' => $this->nullableTrim($item['note'] ?? null),
                    'publish_date' => $item['publish_date'] ?? null,
                    'journal' => $type === 'EA101' ? $this->nullableTrim($item['journal'] ?? null) : null,
                    'quartile' => $type === 'EA101' ? $this->nullableTrim($item['quartile'] ?? null) : null,
                    'impact_factor' => $type === 'EA101' && ($item['impact_factor'] ?? null) !== null && $item['impact_factor'] !== ''
                        ? $item['impact_factor']
                        : null,
                    'book_title' => $type === 'EA119' ? $this->nullableTrim($item['book_title'] ?? null) : null,
                    'publisher_name' => $type === 'EA119' ? $this->nullableTrim($item['publisher_name'] ?? null) : null,
                    'chapter_pages' => $type === 'EA119' ? $this->nullableTrim($item['chapter_pages'] ?? null) : null,
                    'created_by' => auth()->id(),
                ]);

                if (in_array($type, ['EA101', 'EA119'], true)) {
                    $authors = collect($item['authors'] ?? [])
                        ->map(function ($author) {
                            return [
                                'role' => $this->nullableTrim($author['role'] ?? null),
                                'name' => $this->nullableTrim($author['name'] ?? null),
                            ];
                        })
                        ->filter(fn ($author) => $author['name'])
                        ->values()
                        ->all();

                    if (!empty($authors)) {
                        $output->authors()->createMany($authors);
                    }
                }

                if ($type === 'EA103') {
                    $conferences = collect($item['conferences'] ?? [])
                        ->map(function ($conference) {
                            return [
                                'name' => $this->nullableTrim($conference['name'] ?? null),
                                'presentation_type' => $this->nullableTrim($conference['presentation_type'] ?? null),
                                'location' => $this->nullableTrim($conference['location'] ?? null),
                                'presenter_name' => $this->nullableTrim($conference['presenter_name'] ?? null),
                            ];
                        })
                        ->filter(fn ($conference) => $conference['name'] || $conference['presentation_type'] || $conference['location'] || $conference['presenter_name'])
                        ->values()
                        ->all();

                    if (!empty($conferences)) {
                        $output->conferences()->createMany($conferences);
                    }
                }
            }
        });

        return redirect()
            ->route('outputs.external-academic.create')
            ->with('success', 'บันทึก Academic Paper / Presentation เรียบร้อยแล้ว');
    }

    public function updateExternalAcademic(Request $request, ExternalOutput $externalOutput): RedirectResponse
    {
        $request->merge([
            'publish_date' => $this->normalizeThaiDateInput($request->input('publish_date')),
        ]);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:5000'],
            'type' => ['required', 'in:EA101,EA103,EA119,EA120'],
            'job_category' => ['required', 'in:' . implode(',', self::EXTERNAL_JOB_CATEGORIES)],
            'note' => ['nullable', 'string', 'max:5000'],
            'publish_date' => ['required', 'date'],
            'journal' => ['required_if:type,EA101', 'nullable', 'string', 'max:255'],
            'quartile' => ['nullable', 'in:Q1,Q2,Q3,Q4'],
            'impact_factor' => ['nullable', 'numeric', 'min:0', 'max:99999.999'],
            'book_title' => ['required_if:type,EA119', 'nullable', 'string', 'max:1000'],
            'publisher_name' => ['required_if:type,EA119', 'nullable', 'string', 'max:1000'],
            'chapter_pages' => ['required_if:type,EA119', 'nullable', 'string', 'max:1000'],
            'authors' => ['nullable', 'array'],
            'authors.*.role' => ['nullable', 'in:first,co,corresponding'],
            'authors.*.name' => ['nullable', 'string', 'max:255'],
            'conferences' => ['nullable', 'array'],
            'conferences.*.name' => ['required_if:type,EA103', 'nullable', 'string', 'max:255'],
            'conferences.*.presentation_type' => ['nullable', 'in:Oral Presentation,Poster Presentation,Abstract Presentation,Panel Discussion,Keynote Speech,World café'],
            'conferences.*.location' => ['nullable', 'string', 'max:255'],
            'conferences.*.presenter_name' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($validated, $externalOutput) {
            $type = (string) $validated['type'];

            $externalOutput->update([
                'type' => $type,
                'job_category' => $this->nullableTrim($validated['job_category'] ?? null),
                'title' => trim((string) $validated['title']),
                'note' => $this->nullableTrim($validated['note'] ?? null),
                'publish_date' => $validated['publish_date'],
                'journal' => $type === 'EA101' ? $this->nullableTrim($validated['journal'] ?? null) : null,
                'quartile' => $type === 'EA101' ? $this->nullableTrim($validated['quartile'] ?? null) : null,
                'impact_factor' => $type === 'EA101' && ($validated['impact_factor'] ?? null) !== null && $validated['impact_factor'] !== ''
                    ? $validated['impact_factor']
                    : null,
                'book_title' => $type === 'EA119' ? $this->nullableTrim($validated['book_title'] ?? null) : null,
                'publisher_name' => $type === 'EA119' ? $this->nullableTrim($validated['publisher_name'] ?? null) : null,
                'chapter_pages' => $type === 'EA119' ? $this->nullableTrim($validated['chapter_pages'] ?? null) : null,
            ]);

            $externalOutput->authors()->delete();
            $externalOutput->conferences()->delete();

            if (in_array($type, ['EA101', 'EA119'], true)) {
                $authors = collect($validated['authors'] ?? [])
                    ->map(fn ($author) => [
                        'role' => $this->nullableTrim($author['role'] ?? null),
                        'name' => $this->nullableTrim($author['name'] ?? null),
                    ])
                    ->filter(fn ($author) => $author['name'])
                    ->values()
                    ->all();

                if (!empty($authors)) {
                    $externalOutput->authors()->createMany($authors);
                }
            }

            if ($type === 'EA103') {
                $conferences = collect($validated['conferences'] ?? [])
                    ->map(fn ($conference) => [
                        'name' => $this->nullableTrim($conference['name'] ?? null),
                        'presentation_type' => $this->nullableTrim($conference['presentation_type'] ?? null),
                        'location' => $this->nullableTrim($conference['location'] ?? null),
                        'presenter_name' => $this->nullableTrim($conference['presenter_name'] ?? null),
                    ])
                    ->filter(fn ($conference) => $conference['name'] || $conference['presentation_type'] || $conference['location'] || $conference['presenter_name'])
                    ->values()
                    ->all();

                if (!empty($conferences)) {
                    $externalOutput->conferences()->createMany($conferences);
                }
            }
        });

        return redirect()
            ->route('outputs.external-academic.show', $externalOutput)
            ->with('success', 'อัปเดต Academic Paper / Presentation เรียบร้อยแล้ว');
    }

    public function destroyExternalAcademic(ExternalOutput $externalOutput): RedirectResponse
    {
        $externalOutput->delete();

        return redirect()
            ->route('outputs.external-academic')
            ->with('success', 'ลบรายการเรียบร้อยแล้ว');
    }

    private function nullableTrim(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function normalizeThaiDateInput(mixed $value): ?string
    {
        $value = $this->nullableTrim($value);
        if (!$value) {
            return null;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return $value;
        }

        $normalized = str_replace('/', '-', $value);
        $parts = array_values(array_filter(explode('-', $normalized), fn ($part) => $part !== ''));
        if (count($parts) !== 3) {
            return $value;
        }

        [$day, $month, $year] = $parts;
        $day = (int) $day;
        $month = (int) $month;
        $year = (int) $year;

        if ($year > 2400) {
            $year -= 543;
        }

        if (!checkdate($month, $day, $year)) {
            return $value;
        }

        return Carbon::createFromDate($year, $month, $day)->format('Y-m-d');
    }

    public function index(Request $request)
    {
        $extractTypeCode = function ($rawType): ?string {
            $type = trim((string) ($rawType ?? ''));
            if ($type === '') {
                return null;
            }

            if (preg_match('/EA\d{3}/i', $type, $matches)) {
                return strtoupper($matches[0]);
            }

            return null;
        };
        $resolveTypeLabel = function ($rawType) use ($extractTypeCode): ?string {
            $raw = trim((string) ($rawType ?? ''));
            if ($raw === '') {
                return null;
            }

            $code = $extractTypeCode($raw);
            if (!$code) {
                return null;
            }

            if (preg_match('/^' . preg_quote($code, '/') . '\b/i', $raw)) {
                return $raw;
            }

            return $code;
        };

        $query = Output::with('project:id,project_name_th,project_code');
        $user = auth()->user();
        $fullName = trim(($user->prefix ?? '') . ' ' . $user->name);
        $canViewAll = $user->role === 'admin' || $user->role === 'user_all';

        if (!$canViewAll) {
            $query->whereHas('project', function ($q) use ($user, $fullName) {
                $q->where('user_id', $user->id)
                    ->orWhereHas('fteRoles', function ($subQ) use ($fullName) {
                        $subQ->where('name', $fullName);
                    });
            });
        }

        $projectTypeSourceQuery = Output::query();
        if (!$canViewAll) {
            $projectTypeSourceQuery->whereHas('project', function ($q) use ($user, $fullName) {
                $q->where('user_id', $user->id)
                    ->orWhereHas('fteRoles', function ($subQ) use ($fullName) {
                        $subQ->where('name', $fullName);
                    });
            });
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('type', 'like', '%' . $search . '%')
                    ->orWhereHas('project', function ($projectQ) use ($search) {
                        $projectQ->where('project_name_th', 'like', '%' . $search . '%')
                            ->orWhere('project_code', 'like', '%' . $search . '%');
                    });
            });
        }
        if ($request->filled('type')) {
            $typeCode = $extractTypeCode($request->type);
            if ($typeCode) {
                $query->where('type', 'like', $typeCode . '%');
            }
        }
        if ($request->filled('year')) {
            $year = trim((string) $request->year);
            $query->where(function ($q) use ($year) {
                $q->where('year', 'like', '%' . $year . '%');
                if (is_numeric($year) && (int) $year > 2400) {
                    $ce = (int) $year - 543;
                    $q->orWhere('year', 'like', '%' . $ce . '%');
                }
            });
        }
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        $sortable = ['created_at', 'year', 'type', 'title'];
        $sort = in_array($request->get('sort'), $sortable, true) ? $request->get('sort') : 'created_at';
        $dir = $request->get('dir') === 'asc' ? 'asc' : 'desc';

        $projectOutputs = $query->orderBy($sort, $dir)->get();

        $typeOptions = collect();

        $parseYear = function ($raw) {
            if (!is_string($raw)) {
                $raw = (string) $raw;
            }
            if (preg_match('/\d{4}/', $raw, $m)) {
                $y = (int) $m[0];
                if ($y < 2400) {
                    $y += 543;
                }
                return $y;
            }
            return null;
        };

        $yearOptions = Output::select('year')->whereNotNull('year')->distinct()->pluck('year')
            ->map(fn($raw) => $parseYear($raw))
            ->filter()
            ->unique()
            ->values();

        $taskOutputQuery = BackOfficeTaskOutput::query()
            ->with(['task:id,title,created_by'])
            ->when(!$canViewAll, function ($q) use ($user, $fullName) {
                $q->whereHas('task', function ($taskQ) use ($user, $fullName) {
                    $taskQ->where('created_by', $user->id)
                        ->orWhereHas('entries', function ($entryQ) use ($fullName) {
                            $entryQ->where('person_name', $fullName);
                        });
                });
            });

        $taskTypeSourceQuery = BackOfficeTaskOutput::query()
            ->when(!$canViewAll, function ($q) use ($user, $fullName) {
                $q->whereHas('task', function ($taskQ) use ($user, $fullName) {
                    $taskQ->where('created_by', $user->id)
                        ->orWhereHas('entries', function ($entryQ) use ($fullName) {
                            $entryQ->where('person_name', $fullName);
                        });
                });
            });

        if ($request->filled('search')) {
            $taskOutputQuery->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                    ->orWhereHas('task', function ($taskQ) use ($request) {
                        $taskQ->where('title', 'like', '%' . $request->search . '%');
                    });
            });
        }

        if ($request->filled('type')) {
            $typeCode = $extractTypeCode($request->type);
            if ($typeCode) {
                $taskOutputQuery->where('type', 'like', $typeCode . '%');
            }
        }

        if ($request->filled('project_id')) {
            $taskOutputQuery = null;
        }

        $taskOutputs = $taskOutputQuery ? $taskOutputQuery->get() : collect();

        $taskOutputYears = $taskOutputs->pluck('year')
            ->map(fn($raw) => $parseYear((string) $raw))
            ->filter()
            ->unique();
        $yearOptions = $yearOptions->merge($taskOutputYears)->unique()->sortDesc()->values();

        $typeLabelByCode = [
            'EA999' => 'EA999 Other Outputs',
        ];
        $registerTypeLabel = function ($rawType) use (&$typeLabelByCode, $extractTypeCode, $resolveTypeLabel): void {
            $code = $extractTypeCode($rawType);
            if (!$code) {
                return;
            }

            $label = $resolveTypeLabel($rawType) ?? $code;
            $current = $typeLabelByCode[$code] ?? '';

            if ($current === '' || mb_strlen($label) > mb_strlen($current)) {
                $typeLabelByCode[$code] = $label;
            }
        };

        $projectTypeSourceQuery->pluck('type')->each($registerTypeLabel);
        $taskTypeSourceQuery->pluck('type')->each($registerTypeLabel);

        $typeOptions = collect($typeLabelByCode)->sortKeys()->values();

        $normalizeYear = function ($raw) use ($parseYear) {
            $y = $parseYear((string) $raw);
            return $y ? (int) $y : null;
        };

        $items = collect();

        foreach ($projectOutputs as $output) {
            $yearBe = $normalizeYear($output->year ?? '');
            $typeCode = $extractTypeCode($output->type) ?? '-';
            $typeLabel = $typeLabelByCode[$typeCode] ?? (trim((string) ($output->type ?? '')) ?: $typeCode);
            $items->push((object) [
                'title' => $output->title ?: '-',
                'type' => $typeLabel,
                'year_be' => $yearBe,
                'project_label' => $output->project?->project_name_th,
                'source_badge' => null,
                'detail' => null,
                'link' => $output->project ? route('projects.show', $output->project) : null,
                'created_at' => $output->created_at,
                'sort_year' => $yearBe ?? 0,
                'sort_title' => mb_strtolower($output->title ?? ''),
                'sort_type' => mb_strtolower($typeCode),
            ]);
        }

        $yearFilterBe = null;
        if ($request->filled('year')) {
            $yearFilterBe = $normalizeYear($request->year);
        }

        foreach ($taskOutputs as $output) {
            $yearBe = $normalizeYear($output->year ?? '');
            if ($yearFilterBe && $yearBe !== $yearFilterBe) {
                continue;
            }
            $typeCode = $extractTypeCode($output->type) ?? '-';
            $typeLabel = $typeLabelByCode[$typeCode] ?? (trim((string) ($output->type ?? '')) ?: $typeCode);
            $items->push((object) [
                'title' => $output->title ?: '-',
                'type' => $typeLabel,
                'year_be' => $yearBe,
                'project_label' => $output->task?->title,
                'source_badge' => 'งานนอกโครงการวิจัย',
                'detail' => null,
                'link' => $output->task ? route('back_office.show', $output->task->id) : null,
                'created_at' => $output->created_at,
                'sort_year' => $yearBe ?? 0,
                'sort_title' => mb_strtolower($output->title ?? ''),
                'sort_type' => mb_strtolower($typeCode),
            ]);
        }

        $items = $items->sortBy(function ($item) use ($sort) {
            return match ($sort) {
                'year' => $item->sort_year,
                'type' => $item->sort_type,
                'title' => $item->sort_title,
                default => $item->created_at?->timestamp ?? 0,
            };
        });
        if ($dir === 'desc') {
            $items = $items->reverse();
        }

        $perPage = 15;
        $page = (int) $request->get('page', 1);
        $paged = $items->slice(($page - 1) * $perPage, $perPage)->values();
        $outputs = new LengthAwarePaginator($paged, $items->count(), $perPage, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        return view('outputs.index', [
            'outputs' => $outputs,
            'typeOptions' => $typeOptions->unique()->sort()->values(),
            'yearOptions' => $yearOptions,
        ]);
    }

    public function papers(Request $request)
    {
        $sortable = ['created_at', 'year', 'title', 'journal', 'quartile'];
        $sort = in_array($request->get('sort'), $sortable, true) ? $request->get('sort') : 'created_at';
        $dir = $request->get('dir') === 'asc' ? 'asc' : 'desc';
        $payload = $this->buildPapersPayload($request, $sort, $dir);
        $papers = $payload['items'];
        $summary = $payload['summary'];
        $authorOptions = $payload['authorOptions'];
        $journalOptions = $payload['journalOptions'];
        $yearOptions = $payload['yearOptions'];

        $outputs = $this->paginateCollection($papers, $request, 5);

        return view('outputs.papers', [
            'outputs' => $outputs,
            'summary' => $summary,
            'authorOptions' => $authorOptions,
            'journalOptions' => $journalOptions,
            'yearOptions' => $yearOptions,
            'impactFactorMax' => $payload['impactFactorMax'],
            'selectedImpactMax' => $payload['selectedImpactMax'],
            'roleOptions' => collect([
                'first' => 'First Author',
                'co' => 'Co Author',
                'corresponding' => 'Corresponding Author',
            ]),
            'currentSort' => $sort,
            'currentDir' => $dir,
        ]);
    }

    public function papersPdf(Request $request)
    {
        $sort = in_array($request->get('sort'), ['created_at', 'year', 'title', 'journal', 'quartile'], true)
            ? $request->get('sort')
            : 'created_at';
        $dir = $request->get('dir') === 'asc' ? 'asc' : 'desc';
        $payload = $this->buildPapersPayload($request, $sort, $dir);

        $user = auth()->user();
        $reporter = trim(($user->prefix ?? '') . ' ' . ($user->name ?? ''));
        $filters = collect([
            $request->filled('search') ? 'ค้นหา: ' . trim((string) $request->input('search')) : null,
            $request->filled('role') ? 'Role: ' . trim((string) $request->input('role')) : null,
            $request->filled('quartile') ? 'Quartile: ' . trim((string) $request->input('quartile')) : null,
            $request->filled('impact_max')
                ? 'Impact Factor <= ' . trim((string) $request->input('impact_max'))
                : null,
            $request->filled('year') ? 'ปี: ' . trim((string) $request->input('year')) : null,
        ])->filter()->values();

        $pdf = PDF::loadView('outputs.papers-pdf', [
            'papers' => $payload['items'],
            'summary' => $payload['summary'],
            'reporter' => $reporter !== '' ? $reporter : 'ผู้ใช้ระบบ',
            'generatedAt' => now()->setTimezone('Asia/Bangkok'),
            'filters' => $filters,
            'sort' => $sort,
            'dir' => $dir,
        ]);

        return $pdf->stream('research-papers-report.pdf');
    }

    public function presentations(Request $request)
    {
        $sortable = ['created_at', 'year', 'title', 'conference', 'activity'];
        $sort = in_array($request->get('sort'), $sortable, true) ? $request->get('sort') : 'created_at';
        $dir = $request->get('dir') === 'asc' ? 'asc' : 'desc';
        $payload = $this->buildPresentationsPayload($request, $sort, $dir);
        $rows = $payload['items'];

        $outputs = $this->paginateCollection($rows, $request, 20);

        return view('outputs.presentations', [
            'outputs' => $outputs,
            'summary' => $payload['summary'],
            'presenterOptions' => $payload['presenterOptions'],
            'conferenceOptions' => $payload['conferenceOptions'],
            'activityOptions' => $payload['activityOptions'],
            'yearOptions' => $payload['yearOptions'],
            'currentSort' => $sort,
            'currentDir' => $dir,
        ]);
    }

    public function presentationsPdf(Request $request)
    {
        $sort = in_array($request->get('sort'), ['created_at', 'year', 'title', 'conference', 'activity'], true)
            ? $request->get('sort')
            : 'created_at';
        $dir = $request->get('dir') === 'asc' ? 'asc' : 'desc';
        $payload = $this->buildPresentationsPayload($request, $sort, $dir);

        $user = auth()->user();
        $reporter = trim(($user->prefix ?? '') . ' ' . ($user->name ?? ''));
        $filters = collect([
            $request->filled('search') ? 'ค้นหา: ' . trim((string) $request->input('search')) : null,
            $request->filled('activity') ? 'กิจกรรม: ' . trim((string) $request->input('activity')) : null,
            $request->filled('year') ? 'ปี: ' . trim((string) $request->input('year')) : null,
        ])->filter()->values();

        $pdf = PDF::loadView('outputs.presentations-pdf', [
            'presentations' => $payload['items'],
            'summary' => $payload['summary'],
            'reporter' => $reporter !== '' ? $reporter : 'ผู้ใช้ระบบ',
            'generatedAt' => now()->setTimezone('Asia/Bangkok'),
            'filters' => $filters,
        ]);

        return $pdf->stream('presentations-report.pdf');
    }

    public function textbooks(Request $request)
    {
        $sortable = ['created_at', 'year', 'title', 'book_title', 'publisher'];
        $sort = in_array($request->get('sort'), $sortable, true) ? $request->get('sort') : 'created_at';
        $dir = $request->get('dir') === 'asc' ? 'asc' : 'desc';
        $payload = $this->buildTextbooksPayload($request, $sort, $dir);

        $outputs = $this->paginateCollection($payload['items'], $request, 20);

        return view('outputs.textbooks', [
            'outputs' => $outputs,
            'summary' => $payload['summary'],
            'authorOptions' => $payload['authorOptions'],
            'publisherOptions' => $payload['publisherOptions'],
            'yearOptions' => $payload['yearOptions'],
            'currentSort' => $sort,
            'currentDir' => $dir,
        ]);
    }

    public function textbooksPdf(Request $request)
    {
        $sort = in_array($request->get('sort'), ['created_at', 'year', 'title', 'book_title', 'publisher'], true)
            ? $request->get('sort')
            : 'created_at';
        $dir = $request->get('dir') === 'asc' ? 'asc' : 'desc';
        $payload = $this->buildTextbooksPayload($request, $sort, $dir);

        $user = auth()->user();
        $reporter = trim(($user->prefix ?? '') . ' ' . ($user->name ?? ''));
        $filters = collect([
            $request->filled('search') ? 'ค้นหา: ' . trim((string) $request->input('search')) : null,
            $request->filled('author') ? 'Author: ' . trim((string) $request->input('author')) : null,
            $request->filled('year') ? 'ปี: ' . trim((string) $request->input('year')) : null,
        ])->filter()->values();

        $pdf = PDF::loadView('outputs.textbooks-pdf', [
            'textbooks' => $payload['items'],
            'summary' => $payload['summary'],
            'reporter' => $reporter !== '' ? $reporter : 'ผู้ใช้ระบบ',
            'generatedAt' => now()->setTimezone('Asia/Bangkok'),
            'filters' => $filters,
            'sort' => $sort,
            'dir' => $dir,
        ]);

        return $pdf->stream('textbooks-report.pdf');
    }

    private function buildPresentationsPayload(Request $request, string $sort, string $dir): array
    {
        $baseQuery = $this->visibleProjectOutputsQuery($request)
            ->where('type', 'like', 'EA103%')
            ->with(['conferences:id,output_id,name,presentation_type,location,note']);

        $externalQuery = ExternalOutput::query()
            ->where('type', 'EA103')
            ->with(['conferences:id,external_output_id,name,presentation_type,location,presenter_name']);

        $yearFilter = $this->normalizeYearFilter($request->input('year'));
        if ($yearFilter !== null) {
            $baseQuery->where(function (Builder $q) use ($yearFilter) {
                $q->where('year', 'like', '%' . $yearFilter . '%')
                    ->orWhere('year', 'like', '%' . ($yearFilter - 543) . '%');
            });

            $externalQuery->whereYear('publish_date', $yearFilter - 543);
        }

        $search = trim((string) $request->input('search', ''));
        $activity = trim((string) $request->input('activity', ''));
        $baseRows = $baseQuery->get()
            ->flatMap(function (Output $output) {
                $yearBe = $this->normalizeYearFilter($output->year);

                return $output->conferences->map(function ($conference) use ($output, $yearBe) {
                    return (object) [
                        'output_id' => $output->id,
                        'output_code' => $output->output_code,
                        'conference_id' => $conference->id,
                        'title' => $output->title ?: '-',
                        'detail' => $output->project_output ?: null,
                        'year_be' => $yearBe,
                        'project_label' => $output->project?->project_name_th ?: '-',
                        'project_code' => $output->project?->project_code ?: null,
                        'project_link' => $output->project ? route('projects.show', $output->project) . '#output-' . $output->id . '-conference-' . $conference->id : null,
                        'conference_name' => $conference->name ?: '-',
                        'presentation_type' => $conference->presentation_type ?: '-',
                        'location' => $conference->location ?: '-',
                        'presenter_name' => $conference->note ?: '-',
                        'displayed_at' => $output->updated_at ?? $output->created_at,
                        'created_at' => $output->created_at,
                        'sort_year' => $yearBe ?? 0,
                        'sort_title' => mb_strtolower((string) $output->title),
                        'sort_conference' => mb_strtolower((string) $conference->name),
                        'sort_activity' => mb_strtolower((string) $conference->presentation_type),
                    ];
                });
            });

        $externalRows = $externalQuery->get()
            ->flatMap(function (ExternalOutput $output) {
                $yearBe = $output->publish_date ? ((int) $output->publish_date->format('Y')) + 543 : null;

                return $output->conferences->map(function ($conference) use ($output, $yearBe) {
                    return (object) [
                        'output_id' => $output->id,
                        'output_code' => $output->output_code,
                        'conference_id' => $conference->id,
                        'title' => $output->title ?: '-',
                        'detail' => $output->note ?: null,
                        'year_be' => $yearBe,
                        'project_label' => $output->job_category ?: '-',
                        'project_code' => null,
                        'project_link' => route('outputs.external-academic.show', $output),
                        'conference_name' => $conference->name ?: '-',
                        'presentation_type' => $conference->presentation_type ?: '-',
                        'location' => $conference->location ?: '-',
                        'presenter_name' => $conference->presenter_name ?: '-',
                        'displayed_at' => $output->updated_at ?? $output->created_at,
                        'created_at' => $output->created_at,
                        'sort_year' => $yearBe ?? 0,
                        'sort_title' => mb_strtolower((string) $output->title),
                        'sort_conference' => mb_strtolower((string) $conference->name),
                        'sort_activity' => mb_strtolower((string) $conference->presentation_type),
                    ];
                });
            });

        $allRows = $baseRows->merge($externalRows);

        $rows = $allRows
            ->filter(function ($row) use ($search, $activity) {
                if ($search !== '') {
                    $haystack = mb_strtolower(implode(' ', array_filter([
                        $row->output_code,
                        $row->title,
                        $row->project_label,
                        $row->project_code,
                        $row->conference_name,
                        $row->presentation_type,
                        $row->location,
                        $row->presenter_name,
                        $row->detail,
                    ])), 'UTF-8');

                    if (!str_contains($haystack, mb_strtolower($search, 'UTF-8'))) {
                        return false;
                    }
                }

                if ($activity !== '' && $row->presentation_type !== $activity) {
                    return false;
                }

                return true;
            })
            ->values();

        $rows = $this->sortCollection($rows, $sort, $dir, [
            'year' => fn ($item) => $item->sort_year,
            'title' => fn ($item) => $item->sort_title,
            'conference' => fn ($item) => $item->sort_conference,
            'activity' => fn ($item) => $item->sort_activity,
            'created_at' => fn ($item) => $item->created_at?->timestamp ?? 0,
        ]);

        $defaultActivityOptions = collect([
            'Oral Presentation',
            'Poster Presentation',
            'Abstract Presentation',
            'Panel Discussion',
            'Keynote Speech',
            'World café',
        ]);

        return [
            'items' => $rows,
            'summary' => [
            'total_presentations' => $rows->count(),
            'unique_presenters' => $rows->pluck('presenter_name')->filter(fn ($name) => $name !== '-' && trim((string) $name) !== '')->unique()->count(),
            'top_presenter' => $this->topLabel(
                $rows->pluck('presenter_name')->filter(fn ($name) => $name !== '-' && trim((string) $name) !== '')->countBy()
            ),
            'top_conference' => $this->topLabel(
                $rows->pluck('conference_name')->filter(fn ($name) => $name !== '-' && trim((string) $name) !== '')->countBy()
            ),
            ],
            'presenterOptions' => $allRows->pluck('presenter_name')->filter(fn ($name) => $name !== '-' && trim((string) $name) !== '')->unique()->sort()->values(),
            'conferenceOptions' => $allRows->pluck('conference_name')->filter(fn ($name) => $name !== '-' && trim((string) $name) !== '')->unique()->sort()->values(),
            'activityOptions' => $defaultActivityOptions
                ->merge($allRows->pluck('presentation_type'))
                ->filter(fn ($name) => $name !== '-' && trim((string) $name) !== '')
                ->unique()
                ->values(),
            'yearOptions' => $rows->pluck('year_be')->filter()->unique()->sortDesc()->values(),
        ];
    }

    private function visibleProjectOutputsQuery(Request $request): Builder
    {
        $query = Output::query()->with('project:id,project_name_th,project_code,user_id');
        $user = auth()->user();
        $fullName = trim(($user->prefix ?? '') . ' ' . $user->name);
        $canViewAll = $user->role === 'admin' || $user->role === 'user_all';

        if (!$canViewAll) {
            $query->whereHas('project', function (Builder $q) use ($user, $fullName) {
                $q->where('user_id', $user->id)
                    ->orWhereHas('fteRoles', function (Builder $subQ) use ($fullName) {
                        $subQ->where('name', $fullName);
                    });
            });
        }

        return $query;
    }

    private function visibleBackOfficeOutputsQuery(): Builder
    {
        $query = BackOfficeTaskOutput::query()->with('task:id,title,created_by');
        $user = auth()->user();
        $fullName = trim(($user->prefix ?? '') . ' ' . $user->name);
        $canViewAll = $user->role === 'admin' || $user->role === 'user_all';

        if (!$canViewAll) {
            $query->whereHas('task', function (Builder $q) use ($user, $fullName) {
                $q->where('created_by', $user->id)
                    ->orWhereHas('entries', function (Builder $subQ) use ($fullName) {
                        $subQ->where('person_name', $fullName);
                    });
            });
        }

        return $query;
    }

    private function normalizeYearFilter($raw): ?int
    {
        $raw = trim((string) $raw);
        if ($raw === '') {
            return null;
        }

        if (preg_match('/\d{4}/', $raw, $matches)) {
            $year = (int) $matches[0];
            return $year < 2400 ? $year + 543 : $year;
        }

        return null;
    }

    private function sortCollection(Collection $items, string $sort, string $dir, array $map): Collection
    {
        $sorted = $items->sortBy($map[$sort] ?? $map['created_at']);
        return $dir === 'desc' ? $sorted->reverse()->values() : $sorted->values();
    }

    private function paginateCollection(Collection $items, Request $request, int $perPage): LengthAwarePaginator
    {
        $page = max((int) $request->get('page', 1), 1);
        $paged = $items->slice(($page - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator($paged, $items->count(), $perPage, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);
    }

    private function topLabel(Collection $counts): ?string
    {
        if ($counts->isEmpty()) {
            return null;
        }

        $label = $counts->sortDesc()->keys()->first();
        $count = $counts->sortDesc()->first();

        return $label ? sprintf('%s (%d)', $label, $count) : null;
    }

    private function buildPapersPayload(Request $request, string $sort, string $dir): array
    {
        $baseQuery = $this->visibleProjectOutputsQuery($request)
            ->where('type', 'like', 'EA101%')
            ->with(['authors:id,output_id,role,name']);

        $externalQuery = ExternalOutput::query()
            ->where('type', 'EA101')
            ->with(['authors:id,external_output_id,role,name']);

        $yearFilter = $this->normalizeYearFilter($request->input('year'));
        $search = trim((string) $request->input('search', ''));
        $role = trim((string) $request->input('role', ''));
        $quartile = trim((string) $request->input('quartile', ''));
        $impactMax = $request->filled('impact_max') ? (float) $request->input('impact_max') : null;

        if ($search !== '') {
            $baseQuery->where(function (Builder $q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('output_code', 'like', '%' . $search . '%')
                    ->orWhere('type', 'like', '%' . $search . '%')
                    ->orWhere('journal', 'like', '%' . $search . '%')
                    ->orWhere('quartile', 'like', '%' . $search . '%')
                    ->orWhere('project_output', 'like', '%' . $search . '%')
                    ->orWhere('year', 'like', '%' . $search . '%')
                    ->orWhereHas('project', function (Builder $projectQ) use ($search) {
                        $projectQ->where('project_name_th', 'like', '%' . $search . '%')
                            ->orWhere('project_code', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('authors', function (Builder $authorQ) use ($search) {
                        $authorQ->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        if ($role !== '') {
            $baseQuery->whereHas('authors', function (Builder $authorQ) use ($role) {
                $authorQ->where('role', $role);
            });
        }

        if ($quartile !== '') {
            $baseQuery->where('quartile', $quartile);
            $externalQuery->where('quartile', $quartile);
        }

        if ($impactMax !== null) {
            $baseQuery->whereNotNull('impact_factor');
            $baseQuery->where('impact_factor', '<=', $impactMax);
            $externalQuery->whereNotNull('impact_factor');
            $externalQuery->where('impact_factor', '<=', $impactMax);
        }

        if ($yearFilter !== null) {
            $baseQuery->where(function (Builder $q) use ($yearFilter) {
                $q->where('year', 'like', '%' . $yearFilter . '%')
                    ->orWhere('year', 'like', '%' . ($yearFilter - 543) . '%');
            });
            $externalQuery->whereYear('publish_date', $yearFilter - 543);
        }

        $projectPapers = $baseQuery->get()->map(function (Output $output) {
            $yearBe = $this->normalizeYearFilter($output->year);
            $authorsByRole = $output->authors
                ->groupBy(fn ($author) => strtolower((string) $author->role))
                ->map(fn (Collection $group) => $group->pluck('name')->filter()->values());

            return (object) [
                'id' => $output->id,
                'output_code' => $output->output_code,
                'title' => $output->title ?: '-',
                'type' => $output->type ?: '-',
                'year_be' => $yearBe,
                'journal' => $output->journal ?: '-',
                'quartile' => $output->quartile ?: '-',
                'impact_factor' => $output->impact_factor,
                'detail' => $output->project_output ?: null,
                'project_label' => $output->project?->project_name_th ?: '-',
                'project_code' => $output->project?->project_code ?: null,
                'project_link' => $output->project ? route('projects.show', $output->project) . '#output-' . $output->id : null,
                'authors' => $output->authors->pluck('name')->filter()->values(),
                'authors_by_role' => $authorsByRole,
                'displayed_at' => $output->updated_at ?? $output->created_at,
                'created_at' => $output->created_at,
                'sort_title' => mb_strtolower((string) $output->title),
                'sort_journal' => mb_strtolower((string) $output->journal),
                'sort_quartile' => mb_strtolower((string) $output->quartile),
                'sort_year' => $yearBe ?? 0,
            ];
        });

        $externalPapers = $externalQuery->get()
            ->filter(function (ExternalOutput $output) use ($search, $role) {
                if ($search !== '') {
                    $haystack = mb_strtolower(implode(' ', array_filter([
                        $output->output_code,
                        $output->title,
                        $output->journal,
                        $output->quartile,
                        $output->note,
                        $output->job_category,
                        $output->authors->pluck('name')->implode(' '),
                    ])), 'UTF-8');

                    if (!str_contains($haystack, mb_strtolower($search, 'UTF-8'))) {
                        return false;
                    }
                }

                if ($role !== '') {
                    return $output->authors->contains(fn ($author) => strtolower((string) $author->role) === $role);
                }

                return true;
            })
            ->map(function (ExternalOutput $output) {
                $yearBe = $output->publish_date ? ((int) $output->publish_date->format('Y')) + 543 : null;
                $authorsByRole = $output->authors
                    ->groupBy(fn ($author) => strtolower((string) $author->role))
                    ->map(fn (Collection $group) => $group->pluck('name')->filter()->values());

                return (object) [
                    'id' => $output->id,
                    'output_code' => $output->output_code,
                    'title' => $output->title ?: '-',
                    'type' => $output->type ?: '-',
                    'year_be' => $yearBe,
                    'journal' => $output->journal ?: '-',
                    'quartile' => $output->quartile ?: '-',
                    'impact_factor' => $output->impact_factor,
                    'detail' => $output->note ?: null,
                    'project_label' => $output->job_category ?: '-',
                    'project_code' => null,
                    'project_link' => route('outputs.external-academic.show', $output),
                    'authors' => $output->authors->pluck('name')->filter()->values(),
                    'authors_by_role' => $authorsByRole,
                    'displayed_at' => $output->updated_at ?? $output->created_at,
                    'created_at' => $output->created_at,
                    'sort_title' => mb_strtolower((string) $output->title),
                    'sort_journal' => mb_strtolower((string) $output->journal),
                    'sort_quartile' => mb_strtolower((string) $output->quartile),
                    'sort_year' => $yearBe ?? 0,
                ];
            });

        $papers = $projectPapers->merge($externalPapers);

        $papers = $this->sortCollection($papers, $sort, $dir, [
            'year' => fn ($item) => $item->sort_year,
            'title' => fn ($item) => $item->sort_title,
            'journal' => fn ($item) => $item->sort_journal,
            'quartile' => fn ($item) => $item->sort_quartile,
            'created_at' => fn ($item) => $item->created_at?->timestamp ?? 0,
        ]);

        $impactValues = $papers->pluck('impact_factor')
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->map(fn ($value) => (float) $value)
            ->sort()
            ->values();
        $impactFactorMax = $impactValues->isNotEmpty() ? ceil($impactValues->last()) : 10;
        $selectedImpactMax = $impactMax ?? $impactFactorMax;

        return [
            'items' => $papers,
            'summary' => [
                'total_papers' => $papers->count(),
                'unique_authors' => $papers->flatMap(fn ($paper) => $paper->authors)->filter()->unique()->count(),
                'top_author' => $this->topLabel(
                    $papers->flatMap(fn ($paper) => collect($paper->authors)->unique())->countBy()
                ),
                'top_journal' => $this->topLabel(
                    $papers->pluck('journal')->filter(fn ($j) => $j !== '-' && trim((string) $j) !== '')->countBy()
                ),
            ],
            'authorOptions' => $papers->flatMap(fn ($paper) => $paper->authors)->filter()->unique()->sort()->values(),
            'journalOptions' => $papers->pluck('journal')->filter(fn ($j) => $j !== '-' && trim((string) $j) !== '')->unique()->sort()->values(),
            'yearOptions' => $papers->pluck('year_be')->filter()->unique()->sortDesc()->values(),
            'impactFactorMax' => $impactFactorMax,
            'selectedImpactMax' => $selectedImpactMax,
        ];
    }

    private function buildTextbooksPayload(Request $request, string $sort, string $dir): array
    {
        $baseQuery = $this->visibleProjectOutputsQuery($request)
            ->where('type', 'like', 'EA119%')
            ->with(['bookAuthors:id,output_id,name']);

        $backOfficeQuery = $this->visibleBackOfficeOutputsQuery()
            ->where('type', 'like', 'EA119%');

        $externalQuery = ExternalOutput::query()
            ->where('type', 'EA119')
            ->with(['authors:id,external_output_id,name']);

        $yearFilter = $this->normalizeYearFilter($request->input('year'));
        if ($yearFilter !== null) {
            $baseQuery->where(function (Builder $q) use ($yearFilter) {
                $q->where('year', 'like', '%' . $yearFilter . '%')
                    ->orWhere('year', 'like', '%' . ($yearFilter - 543) . '%');
            });

            $backOfficeQuery->where(function (Builder $q) use ($yearFilter) {
                $q->where('year', 'like', '%' . $yearFilter . '%')
                    ->orWhere('year', 'like', '%' . ($yearFilter - 543) . '%');
            });

            $externalQuery->whereYear('publish_date', $yearFilter - 543);
        }

        $search = trim((string) $request->input('search', ''));
        $authorFilter = trim((string) $request->input('author', ''));

        $projectRows = $baseQuery->get()
            ->filter(function (Output $output) use ($search, $authorFilter) {
                $authors = $output->bookAuthors->pluck('name')->filter();

                if ($authorFilter !== '' && !$authors->contains($authorFilter)) {
                    return false;
                }

                if ($search !== '') {
                    $haystack = mb_strtolower(implode(' ', array_filter([
                        $output->output_code,
                        $output->title,
                        $output->book_title,
                        $output->publisher_name,
                        $output->chapter_pages,
                        $output->project_output,
                        $output->project?->project_name_th,
                        $output->project?->project_code,
                        $authors->implode(' '),
                    ])), 'UTF-8');

                    if (!str_contains($haystack, mb_strtolower($search, 'UTF-8'))) {
                        return false;
                    }
                }

                return true;
            })
            ->map(function (Output $output) {
                $yearBe = $this->normalizeYearFilter($output->year);
                $authors = $output->bookAuthors->pluck('name')->filter()->values();

                return (object) [
                    'id' => $output->id,
                    'output_code' => $output->output_code,
                    'title' => $output->title ?: '-',
                    'book_title' => $output->book_title ?: '-',
                    'publisher_name' => $output->publisher_name ?: '-',
                    'chapter_pages' => $output->chapter_pages ?: '-',
                    'year_be' => $yearBe,
                    'detail' => $output->project_output ?: null,
                    'project_label' => $output->project?->project_name_th ?: '-',
                    'project_code' => $output->project?->project_code ?: null,
                    'project_link' => $output->project ? route('projects.show', $output->project) . '#output-' . $output->id : null,
                    'authors' => $authors,
                    'displayed_at' => $output->updated_at ?? $output->created_at,
                    'created_at' => $output->created_at,
                    'sort_year' => $yearBe ?? 0,
                    'sort_title' => mb_strtolower((string) $output->title),
                    'sort_book_title' => mb_strtolower((string) $output->book_title),
                    'sort_publisher' => mb_strtolower((string) $output->publisher_name),
                ];
            });

        $backOfficeRows = $backOfficeQuery->get()
            ->filter(function (BackOfficeTaskOutput $output) use ($search, $authorFilter) {
                $authors = collect($output->book_authors ?? [])
                    ->map(function ($author) {
                        if (is_array($author)) {
                            return trim((string) ($author['name'] ?? ''));
                        }
                        return trim((string) $author);
                    })
                    ->filter()
                    ->values();

                if ($authorFilter !== '' && !$authors->contains($authorFilter)) {
                    return false;
                }

                if ($search !== '') {
                    $haystack = mb_strtolower(implode(' ', array_filter([
                        $output->title,
                        $output->book_title,
                        $output->publisher_name,
                        $output->chapter_pages,
                        $output->project_output,
                        $output->task?->title,
                        $authors->implode(' '),
                    ])), 'UTF-8');

                    if (!str_contains($haystack, mb_strtolower($search, 'UTF-8'))) {
                        return false;
                    }
                }

                return true;
            })
            ->map(function (BackOfficeTaskOutput $output) {
                $yearBe = $this->normalizeYearFilter($output->year);
                $authors = collect($output->book_authors ?? [])
                    ->map(function ($author) {
                        if (is_array($author)) {
                            return trim((string) ($author['name'] ?? ''));
                        }
                        return trim((string) $author);
                    })
                    ->filter()
                    ->values();

                return (object) [
                    'id' => $output->id,
                    'output_code' => null,
                    'title' => $output->title ?: '-',
                    'book_title' => $output->book_title ?: '-',
                    'publisher_name' => $output->publisher_name ?: '-',
                    'chapter_pages' => $output->chapter_pages ?: '-',
                    'year_be' => $yearBe,
                    'detail' => $output->project_output ?: null,
                    'project_label' => $output->task?->title ?: 'งานนอกโครงการวิจัย',
                    'project_code' => null,
                    'project_link' => $output->task ? route('back_office.show', $output->task->id) : null,
                    'authors' => $authors,
                    'displayed_at' => $output->updated_at ?? $output->created_at,
                    'created_at' => $output->created_at,
                    'sort_year' => $yearBe ?? 0,
                    'sort_title' => mb_strtolower((string) $output->title),
                    'sort_book_title' => mb_strtolower((string) $output->book_title),
                    'sort_publisher' => mb_strtolower((string) $output->publisher_name),
                ];
            });

        $externalRows = $externalQuery->get()
            ->filter(function (ExternalOutput $output) use ($search, $authorFilter) {
                $authors = $output->authors->pluck('name')->filter();

                if ($authorFilter !== '' && !$authors->contains($authorFilter)) {
                    return false;
                }

                if ($search !== '') {
                    $haystack = mb_strtolower(implode(' ', array_filter([
                        $output->output_code,
                        $output->title,
                        $output->book_title,
                        $output->publisher_name,
                        $output->chapter_pages,
                        $output->note,
                        $output->job_category,
                        $authors->implode(' '),
                    ])), 'UTF-8');

                    if (!str_contains($haystack, mb_strtolower($search, 'UTF-8'))) {
                        return false;
                    }
                }

                return true;
            })
            ->map(function (ExternalOutput $output) {
                $yearBe = $output->publish_date ? ((int) $output->publish_date->format('Y')) + 543 : null;
                $authors = $output->authors->pluck('name')->filter()->values();

                return (object) [
                    'id' => $output->id,
                    'output_code' => $output->output_code,
                    'title' => $output->title ?: '-',
                    'book_title' => $output->book_title ?: '-',
                    'publisher_name' => $output->publisher_name ?: '-',
                    'chapter_pages' => $output->chapter_pages ?: '-',
                    'year_be' => $yearBe,
                    'detail' => $output->note ?: null,
                    'project_label' => $output->job_category ?: '-',
                    'project_code' => null,
                    'project_link' => route('outputs.external-academic.show', $output),
                    'authors' => $authors,
                    'displayed_at' => $output->updated_at ?? $output->created_at,
                    'created_at' => $output->created_at,
                    'sort_year' => $yearBe ?? 0,
                    'sort_title' => mb_strtolower((string) $output->title),
                    'sort_book_title' => mb_strtolower((string) $output->book_title),
                    'sort_publisher' => mb_strtolower((string) $output->publisher_name),
                ];
            });

        $rows = $projectRows->merge($backOfficeRows)->merge($externalRows);

        $rows = $this->sortCollection($rows, $sort, $dir, [
            'year' => fn ($item) => $item->sort_year,
            'title' => fn ($item) => $item->sort_title,
            'book_title' => fn ($item) => $item->sort_book_title,
            'publisher' => fn ($item) => $item->sort_publisher,
            'created_at' => fn ($item) => $item->created_at?->timestamp ?? 0,
        ]);

        return [
            'items' => $rows,
            'summary' => [
                'total_textbooks' => $rows->count(),
                'unique_authors' => $rows->flatMap(fn ($row) => $row->authors)->filter()->unique()->count(),
                'top_author' => $this->topLabel(
                    $rows->flatMap(fn ($row) => collect($row->authors)->unique())->countBy()
                ),
                'top_publisher' => $this->topLabel(
                    $rows->pluck('publisher_name')->filter(fn ($name) => $name !== '-' && trim((string) $name) !== '')->countBy()
                ),
            ],
            'authorOptions' => $rows->flatMap(fn ($row) => $row->authors)->filter()->unique()->sort()->values(),
            'publisherOptions' => $rows->pluck('publisher_name')->filter(fn ($name) => $name !== '-' && trim((string) $name) !== '')->unique()->sort()->values(),
            'yearOptions' => $rows->pluck('year_be')->filter()->unique()->sortDesc()->values(),
        ];
    }
}
