<?php

namespace App\Http\Controllers;

use App\Models\BackOfficeTask;
use App\Models\BackOfficeTaskOutput;
use App\Models\CoResearch;
use App\Models\ExternalOutput;
use App\Models\ExternalOutputAwardRecipient;
use App\Models\ExternalOutputBookAuthor;
use App\Models\ExternalOutputConference;
use App\Models\ExternalOutputConferencePresenter;
use App\Models\FteRole;
use App\Models\Output;
use App\Models\OutputAwardRecipient;
use App\Models\OutputAuthor;
use App\Models\OutputBookAuthor;
use App\Models\OutputConference;
use App\Models\OutputConferencePresenter;
use App\Models\Project;
use App\Models\User;
use App\Support\AuditLogger;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PDF;

class OutputController extends Controller
{
    private const EXTERNAL_JOB_CATEGORIES = [
        '01 Aging and Frailty',
        '02 Health Systems and HTA',
        '03 Rational Drug Use (RDU)',
        '04 Real-World Evidence (RWE) and Clinical–Technical Cohorts',
        '05 Global Health',
        '06 Other Areas',
    ];

    public function externalAcademic(Request $request)
    {
        $query = ExternalOutput::query()
            ->with(['authors', 'awardRecipients', 'bookAuthors', 'conferences.presenters', 'creator', 'updater'])
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
                    ->orWhereHas('awardRecipients', function ($recipientQ) use ($search) {
                        $recipientQ->where('name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('bookAuthors', function ($bookAuthorQ) use ($search) {
                        $bookAuthorQ->where('name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('conferences', function ($conferenceQ) use ($search) {
                        $conferenceQ->where('name', 'like', '%' . $search . '%')
                            ->orWhere('presentation_type', 'like', '%' . $search . '%')
                            ->orWhere('location', 'like', '%' . $search . '%')
                            ->orWhere('presenter_name', 'like', '%' . $search . '%')
                            ->orWhereHas('presenters', function ($presenterQ) use ($search) {
                                $presenterQ->where('name', 'like', '%' . $search . '%');
                            });
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
        $awardCount = (clone $statsQuery)->where('type', 'EA118')->count();
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

        $projectOptions = Project::query()
            ->select(['id', 'project_code', 'project_name_th'])
            ->orderByDesc('id')
            ->get();

        return view('outputs.external-academic-index', [
            'outputs' => $outputs,
            'allCount' => $allCount,
            'paperCount' => $paperCount,
            'presentationCount' => $presentationCount,
            'awardCount' => $awardCount,
            'textbookCount' => $textbookCount,
            'manualCount' => $manualCount,
            'search' => $request->string('search')->toString(),
            'type' => $request->string('type')->toString(),
            'jobCategoryOptions' => self::EXTERNAL_JOB_CATEGORIES,
            'projectOptions' => $projectOptions,
        ]);
    }

    public function externalAcademicCreate()
    {
        $userDirectory = $this->buildOutputUserDirectory();

        $authorOptions = OutputAuthor::query()
            ->whereNotNull('name')
            ->whereRaw("TRIM(name) <> ''")
            ->selectRaw('TRIM(name) as name')
            ->distinct()
            ->orderBy('name')
            ->pluck('name')
            ->values();

        $bookAuthorOptions = User::query()
            ->selectRaw("TRIM(CONCAT(COALESCE(prefix,''), ' ', COALESCE(name,''))) as name")
            ->whereRaw("TRIM(CONCAT(COALESCE(prefix,''), ' ', COALESCE(name,''))) <> ''")
            ->orderBy('name')
            ->pluck('name')
            ->unique()
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
            'bookAuthorOptions' => $bookAuthorOptions,
            'presenterOptions' => $presenterOptions,
            'userDirectory' => $userDirectory,
            'jobCategoryOptions' => self::EXTERNAL_JOB_CATEGORIES,
        ]);
    }

    public function externalAcademicShow(ExternalOutput $externalOutput)
    {
        $externalOutput->load(['authors', 'awardRecipients', 'bookAuthors', 'conferences.presenters', 'creator', 'updater']);

        return view('outputs.external-academic-show', [
            'externalOutput' => $externalOutput,
        ]);
    }

    public function externalAcademicEdit(ExternalOutput $externalOutput)
    {
        $userDirectory = $this->buildOutputUserDirectory();

        $authorOptions = OutputAuthor::query()
            ->whereNotNull('name')
            ->whereRaw("TRIM(name) <> ''")
            ->selectRaw('TRIM(name) as name')
            ->distinct()
            ->orderBy('name')
            ->pluck('name')
            ->values();

        $bookAuthorOptions = User::query()
            ->selectRaw("TRIM(CONCAT(COALESCE(prefix,''), ' ', COALESCE(name,''))) as name")
            ->whereRaw("TRIM(CONCAT(COALESCE(prefix,''), ' ', COALESCE(name,''))) <> ''")
            ->orderBy('name')
            ->pluck('name')
            ->unique()
            ->values();

        $presenterOptions = User::query()
            ->selectRaw("TRIM(CONCAT(COALESCE(prefix,''), ' ', COALESCE(name,''))) as name")
            ->whereRaw("TRIM(CONCAT(COALESCE(prefix,''), ' ', COALESCE(name,''))) <> ''")
            ->orderBy('name')
            ->pluck('name')
            ->unique()
            ->values();

        $externalOutput->load(['authors', 'awardRecipients', 'bookAuthors', 'conferences.presenters']);

        return view('outputs.external-academic-edit', [
            'externalOutput' => $externalOutput,
            'authorOptions' => $authorOptions,
            'bookAuthorOptions' => $bookAuthorOptions,
            'presenterOptions' => $presenterOptions,
            'userDirectory' => $userDirectory,
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
            'outputs.*.type' => ['required', 'in:EA101,EA103,EA118,EA119,EA120'],
            'outputs.*.job_category' => ['required', 'in:' . implode(',', self::EXTERNAL_JOB_CATEGORIES)],
            'outputs.*.note' => ['nullable', 'string', 'max:5000'],
            'outputs.*.publish_date' => ['required', 'date'],
            'outputs.*.journal' => ['required_if:outputs.*.type,EA101', 'nullable', 'string', 'max:255'],
            'outputs.*.quartile' => ['nullable', 'in:Q1,Q2,Q3,Q4'],
            'outputs.*.impact_factor' => ['nullable', 'numeric', 'min:0', 'max:99999.999'],
            'outputs.*.book_title' => ['required_if:outputs.*.type,EA119', 'nullable', 'string', 'max:1000'],
            'outputs.*.publisher_name' => ['required_if:outputs.*.type,EA119', 'nullable', 'string', 'max:1000'],
            'outputs.*.chapter_pages' => ['required_if:outputs.*.type,EA119', 'nullable', 'string', 'max:1000'],
            'outputs.*.isbn' => ['nullable', 'string', 'max:1000'],
            'outputs.*.authors' => ['nullable', 'array'],
            'outputs.*.authors.*.person_type' => ['nullable', 'in:unit,internal,external'],
            'outputs.*.authors.*.user_id' => ['nullable', 'integer', 'exists:users,id'],
            'outputs.*.authors.*.role' => ['nullable', 'in:first,co,corresponding,awardee'],
            'outputs.*.authors.*.name' => ['nullable', 'string', 'max:255'],
            'outputs.*.conference_id' => ['nullable', 'integer', 'exists:external_output_conferences,id'],
            'outputs.*.conference_name' => ['required_if:outputs.*.type,EA103', 'nullable', 'string', 'max:255'],
            'outputs.*.conference_presentation_type' => ['nullable', 'in:Oral Presentation,Poster Presentation,Abstract Presentation,Panel Discussion,Keynote Speech,World café'],
            'outputs.*.conference_location' => ['nullable', 'string', 'max:255'],
            'outputs.*.conference_presenters' => ['nullable', 'array'],
            'outputs.*.conference_presenters.*.id' => ['nullable', 'integer', 'exists:external_output_conference_presenters,id'],
            'outputs.*.conference_presenters.*.person_type' => ['nullable', 'in:internal,external'],
            'outputs.*.conference_presenters.*.user_id' => ['nullable', 'integer', 'exists:users,id'],
            'outputs.*.conference_presenters.*.note' => ['nullable', 'string', 'max:255'],
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
                    'isbn' => $type === 'EA119' ? $this->nullableTrim($item['isbn'] ?? null) : null,
                    'created_by' => auth()->id(),
                    'updated_by' => null,
                ]);

                if ($type === 'EA101') {
                    $authors = collect($item['authors'] ?? [])
                        ->map(function ($author) use ($type) {
                            $personType = trim((string) ($author['person_type'] ?? ''));
                            $userId = (($author['user_id'] ?? null) !== null && (int) $author['user_id'] > 0)
                                ? (int) $author['user_id']
                                : null;
                            $linkedUser = $personType === 'internal' && $userId ? User::find($userId) : null;
                            $name = $this->nullableTrim($author['name'] ?? null);
                            if ($personType === 'unit') {
                                $userId = null;
                                $name = 'หน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ';
                            } elseif ($linkedUser) {
                                $name = $type === 'EA101'
                                    ? $this->nullableTrim($linkedUser->research_display_name)
                                    : $this->nullableTrim($linkedUser->full_name);
                            } elseif ($personType !== 'internal') {
                                $userId = null;
                            }

                            return [
                                'user_id' => $userId,
                                'role' => $this->nullableTrim($author['role'] ?? null),
                                'name' => $name,
                            ];
                        })
                        ->filter(fn ($author) => $author['name'])
                        ->values()
                        ->all();

                    if (!empty($authors)) {
                        $output->authors()->createMany($authors);
                    }
                }

                if ($type === 'EA118') {
                    $awardRecipients = collect($item['authors'] ?? [])
                        ->map(function ($author) {
                            $personType = trim((string) ($author['person_type'] ?? ''));
                            $userId = (($author['user_id'] ?? null) !== null && (int) $author['user_id'] > 0)
                                ? (int) $author['user_id']
                                : null;
                            $linkedUser = $personType === 'internal' && $userId ? User::find($userId) : null;
                            $name = $this->nullableTrim($author['name'] ?? null);
                            if ($personType === 'unit') {
                                $userId = null;
                                $name = 'หน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ';
                            } elseif ($linkedUser) {
                                $name = $this->nullableTrim($linkedUser->full_name);
                            } elseif ($personType !== 'internal') {
                                $userId = null;
                            }

                            return [
                                'user_id' => $userId,
                                'name' => $name,
                            ];
                        })
                        ->filter(fn ($recipient) => $recipient['name'])
                        ->values()
                        ->all();

                    if (! empty($awardRecipients)) {
                        $output->awardRecipients()->createMany($awardRecipients);
                    }
                }

                if ($type === 'EA119') {
                    $bookAuthors = collect($item['authors'] ?? [])
                        ->map(function ($author) {
                            $personType = trim((string) ($author['person_type'] ?? ''));
                            $userId = (($author['user_id'] ?? null) !== null && (int) $author['user_id'] > 0)
                                ? (int) $author['user_id']
                                : null;
                            $linkedUser = $personType === 'internal' && $userId ? User::find($userId) : null;
                            $name = $this->nullableTrim($author['name'] ?? null);
                            if ($linkedUser) {
                                $name = $this->nullableTrim($linkedUser->full_name);
                            } elseif ($personType !== 'internal') {
                                $userId = null;
                            }

                            return [
                                'user_id' => $userId,
                                'name' => $name,
                            ];
                        })
                        ->filter(fn ($author) => $author['name'])
                        ->values()
                        ->all();

                    if (! empty($bookAuthors)) {
                        $output->bookAuthors()->createMany($bookAuthors);
                    }
                }

                $this->syncExternalOutputConferences($output, $item, $type === 'EA103');
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
            'type' => ['required', 'in:EA101,EA103,EA118,EA119,EA120'],
            'job_category' => ['required', 'in:' . implode(',', self::EXTERNAL_JOB_CATEGORIES)],
            'note' => ['nullable', 'string', 'max:5000'],
            'publish_date' => ['required', 'date'],
            'journal' => ['required_if:type,EA101', 'nullable', 'string', 'max:255'],
            'quartile' => ['nullable', 'in:Q1,Q2,Q3,Q4'],
            'impact_factor' => ['nullable', 'numeric', 'min:0', 'max:99999.999'],
            'book_title' => ['required_if:type,EA119', 'nullable', 'string', 'max:1000'],
            'publisher_name' => ['required_if:type,EA119', 'nullable', 'string', 'max:1000'],
            'chapter_pages' => ['required_if:type,EA119', 'nullable', 'string', 'max:1000'],
            'isbn' => ['nullable', 'string', 'max:1000'],
            'authors' => ['nullable', 'array'],
            'authors.*.person_type' => ['nullable', 'in:unit,internal,external'],
            'authors.*.user_id' => ['nullable', 'integer', 'exists:users,id'],
            'authors.*.role' => ['nullable', 'in:first,co,corresponding,awardee'],
            'authors.*.name' => ['nullable', 'string', 'max:255'],
            'conference_id' => ['nullable', 'integer', 'exists:external_output_conferences,id'],
            'conference_name' => ['required_if:type,EA103', 'nullable', 'string', 'max:255'],
            'conference_presentation_type' => ['nullable', 'in:Oral Presentation,Poster Presentation,Abstract Presentation,Panel Discussion,Keynote Speech,World café'],
            'conference_location' => ['nullable', 'string', 'max:255'],
            'conference_presenters' => ['nullable', 'array'],
            'conference_presenters.*.id' => ['nullable', 'integer', 'exists:external_output_conference_presenters,id'],
            'conference_presenters.*.person_type' => ['nullable', 'in:internal,external'],
            'conference_presenters.*.user_id' => ['nullable', 'integer', 'exists:users,id'],
            'conference_presenters.*.note' => ['nullable', 'string', 'max:255'],
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
                'isbn' => $type === 'EA119' ? $this->nullableTrim($validated['isbn'] ?? null) : null,
                'updated_by' => auth()->id(),
            ]);

            $externalOutput->authors()->delete();
            $externalOutput->awardRecipients()->delete();
            $externalOutput->bookAuthors()->delete();

            if ($type === 'EA101') {
                $authors = collect($validated['authors'] ?? [])
                    ->map(function ($author) use ($type) {
                        $personType = trim((string) ($author['person_type'] ?? ''));
                        $userId = (($author['user_id'] ?? null) !== null && (int) $author['user_id'] > 0)
                            ? (int) $author['user_id']
                            : null;
                        $linkedUser = $personType === 'internal' && $userId ? User::find($userId) : null;
                        $name = $this->nullableTrim($author['name'] ?? null);
                        if ($personType === 'unit') {
                            $userId = null;
                            $name = 'หน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ';
                        } elseif ($linkedUser) {
                            $name = $type === 'EA101'
                                ? $this->nullableTrim($linkedUser->research_display_name)
                                : $this->nullableTrim($linkedUser->full_name);
                        } elseif ($personType !== 'internal') {
                            $userId = null;
                        }

                        return [
                            'user_id' => $userId,
                            'role' => $this->nullableTrim($author['role'] ?? null),
                            'name' => $name,
                        ];
                    })
                    ->filter(fn ($author) => $author['name'])
                    ->values()
                    ->all();

                if (!empty($authors)) {
                    $externalOutput->authors()->createMany($authors);
                }
            }

            if ($type === 'EA118') {
                $awardRecipients = collect($validated['authors'] ?? [])
                    ->map(function ($author) {
                        $personType = trim((string) ($author['person_type'] ?? ''));
                        $userId = (($author['user_id'] ?? null) !== null && (int) $author['user_id'] > 0)
                            ? (int) $author['user_id']
                            : null;
                        $linkedUser = $personType === 'internal' && $userId ? User::find($userId) : null;
                        $name = $this->nullableTrim($author['name'] ?? null);
                        if ($personType === 'unit') {
                            $userId = null;
                            $name = 'หน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ';
                        } elseif ($linkedUser) {
                            $name = $this->nullableTrim($linkedUser->full_name);
                        } elseif ($personType !== 'internal') {
                            $userId = null;
                        }

                        return [
                            'user_id' => $userId,
                            'name' => $name,
                        ];
                    })
                    ->filter(fn ($recipient) => $recipient['name'])
                    ->values()
                    ->all();

                if (! empty($awardRecipients)) {
                    $externalOutput->awardRecipients()->createMany($awardRecipients);
                }
            }

            if ($type === 'EA119') {
                $bookAuthors = collect($validated['authors'] ?? [])
                    ->map(function ($author) {
                        $personType = trim((string) ($author['person_type'] ?? ''));
                        $userId = (($author['user_id'] ?? null) !== null && (int) $author['user_id'] > 0)
                            ? (int) $author['user_id']
                            : null;
                        $linkedUser = $personType === 'internal' && $userId ? User::find($userId) : null;
                        $name = $this->nullableTrim($author['name'] ?? null);
                        if ($linkedUser) {
                            $name = $this->nullableTrim($linkedUser->full_name);
                        } elseif ($personType !== 'internal') {
                            $userId = null;
                        }

                        return [
                            'user_id' => $userId,
                            'name' => $name,
                        ];
                    })
                    ->filter(fn ($author) => $author['name'])
                    ->values()
                    ->all();

                if (! empty($bookAuthors)) {
                    $externalOutput->bookAuthors()->createMany($bookAuthors);
                }
            }

            $this->syncExternalOutputConferences($externalOutput, $validated, $type === 'EA103');
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

    public function moveExternalAcademicToProject(Request $request, ExternalOutput $externalOutput): RedirectResponse
    {
        $validated = $request->validate([
            'project_id' => ['required', 'integer', 'exists:projects,id'],
        ]);

        $project = Project::query()->findOrFail((int) $validated['project_id']);
        $externalOutput->loadMissing(['authors', 'awardRecipients', 'bookAuthors', 'conferences.presenters']);

        DB::transaction(function () use ($externalOutput, $project) {
            $output = Output::create([
                'project_id' => $project->id,
                'type' => $this->mapExternalTypeToProjectType((string) $externalOutput->type),
                'title' => $this->nullableTrim($externalOutput->title),
                'year' => $externalOutput->publish_date?->format('d-m-Y'),
                'project_output' => $this->nullableTrim($externalOutput->note),
                'journal' => $externalOutput->type === 'EA101' ? $this->nullableTrim($externalOutput->journal) : null,
                'quartile' => $externalOutput->type === 'EA101' ? $this->nullableTrim($externalOutput->quartile) : null,
                'impact_factor' => $externalOutput->type === 'EA101' ? $externalOutput->impact_factor : null,
                'book_title' => $externalOutput->type === 'EA119' ? $this->nullableTrim($externalOutput->book_title) : null,
                'publisher_name' => $externalOutput->type === 'EA119' ? $this->nullableTrim($externalOutput->publisher_name) : null,
                'chapter_pages' => $externalOutput->type === 'EA119' ? $this->nullableTrim($externalOutput->chapter_pages) : null,
                'isbn' => $externalOutput->type === 'EA119' ? $this->nullableTrim($externalOutput->isbn) : null,
            ]);

            if ($externalOutput->type === 'EA101') {
                $authorPayloads = $externalOutput->authors
                    ->map(function ($author) {
                        $name = $this->nullableTrim($author->name);
                        $role = $this->nullableTrim($author->role);
                        if (! $name || ! $role) {
                            return null;
                        }

                        return [
                            'role' => $role,
                            'name' => $name,
                            'user_id' => $author->user_id ?: null,
                        ];
                    })
                    ->filter()
                    ->values()
                    ->all();

                if ($authorPayloads !== []) {
                    $output->authors()->createMany($authorPayloads);
                }
            }

            if ($externalOutput->type === 'EA118') {
                $recipientPayloads = $externalOutput->awardRecipients
                    ->map(function ($recipient) {
                        $name = $this->nullableTrim($recipient->name);
                        if (! $name) {
                            return null;
                        }

                        return [
                            'name' => $name,
                            'user_id' => $recipient->user_id ?: null,
                        ];
                    })
                    ->filter()
                    ->values()
                    ->all();

                if ($recipientPayloads !== []) {
                    $output->awardRecipients()->createMany($recipientPayloads);
                }
            }

            if ($externalOutput->type === 'EA119') {
                $bookAuthorPayloads = $externalOutput->bookAuthors
                    ->map(function ($author) {
                        $name = $this->nullableTrim($author->name);
                        if (! $name) {
                            return null;
                        }

                        return [
                            'name' => $name,
                            'user_id' => $author->user_id ?: null,
                        ];
                    })
                    ->filter()
                    ->values()
                    ->all();

                if ($bookAuthorPayloads !== []) {
                    $output->bookAuthors()->createMany($bookAuthorPayloads);
                }
            }

            if ($externalOutput->type === 'EA103') {
                foreach ($externalOutput->conferences as $conference) {
                    $projectConference = $output->conferences()->create([
                        'name' => $this->nullableTrim($conference->name),
                        'presentation_type' => $this->nullableTrim($conference->presentation_type),
                        'location' => $this->nullableTrim($conference->location),
                    ]);

                    $presenterPayloads = $conference->presenters
                        ->map(function ($presenter) {
                            $name = $this->nullableTrim($presenter->name);
                            if (! $name) {
                                return null;
                            }

                            return [
                                'name' => $name,
                                'user_id' => $presenter->user_id ?: null,
                            ];
                        })
                        ->filter()
                        ->values()
                        ->all();

                    if ($presenterPayloads !== []) {
                        $projectConference->presenters()->createMany($presenterPayloads);
                    }
                }
            }

            $externalOutput->delete();
        });

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'ย้ายผลงานไปยังโครงการเรียบร้อยแล้ว');
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

    private function mapExternalTypeToProjectType(string $type): string
    {
        return match (trim($type)) {
            'EA101' => 'EA101 Research Paper or Journal Article',
            'EA103' => 'EA103 Presentations in Academic Conferences',
            'EA118' => 'EA118 Award and Recognition',
            'EA119' => 'EA119 Textbook / Book chapter',
            'EA120' => 'EA120 Manual / Instruction / Guideline',
            default => trim($type) !== '' ? trim($type) : 'EA999 Other Outputs',
        };
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

        $query = Output::with([
            'project:id,project_name_th,project_code',
            'authors:id,output_id,role,name',
            'awardRecipients:id,output_id,name',
        ]);
        $user = auth()->user();
        $fullName = trim(($user->prefix ?? '') . ' ' . $user->name);
        $canViewAll = $user->isAdminLike();

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
            ->with(['task:id,backoffice_id,title,created_by'])
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

        $externalOutputQuery = $this->visibleExternalOutputsQuery()->with([
            'authors:id,external_output_id,name',
            'awardRecipients:id,external_output_id,name',
            'bookAuthors:id,external_output_id,name',
            'conferences.presenters',
        ]);
        $externalTypeSourceQuery = $this->visibleExternalOutputsQuery();

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $externalOutputQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('type', 'like', '%' . $search . '%')
                    ->orWhere('output_code', 'like', '%' . $search . '%')
                    ->orWhere('job_category', 'like', '%' . $search . '%')
                    ->orWhere('journal', 'like', '%' . $search . '%')
                    ->orWhere('note', 'like', '%' . $search . '%')
                    ->orWhereHas('authors', function ($authorQ) use ($search) {
                        $authorQ->where('name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('awardRecipients', function ($recipientQ) use ($search) {
                        $recipientQ->where('name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('bookAuthors', function ($bookAuthorQ) use ($search) {
                        $bookAuthorQ->where('name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('conferences', function ($conferenceQ) use ($search) {
                        $conferenceQ->where('name', 'like', '%' . $search . '%')
                            ->orWhere('presentation_type', 'like', '%' . $search . '%')
                            ->orWhere('location', 'like', '%' . $search . '%')
                            ->orWhere('presenter_name', 'like', '%' . $search . '%')
                            ->orWhereHas('presenters', function ($presenterQ) use ($search) {
                                $presenterQ->where('name', 'like', '%' . $search . '%');
                            });
                    });
            });
        }

        if ($request->filled('type')) {
            $typeCode = $extractTypeCode($request->type);
            if ($typeCode) {
                $externalOutputQuery->where('type', 'like', $typeCode . '%');
            }
        }

        if ($request->filled('year')) {
            $year = trim((string) $request->year);
            $yearBe = $parseYear($year);
            if ($yearBe) {
                $externalOutputQuery->whereYear('publish_date', $yearBe - 543);
            }
        }

        if ($request->filled('project_id')) {
            $externalOutputQuery = null;
        }

        $externalOutputs = $externalOutputQuery ? $externalOutputQuery->get() : collect();

        $taskOutputYears = $taskOutputs->pluck('year')
            ->map(fn($raw) => $parseYear((string) $raw))
            ->filter()
            ->unique();
        $externalOutputYears = $externalOutputs->map(function (ExternalOutput $output) {
            return $output->publish_date ? ((int) $output->publish_date->format('Y')) + 543 : null;
        })->filter()->unique();
        $yearOptions = $yearOptions->merge($taskOutputYears)->merge($externalOutputYears)->unique()->sortDesc()->values();

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
        $externalTypeSourceQuery->pluck('type')->each($registerTypeLabel);

        $typeOptions = collect($typeLabelByCode)->sortKeys()->values();

        $normalizeYear = function ($raw) use ($parseYear) {
            $y = $parseYear((string) $raw);
            return $y ? (int) $y : null;
        };

        $items = collect();

        foreach ($projectOutputs as $output) {
            $yearBe = $normalizeYear($output->year ?? '');
            $workStatus = $yearBe ? 'completed' : 'in_progress';
            $typeCode = $extractTypeCode($output->type) ?? '-';
            $typeLabel = $typeLabelByCode[$typeCode] ?? (trim((string) ($output->type ?? '')) ?: $typeCode);
            $awardees = $typeCode === 'EA118'
                ? $output->awardRecipients->pluck('name')->filter()->implode(', ')
                : '';
            $detail = trim((string) ($output->project_output ?? ''));
            if ($awardees !== '') {
                $detail = $detail !== ''
                    ? 'ผู้ได้รับรางวัล: ' . $awardees . ' | ' . $detail
                    : 'ผู้ได้รับรางวัล: ' . $awardees;
            }
            $relatedLink = $this->extractFirstUrlFromText($detail);
            $detailText = $this->stripUrlsFromText($detail);
            $items->push((object) [
                'title' => $output->title ?: '-',
                'type' => $typeLabel,
                'year_be' => $yearBe,
                'work_status' => $workStatus,
                'project_label' => $output->project?->project_name_th,
                'source_badge' => null,
                'detail' => $detailText !== '' ? $detailText : null,
                'related_link' => $relatedLink,
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
            $workStatus = $yearBe ? 'completed' : 'in_progress';
            $typeCode = $extractTypeCode($output->type) ?? '-';
            $typeLabel = $typeLabelByCode[$typeCode] ?? (trim((string) ($output->type ?? '')) ?: $typeCode);
            $items->push((object) [
                'title' => $output->title ?: '-',
                'type' => $typeLabel,
                'year_be' => $yearBe,
                'work_status' => $workStatus,
                'project_label' => $output->task?->title,
                'source_badge' => 'งานนอกโครงการวิจัย',
                'detail' => null,
                'related_link' => null,
                'link' => $output->task ? route('back_office.show', $output->task) : null,
                'created_at' => $output->created_at,
                'sort_year' => $yearBe ?? 0,
                'sort_title' => mb_strtolower($output->title ?? ''),
                'sort_type' => mb_strtolower($typeCode),
            ]);
        }

        foreach ($externalOutputs as $output) {
            $yearBe = $output->publish_date ? ((int) $output->publish_date->format('Y')) + 543 : null;
            if ($yearFilterBe && $yearBe !== $yearFilterBe) {
                continue;
            }
            $workStatus = $yearBe ? 'completed' : 'in_progress';
            $typeCode = $extractTypeCode($output->type) ?? '-';
            $typeLabel = $typeLabelByCode[$typeCode] ?? (trim((string) ($output->type ?? '')) ?: $typeCode);
            $referenceLabel = trim((string) ($output->job_category ?? '')) ?: 'ผลงานภายนอก';
            $awardees = $typeCode === 'EA118'
                ? $output->awardRecipients->pluck('name')->filter()->implode(', ')
                : '';
            $detail = trim((string) ($output->note ?? ''));
            if ($awardees !== '') {
                $detail = $detail !== ''
                    ? 'ผู้ได้รับรางวัล: ' . $awardees . ' | ' . $detail
                    : 'ผู้ได้รับรางวัล: ' . $awardees;
            }
            $relatedLink = $this->extractFirstUrlFromText($detail);
            $detailText = $this->stripUrlsFromText($detail);

            $items->push((object) [
                'title' => $output->title ?: '-',
                'type' => $typeLabel,
                'year_be' => $yearBe,
                'work_status' => $workStatus,
                'project_label' => $referenceLabel,
                'source_badge' => 'Extra',
                'detail' => $detailText !== '' ? $detailText : null,
                'related_link' => $relatedLink,
                'link' => route('outputs.external-academic.show', $output),
                'created_at' => $output->created_at,
                'sort_year' => $yearBe ?? 0,
                'sort_title' => mb_strtolower($output->title ?? ''),
                'sort_type' => mb_strtolower($typeCode),
            ]);
        }

        if ($request->filled('work_status')) {
            $workStatus = trim((string) $request->input('work_status'));
            if (in_array($workStatus, ['in_progress', 'completed'], true)) {
                $items = $items->filter(fn ($item) => ($item->work_status ?? null) === $workStatus)->values();
            }
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
            'workStatusOptions' => collect([
                'in_progress' => 'อยู่ระหว่างการดำเนินงาน',
                'completed' => 'เสร็จแล้ว',
            ]),
        ]);
    }

    public function visualization(Request $request)
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
        $normalizeNames = static function ($items): array {
            return collect($items)
                ->map(function ($item) {
                    if (is_array($item)) {
                        return trim((string) ($item['name'] ?? ''));
                    }
                    return trim((string) $item);
                })
                ->filter()
                ->values()
                ->all();
        };
        $sourceLabelMap = [
            'project' => 'โครงการวิจัย',
            'back_office' => 'งานนอกโครงการ',
            'external' => 'ผลงานภายนอก',
        ];

        $projectRows = $this->visibleProjectOutputsQuery($request)
            ->with([
                'authors:id,output_id,role,name',
                'awardRecipients:id,output_id,name',
                'bookAuthors:id,output_id,name',
                'conferences:id,output_id,name,presentation_type,location,note',
                'conferences.presenters:id,output_conference_id,name',
                'project:id,project_name_th,project_type,budget',
            ])
            ->select([
                'id',
                'project_id',
                'type',
                'title',
                'year',
                'project_output',
                'journal',
                'quartile',
                'impact_factor',
                'book_title',
                'publisher_name',
                'created_at',
            ])
            ->get()
            ->map(function (Output $output) use ($extractTypeCode, $sourceLabelMap) {
                $typeCode = $extractTypeCode($output->type) ?? 'OTHER';
                $authors = $typeCode === 'EA119'
                    ? $output->bookAuthors->pluck('name')->filter()->values()->all()
                    : ($typeCode === 'EA118'
                        ? $output->awardRecipients->pluck('name')->filter()->values()->all()
                        : $output->authors->pluck('name')->filter()->values()->all());
                $fallbackPresenter = $authors[0] ?? '';
                $conferenceWithPresenter = $output->conferences->first(function ($conferenceItem) {
                    return trim((string) $conferenceItem->presenterDisplay()) !== '';
                });
                $conference = $conferenceWithPresenter ?: $output->conferences->first();
                $conferencePresenter = trim((string) ($conference?->presenterDisplay() ?? ''));

                return (object) [
                    'source' => 'project',
                    'source_label' => $sourceLabelMap['project'],
                    'project_id' => $output->project?->id,
                    'project_name' => trim((string) ($output->project?->project_name_th ?? '')),
                    'project_type' => trim((string) ($output->project?->project_type ?? '')),
                    'project_budget' => $output->project?->budget,
                    'type_code' => $typeCode,
                    'title' => trim((string) ($output->title ?? '')) ?: '-',
                    'year_be' => $this->normalizeYearFilter($output->year),
                    'reference' => trim((string) ($output->project?->project_name_th ?? '')) ?: '-',
                    'link' => $output->project ? route('projects.show', $output->project) . '#output-' . $output->id : null,
                    'detail' => trim((string) ($output->project_output ?? '')),
                    'created_at' => $output->created_at,
                    'journal' => trim((string) ($output->journal ?? '')),
                    'quartile' => strtoupper(trim((string) ($output->quartile ?? ''))),
                    'impact_factor' => $output->impact_factor !== null ? (float) $output->impact_factor : null,
                    'conference_name' => trim((string) ($conference?->name ?? '')),
                    'presentation_type' => trim((string) ($conference?->presentation_type ?? '')),
                    'presenter_name' => $conferencePresenter !== '' ? $conferencePresenter : trim((string) $fallbackPresenter),
                    'book_title' => trim((string) ($output->book_title ?? '')),
                    'publisher_name' => trim((string) ($output->publisher_name ?? '')),
                    'authors' => $authors,
                ];
            });

        $backOfficeRows = $this->visibleBackOfficeOutputsQuery()
            ->select([
                'id',
                'task_id',
                'type',
                'title',
                'year',
                'project_output',
                'journal',
                'quartile',
                'impact_factor',
                'book_title',
                'publisher_name',
                'authors',
                'book_authors',
                'conferences',
                'created_at',
            ])
            ->get()
            ->map(function (BackOfficeTaskOutput $output) use ($extractTypeCode, $sourceLabelMap, $normalizeNames) {
                $typeCode = $extractTypeCode($output->type) ?? 'OTHER';
                $conference = collect($output->conferences ?? [])->first();
                $authors = $typeCode === 'EA119'
                    ? $normalizeNames($output->book_authors ?? [])
                    : $normalizeNames($output->authors ?? []);
                $fallbackPresenter = $authors[0] ?? '';

                return (object) [
                    'source' => 'back_office',
                    'source_label' => $sourceLabelMap['back_office'],
                    'project_id' => null,
                    'project_name' => '',
                    'project_type' => '',
                    'project_budget' => null,
                    'type_code' => $typeCode,
                    'title' => trim((string) ($output->title ?? '')) ?: '-',
                    'year_be' => $this->normalizeYearFilter($output->year),
                    'reference' => trim((string) ($output->task?->title ?? '')) ?: 'งานนอกโครงการวิจัย',
                    'link' => $output->task ? route('back_office.show', $output->task) : null,
                    'detail' => trim((string) ($output->project_output ?? '')),
                    'created_at' => $output->created_at,
                    'journal' => trim((string) ($output->journal ?? '')),
                    'quartile' => strtoupper(trim((string) ($output->quartile ?? ''))),
                    'impact_factor' => $output->impact_factor !== null ? (float) $output->impact_factor : null,
                    'conference_name' => trim((string) (($conference['name'] ?? '') ?: '')),
                    'presentation_type' => trim((string) (($conference['presentation_type'] ?? '') ?: '')),
                    'presenter_name' => trim((string) (($conference['presenter_name'] ?? '') ?: $fallbackPresenter)),
                    'book_title' => trim((string) ($output->book_title ?? '')),
                    'publisher_name' => trim((string) ($output->publisher_name ?? '')),
                    'authors' => $authors,
                ];
            });

        $externalRows = $this->visibleExternalOutputsQuery()
            ->with([
                'authors:id,external_output_id,role,name',
                'awardRecipients:id,external_output_id,name',
                'bookAuthors:id,external_output_id,name',
                'conferences:id,external_output_id,name,presentation_type,location,presenter_name',
                'conferences.presenters:id,external_output_conference_id,name',
            ])
            ->select([
                'id',
                'type',
                'title',
                'publish_date',
                'job_category',
                'note',
                'journal',
                'quartile',
                'impact_factor',
                'book_title',
                'publisher_name',
                'created_at',
            ])
            ->get()
            ->map(function (ExternalOutput $output) use ($extractTypeCode, $sourceLabelMap) {
                $typeCode = $extractTypeCode($output->type) ?? 'OTHER';
                $conference = $output->conferences->first();
                $authors = $typeCode === 'EA119'
                    ? $output->bookAuthors->pluck('name')->filter()->values()->all()
                    : ($typeCode === 'EA118'
                        ? $output->awardRecipients->pluck('name')->filter()->values()->all()
                        : $output->authors->pluck('name')->filter()->values()->all());
                $fallbackPresenter = $authors[0] ?? '';
                $conferencePresenter = trim((string) ($conference?->presenterDisplay() ?? ''));
                return (object) [
                    'source' => 'external',
                    'source_label' => $sourceLabelMap['external'],
                    'project_id' => null,
                    'project_name' => '',
                    'project_type' => '',
                    'project_budget' => null,
                    'type_code' => $typeCode,
                    'title' => trim((string) ($output->title ?? '')) ?: '-',
                    'year_be' => $output->publish_date ? ((int) $output->publish_date->format('Y')) + 543 : null,
                    'reference' => trim((string) ($output->job_category ?? '')) ?: 'ผลงานภายนอก',
                    'link' => route('outputs.external-academic.show', $output),
                    'detail' => trim((string) ($output->note ?? '')),
                    'created_at' => $output->created_at,
                    'journal' => trim((string) ($output->journal ?? '')),
                    'quartile' => strtoupper(trim((string) ($output->quartile ?? ''))),
                    'impact_factor' => $output->impact_factor !== null ? (float) $output->impact_factor : null,
                    'conference_name' => trim((string) ($conference?->name ?? '')),
                    'presentation_type' => trim((string) ($conference?->presentation_type ?? '')),
                    'presenter_name' => $conferencePresenter !== '' ? $conferencePresenter : trim((string) $fallbackPresenter),
                    'book_title' => trim((string) ($output->book_title ?? '')),
                    'publisher_name' => trim((string) ($output->publisher_name ?? '')),
                    'authors' => $authors,
                ];
            });

        $rows = $projectRows->merge($backOfficeRows)->merge($externalRows);

        $selectedSource = trim((string) $request->input('source', ''));
        $selectedType = strtoupper(trim((string) $request->input('type', '')));
        $allKnownYears = $rows->pluck('year_be')
            ->filter(fn ($year) => is_numeric($year))
            ->map(fn ($year) => (int) $year)
            ->unique()
            ->sort()
            ->values();
        $currentYearBe = (int) now()->setTimezone('Asia/Bangkok')->format('Y') + 543;
        $hasYearInput = $request->filled('year_from') || $request->filled('year_to');
        $yearFrom = $this->normalizeYearFilter($request->input('year_from'));
        $yearTo = $this->normalizeYearFilter($request->input('year_to'));
        if (!$hasYearInput) {
            $yearFrom = 2565;
            $yearTo = $currentYearBe;
        }
        if ($yearFrom !== null && $yearTo !== null && $yearFrom > $yearTo) {
            [$yearFrom, $yearTo] = [$yearTo, $yearFrom];
        }

        $filtered = $rows->filter(function ($row) use ($selectedSource, $selectedType, $yearFrom, $yearTo) {
            if ($selectedSource !== '' && $row->source !== $selectedSource) {
                return false;
            }
            if ($selectedType !== '' && strtoupper((string) $row->type_code) !== $selectedType) {
                return false;
            }
            $year = is_numeric($row->year_be) ? (int) $row->year_be : null;
            if ($yearFrom !== null && ($year === null || $year < $yearFrom)) {
                return false;
            }
            if ($yearTo !== null && ($year === null || $year > $yearTo)) {
                return false;
            }
            return true;
        })->values();

        $sourceCounts = collect($sourceLabelMap)->mapWithKeys(function ($label, $key) use ($filtered) {
            return [$key => (int) $filtered->where('source', $key)->count()];
        });

        $projectTypeLabelByCode = [
            '01' => '01 Aging and Frailty',
            '02' => '02 Health Systems and HTA',
            '03' => '03 Rational Drug Use (RDU)',
            '04' => '04 Real-World Evidence (RWE) and Clinical-Technical Cohorts',
            '05' => '05 Global Health',
            '06' => '06 Other Areas',
        ];

        $normalizeProjectType = static function (?string $rawType) use ($projectTypeLabelByCode): string {
            $type = trim((string) $rawType);
            foreach ($projectTypeLabelByCode as $code => $label) {
                if (str_starts_with($type, $code)) {
                    return $label;
                }
            }
            return $projectTypeLabelByCode['06'];
        };

        $parseBudget = static function ($raw): float {
            if ($raw === null) {
                return 0.0;
            }
            if (is_numeric($raw)) {
                return (float) $raw;
            }
            $normalized = preg_replace('/[^0-9.\-]/', '', (string) $raw);
            return is_numeric($normalized) ? (float) $normalized : 0.0;
        };
        $extractFirstUrl = static function (?string $text): ?string {
            $value = trim((string) $text);
            if ($value === '') {
                return null;
            }
            if (preg_match('/https?:\/\/[^\s<>"\'\]\)]+/iu', $value, $matches) === 1) {
                return trim((string) ($matches[0] ?? '')) ?: null;
            }
            return null;
        };
        $buildSummaryListItem = static function ($row) use ($extractFirstUrl) {
            $detail = trim((string) ($row->detail ?? ''));
            $sourceLink = trim((string) ($row->link ?? '')) ?: null;
            $detailLink = $extractFirstUrl($detail);

            return (object) array_merge((array) $row, [
                'modal_link' => $detailLink ?: $sourceLink,
                'modal_detail' => $detail !== '' ? $detail : null,
            ]);
        };
        $sortRowsNewestFirst = static function (Collection $rows): Collection {
            return $rows->sort(function ($left, $right) {
                $leftYear = is_numeric($left->year_be ?? null) ? (int) $left->year_be : PHP_INT_MIN;
                $rightYear = is_numeric($right->year_be ?? null) ? (int) $right->year_be : PHP_INT_MIN;

                if ($leftYear !== $rightYear) {
                    return $rightYear <=> $leftYear;
                }

                $leftCreatedAt = $left->created_at?->timestamp ?? PHP_INT_MIN;
                $rightCreatedAt = $right->created_at?->timestamp ?? PHP_INT_MIN;

                if ($leftCreatedAt !== $rightCreatedAt) {
                    return $rightCreatedAt <=> $leftCreatedAt;
                }

                return strcmp(
                    trim((string) ($left->title ?? '')),
                    trim((string) ($right->title ?? ''))
                );
            })->values();
        };

        $projectOutputRows = $filtered
            ->where('source', 'project')
            ->filter(fn ($row) => is_numeric($row->project_id))
            ->values();

        $projectTypeTotalOutputs = (int) $projectOutputRows->count();
        $eaFullNameMap = [
            'EA101' => 'Research Paper or Journal Article',
            'EA102' => 'Reports and White Papers',
            'EA103' => 'Presentations in Academic Conferences',
            'EA104' => 'Data Sets and Databases',
            'EA105' => 'Inventions or Innovations (Application / Website)',
            'EA106' => 'Policy Recommendations',
            'EA107' => 'Educational Materials',
            'EA108' => 'Communication Materials (Creations)',
            'EA109' => 'Change in Practices / Procedures / Policy',
            'EA110' => 'Number of Participants (Persons)',
            'EA111' => 'Satisfaction Rating (1-5)',
            'EA112' => 'Present to Committee Meeting / Policy Maker / Board',
            'EA113' => 'Appointment Orders (SIHP as Committee / Secretary)',
            'EA114' => 'Events / Forum',
            'EA115' => 'Capacity Building / Knowledge Sharing',
            'EA116' => 'Research Project',
            'EA117' => 'MOU',
            'EA118' => 'Award and Recognition',
            'EA119' => 'Textbook / Book Chapter',
            'EA120' => 'Manual / Instruction / Guideline',
            'EA999' => 'Other Outputs',
            'OTHER' => 'Other Outputs',
        ];
        $projectTypeSummary = collect($projectTypeLabelByCode)->map(function ($label) use ($projectOutputRows, $normalizeProjectType, $parseBudget, $projectTypeTotalOutputs, $eaFullNameMap, $extractFirstUrl, $sortRowsNewestFirst) {
            $rows = $projectOutputRows->filter(fn ($row) => $normalizeProjectType($row->project_type) === $label)->values();
            $outputCount = (int) $rows->count();
            $projectCount = (int) $rows->pluck('project_id')->filter(fn ($id) => is_numeric($id))->unique()->count();
            $budgetTotal = (float) $rows->unique(fn ($row) => (int) ($row->project_id ?? 0))->sum(fn ($row) => $parseBudget($row->project_budget));
            return (object) [
                'label' => $label,
                'output_count' => $outputCount,
                'project_count' => $projectCount,
                'pct' => $projectTypeTotalOutputs > 0 ? round(($outputCount / $projectTypeTotalOutputs) * 100, 1) : 0.0,
                'budget_total' => $budgetTotal,
                'items' => $sortRowsNewestFirst($rows)
                    ->map(function ($row) use ($eaFullNameMap, $extractFirstUrl) {
                        $typeCode = strtoupper((string) ($row->type_code ?? 'OTHER'));
                        $detail = trim((string) ($row->detail ?? ''));
                        $detailLink = $extractFirstUrl($detail);
                        $sourceLink = trim((string) ($row->link ?? '')) ?: null;
                        return (object) [
                            'title' => trim((string) ($row->title ?? '')) ?: '-',
                            'type_code' => $typeCode,
                            'type_label' => $eaFullNameMap[$typeCode] ?? ('ประเภท ' . $typeCode),
                            'year_be' => is_numeric($row->year_be ?? null) ? (int) $row->year_be : null,
                            'link' => $detailLink ?: $sourceLink,
                            'source_link' => $sourceLink,
                            'detail' => $detail !== '' ? $detail : null,
                        ];
                    })
                    ->filter(fn ($item) => trim((string) ($item->title ?? '')) !== '')
                    ->unique()
                    ->values(),
            ];
        })->values();

        $typeCounts = $filtered
            ->groupBy(fn ($row) => strtoupper((string) ($row->type_code ?: 'OTHER')))
            ->map(fn (Collection $group) => $group->count())
            ->sortDesc();

        $knownYears = $filtered->pluck('year_be')->filter(fn ($year) => is_numeric($year))->map(fn ($year) => (int) $year)->unique()->sort()->values();
        $hasUnknownYear = $filtered->contains(fn ($row) => !is_numeric($row->year_be));
        $yearAxis = $knownYears->map(fn ($year) => (string) $year)->values();
        if ($hasUnknownYear) {
            $yearAxis->push('__unknown__');
        }
        $yearAxisLabels = $yearAxis->map(fn ($key) => $key === '__unknown__' ? 'ไม่ระบุปี' : (string) $key)->values();
        $yearBySource = collect($sourceLabelMap)->mapWithKeys(function ($label, $sourceKey) use ($filtered, $yearAxis) {
            $series = $yearAxis->map(function ($yearKey) use ($filtered, $sourceKey) {
                if ($yearKey === '__unknown__') {
                    return (int) $filtered->where('source', $sourceKey)->filter(fn ($row) => !is_numeric($row->year_be))->count();
                }
                return (int) $filtered->where('source', $sourceKey)->where('year_be', (int) $yearKey)->count();
            })->values();
            return [$sourceKey => $series];
        });

        $yearOptions = $rows->pluck('year_be')->filter(fn ($year) => is_numeric($year))->map(fn ($year) => (int) $year)->unique()->sortDesc()->values();
        $typeOptions = $rows->pluck('type_code')->filter()->map(fn ($type) => strtoupper((string) $type))->unique()->sort()->values();

        $paperRows = $filtered->where('type_code', 'EA101')->values();
        $presentationRows = $filtered->where('type_code', 'EA103')->values();
        $textbookRows = $filtered->where('type_code', 'EA119')->values();
        $categorizedTotal = (int) ($paperRows->count() + $presentationRows->count() + $textbookRows->count());

        $paperQuartileLabels = collect(['Q1', 'Q2', 'Q3', 'Q4', 'ไม่ระบุ']);
        $paperQuartileValues = $paperQuartileLabels->map(function ($label) use ($paperRows) {
            if ($label === 'ไม่ระบุ') {
                return (int) $paperRows->filter(fn ($row) => trim((string) ($row->quartile ?? '')) === '')->count();
            }
            return (int) $paperRows->where('quartile', $label)->count();
        })->values();

        $presentationActivity = $presentationRows
            ->map(fn ($row) => trim((string) ($row->presentation_type ?? '')) ?: 'ไม่ระบุ')
            ->countBy()
            ->sortDesc()
            ->take(8);

        $textbookYears = $textbookRows
            ->map(fn ($row) => is_numeric($row->year_be ?? null) ? (int) $row->year_be : null)
            ->filter()
            ->countBy()
            ->sortKeys();

        $paperSummary = [
            'count' => $paperRows->count(),
            'top_journal' => $paperRows->map(fn ($row) => trim((string) ($row->journal ?? '')))->filter()->countBy()->sortDesc()->keys()->first(),
            'avg_if' => round((float) $paperRows->pluck('impact_factor')->filter(fn ($v) => is_numeric($v))->avg(), 2),
            'all' => $sortRowsNewestFirst($paperRows)->map($buildSummaryListItem)->values(),
            'latest' => $sortRowsNewestFirst($paperRows)->take(5)->map($buildSummaryListItem)->values(),
        ];

        $presentationSummary = [
            'count' => $presentationRows->count(),
            'top_conference' => $presentationRows->map(fn ($row) => trim((string) ($row->conference_name ?? '')))->filter()->countBy()->sortDesc()->keys()->first(),
            'top_presenter' => $presentationRows->map(fn ($row) => trim((string) ($row->presenter_name ?? '')))->filter()->countBy()->sortDesc()->keys()->first(),
            'all' => $sortRowsNewestFirst($presentationRows)->map($buildSummaryListItem)->values(),
            'latest' => $sortRowsNewestFirst($presentationRows)->take(5)->map($buildSummaryListItem)->values(),
        ];

        $textbookSummary = [
            'count' => $textbookRows->count(),
            'top_publisher' => $textbookRows->map(fn ($row) => trim((string) ($row->publisher_name ?? '')))->filter()->countBy()->sortDesc()->keys()->first(),
            'unique_authors' => $textbookRows->flatMap(fn ($row) => collect($row->authors ?? []))->filter()->unique()->count(),
            'latest_year' => $textbookYears->keys()->last(),
            'peak_year' => $textbookYears->sortDesc()->keys()->first(),
            'peak_year_count' => (int) ($textbookYears->max() ?? 0),
            'latest' => $sortRowsNewestFirst($textbookRows)->take(5)->map($buildSummaryListItem)->values(),
        ];

        $yearTotals = $filtered
            ->pluck('year_be')
            ->filter(fn ($year) => is_numeric($year))
            ->map(fn ($year) => (int) $year)
            ->countBy()
            ->sortKeys()
            ->map(fn ($count) => (int) $count);

        $latestYear = $yearTotals->keys()->last();
        $previousYear = $yearTotals->keys()->slice(-2, 1)->first();
        $firstYear = $yearTotals->keys()->first();
        $latestYearCount = is_numeric($latestYear) ? (int) ($yearTotals->get($latestYear) ?? 0) : 0;
        $previousYearCount = is_numeric($previousYear) ? (int) ($yearTotals->get($previousYear) ?? 0) : 0;
        $firstYearCount = is_numeric($firstYear) ? (int) ($yearTotals->get($firstYear) ?? 0) : 0;
        $yearDelta = $latestYearCount - $previousYearCount;
        $yearGrowthPct = $previousYearCount > 0 ? round(($yearDelta / $previousYearCount) * 100, 1) : null;
        $knownYearTotal = (int) $yearTotals->sum();
        $knownYearCount = (int) $yearTotals->count();
        // "Forward growth" should compare base year to latest year directly.
        // Using sum(all years) inflates percentage and confuses interpretation.
        $cumulativeGrowthCount = max(0, $latestYearCount - $firstYearCount);
        $cumulativeGrowthPct = $firstYearCount > 0
            ? round(($cumulativeGrowthCount / $firstYearCount) * 100, 1)
            : null;
        $avgPerYear = $knownYearCount > 0 ? round($knownYearTotal / $knownYearCount, 1) : 0.0;
        $peakCount = $yearTotals->max();
        $peakYear = $peakCount !== null
            ? $yearTotals->filter(fn ($count) => (int) $count === (int) $peakCount)->keys()->last()
            : null;

        $totalCount = (int) $filtered->count();

        $sourceShare = collect($sourceLabelMap)->map(function ($label, $sourceKey) use ($sourceCounts, $totalCount) {
            $count = (int) ($sourceCounts[$sourceKey] ?? 0);
            return (object) [
                'source' => $sourceKey,
                'label' => $label,
                'count' => $count,
                'pct' => $totalCount > 0 ? round(($count / $totalCount) * 100, 1) : 0.0,
            ];
        })->sortByDesc('count')->values();

        $typeShare = collect([
            ['code' => 'EA101', 'label' => 'Research Paper', 'count' => (int) $paperRows->count()],
            ['code' => 'EA103', 'label' => 'Presentation', 'count' => (int) $presentationRows->count()],
            ['code' => 'EA119', 'label' => 'Textbook', 'count' => (int) $textbookRows->count()],
        ])->map(function ($item) use ($categorizedTotal) {
            $item['pct'] = $categorizedTotal > 0
                ? round((((int) $item['count']) / $categorizedTotal) * 100, 1)
                : 0.0;
            return (object) $item;
        })->sortByDesc('count')->values();

        return view('outputs.visualization', [
            'rows' => $filtered,
            'sourceLabelMap' => $sourceLabelMap,
            'sourceCounts' => $sourceCounts,
            'typeCounts' => $typeCounts,
            'yearAxisLabels' => $yearAxisLabels,
            'yearBySource' => $yearBySource,
            'yearOptions' => $yearOptions,
            'typeOptions' => $typeOptions,
            'selectedSource' => $selectedSource,
            'selectedType' => $selectedType,
            'selectedYearFrom' => $yearFrom,
            'selectedYearTo' => $yearTo,
            'yearTotals' => $yearTotals,
            'trendSummary' => [
                'latest_year' => is_numeric($latestYear) ? (int) $latestYear : null,
                'previous_year' => is_numeric($previousYear) ? (int) $previousYear : null,
                'latest_count' => $latestYearCount,
                'previous_count' => $previousYearCount,
                'delta' => $yearDelta,
                'growth_pct' => $yearGrowthPct,
            ],
            'forwardSummary' => [
                'first_year' => is_numeric($firstYear) ? (int) $firstYear : null,
                'first_count' => $firstYearCount,
                'latest_year' => is_numeric($latestYear) ? (int) $latestYear : null,
                'latest_count' => $latestYearCount,
                'cumulative_growth_count' => $cumulativeGrowthCount,
                'cumulative_growth_pct' => $cumulativeGrowthPct,
                'avg_per_year' => $avgPerYear,
                'peak_year' => is_numeric($peakYear) ? (int) $peakYear : null,
                'peak_count' => $peakCount !== null ? (int) $peakCount : 0,
                'known_year_total' => $knownYearTotal,
                'known_year_count' => $knownYearCount,
            ],
            'sourceShare' => $sourceShare,
            'projectTypeSummary' => $projectTypeSummary,
            'projectTypeTotalOutputs' => $projectTypeTotalOutputs,
            'typeShare' => $typeShare,
            'paperSummary' => $paperSummary,
            'presentationSummary' => $presentationSummary,
            'textbookSummary' => $textbookSummary,
            'paperQuartileLabels' => $paperQuartileLabels,
            'paperQuartileValues' => $paperQuartileValues,
            'presentationActivityLabels' => $presentationActivity->keys()->values(),
            'presentationActivityValues' => $presentationActivity->values()->values(),
            'textbookYearLabels' => $textbookYears->keys()->values(),
            'textbookYearValues' => $textbookYears->values()->values(),
        ]);
    }

    public function people(Request $request)
    {
        $viewer = $request->user();
        $canViewAll = $this->canViewAllPersonalOutputs($viewer);
        $search = trim((string) $request->input('search', ''));

        $usersQuery = User::query()
            ->select(['id', 'prefix', 'name', 'prefix_en', 'name_en', 'email', 'role', 'position', 'profile_photo_path'])
            ->where('role', '!=', User::ROLE_RESIGNED)
            ->orderBy('name');

        if (! $canViewAll) {
            $usersQuery->whereKey($viewer->id);
        }

        if ($search !== '') {
            $usersQuery->where(function (Builder $query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('prefix', 'like', '%' . $search . '%')
                    ->orWhere('name_en', 'like', '%' . $search . '%')
                    ->orWhere('prefix_en', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('position', 'like', '%' . $search . '%');
            });
        }

        $users = $usersQuery->get()->values();
        $statsMap = $this->buildPersonalUserStatsMap($users);

        $people = $users->map(function (User $user) use ($statsMap) {
            $stats = $statsMap->get($user->id, $this->emptyPersonalStats());

            return (object) [
                'user' => $user,
                'stats' => $stats,
                'display_name' => $user->full_name,
                'display_name_en' => $user->research_display_name !== $user->full_name ? $user->research_display_name : null,
                'profile_photo_url' => $this->resolveUserProfilePhotoUrl($user),
            ];
        })->sortByDesc(fn ($item) => $item->stats['total_outputs'] + $item->stats['pi_projects'] + $item->stats['co_pi_projects'] + $item->stats['co_research_projects'])
            ->values();

        return view('outputs.people-index', [
            'people' => $people,
            'canViewAll' => $canViewAll,
            'search' => $search,
        ]);
    }

    public function personShow(Request $request, string $person)
    {
        $user = $this->resolvePersonalOutputUser($person);
        $this->authorizePersonalOutputAccess($request->user(), $user);

        $payload = $this->buildPersonalOutputPayload($user);
        return view('outputs.people-show', $payload);
    }

    public function personPdf(Request $request, string $person)
    {
        $user = $this->resolvePersonalOutputUser($person);
        $viewer = $request->user();
        $this->authorizePersonalOutputAccess($viewer, $user);

        $payload = $this->buildPersonalOutputPayload($user);
        $generatedAt = now()->setTimezone('Asia/Bangkok');
        $reporter = trim((string) $viewer->full_name) !== '' ? $viewer->full_name : 'ผู้ใช้ระบบ';

        $pdf = PDF::loadView('outputs.people-pdf', array_merge($payload, [
            'generatedAt' => $generatedAt,
            'reporter' => $reporter,
            'profilePhotoDataUri' => $this->resolveUserProfilePhotoDataUriForPdf($user),
        ]));
        $filename = $this->buildOutputPdfFilename('OUTPUT_PERSONAL', $user->full_name ?: $user->name);
        AuditLogger::logFileDownload($filename, [
            'subject_type' => 'output_personal_pdf',
            'subject_label' => (string) ($user->full_name ?: $user->name),
        ], $request);

        return $pdf->stream($filename);
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
            $request->filled('author') ? 'Author: ' . trim((string) $request->input('author')) : null,
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
        $filename = $this->buildOutputPdfFilename('OUTPUT_PAPERS');
        AuditLogger::logFileDownload($filename, [
            'subject_type' => 'output_papers_pdf',
            'subject_label' => 'Research Papers and Journal Articles',
        ], $request);

        return $pdf->stream($filename);
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
            $request->filled('presenter') ? 'ผู้นำเสนอ: ' . trim((string) $request->input('presenter')) : null,
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
        $filename = $this->buildOutputPdfFilename('OUTPUT_PRESENTATIONS');
        AuditLogger::logFileDownload($filename, [
            'subject_type' => 'output_presentations_pdf',
            'subject_label' => 'Presentations in Academic Conferences',
        ], $request);

        return $pdf->stream($filename);
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
        $filename = $this->buildOutputPdfFilename('OUTPUT_TEXTBOOKS');
        AuditLogger::logFileDownload($filename, [
            'subject_type' => 'output_textbooks_pdf',
            'subject_label' => 'Textbook / Book Chapter',
        ], $request);

        return $pdf->stream($filename);
    }

    private function buildOutputPdfFilename(string $prefix, ?string $subject = null): string
    {
        $dateStr = now()->setTimezone('Asia/Bangkok')->format('Ymd_Hi');
        $base = strtoupper(trim($prefix)) . '_' . $dateStr;

        $subject = trim((string) ($subject ?? ''));
        if ($subject !== '') {
            $subject = preg_replace('/[\/\\\\\?\%\*\:\|"<>\x00-\x1F]/u', ' ', $subject);
            $subject = preg_replace('/\s+/u', '_', $subject ?? '');
            $subject = trim((string) $subject, '_');
            if ($subject !== '') {
                $base .= '_' . Str::limit($subject, 80, '');
            }
        }

        return $base . '.pdf';
    }

    private function buildPresentationsPayload(Request $request, string $sort, string $dir): array
    {
        $baseQuery = $this->visibleProjectOutputsQuery($request)
            ->where('type', 'like', 'EA103%')
            ->with(['conferences:id,output_id,name,presentation_type,location,note', 'conferences.presenters:id,output_conference_id,name']);

        $backOfficeQuery = $this->visibleBackOfficeOutputsQuery()
            ->where('type', 'like', 'EA103%');

        $externalQuery = ExternalOutput::query()
            ->where('type', 'EA103')
            ->with(['conferences:id,external_output_id,name,presentation_type,location,presenter_name', 'conferences.presenters:id,external_output_conference_id,name']);

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
        $presenterFilter = trim((string) $request->input('presenter', ''));
        $activity = trim((string) $request->input('activity', ''));
        $baseRows = $baseQuery->get()
            ->flatMap(function (Output $output) {
                $yearBe = $this->normalizeYearFilter($output->year);

                return $output->conferences->map(function ($conference) use ($output, $yearBe) {
                    return (object) [
                        'output_id' => $output->id,
                        'output_code' => $output->output_code,
                        'source_badge' => null,
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
                        'presenter_name' => $conference->presenterDisplay() ?: '-',
                        'displayed_at' => $output->updated_at ?? $output->created_at,
                        'created_at' => $output->created_at,
                        'sort_year' => $yearBe ?? 0,
                        'sort_title' => mb_strtolower((string) $output->title),
                        'sort_conference' => mb_strtolower((string) $conference->name),
                        'sort_activity' => mb_strtolower((string) $conference->presentation_type),
                    ];
                });
            });

        $backOfficeRows = $backOfficeQuery->get()
            ->flatMap(function (BackOfficeTaskOutput $output) {
                $yearBe = $this->normalizeYearFilter($output->year);
                $authors = collect($output->authors ?? [])
                    ->map(function ($author) {
                        if (is_array($author)) {
                            return trim((string) ($author['name'] ?? ''));
                        }

                        return trim((string) $author);
                    })
                    ->filter()
                    ->values();
                $fallbackPresenter = $authors->first() ?: '-';

                return collect($output->conferences ?? [])->map(function ($conference, $index) use ($output, $yearBe, $fallbackPresenter) {
                    $conference = is_array($conference) ? $conference : [];
                    $presenter = trim((string) (($conference['presenter_name'] ?? $conference['note'] ?? '') ?: ''));

                    return (object) [
                        'output_id' => $output->id,
                        'output_code' => null,
                        'source_badge' => 'งานนอกโครงการ',
                        'conference_id' => $conference['id'] ?? ('bo-' . $output->id . '-' . $index),
                        'title' => $output->title ?: '-',
                        'detail' => $output->project_output ?: null,
                        'year_be' => $yearBe,
                        'project_label' => $output->task?->title ?: '-',
                        'project_code' => null,
                        'project_link' => $output->task ? route('back_office.show', $output->task) : null,
                        'conference_name' => trim((string) (($conference['name'] ?? '') ?: '-')),
                        'presentation_type' => trim((string) (($conference['presentation_type'] ?? '') ?: '-')),
                        'location' => trim((string) (($conference['location'] ?? '') ?: '-')),
                        'presenter_name' => $presenter !== '' ? $presenter : $fallbackPresenter,
                        'displayed_at' => $output->updated_at ?? $output->created_at,
                        'created_at' => $output->created_at,
                        'sort_year' => $yearBe ?? 0,
                        'sort_title' => mb_strtolower((string) $output->title),
                        'sort_conference' => mb_strtolower((string) ($conference['name'] ?? '')),
                        'sort_activity' => mb_strtolower((string) ($conference['presentation_type'] ?? '')),
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
                        'source_badge' => null,
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
                        'presenter_name' => $conference->presenterDisplay() ?: '-',
                        'displayed_at' => $output->updated_at ?? $output->created_at,
                        'created_at' => $output->created_at,
                        'sort_year' => $yearBe ?? 0,
                        'sort_title' => mb_strtolower((string) $output->title),
                        'sort_conference' => mb_strtolower((string) $conference->name),
                        'sort_activity' => mb_strtolower((string) $conference->presentation_type),
                    ];
                });
            });

        $allRows = $baseRows->merge($backOfficeRows)->merge($externalRows);

        $rows = $allRows
            ->filter(function ($row) use ($search, $activity, $presenterFilter) {
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

                if ($presenterFilter !== '' && trim((string) $row->presenter_name) !== $presenterFilter) {
                    return false;
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
        $canViewAll = $user->isAdminLike();

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
        $canViewAll = $user->isAdminLike();

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

    private function visibleExternalOutputsQuery(): Builder
    {
        return ExternalOutput::query();
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

    private function resolveExternalOutputConferenceUser(?int $userId, ?string $name): ?User
    {
        if ($userId && $userId > 0) {
            return User::query()->find($userId);
        }

        $name = trim((string) $name);
        if ($name === '') {
            return null;
        }

        return User::query()
            ->get()
            ->first(function (User $user) use ($name) {
                return trim((string) $user->full_name) === $name;
            });
    }

    private function syncExternalOutputConferences(ExternalOutput $output, array $row, bool $isEA103): void
    {
        if (! $isEA103) {
            $output->conferences()->each(function (ExternalOutputConference $conference) {
                $conference->presenters()->delete();
                $conference->delete();
            });

            return;
        }

        $conferenceId = (($row['conference_id'] ?? null) !== null && (int) $row['conference_id'] > 0)
            ? (int) $row['conference_id']
            : null;
        $conferenceName = trim((string) ($row['conference_name'] ?? ''));
        $presentationType = trim((string) ($row['conference_presentation_type'] ?? ''));
        $location = trim((string) ($row['conference_location'] ?? ''));
        $presenters = is_array($row['conference_presenters'] ?? null) ? $row['conference_presenters'] : [];

        $hasConferencePayload = $conferenceName !== '' || $presentationType !== '' || $location !== '';
        $hasPresenterPayload = collect($presenters)->contains(function ($presenter) {
            return trim((string) ($presenter['note'] ?? '')) !== ''
                || (int) ($presenter['user_id'] ?? 0) > 0
                || trim((string) ($presenter['person_type'] ?? '')) !== '';
        });

        if (! $hasConferencePayload && ! $hasPresenterPayload) {
            $output->conferences()->each(function (ExternalOutputConference $conference) {
                $conference->presenters()->delete();
                $conference->delete();
            });

            return;
        }

        $conference = null;
        if ($conferenceId) {
            $conference = $output->conferences()->whereKey($conferenceId)->first();
        }
        if (! $conference) {
            $conference = $output->conferences()->orderBy('id')->first();
        }
        if (! $conference) {
            $conference = $output->conferences()->make();
        }

        $conference->fill([
            'name' => $conferenceName !== '' ? $conferenceName : null,
            'presentation_type' => $presentationType !== '' ? $presentationType : null,
            'location' => $location !== '' ? $location : null,
            'user_id' => null,
            'presenter_name' => null,
        ]);
        $conference->externalOutput()->associate($output);
        $conference->save();

        $presenterIds = [];
        foreach ($presenters as $index => $presenter) {
            $personType = trim((string) ($presenter['person_type'] ?? ''));
            $userId = (($presenter['user_id'] ?? null) !== null && (int) $presenter['user_id'] > 0)
                ? (int) $presenter['user_id']
                : null;
            $presenterName = trim((string) ($presenter['note'] ?? ''));

            if ($personType === 'internal') {
                $user = $this->resolveExternalOutputConferenceUser($userId, $presenterName);
                if (! $user) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        "conference_presenters.$index.user_id" => 'กรุณาเลือกผู้นำเสนอจากรายชื่อบุคลากร',
                    ]);
                }

                $userId = $user->id;
                $presenterName = trim((string) $user->full_name);
            } elseif ($personType === 'external') {
                $userId = null;
            }

            if ($presenterName === '' && ! $userId) {
                continue;
            }

            $payload = [
                'user_id' => $userId,
                'name' => $presenterName !== '' ? $presenterName : null,
            ];

            if (! empty($presenter['id'])) {
                $model = $conference->presenters()->whereKey($presenter['id'])->first();
                if ($model) {
                    $model->update($payload);
                    $presenterIds[] = $model->id;
                }
            } else {
                $new = $conference->presenters()->create($payload);
                $presenterIds[] = $new->id;
            }
        }

        if ($presenterIds !== []) {
            $conference->presenters()->whereNotIn('id', $presenterIds)->delete();
        } else {
            $conference->presenters()->delete();
        }

        $output->conferences()
            ->where('id', '!=', $conference->id)
            ->get()
            ->each(function (ExternalOutputConference $extraConference) {
                $extraConference->presenters()->delete();
                $extraConference->delete();
            });
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

        $backOfficeQuery = $this->visibleBackOfficeOutputsQuery()
            ->where('type', 'like', 'EA101%');

        $externalQuery = ExternalOutput::query()
            ->where('type', 'EA101')
            ->with(['authors:id,external_output_id,role,name']);

        $yearFilter = $this->normalizeYearFilter($request->input('year'));
        $search = trim((string) $request->input('search', ''));
        $authorFilter = trim((string) $request->input('author', ''));
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

        if ($role !== '' || $authorFilter !== '') {
            $baseQuery->whereHas('authors', function (Builder $authorQ) use ($role, $authorFilter) {
                if ($role !== '') {
                    $authorQ->where('role', $role);
                }

                if ($authorFilter !== '') {
                    $authorQ->where('name', $authorFilter);
                }
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
            $backOfficeQuery->where(function (Builder $q) use ($yearFilter) {
                $q->where('year', 'like', '%' . $yearFilter . '%')
                    ->orWhere('year', 'like', '%' . ($yearFilter - 543) . '%');
            });
            $externalQuery->whereYear('publish_date', $yearFilter - 543);
        }

        $projectPapers = $baseQuery->get()->map(function (Output $output) use ($authorFilter) {
            $yearBe = $this->normalizeYearFilter($output->year);
            $authorsByRole = $output->authors
                ->groupBy(fn ($author) => strtolower((string) $author->role))
                ->map(fn (Collection $group) => $group->pluck('name')->filter()->values());
            $matchedAuthorRoles = collect();

            if ($authorFilter !== '') {
                $matchedAuthorRoles = $output->authors
                    ->filter(fn ($author) => trim((string) $author->name) === $authorFilter)
                    ->pluck('role')
                    ->filter()
                    ->map(fn ($role) => $this->formatAuthorRoleLabel((string) $role))
                    ->unique()
                    ->values();
            }

            return (object) [
                'id' => $output->id,
                'output_code' => $output->output_code,
                'source_badge' => null,
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
                'matched_author_roles' => $matchedAuthorRoles,
                'displayed_at' => $output->updated_at ?? $output->created_at,
                'created_at' => $output->created_at,
                'sort_title' => mb_strtolower((string) $output->title),
                'sort_journal' => mb_strtolower((string) $output->journal),
                'sort_quartile' => mb_strtolower((string) $output->quartile),
                'sort_year' => $yearBe ?? 0,
            ];
        });

        $backOfficePapers = $backOfficeQuery->get()
            ->filter(function (BackOfficeTaskOutput $output) use ($search, $role, $authorFilter) {
                $authors = collect($output->authors ?? [])
                    ->map(function ($author) {
                        if (is_array($author)) {
                            return trim((string) ($author['name'] ?? ''));
                        }

                        return trim((string) $author);
                    })
                    ->filter()
                    ->values();

                if ($search !== '') {
                    $haystack = mb_strtolower(implode(' ', array_filter([
                        $output->title,
                        $output->journal,
                        $output->quartile,
                        $output->project_output,
                        $output->year,
                        $output->task?->title,
                        $authors->implode(' '),
                    ])), 'UTF-8');

                    if (!str_contains($haystack, mb_strtolower($search, 'UTF-8'))) {
                        return false;
                    }
                }

                if ($authorFilter !== '' || $role !== '') {
                    return collect($output->authors ?? [])->contains(function ($author) use ($authorFilter, $role) {
                        if (!is_array($author)) {
                            return false;
                        }

                        $name = trim((string) ($author['name'] ?? ''));
                        $authorRole = strtolower(trim((string) ($author['role'] ?? '')));

                        if ($authorFilter !== '' && $name !== $authorFilter) {
                            return false;
                        }

                        if ($role !== '' && $authorRole !== strtolower($role)) {
                            return false;
                        }

                        return true;
                    });
                }

                return true;
            })
            ->map(function (BackOfficeTaskOutput $output) use ($authorFilter) {
                $yearBe = $this->normalizeYearFilter($output->year);
                $authors = collect($output->authors ?? [])
                    ->map(function ($author) {
                        if (is_array($author)) {
                            return [
                                'role' => strtolower(trim((string) ($author['role'] ?? ''))),
                                'name' => trim((string) ($author['name'] ?? '')),
                            ];
                        }

                        return [
                            'role' => '',
                            'name' => trim((string) $author),
                        ];
                    })
                    ->filter(fn ($author) => $author['name'] !== '')
                    ->values();
                $authorsByRole = $authors
                    ->filter(fn ($author) => $author['role'] !== '')
                    ->groupBy('role')
                    ->map(fn (Collection $group) => $group->pluck('name')->filter()->values());
                $matchedAuthorRoles = collect();

                if ($authorFilter !== '') {
                    $matchedAuthorRoles = $authors
                        ->filter(fn ($author) => $author['name'] === $authorFilter)
                        ->pluck('role')
                        ->filter()
                        ->map(fn ($role) => $this->formatAuthorRoleLabel((string) $role))
                        ->unique()
                        ->values();
                }

                return (object) [
                    'id' => $output->id,
                    'output_code' => null,
                    'source_badge' => 'งานนอกโครงการ',
                    'title' => $output->title ?: '-',
                    'type' => $output->type ?: '-',
                    'year_be' => $yearBe,
                    'journal' => $output->journal ?: '-',
                    'quartile' => $output->quartile ?: '-',
                    'impact_factor' => $output->impact_factor,
                    'detail' => $output->project_output ?: null,
                    'project_label' => $output->task?->title ?: 'งานนอกโครงการวิจัย',
                    'project_code' => null,
                    'project_link' => $output->task ? route('back_office.show', $output->task) : null,
                    'authors' => $authors->pluck('name')->filter()->values(),
                    'authors_by_role' => $authorsByRole,
                    'matched_author_roles' => $matchedAuthorRoles,
                    'displayed_at' => $output->updated_at ?? $output->created_at,
                    'created_at' => $output->created_at,
                    'sort_title' => mb_strtolower((string) $output->title),
                    'sort_journal' => mb_strtolower((string) $output->journal),
                    'sort_quartile' => mb_strtolower((string) $output->quartile),
                    'sort_year' => $yearBe ?? 0,
                ];
            });

        $externalPapers = $externalQuery->get()
            ->filter(function (ExternalOutput $output) use ($search, $role, $authorFilter) {
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

                if ($authorFilter !== '' || $role !== '') {
                    return $output->authors->contains(function ($author) use ($authorFilter, $role) {
                        if ($authorFilter !== '' && trim((string) $author->name) !== $authorFilter) {
                            return false;
                        }

                        if ($role !== '' && strtolower(trim((string) $author->role)) !== strtolower($role)) {
                            return false;
                        }

                        return true;
                    });
                }

                return true;
            })
            ->map(function (ExternalOutput $output) use ($authorFilter) {
                $yearBe = $output->publish_date ? ((int) $output->publish_date->format('Y')) + 543 : null;
                $authorsByRole = $output->authors
                    ->groupBy(fn ($author) => strtolower((string) $author->role))
                    ->map(fn (Collection $group) => $group->pluck('name')->filter()->values());
                $matchedAuthorRoles = collect();

                if ($authorFilter !== '') {
                    $matchedAuthorRoles = $output->authors
                        ->filter(fn ($author) => trim((string) $author->name) === $authorFilter)
                        ->pluck('role')
                        ->filter()
                        ->map(fn ($role) => $this->formatAuthorRoleLabel((string) $role))
                        ->unique()
                        ->values();
                }

                return (object) [
                    'id' => $output->id,
                    'output_code' => $output->output_code,
                    'source_badge' => null,
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
                    'matched_author_roles' => $matchedAuthorRoles,
                    'displayed_at' => $output->updated_at ?? $output->created_at,
                    'created_at' => $output->created_at,
                    'sort_title' => mb_strtolower((string) $output->title),
                    'sort_journal' => mb_strtolower((string) $output->journal),
                    'sort_quartile' => mb_strtolower((string) $output->quartile),
                    'sort_year' => $yearBe ?? 0,
                ];
            });

        $papers = $projectPapers->merge($backOfficePapers)->merge($externalPapers);

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
            ->with(['bookAuthors:id,external_output_id,name']);

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
                    'project_link' => $output->task ? route('back_office.show', $output->task) : null,
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
                $authors = $output->bookAuthors->pluck('name')->filter()->values();

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

    private function canViewAllPersonalOutputs(?User $user): bool
    {
        return $user?->isAdminEquivalent() ?? false;
    }

    private function authorizePersonalOutputAccess(?User $viewer, User $subject): void
    {
        if (! $viewer) {
            abort(403);
        }

        if ($this->canViewAllPersonalOutputs($viewer)) {
            return;
        }

        if ((int) $viewer->id !== (int) $subject->id) {
            abort(403);
        }
    }

    private function buildPersonalUserStatsMap(Collection $users): Collection
    {
        $userIds = $users->pluck('id')->map(fn ($id) => (int) $id)->filter()->values();
        if ($userIds->isEmpty()) {
            return collect();
        }

        $statsMap = $userIds->mapWithKeys(fn (int $userId) => [$userId => $this->emptyPersonalStats()]);

        $nameUserIdsMap = [];
        foreach ($users as $user) {
            foreach (array_unique(array_filter([
                $this->normalizePersonNameForMatch($user->full_name),
                $this->normalizePersonNameForMatch((string) $user->name),
                $this->normalizePersonNameForMatch($user->research_display_name),
                $this->normalizePersonNameForMatch((string) ($user->name_en ?? '')),
            ])) as $normalizedName) {
                $nameUserIdsMap[$normalizedName] ??= [];
                $nameUserIdsMap[$normalizedName][] = (int) $user->id;
            }
        }

        $projectUserIdsMap = [];
        Project::query()
            ->select([
                'id',
                'pi_name',
            ])
            ->with([
                'copis:id,project_id,name',
                'coResearches:id,project_id,name',
            ])
            ->get()
            ->each(function (Project $project) use (&$statsMap, &$projectUserIdsMap, $nameUserIdsMap) {
                $projectId = (int) $project->id;

                foreach ($this->resolveMatchedUserIds($project->pi_name, $nameUserIdsMap) as $userId) {
                    $stats = $statsMap[$userId];
                    $stats['pi_projects']++;
                    $statsMap[$userId] = $stats;
                    $projectUserIdsMap[$projectId][$userId] = true;
                }

                foreach ($project->copis as $copi) {
                    foreach ($this->resolveMatchedUserIds($copi->name ?? null, $nameUserIdsMap) as $userId) {
                        $stats = $statsMap[$userId];
                        $stats['co_pi_projects']++;
                        $statsMap[$userId] = $stats;
                        $projectUserIdsMap[$projectId][$userId] = true;
                    }
                }

                foreach ($project->coResearches as $coResearch) {
                    foreach ($this->resolveMatchedUserIds($coResearch->name ?? null, $nameUserIdsMap) as $userId) {
                        $stats = $statsMap[$userId];
                        $stats['co_research_projects']++;
                        $statsMap[$userId] = $stats;
                        $projectUserIdsMap[$projectId][$userId] = true;
                    }
                }
            });

        foreach ($projectUserIdsMap as $projectId => $matchedUserIds) {
            foreach (array_keys($matchedUserIds) as $userId) {
                $stats = $statsMap[$userId];
                $stats['total_projects']++;
                $statsMap[$userId] = $stats;
            }
        }

        BackOfficeTask::query()
            ->select(['id'])
            ->with(['entries:id,task_id,person_name'])
            ->get()
            ->each(function (BackOfficeTask $task) use (&$statsMap, $nameUserIdsMap) {
                $matchedUserIds = $task->entries
                    ->flatMap(fn ($entry) => $this->resolveMatchedUserIds($entry->person_name ?? null, $nameUserIdsMap))
                    ->unique()
                    ->values();

                foreach ($matchedUserIds as $userId) {
                    if (! $statsMap->has((int) $userId)) {
                        continue;
                    }

                    $stats = $statsMap[(int) $userId];
                    $stats['total_projects']++;
                    $statsMap[(int) $userId] = $stats;
                }
            });

        $this->applyCountMapToStats(
            $statsMap,
            DB::table('output_authors')
                ->join('outputs', 'outputs.id', '=', 'output_authors.output_id')
                ->whereIn('output_authors.user_id', $userIds)
                ->where('outputs.type', 'like', 'EA101%')
                ->groupBy('output_authors.user_id')
                ->pluck(DB::raw('COUNT(*)'), 'output_authors.user_id'),
            'research_outputs'
        );

        $this->applyCountMapToStats(
            $statsMap,
            DB::table('output_conference_presenters')
                ->join('output_conferences', 'output_conferences.id', '=', 'output_conference_presenters.output_conference_id')
                ->join('outputs', 'outputs.id', '=', 'output_conferences.output_id')
                ->whereIn('output_conference_presenters.user_id', $userIds)
                ->where('outputs.type', 'like', 'EA103%')
                ->groupBy('output_conference_presenters.user_id')
                ->pluck(DB::raw('COUNT(*)'), 'output_conference_presenters.user_id'),
            'presentation_outputs'
        );

        $this->applyCountMapToStats(
            $statsMap,
            DB::table('output_book_authors')
                ->join('outputs', 'outputs.id', '=', 'output_book_authors.output_id')
                ->whereIn('output_book_authors.user_id', $userIds)
                ->where('outputs.type', 'like', 'EA119%')
                ->groupBy('output_book_authors.user_id')
                ->pluck(DB::raw('COUNT(*)'), 'output_book_authors.user_id'),
            'textbook_outputs'
        );

        $this->applyCountMapToStats(
            $statsMap,
            DB::table('output_award_recipients')
                ->join('outputs', 'outputs.id', '=', 'output_award_recipients.output_id')
                ->whereIn('output_award_recipients.user_id', $userIds)
                ->where('outputs.type', 'like', 'EA118%')
                ->groupBy('output_award_recipients.user_id')
                ->pluck(DB::raw('COUNT(*)'), 'output_award_recipients.user_id'),
            'award_outputs'
        );

        $this->applyCountMapToStats(
            $statsMap,
            DB::table('external_output_authors')
                ->join('external_outputs', 'external_outputs.id', '=', 'external_output_authors.external_output_id')
                ->whereIn('external_output_authors.user_id', $userIds)
                ->where('external_outputs.type', 'EA101')
                ->groupBy('external_output_authors.user_id')
                ->pluck(DB::raw('COUNT(*)'), 'external_output_authors.user_id'),
            'research_outputs'
        );

        $this->applyCountMapToStats(
            $statsMap,
            DB::table('external_output_conference_presenters')
                ->join('external_output_conferences', 'external_output_conferences.id', '=', 'external_output_conference_presenters.external_output_conference_id')
                ->join('external_outputs', 'external_outputs.id', '=', 'external_output_conferences.external_output_id')
                ->whereIn('external_output_conference_presenters.user_id', $userIds)
                ->where('external_outputs.type', 'EA103')
                ->groupBy('external_output_conference_presenters.user_id')
                ->pluck(DB::raw('COUNT(*)'), 'external_output_conference_presenters.user_id'),
            'presentation_outputs'
        );

        $this->applyCountMapToStats(
            $statsMap,
            DB::table('external_output_book_authors')
                ->join('external_outputs', 'external_outputs.id', '=', 'external_output_book_authors.external_output_id')
                ->whereIn('external_output_book_authors.user_id', $userIds)
                ->where('external_outputs.type', 'EA119')
                ->groupBy('external_output_book_authors.user_id')
                ->pluck(DB::raw('COUNT(*)'), 'external_output_book_authors.user_id'),
            'textbook_outputs'
        );

        $this->applyCountMapToStats(
            $statsMap,
            DB::table('external_output_award_recipients')
                ->join('external_outputs', 'external_outputs.id', '=', 'external_output_award_recipients.external_output_id')
                ->whereIn('external_output_award_recipients.user_id', $userIds)
                ->where('external_outputs.type', 'EA118')
                ->groupBy('external_output_award_recipients.user_id')
                ->pluck(DB::raw('COUNT(*)'), 'external_output_award_recipients.user_id'),
            'award_outputs'
        );

        BackOfficeTask::query()
            ->with(['entries:id,task_id,person_name', 'outputs:id,task_id,type,conferences,authors,book_authors'])
            ->get()
            ->each(function (BackOfficeTask $task) use (&$statsMap, $nameUserIdsMap) {
                foreach ($task->outputs as $output) {
                    $matchedUserIds = collect($this->resolveMatchedBackOfficeOutputUserIds($output, $nameUserIdsMap, $task))
                        ->unique()
                        ->values();

                    if ($matchedUserIds->isEmpty()) {
                        continue;
                    }

                    $typeCode = $this->extractOutputTypeCode($output->type);
                    if ($typeCode === null) {
                        continue;
                    }

                    $field = match ($typeCode) {
                        'EA101' => 'research_outputs',
                        'EA103' => 'presentation_outputs',
                        'EA118' => 'award_outputs',
                        'EA119' => 'textbook_outputs',
                        default => null,
                    };

                    if ($field === null) {
                        continue;
                    }

                    $increment = $typeCode === 'EA103'
                        ? count(is_array($output->conferences) ? $output->conferences : [])
                        : 1;

                    if ($increment <= 0) {
                        continue;
                    }

                    foreach ($matchedUserIds as $userId) {
                        $stats = $statsMap[(int) $userId];
                        $stats[$field] += $increment;
                        $statsMap[(int) $userId] = $stats;
                    }
                }
            });

        return $statsMap->map(function (array $stats) {
            $stats['total_outputs'] = $stats['research_outputs'] + $stats['presentation_outputs'] + $stats['textbook_outputs'] + $stats['award_outputs'];

            return $stats;
        });
    }

    private function applyCountMapToStats(Collection &$statsMap, $countMap, string $field): void
    {
        foreach ($countMap as $userId => $count) {
            $userId = (int) $userId;
            if (! $statsMap->has($userId)) {
                continue;
            }

            $stats = $statsMap[$userId];
            $stats[$field] += (int) $count;
            $statsMap[$userId] = $stats;
        }
    }

    private function resolveMatchedUserIds(?string $candidate, array $nameUserIdsMap): array
    {
        $normalizedCandidate = $this->normalizePersonNameForMatch($candidate);
        if ($normalizedCandidate === '') {
            return [];
        }

        return array_values(array_unique($nameUserIdsMap[$normalizedCandidate] ?? []));
    }

    private function resolveMatchedBackOfficeOutputUserIds(BackOfficeTaskOutput $output, array $nameUserIdsMap, ?BackOfficeTask $task = null): array
    {
        $matchedUserIds = $this->extractBackOfficeOutputContributorNames($output)
            ->flatMap(fn ($name) => $this->resolveMatchedUserIds($name, $nameUserIdsMap))
            ->unique()
            ->values();

        if ($matchedUserIds->isNotEmpty()) {
            return $matchedUserIds->all();
        }

        $task ??= $output->task;

        return collect($task?->entries ?? [])
            ->flatMap(fn ($entry) => $this->resolveMatchedUserIds($entry->person_name ?? null, $nameUserIdsMap))
            ->unique()
            ->values()
            ->all();
    }

    private function extractBackOfficeOutputContributorNames(BackOfficeTaskOutput $output): Collection
    {
        $authorNames = collect($output->authors ?? [])
            ->map(function ($author) {
                if (is_array($author)) {
                    return trim((string) ($author['name'] ?? ''));
                }

                return trim((string) $author);
            });

        $bookAuthorNames = collect($output->book_authors ?? [])
            ->map(function ($author) {
                if (is_array($author)) {
                    return trim((string) ($author['name'] ?? ''));
                }

                return trim((string) $author);
            });

        $presenterNames = collect($output->conferences ?? [])
            ->map(function ($conference) {
                $conference = is_array($conference) ? $conference : [];

                return trim((string) ($conference['presenter_name'] ?? ''));
            });

        return $authorNames
            ->concat($bookAuthorNames)
            ->concat($presenterNames)
            ->filter()
            ->unique()
            ->values();
    }

    private function buildPersonalOutputPayload(User $user): array
    {
        $projectRows = $this->buildPersonalProjectRows($user);
        $researchRows = $this->buildPersonalResearchRows($user);
        $presentationRows = $this->buildPersonalPresentationRows($user);
        $textbookRows = $this->buildPersonalTextbookRows($user);
        $awardRows = $this->buildPersonalAwardRows($user);
        $projectStatRows = $projectRows->where('source_label', 'Project')->values();

        $stats = [
            'pi_projects' => (int) $projectStatRows->filter(fn ($row) => in_array('PI', $row->roles, true))->count(),
            'co_pi_projects' => (int) $projectStatRows->filter(fn ($row) => in_array('Co-PI', $row->roles, true))->count(),
            'co_research_projects' => (int) $projectStatRows->filter(fn ($row) => in_array('Co-Researcher', $row->roles, true))->count(),
            'research_outputs' => (int) $researchRows->count(),
            'presentation_outputs' => (int) $presentationRows->count(),
            'textbook_outputs' => (int) $textbookRows->count(),
            'award_outputs' => (int) $awardRows->count(),
        ];
        $stats['total_outputs'] = $stats['research_outputs'] + $stats['presentation_outputs'] + $stats['textbook_outputs'] + $stats['award_outputs'];
        $stats['total_projects'] = (int) $projectRows->count();

        return [
            'subject' => $user,
            'subjectRouteKey' => $this->personalOutputRouteKey($user),
            'stats' => $stats,
            'projectRows' => $projectRows,
            'researchRows' => $researchRows,
            'presentationRows' => $presentationRows,
            'textbookRows' => $textbookRows,
            'awardRows' => $awardRows,
            'profilePhotoUrl' => $this->resolveUserProfilePhotoUrl($user),
        ];
    }

    private function buildPersonalProjectRows(User $user): Collection
    {
        $rows = collect();
        $matchesUser = fn (?string $value) => $this->matchesUserThaiName($value, $user);

        Project::query()
            ->select([
                'id',
                'project_code',
                'project_name_th',
                'project_type',
                'theme',
                'start_year',
                'start_date',
                'execution_status',
                'planning_status',
                'review_status',
                'closure_status',
                'pi_name',
            ])
            ->get()
            ->filter(fn (Project $project) => $matchesUser($project->pi_name))
            ->each(function (Project $project) use (&$rows) {
                $rows->push($this->formatPersonalProjectRow($project, 'PI'));
            });

        Project::query()
            ->select([
                'id',
                'project_code',
                'project_name_th',
                'project_type',
                'theme',
                'start_year',
                'start_date',
                'execution_status',
                'planning_status',
                'review_status',
                'closure_status',
            ])
            ->with('copis:id,project_id,name')
            ->get()
            ->filter(function (Project $project) use ($matchesUser) {
                return $project->copis->contains(fn ($copi) => $matchesUser($copi->name));
            })
            ->each(function (Project $project) use (&$rows) {
                $rows->push($this->formatPersonalProjectRow($project, 'Co-PI'));
            });

        Project::query()
            ->select([
                'id',
                'project_code',
                'project_name_th',
                'project_type',
                'theme',
                'start_year',
                'start_date',
                'execution_status',
                'planning_status',
                'review_status',
                'closure_status',
            ])
            ->with('coResearches:id,project_id,name')
            ->get()
            ->filter(function (Project $project) use ($matchesUser) {
                return $project->coResearches->contains(fn ($coResearch) => $matchesUser($coResearch->name));
            })
            ->each(function (Project $project) use (&$rows) {
                $rows->push($this->formatPersonalProjectRow($project, 'Co-Researcher'));
            });

        $projectRows = $rows
            ->groupBy('project_id')
            ->map(function (Collection $group) {
                $first = $group->first();
                $first->roles = $group->pluck('role')->unique()->values()->all();

                return $first;
            })
            ->values();

        $backOfficeRows = BackOfficeTask::query()
            ->with(['entries:id,task_id,person_name', 'outputs:id,task_id,year', 'creator:id,prefix,name'])
            ->get()
            ->filter(function (BackOfficeTask $task) use ($user) {
                return $task->entries->contains(fn ($entry) => $this->matchesUserThaiName($entry->person_name ?? null, $user));
            })
            ->map(function (BackOfficeTask $task) {
                $yearBe = $task->outputs
                    ->map(fn ($output) => $this->normalizeYearFilter($output->year))
                    ->filter()
                    ->max();

                if ($yearBe === null && filled($task->start_date)) {
                    try {
                        $year = Carbon::parse($task->start_date)->year;
                        $yearBe = $year < 2400 ? $year + 543 : $year;
                    } catch (\Throwable $exception) {
                        $yearBe = null;
                    }
                }

                return (object) [
                    'project_id' => 'backoffice-' . $task->id,
                    'project_name' => trim((string) ($task->title ?? '')) ?: 'งานนอกโครงการ',
                    'project_code' => null,
                    'year_be' => $yearBe,
                    'execution_status' => 'งานนอกโครงการ',
                    'content_theme' => 'งานนอกโครงการวิจัย',
                    'project_type_label' => 'Back Office Task',
                    'role' => 'Back Office',
                    'roles' => ['Back Office'],
                    'sort_year' => $yearBe ?? 0,
                    'link' => route('back_office.show', $task),
                    'source_label' => 'Back Office',
                ];
            });

        return $projectRows->concat($backOfficeRows)
            ->sortByDesc(fn ($row) => $row->sort_year)
            ->values();
    }

    private function buildPersonalResearchRows(User $user): Collection
    {
        $projectRows = Output::query()
            ->where('type', 'like', 'EA101%')
            ->whereHas('authors', function (Builder $query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with([
                'authors:id,output_id,user_id,role,name',
                'project:id,project_name_th,project_code',
            ])
            ->get()
            ->map(function (Output $output) use ($user) {
                $userRoles = $output->authors
                    ->where('user_id', $user->id)
                    ->pluck('role')
                    ->filter()
                    ->map(fn ($role) => $this->formatAuthorRoleLabel((string) $role))
                    ->unique()
                    ->values();

                return (object) [
                    'title' => trim((string) ($output->title ?? '')) ?: '-',
                    'year_be' => $this->normalizeYearFilter($output->year),
                    'journal' => trim((string) ($output->journal ?? '')) ?: '-',
                    'quartile' => trim((string) ($output->quartile ?? '')) ?: '-',
                    'impact_factor' => $output->impact_factor,
                    'roles' => $userRoles,
                    'author_line' => $output->authors->pluck('name')->filter()->implode(', '),
                    'source_label' => 'Project',
                    'reference_label' => trim((string) ($output->project?->project_name_th ?? '')) ?: '-',
                    'reference_link' => $output->project ? route('projects.show', $output->project) : null,
                    'link' => $this->extractFirstUrlFromText($output->project_output)
                        ?: ($output->project ? route('projects.show', $output->project) . '#output-' . $output->id : null),
                    'sort_year' => $this->normalizeYearFilter($output->year) ?? 0,
                ];
            });

        $backOfficeRows = BackOfficeTaskOutput::query()
            ->with(['task:id,backoffice_id,title', 'task.entries:id,task_id,person_name'])
            ->where('type', 'like', 'EA101%')
            ->get()
            ->filter(function (BackOfficeTaskOutput $output) use ($user) {
                $matchedContributor = $this->extractBackOfficeOutputContributorNames($output)
                    ->contains(fn ($name) => $this->matchesUserOutputContributor($name, $user));

                if ($matchedContributor) {
                    return true;
                }

                if ($this->extractBackOfficeOutputContributorNames($output)->isNotEmpty()) {
                    return false;
                }

                return collect($output->task?->entries ?? [])
                    ->contains(fn ($entry) => $this->matchesUserThaiName($entry->person_name ?? null, $user));
            })
            ->map(function (BackOfficeTaskOutput $output) use ($user) {
                $authors = collect($output->authors ?? [])
                    ->map(function ($author) {
                        if (is_array($author)) {
                            return [
                                'role' => trim((string) ($author['role'] ?? '')),
                                'name' => trim((string) ($author['name'] ?? '')),
                            ];
                        }

                        return [
                            'role' => '',
                            'name' => trim((string) $author),
                        ];
                    })
                    ->filter(fn ($author) => $author['name'] !== '')
                    ->values();

                $userRoles = $authors
                    ->filter(fn ($author) => $this->matchesUserOutputContributor($author['name'] ?? null, $user))
                    ->pluck('role')
                    ->filter()
                    ->map(fn ($role) => $this->formatAuthorRoleLabel((string) $role))
                    ->unique()
                    ->values();

                if ($userRoles->isEmpty() && $this->extractBackOfficeOutputContributorNames($output)->isEmpty()) {
                    $userRoles = $authors
                        ->pluck('role')
                        ->filter()
                        ->map(fn ($role) => $this->formatAuthorRoleLabel((string) $role))
                        ->unique()
                        ->values();
                }

                return (object) [
                    'title' => trim((string) ($output->title ?? '')) ?: '-',
                    'year_be' => $this->normalizeYearFilter($output->year),
                    'journal' => trim((string) ($output->journal ?? '')) ?: '-',
                    'quartile' => trim((string) ($output->quartile ?? '')) ?: '-',
                    'impact_factor' => $output->impact_factor,
                    'roles' => $userRoles,
                    'author_line' => $authors->pluck('name')->implode(', '),
                    'source_label' => 'Back Office',
                    'reference_label' => trim((string) ($output->task?->title ?? '')) ?: 'งานนอกโครงการวิจัย',
                    'reference_link' => $output->task ? route('back_office.show', $output->task) : null,
                    'link' => $this->extractFirstUrlFromText($output->project_output)
                        ?: ($output->task ? route('back_office.show', $output->task) : null),
                    'sort_year' => $this->normalizeYearFilter($output->year) ?? 0,
                ];
            });

        $externalRows = ExternalOutput::query()
            ->where('type', 'EA101')
            ->whereHas('authors', function (Builder $query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with(['authors:id,external_output_id,user_id,role,name'])
            ->get()
            ->map(function (ExternalOutput $output) use ($user) {
                $userRoles = $output->authors
                    ->where('user_id', $user->id)
                    ->pluck('role')
                    ->filter()
                    ->map(fn ($role) => $this->formatAuthorRoleLabel((string) $role))
                    ->unique()
                    ->values();
                $yearBe = $output->publish_date ? ((int) $output->publish_date->format('Y')) + 543 : null;

                return (object) [
                    'title' => trim((string) ($output->title ?? '')) ?: '-',
                    'year_be' => $yearBe,
                    'journal' => trim((string) ($output->journal ?? '')) ?: '-',
                    'quartile' => trim((string) ($output->quartile ?? '')) ?: '-',
                    'impact_factor' => $output->impact_factor,
                    'roles' => $userRoles,
                    'author_line' => $output->authors->pluck('name')->filter()->implode(', '),
                    'source_label' => 'Extra',
                    'reference_label' => trim((string) ($output->job_category ?? '')) ?: 'External Academic',
                    'reference_link' => route('outputs.external-academic.show', $output),
                    'link' => $this->extractFirstUrlFromText($output->note)
                        ?: route('outputs.external-academic.show', $output),
                    'sort_year' => $yearBe ?? 0,
                ];
            });

        return collect($projectRows)->concat($backOfficeRows)->concat($externalRows)
            ->sortByDesc(fn ($row) => $row->sort_year)
            ->values();
    }

    private function buildPersonalPresentationRows(User $user): Collection
    {
        $projectRows = OutputConferencePresenter::query()
            ->where('user_id', $user->id)
            ->whereHas('conference.output', function (Builder $query) {
                $query->where('type', 'like', 'EA103%');
            })
            ->with(['conference.output:id,project_id,title,year,project_output', 'conference.output.project:id,project_name_th,project_code', 'conference:id,output_id,name,presentation_type,location'])
            ->get()
            ->map(function (OutputConferencePresenter $item) {
                $conference = $item->conference;
                $output = $conference?->output;
                $yearBe = $this->normalizeYearFilter($output?->year);

                return (object) [
                    'title' => trim((string) ($output?->title ?? '')) ?: '-',
                    'conference_name' => trim((string) ($conference?->name ?? '')) ?: '-',
                    'presentation_type' => trim((string) ($conference?->presentation_type ?? '')) ?: '-',
                    'location' => trim((string) ($conference?->location ?? '')) ?: null,
                    'presenter_name' => trim((string) ($item->name ?? '')) ?: $output?->project?->project_name_th,
                    'year_be' => $yearBe,
                    'source_label' => 'Project',
                    'reference_label' => trim((string) ($output?->project?->project_name_th ?? '')) ?: '-',
                    'link' => $this->extractFirstUrlFromText($output?->project_output)
                        ?: ($output?->project ? route('projects.show', $output->project) . '#output-' . $output->id : null),
                    'sort_year' => $yearBe ?? 0,
                ];
            });

        $backOfficeRows = BackOfficeTaskOutput::query()
            ->with(['task:id,backoffice_id,title', 'task.entries:id,task_id,person_name'])
            ->where('type', 'like', 'EA103%')
            ->get()
            ->flatMap(function (BackOfficeTaskOutput $output) use ($user) {
                $contributorNames = $this->extractBackOfficeOutputContributorNames($output);
                $matchesContributor = $contributorNames
                    ->contains(fn ($name) => $this->matchesUserOutputContributor($name, $user));
                $matchesTaskPerson = collect($output->task?->entries ?? [])
                    ->contains(fn ($entry) => $this->matchesUserThaiName($entry->person_name ?? null, $user));

                if (! $matchesContributor && ($contributorNames->isNotEmpty() || ! $matchesTaskPerson)) {
                    return collect();
                }

                $authors = collect($output->authors ?? [])
                    ->map(function ($author) {
                        if (is_array($author)) {
                            return trim((string) ($author['name'] ?? ''));
                        }

                        return trim((string) $author);
                    })
                    ->filter()
                    ->values();

                return collect($output->conferences ?? [])
                    ->map(function ($conference) use ($output, $user, $authors) {
                        $conference = is_array($conference) ? $conference : [];
                        $presenter = trim((string) (($conference['presenter_name'] ?? $conference['note'] ?? '') ?: ''));

                        return (object) [
                            'title' => trim((string) ($output->title ?? '')) ?: '-',
                            'conference_name' => trim((string) (($conference['name'] ?? '') ?: '-')),
                            'presentation_type' => trim((string) (($conference['presentation_type'] ?? '') ?: '-')),
                            'location' => trim((string) (($conference['location'] ?? '') ?: '')) ?: null,
                            'presenter_name' => $presenter !== '' ? $presenter : ($authors->first() ?: $user->full_name),
                            'year_be' => $this->normalizeYearFilter($output->year),
                            'source_label' => 'Back Office',
                            'reference_label' => trim((string) ($output->task?->title ?? '')) ?: 'งานนอกโครงการวิจัย',
                            'link' => $this->extractFirstUrlFromText($output->project_output)
                                ?: ($output->task ? route('back_office.show', $output->task) : null),
                            'sort_year' => $this->normalizeYearFilter($output->year) ?? 0,
                        ];
                    });
            })
            ->values();

        $externalRows = ExternalOutputConferencePresenter::query()
            ->where('user_id', $user->id)
            ->whereHas('conference.externalOutput', function (Builder $query) {
                $query->where('type', 'EA103');
            })
            ->with(['conference.externalOutput:id,title,publish_date,job_category,note', 'conference:id,external_output_id,name,presentation_type,location'])
            ->get()
            ->map(function (ExternalOutputConferencePresenter $item) use ($user) {
                $conference = $item->conference;
                $externalOutput = $conference?->externalOutput;
                $yearBe = $externalOutput?->publish_date ? ((int) $externalOutput->publish_date->format('Y')) + 543 : null;

                return (object) [
                    'title' => trim((string) ($externalOutput?->title ?? '')) ?: '-',
                    'conference_name' => trim((string) ($conference?->name ?? '')) ?: '-',
                    'presentation_type' => trim((string) ($conference?->presentation_type ?? '')) ?: '-',
                    'location' => trim((string) ($conference?->location ?? '')) ?: null,
                    'presenter_name' => trim((string) ($item->name ?? '')) ?: $user->full_name,
                    'year_be' => $yearBe,
                    'source_label' => 'Extra',
                    'reference_label' => trim((string) ($externalOutput?->job_category ?? '')) ?: 'External Academic',
                    'link' => $this->extractFirstUrlFromText($externalOutput?->note)
                        ?: ($externalOutput ? route('outputs.external-academic.show', $externalOutput) : null),
                    'sort_year' => $yearBe ?? 0,
                ];
            });

        return collect($projectRows)->concat($backOfficeRows)->concat($externalRows)
            ->sortByDesc(fn ($row) => $row->sort_year)
            ->values();
    }

    private function buildPersonalTextbookRows(User $user): Collection
    {
        $projectRows = OutputBookAuthor::query()
            ->where('user_id', $user->id)
            ->whereHas('output', function (Builder $query) {
                $query->where('type', 'like', 'EA119%');
            })
            ->with(['output:id,project_id,title,year,book_title,publisher_name,chapter_pages,project_output', 'output.project:id,project_name_th,project_code'])
            ->get()
            ->map(function (OutputBookAuthor $item) {
                $yearBe = $this->normalizeYearFilter($item->output?->year);

                return (object) [
                    'title' => trim((string) ($item->output?->title ?? '')) ?: '-',
                    'book_title' => trim((string) ($item->output?->book_title ?? '')) ?: '-',
                    'publisher_name' => trim((string) ($item->output?->publisher_name ?? '')) ?: '-',
                    'chapter_pages' => trim((string) ($item->output?->chapter_pages ?? '')) ?: null,
                    'year_be' => $yearBe,
                    'source_label' => 'Project',
                    'reference_label' => trim((string) ($item->output?->project?->project_name_th ?? '')) ?: '-',
                    'link' => $this->extractFirstUrlFromText($item->output?->project_output)
                        ?: ($item->output?->project ? route('projects.show', $item->output->project) . '#output-' . $item->output->id : null),
                    'sort_year' => $yearBe ?? 0,
                ];
            });

        $backOfficeRows = BackOfficeTaskOutput::query()
            ->with(['task:id,backoffice_id,title', 'task.entries:id,task_id,person_name'])
            ->where('type', 'like', 'EA119%')
            ->get()
            ->filter(function (BackOfficeTaskOutput $output) use ($user) {
                $matchedContributor = $this->extractBackOfficeOutputContributorNames($output)
                    ->contains(fn ($name) => $this->matchesUserOutputContributor($name, $user));

                if ($matchedContributor) {
                    return true;
                }

                if ($this->extractBackOfficeOutputContributorNames($output)->isNotEmpty()) {
                    return false;
                }

                return collect($output->task?->entries ?? [])
                    ->contains(fn ($entry) => $this->matchesUserThaiName($entry->person_name ?? null, $user));
            })
            ->map(function (BackOfficeTaskOutput $output) {
                return (object) [
                    'title' => trim((string) ($output->title ?? '')) ?: '-',
                    'book_title' => trim((string) ($output->book_title ?? '')) ?: '-',
                    'publisher_name' => trim((string) ($output->publisher_name ?? '')) ?: '-',
                    'chapter_pages' => trim((string) ($output->chapter_pages ?? '')) ?: null,
                    'year_be' => $this->normalizeYearFilter($output->year),
                    'source_label' => 'Back Office',
                    'reference_label' => trim((string) ($output->task?->title ?? '')) ?: 'งานนอกโครงการวิจัย',
                    'link' => $this->extractFirstUrlFromText($output->project_output)
                        ?: ($output->task ? route('back_office.show', $output->task) : null),
                    'sort_year' => $this->normalizeYearFilter($output->year) ?? 0,
                ];
            });

        $externalRows = ExternalOutputBookAuthor::query()
            ->where('user_id', $user->id)
            ->whereHas('externalOutput', function (Builder $query) {
                $query->where('type', 'EA119');
            })
            ->with(['externalOutput:id,title,publish_date,job_category,book_title,publisher_name,chapter_pages,note'])
            ->get()
            ->map(function (ExternalOutputBookAuthor $item) {
                $yearBe = $item->externalOutput?->publish_date ? ((int) $item->externalOutput->publish_date->format('Y')) + 543 : null;

                return (object) [
                    'title' => trim((string) ($item->externalOutput?->title ?? '')) ?: '-',
                    'book_title' => trim((string) ($item->externalOutput?->book_title ?? '')) ?: '-',
                    'publisher_name' => trim((string) ($item->externalOutput?->publisher_name ?? '')) ?: '-',
                    'chapter_pages' => trim((string) ($item->externalOutput?->chapter_pages ?? '')) ?: null,
                    'year_be' => $yearBe,
                    'source_label' => 'Extra',
                    'reference_label' => trim((string) ($item->externalOutput?->job_category ?? '')) ?: 'External Academic',
                    'link' => $this->extractFirstUrlFromText($item->externalOutput?->note)
                        ?: ($item->externalOutput ? route('outputs.external-academic.show', $item->externalOutput) : null),
                    'sort_year' => $yearBe ?? 0,
                ];
            });

        return collect($projectRows)->concat($backOfficeRows)->concat($externalRows)
            ->sortByDesc(fn ($row) => $row->sort_year)
            ->values();
    }

    private function buildPersonalAwardRows(User $user): Collection
    {
        $projectRows = OutputAwardRecipient::query()
            ->where('user_id', $user->id)
            ->whereHas('output', function (Builder $query) {
                $query->where('type', 'like', 'EA118%');
            })
            ->with(['output:id,project_id,title,year,project_output', 'output.project:id,project_name_th,project_code'])
            ->get()
            ->map(function (OutputAwardRecipient $item) {
                $yearBe = $this->normalizeYearFilter($item->output?->year);

                return (object) [
                    'title' => trim((string) ($item->output?->title ?? '')) ?: '-',
                    'note' => trim((string) ($item->output?->project_output ?? '')) ?: null,
                    'recipient_name' => trim((string) ($item->name ?? '')) ?: null,
                    'year_be' => $yearBe,
                    'source_label' => 'Project',
                    'reference_label' => trim((string) ($item->output?->project?->project_name_th ?? '')) ?: '-',
                    'link' => $this->extractFirstUrlFromText($item->output?->project_output)
                        ?: ($item->output?->project ? route('projects.show', $item->output->project) . '#output-' . $item->output->id : null),
                    'sort_year' => $yearBe ?? 0,
                ];
            });

        $externalRows = ExternalOutputAwardRecipient::query()
            ->where('user_id', $user->id)
            ->whereHas('externalOutput', function (Builder $query) {
                $query->where('type', 'EA118');
            })
            ->with(['externalOutput:id,title,publish_date,job_category,note'])
            ->get()
            ->map(function (ExternalOutputAwardRecipient $item) {
                $yearBe = $item->externalOutput?->publish_date ? ((int) $item->externalOutput->publish_date->format('Y')) + 543 : null;

                return (object) [
                    'title' => trim((string) ($item->externalOutput?->title ?? '')) ?: '-',
                    'note' => trim((string) ($item->externalOutput?->note ?? '')) ?: null,
                    'recipient_name' => trim((string) ($item->name ?? '')) ?: null,
                    'year_be' => $yearBe,
                    'source_label' => 'Extra',
                    'reference_label' => trim((string) ($item->externalOutput?->job_category ?? '')) ?: 'External Academic',
                    'link' => $this->extractFirstUrlFromText($item->externalOutput?->note)
                        ?: ($item->externalOutput ? route('outputs.external-academic.show', $item->externalOutput) : null),
                    'sort_year' => $yearBe ?? 0,
                ];
            });

        return collect($projectRows)->concat($externalRows)
            ->sortByDesc(fn ($row) => $row->sort_year)
            ->values();
    }

    private function buildPersonalInnovationRows(User $user): Collection
    {
        $projectRows = Output::query()
            ->where('type', 'like', 'EA105%')
            ->where(function (Builder $query) use ($user) {
                $query->whereHas('authors', function (Builder $subQuery) use ($user) {
                    $subQuery->where('user_id', $user->id);
                })->orWhereHas('project', function (Builder $subQuery) use ($user) {
                    $subQuery->where('pi_name', trim(($user->prefix ?? '') . ' ' . ($user->name ?? '')));
                });
            })
            ->with([
                'authors:id,output_id,user_id,role,name',
                'project:id,project_name_th,project_code,pi_name',
            ])
            ->get()
            ->map(function (Output $output) use ($user) {
                $authors = $output->authors
                    ->map(fn ($author) => trim((string) ($author->name ?? '')))
                    ->filter()
                    ->values();
                $userRoles = $output->authors
                    ->where('user_id', $user->id)
                    ->pluck('role')
                    ->filter()
                    ->map(fn ($role) => $this->formatAuthorRoleLabel((string) $role))
                    ->unique()
                    ->values();

                return (object) [
                    'title' => trim((string) ($output->title ?? '')) ?: '-',
                    'output_type' => 'Innovation / Website',
                    'year_be' => $this->normalizeYearFilter($output->year),
                    'contributors' => $authors->implode(', '),
                    'roles' => $userRoles,
                    'source_label' => 'Project',
                    'reference_label' => trim((string) ($output->project?->project_name_th ?? '')) ?: '-',
                    'link' => $this->extractFirstUrlFromText($output->project_output)
                        ?: ($output->project ? route('projects.show', $output->project) . '#output-' . $output->id : null),
                    'sort_year' => $this->normalizeYearFilter($output->year) ?? 0,
                ];
            });

        $backOfficeRows = BackOfficeTaskOutput::query()
            ->with(['task:id,backoffice_id,title', 'task.entries:id,task_id,person_name'])
            ->where('type', 'like', 'EA105%')
            ->get()
            ->filter(function (BackOfficeTaskOutput $output) use ($user) {
                return collect($output->task?->entries ?? [])
                    ->contains(fn ($entry) => $this->matchesUserThaiName($entry->person_name ?? null, $user));
            })
            ->map(function (BackOfficeTaskOutput $output) use ($user) {
                $contributors = collect($output->authors ?? [])
                    ->map(function ($author) {
                        if (is_array($author)) {
                            return trim((string) ($author['name'] ?? ''));
                        }

                        return trim((string) $author);
                    })
                    ->filter()
                    ->values();

                $userRoles = collect($output->authors ?? [])
                    ->map(function ($author) {
                        if (is_array($author)) {
                            return [
                                'name' => trim((string) ($author['name'] ?? '')),
                                'role' => trim((string) ($author['role'] ?? '')),
                            ];
                        }

                        return [
                            'name' => trim((string) $author),
                            'role' => '',
                        ];
                    })
                    ->filter(fn ($author) => $this->matchesUserOutputContributor($author['name'] ?? null, $user))
                    ->pluck('role')
                    ->filter()
                    ->map(fn ($role) => $this->formatAuthorRoleLabel((string) $role))
                    ->unique()
                    ->values();

                return (object) [
                    'title' => trim((string) ($output->title ?? '')) ?: '-',
                    'output_type' => 'Innovation / Website',
                    'year_be' => $this->normalizeYearFilter($output->year),
                    'contributors' => $contributors->implode(', '),
                    'roles' => $userRoles,
                    'source_label' => 'Back Office',
                    'reference_label' => trim((string) ($output->task?->title ?? '')) ?: 'งานนอกโครงการวิจัย',
                    'link' => $this->extractFirstUrlFromText($output->project_output)
                        ?: ($output->task ? route('back_office.show', $output->task) : null),
                    'sort_year' => $this->normalizeYearFilter($output->year) ?? 0,
                ];
            });

        $externalRows = ExternalOutput::query()
            ->where('type', 'EA105')
            ->whereHas('authors', function (Builder $query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with(['authors:id,external_output_id,user_id,role,name'])
            ->get()
            ->map(function (ExternalOutput $output) use ($user) {
                $yearBe = $output->publish_date ? ((int) $output->publish_date->format('Y')) + 543 : null;
                $contributors = $output->authors
                    ->map(fn ($author) => trim((string) ($author->name ?? '')))
                    ->filter()
                    ->values();
                $userRoles = $output->authors
                    ->where('user_id', $user->id)
                    ->pluck('role')
                    ->filter()
                    ->map(fn ($role) => $this->formatAuthorRoleLabel((string) $role))
                    ->unique()
                    ->values();

                return (object) [
                    'title' => trim((string) ($output->title ?? '')) ?: '-',
                    'output_type' => 'Innovation / Website',
                    'year_be' => $yearBe,
                    'contributors' => $contributors->implode(', '),
                    'roles' => $userRoles,
                    'source_label' => 'Extra',
                    'reference_label' => trim((string) ($output->job_category ?? '')) ?: 'External Academic',
                    'link' => $this->extractFirstUrlFromText($output->note)
                        ?: route('outputs.external-academic.show', $output),
                    'sort_year' => $yearBe ?? 0,
                ];
            });

        return collect($projectRows)->concat($backOfficeRows)->concat($externalRows)
            ->sortByDesc(fn ($row) => $row->sort_year)
            ->values();
    }

    private function formatPersonalProjectRow(Project $project, string $role): object
    {
        $yearBe = $this->extractProjectYearBe($project);

        return (object) [
            'project_id' => (int) $project->id,
            'project_name' => trim((string) ($project->project_name_th ?? '')) ?: '-',
            'project_code' => trim((string) ($project->project_code ?? '')) ?: null,
            'year_be' => $yearBe,
            'execution_status' => $this->resolveProjectStatusLabel($project),
            'content_theme' => trim((string) ($project->theme ?? '')) ?: '-',
            'project_type_label' => trim((string) ($project->project_type ?? '')) ?: '-',
            'role' => $role,
            'roles' => [$role],
            'sort_year' => $yearBe ?? 0,
            'link' => route('projects.show', $project),
            'source_label' => 'Project',
        ];
    }

    private function buildPersonalOtherRows(User $user): Collection
    {
        $excludedTypeCodes = ['EA101', 'EA103', 'EA118', 'EA119'];
        $projectIds = $this->personalProjectIds($user);

        $projectRows = $projectIds->isEmpty()
            ? collect()
            : Output::query()
                ->with(['project:id,project_name_th,project_code', 'authors:id,output_id,user_id,name'])
                ->whereIn('project_id', $projectIds)
                ->get()
                ->filter(function (Output $output) use ($excludedTypeCodes) {
                    $typeCode = $this->extractOutputTypeCode($output->type);
                    return $typeCode !== null && !in_array($typeCode, $excludedTypeCodes, true);
                })
                ->map(function (Output $output) {
                    $typeCode = $this->extractOutputTypeCode($output->type) ?? 'OTHER';
                    return (object) [
                        'title' => trim((string) ($output->title ?? '')) ?: '-',
                        'type_code' => $typeCode,
                        'type_label' => $this->resolveOutputTypeLabel($typeCode),
                        'year_be' => $this->normalizeYearFilter($output->year),
                        'contributors' => $output->authors->pluck('name')->filter()->implode(', '),
                        'source_label' => 'Project',
                        'reference_label' => trim((string) ($output->project?->project_name_th ?? '')) ?: '-',
                        'link' => $this->extractFirstUrlFromText($output->project_output)
                            ?: ($output->project ? route('projects.show', $output->project) . '#output-' . $output->id : null),
                        'sort_year' => $this->normalizeYearFilter($output->year) ?? 0,
                    ];
                });

        $backOfficeRows = BackOfficeTaskOutput::query()
            ->with(['task:id,backoffice_id,title', 'task.entries:id,task_id,person_name'])
            ->get()
            ->filter(function (BackOfficeTaskOutput $output) use ($user, $excludedTypeCodes) {
                $typeCode = $this->extractOutputTypeCode($output->type);
                if ($typeCode === null || in_array($typeCode, $excludedTypeCodes, true)) {
                    return false;
                }

                $matchedContributor = $this->extractBackOfficeOutputContributorNames($output)
                    ->contains(fn ($name) => $this->matchesUserOutputContributor($name, $user));

                if ($matchedContributor) {
                    return true;
                }

                if ($this->extractBackOfficeOutputContributorNames($output)->isNotEmpty()) {
                    return false;
                }

                return collect($output->task?->entries ?? [])
                    ->contains(fn ($entry) => $this->matchesUserThaiName($entry->person_name ?? null, $user));
            })
            ->map(function (BackOfficeTaskOutput $output) {
                $typeCode = $this->extractOutputTypeCode($output->type) ?? 'OTHER';
                $contributors = collect($output->authors ?? [])
                    ->map(function ($author) {
                        if (is_array($author)) {
                            return trim((string) ($author['name'] ?? ''));
                        }

                        return trim((string) $author);
                    })
                    ->filter()
                    ->implode(', ');

                return (object) [
                    'title' => trim((string) ($output->title ?? '')) ?: '-',
                    'type_code' => $typeCode,
                    'type_label' => $this->resolveOutputTypeLabel($typeCode),
                    'year_be' => $this->normalizeYearFilter($output->year),
                    'contributors' => $contributors,
                    'source_label' => 'Back Office',
                    'reference_label' => trim((string) ($output->task?->title ?? '')) ?: 'งานนอกโครงการวิจัย',
                    'link' => $this->extractFirstUrlFromText($output->project_output)
                        ?: ($output->task ? route('back_office.show', $output->task) : null),
                    'sort_year' => $this->normalizeYearFilter($output->year) ?? 0,
                ];
            });

        $externalRows = ExternalOutput::query()
            ->with(['authors:id,external_output_id,user_id,name'])
            ->whereHas('authors', function (Builder $query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->get()
            ->filter(function (ExternalOutput $output) use ($excludedTypeCodes) {
                $typeCode = $this->extractOutputTypeCode($output->type);
                return $typeCode !== null && !in_array($typeCode, $excludedTypeCodes, true);
            })
            ->map(function (ExternalOutput $output) {
                $typeCode = $this->extractOutputTypeCode($output->type) ?? 'OTHER';
                $yearBe = $output->publish_date ? ((int) $output->publish_date->format('Y')) + 543 : null;

                return (object) [
                    'title' => trim((string) ($output->title ?? '')) ?: '-',
                    'type_code' => $typeCode,
                    'type_label' => $this->resolveOutputTypeLabel($typeCode),
                    'year_be' => $yearBe,
                    'contributors' => $output->authors->pluck('name')->filter()->implode(', '),
                    'source_label' => 'Extra',
                    'reference_label' => trim((string) ($output->job_category ?? '')) ?: 'External Academic',
                    'link' => $this->extractFirstUrlFromText($output->note)
                        ?: route('outputs.external-academic.show', $output),
                    'sort_year' => $yearBe ?? 0,
                ];
            });

        return $projectRows->concat($backOfficeRows)->concat($externalRows)
            ->sortByDesc(fn ($row) => $row->sort_year)
            ->values();
    }

    private function personalProjectIds(User $user): Collection
    {
        $matchesUser = fn (?string $value) => $this->matchesUserThaiName($value, $user);

        $piProjectIds = Project::query()
            ->select(['id', 'pi_name'])
            ->get()
            ->filter(fn (Project $project) => $matchesUser($project->pi_name))
            ->pluck('id');

        $coPiProjectIds = Project::query()
            ->select('id')
            ->with('copis:id,project_id,name')
            ->get()
            ->filter(fn (Project $project) => $project->copis->contains(fn ($copi) => $matchesUser($copi->name)))
            ->pluck('id');

        $coResearchProjectIds = Project::query()
            ->select('id')
            ->with('coResearches:id,project_id,name')
            ->get()
            ->filter(fn (Project $project) => $project->coResearches->contains(fn ($coResearch) => $matchesUser($coResearch->name)))
            ->pluck('id');

        return $piProjectIds
            ->concat($coPiProjectIds)
            ->concat($coResearchProjectIds)
            ->filter()
            ->unique()
            ->values();
    }

    private function extractOutputTypeCode($rawType): ?string
    {
        $type = trim((string) ($rawType ?? ''));
        if ($type === '') {
            return null;
        }

        if (preg_match('/EA\d{3}/i', $type, $matches)) {
            return strtoupper($matches[0]);
        }

        return null;
    }

    private function resolveOutputTypeLabel(?string $typeCode): string
    {
        return [
            'EA101' => 'Research Paper or Journal Article',
            'EA102' => 'Reports and White Papers',
            'EA103' => 'Presentations in Academic Conferences',
            'EA104' => 'Data Sets and Databases',
            'EA105' => 'Inventions or Innovations (Application / Website)',
            'EA106' => 'Policy Recommendations',
            'EA107' => 'Educational Materials',
            'EA108' => 'Communication Materials (Creations)',
            'EA109' => 'Change in Practices / Procedures / Policy',
            'EA110' => 'Number of Participants (Persons)',
            'EA111' => 'Satisfaction Rating (1-5)',
            'EA112' => 'Present to Committee Meeting / Policy Maker / Board',
            'EA113' => 'Appointment Orders (SIHP as Committee / Secretary)',
            'EA114' => 'Events / Forum',
            'EA115' => 'Capacity Building / Knowledge Sharing',
            'EA116' => 'Research Project',
            'EA117' => 'MOU',
            'EA118' => 'Award and Recognition',
            'EA119' => 'Textbook / Book Chapter',
            'EA120' => 'Manual / Instruction / Guideline',
            'EA999' => 'Other Outputs',
            'OTHER' => 'Other Outputs',
        ][$typeCode ?? 'OTHER'] ?? ($typeCode ?: 'Other Outputs');
    }

    private function extractProjectYearBe(Project $project): ?int
    {
        $startYear = $this->normalizeYearFilter($project->start_year);
        if ($startYear !== null) {
            return $startYear;
        }

        $startDate = trim((string) ($project->start_date ?? ''));
        if ($startDate === '') {
            return null;
        }

        try {
            $year = Carbon::parse($startDate)->year;
            return $year < 2400 ? $year + 543 : $year;
        } catch (\Throwable $exception) {
            return null;
        }
    }

    private function formatAuthorRoleLabel(string $role): string
    {
        return match (strtolower(trim($role))) {
            'first' => 'First Author',
            'co' => 'Co-Author',
            'corresponding' => 'Corresponding Author',
            'awardee' => 'Awardee',
            default => ucfirst($role),
        };
    }

    private function emptyPersonalStats(): array
    {
        return [
            'pi_projects' => 0,
            'co_pi_projects' => 0,
            'co_research_projects' => 0,
            'research_outputs' => 0,
            'presentation_outputs' => 0,
            'textbook_outputs' => 0,
            'award_outputs' => 0,
            'total_outputs' => 0,
            'total_projects' => 0,
        ];
    }

    private function matchesUserThaiName(?string $candidate, User $user): bool
    {
        $normalizedCandidate = $this->normalizePersonNameForMatch($candidate);
        if ($normalizedCandidate === '') {
            return false;
        }

        return in_array($normalizedCandidate, [
            $this->normalizePersonNameForMatch($user->full_name),
            $this->normalizePersonNameForMatch((string) $user->name),
        ], true);
    }

    private function matchesUserOutputContributor(?string $candidate, User $user): bool
    {
        $normalizedCandidate = $this->normalizePersonNameForMatch($candidate);
        if ($normalizedCandidate === '') {
            return false;
        }

        return in_array($normalizedCandidate, array_filter([
            $this->normalizePersonNameForMatch($user->full_name),
            $this->normalizePersonNameForMatch((string) $user->name),
            $this->normalizePersonNameForMatch($user->research_display_name),
            $this->normalizePersonNameForMatch((string) ($user->name_en ?? '')),
        ]), true);
    }

    private function normalizePersonNameForMatch(?string $value): string
    {
        $value = mb_strtolower(trim((string) $value), 'UTF-8');
        $value = preg_replace('/\s+/u', '', $value) ?? '';

        return $value;
    }

    private function extractFirstUrlFromText(?string $text): ?string
    {
        $value = trim((string) $text);
        if ($value === '') {
            return null;
        }

        if (preg_match('/https?:\/\/[^\s<>"\'\]\)]+/iu', $value, $matches) === 1) {
            return trim((string) ($matches[0] ?? '')) ?: null;
        }

        return null;
    }

    private function stripUrlsFromText(?string $text): string
    {
        $value = trim((string) $text);
        if ($value === '') {
            return '';
        }

        $cleaned = preg_replace('/https?:\/\/[^\s<>"\'\]\)]+/iu', '', $value) ?? $value;
        $cleaned = preg_replace('/\s*\|\s*/u', ' | ', $cleaned) ?? $cleaned;
        $cleaned = preg_replace('/\s{2,}/u', ' ', $cleaned) ?? $cleaned;

        return trim((string) $cleaned, " \t\n\r\0\x0B|");
    }

    private function resolveProjectStatusLabel(Project $project): ?string
    {
        foreach ([
            $project->execution_status ?? null,
            $project->planning_status ?? null,
            $project->review_status ?? null,
            $project->closure_status ?? null,
        ] as $candidate) {
            $value = trim((string) $candidate);
            if ($value !== '' && ! is_numeric($value)) {
                return $value;
            }
        }

        return null;
    }

    private function resolvePersonalOutputUser(string $person): User
    {
        $lookup = trim($person);

        if ($lookup === '') {
            abort(404);
        }

        if (ctype_digit($lookup)) {
            return User::query()->findOrFail((int) $lookup);
        }

        $matchedUsers = User::query()
            ->select(['id', 'prefix', 'name', 'prefix_en', 'name_en', 'role', 'position', 'profile_photo_path', 'updated_at'])
            ->get()
            ->filter(fn (User $user) => trim((string) $user->full_name) === $lookup)
            ->values();

        if ($matchedUsers->count() === 1) {
            return $matchedUsers->first();
        }

        abort(404);
    }

    private function personalOutputRouteKey(User $user): string
    {
        return trim((string) $user->full_name);
    }

    private function resolveUserProfilePhotoPathForPdf(User $user): ?string
    {
        $relativePath = trim((string) ($user->profile_photo_path ?? ''));
        $candidates = [];

        if ($relativePath !== '') {
            $candidates = [
                storage_path('app/public/' . ltrim($relativePath, '/')),
                public_path('storage/' . ltrim($relativePath, '/')),
            ];
        }

        $candidates[] = public_path('images/default-avatar.png');

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function resolveUserProfilePhotoDataUriForPdf(User $user): ?string
    {
        $path = $this->resolveUserProfilePhotoPathForPdf($user);
        if (! $path || ! is_file($path)) {
            return null;
        }

        $mime = mime_content_type($path) ?: 'image/png';
        $contents = @file_get_contents($path);

        if ($contents === false) {
            return null;
        }

        return 'data:' . $mime . ';base64,' . base64_encode($contents);
    }

    private function resolveUserProfilePhotoUrl(User $user): string
    {
        $relativePath = trim((string) ($user->profile_photo_path ?? ''));

        if ($relativePath !== '') {
            return asset('storage/' . ltrim($relativePath, '/')) . '?v=' . optional($user->updated_at)->timestamp;
        }

        return 'https://ui-avatars.com/api/?name=' . urlencode($user->full_name) . '&background=0F172A&color=fff&size=128';
    }

    private function buildOutputUserDirectory(): Collection
    {
        return User::query()
            ->select(['id', 'prefix', 'name', 'prefix_en', 'name_en'])
            ->orderBy('name')
            ->get()
            ->map(function (User $user) {
                return [
                    'id' => $user->id,
                    'full_name' => $user->full_name,
                    'research_display_name' => $user->research_display_name,
                ];
            })
            ->values();
    }
}
