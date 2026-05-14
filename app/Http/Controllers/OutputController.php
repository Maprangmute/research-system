<?php

namespace App\Http\Controllers;

use App\Models\BackOfficeTask;
use App\Models\BackOfficeEntry;
use App\Models\BackOfficeTaskOutput;
use App\Models\AiVisualizationInsight;
use App\Models\CoResearch;
use App\Models\Copi;
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
use App\Models\OutputLinkPreview;
use App\Models\Project;
use App\Models\User;
use App\Support\AuditLogger;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PDF;

class OutputController extends Controller
{
    private const GEMINI_REMOTE_GATEWAY_URL = 'https://back.sihealthpolicy.com/api/ask-gemini';

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
            'outputs.*.quartile' => ['nullable', 'in:Q1,Q2,Q3,Q4,TCI 1,TCI 2'],
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
            'outputs.*.conference_presentation_type' => ['nullable', 'in:Oral Presentation,Poster Presentation,Abstract Presentation,Panel Discussion,Keynote Speech,World café,Moderator'],
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
            'quartile' => ['nullable', 'in:Q1,Q2,Q3,Q4,TCI 1,TCI 2'],
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
            'conference_presentation_type' => ['nullable', 'in:Oral Presentation,Poster Presentation,Abstract Presentation,Panel Discussion,Keynote Speech,World café,Moderator'],
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
        $payload = $this->buildOutputsIndexPayload($request);

        return view('outputs.index', [
            'outputs' => $payload['outputs'],
            'typeOptions' => $payload['typeOptions'],
            'yearOptions' => $payload['yearOptions'],
            'workStatusOptions' => $payload['workStatusOptions'],
            'exportMinDate' => $this->exportMinDate($payload['items']),
        ]);
    }

    public function outputsPdf(Request $request)
    {
        ini_set('pcre.backtrack_limit', '5000000');

        $payload = $this->buildOutputsIndexPayload($request);
        $rows = $payload['items'];
        $generatedAt = now('Asia/Bangkok');
        $generatedBy = trim((string) (auth()->user()?->full_name ?? '')) ?: 'ไม่ระบุ';
        $generatedLabel = $generatedAt->locale('th')->translatedFormat('j F') . ' ' . ($generatedAt->year + 543) . $generatedAt->translatedFormat(' เวลา H:i');
        $filterLabel = $this->buildOutputsExportFilterLabel($request);
        $stamp = $generatedAt->format('Ymd_His');
        $filename = "outputs_report_{$stamp}.pdf";

        $pdf = PDF::loadView('outputs.export-pdf', [
            'rows' => $rows,
            'summary' => $this->buildOutputsExportSummary($rows),
            'filterLabel' => $filterLabel,
            'generatedAt' => $generatedAt,
            'generatedBy' => $generatedBy,
        ], [], [
            'format' => 'A4-L',
        ]);

        AuditLogger::logFileDownload($filename, [
            'subject_type' => 'outputs_export',
            'subject_label' => 'Outputs PDF',
        ], $request);

        return $pdf->stream($filename);
    }

    public function outputsExcel(Request $request)
    {
        $payload = $this->buildOutputsIndexPayload($request);
        $rows = $payload['items'];
        $generatedAt = now('Asia/Bangkok');
        $generatedBy = trim((string) (auth()->user()?->full_name ?? '')) ?: 'ไม่ระบุ';
        $generatedLabel = $generatedAt->locale('th')->translatedFormat('j F') . ' ' . ($generatedAt->year + 543) . $generatedAt->translatedFormat(' เวลา H:i');
        $filterLabel = $this->buildOutputsExportFilterLabel($request);
        $stamp = $generatedAt->format('Ymd_His');
        $filename = "outputs_report_{$stamp}.xlsx";
        $summary = $this->buildOutputsExportSummary($rows);
        $columns = $this->outputsExcelColumns();

        AuditLogger::logFileDownload($filename, [
            'subject_type' => 'outputs_export',
            'subject_label' => 'Outputs Excel',
        ], $request);

        return $this->xlsxResponse(
            $filename,
            'Outputs',
            $columns,
            $rows->map(fn ($row, $index) => $this->outputExcelRow($row, (int) $index + 1))->values(),
            [
                ['รายงาน Output'],
                ['เงื่อนไข', $filterLabel],
                ['ออกรายงาน', $generatedLabel],
                ['ผู้สร้างรายงาน', $generatedBy],
                ['สรุป', 'ทั้งหมด ' . number_format($summary['total']) . ' รายการ | Project ' . number_format($summary['project']) . ' | Mission ' . number_format($summary['mission']) . ' | Extra ' . number_format($summary['extra'])],
            ]
        );
    }

    private function buildOutputsIndexPayload(Request $request): array
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
            'bookAuthors:id,output_id,name',
            'conferences:id,output_id,name,presentation_type,location,note',
            'conferences.presenters:id,output_conference_id,name',
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
            ->with([
                'task:id,backoffice_id,title,created_by',
                'contributorLinks.user',
                'authorLinks.user',
                'awardRecipientLinks.user',
                'bookAuthorLinks.user',
                'conferenceLinks.presenters.user',
            ])
            ->when(!$canViewAll, function ($q) use ($user, $fullName) {
                $q->where(function ($scope) use ($user, $fullName) {
                    $scope->where('owner_user_id', $user->id)
                        ->orWhere('owner_name', $fullName)
                        ->orWhereHas('contributorLinks', function ($contributorQ) use ($user, $fullName) {
                            $contributorQ->where('user_id', $user->id)
                                ->orWhere('name', $fullName);
                        })
                        ->orWhereHas('task', function ($taskQ) use ($user, $fullName) {
                            $taskQ->where('created_by', $user->id)
                                ->orWhereHas('entries', function ($entryQ) use ($fullName) {
                                    $entryQ->where('person_name', $fullName);
                                });
                        });
                });
            });

        $taskTypeSourceQuery = BackOfficeTaskOutput::query()
            ->when(!$canViewAll, function ($q) use ($user, $fullName) {
                $q->where(function ($scope) use ($user, $fullName) {
                    $scope->where('owner_user_id', $user->id)
                        ->orWhere('owner_name', $fullName)
                        ->orWhereHas('contributorLinks', function ($contributorQ) use ($user, $fullName) {
                            $contributorQ->where('user_id', $user->id)
                                ->orWhere('name', $fullName);
                        })
                        ->orWhereHas('task', function ($taskQ) use ($user, $fullName) {
                            $taskQ->where('created_by', $user->id)
                                ->orWhereHas('entries', function ($entryQ) use ($fullName) {
                                    $entryQ->where('person_name', $fullName);
                                });
                        });
                });
            });

        if ($request->filled('search')) {
            $taskOutputQuery->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('owner_name', 'like', '%' . $request->search . '%')
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
            'authors:id,external_output_id,role,name',
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
        $formatImpactFactor = static function ($value): ?string {
            if ($value === null || $value === '' || !is_numeric($value)) {
                return null;
            }

            return rtrim(rtrim(number_format((float) $value, 3, '.', ''), '0'), '.');
        };
        $filterSpecialFields = static function (array $fields): array {
            return collect($fields)
                ->map(fn (array $field) => [
                    'label' => trim((string) ($field['label'] ?? '')),
                    'value' => trim((string) ($field['value'] ?? '')),
                ])
                ->filter(fn (array $field) => $field['label'] !== '' && $field['value'] !== '')
                ->values()
                ->all();
        };
        $formatAuthorRole = static function (?string $role): string {
            return match (strtolower(trim((string) $role))) {
                'first' => 'First-author',
                'co' => 'Co-author',
                'corresponding' => 'Corresponding-author',
                'awardee' => 'Awardee',
                default => '',
            };
        };
        $formatPeopleWithRoles = static function ($items) use ($formatAuthorRole): array {
            return collect($items)
                ->map(function ($item) use ($formatAuthorRole) {
                    $name = is_array($item) ? trim((string) ($item['name'] ?? '')) : trim((string) ($item->name ?? $item));
                    $role = is_array($item) ? $formatAuthorRole($item['role'] ?? null) : $formatAuthorRole($item->role ?? null);

                    return $name !== '' && $role !== '' ? "{$name} ({$role})" : $name;
                })
                ->filter()
                ->values()
                ->all();
        };
        $formatPeopleNames = static function ($items): array {
            return collect($items)
                ->map(fn ($item) => is_array($item) ? trim((string) ($item['name'] ?? '')) : trim((string) ($item->name ?? $item)))
                ->filter()
                ->values()
                ->all();
        };
        $buildSpecialPayload = static function (
            string $typeCode,
            array $fields = [],
            array $people = [],
            ?string $peopleLabel = null
        ) use ($filterSpecialFields): ?array {
            $typeCode = strtoupper(trim($typeCode));
            $config = [
                'EA101' => ['heading' => 'ข้อมูล RESEARCH PAPER OR JOURNAL ARTICLE', 'tone' => 'paper', 'people_label' => 'Authors'],
                'EA103' => ['heading' => 'ข้อมูล PRESENTATIONS IN ACADEMIC CONFERENCES', 'tone' => 'presentation', 'people_label' => 'Presenter'],
                'EA118' => ['heading' => 'ข้อมูล AWARD AND RECOGNITION', 'tone' => 'award', 'people_label' => 'Awardee'],
                'EA119' => ['heading' => 'ข้อมูล TEXTBOOK / BOOK CHAPTER', 'tone' => 'textbook', 'people_label' => 'Authors'],
            ][$typeCode] ?? null;

            if (!$config) {
                return null;
            }

            $fields = $filterSpecialFields($fields);
            $people = collect($people)->map(fn ($name) => trim((string) $name))->filter()->values()->all();

            if (empty($fields) && empty($people)) {
                return null;
            }

            return [
                'heading' => $config['heading'],
                'tone' => $config['tone'],
                'fields' => $fields,
                'people_label' => $peopleLabel ?: $config['people_label'],
                'people' => $people,
            ];
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
            $conference = $output->conferences->first();
            $special = $buildSpecialPayload($typeCode, match ($typeCode) {
                'EA101' => [
                    ['label' => 'Journal', 'value' => $output->journal],
                    ['label' => 'Ranking', 'value' => $output->quartile],
                    ['label' => 'IF', 'value' => $formatImpactFactor($output->impact_factor)],
                ],
                'EA103' => [
                    ['label' => 'Conference', 'value' => $conference?->name],
                    ['label' => 'Type', 'value' => $conference?->presentation_type],
                    ['label' => 'Place', 'value' => $conference?->location],
                ],
                'EA119' => [
                    ['label' => 'Book', 'value' => $output->book_title],
                    ['label' => 'Publisher', 'value' => $output->publisher_name],
                    ['label' => 'Pages', 'value' => $output->chapter_pages],
                    ['label' => 'ISBN', 'value' => $output->isbn],
                ],
                default => [],
            }, match ($typeCode) {
                'EA101' => $formatPeopleWithRoles($output->authors),
                'EA103' => $conference ? $formatPeopleNames($conference->presenters) : [],
                'EA118' => $formatPeopleNames($output->awardRecipients),
                'EA119' => $formatPeopleNames($output->bookAuthors),
                default => [],
            });
            $items->push((object) [
                'title' => $output->title ?: '-',
                'type' => $typeLabel,
                'year_be' => $yearBe,
                'work_status' => $workStatus,
                'project_label' => $output->project?->project_name_th,
                'source_badge' => null,
                'detail' => $detailText !== '' ? $detailText : null,
                'related_link' => $relatedLink,
                'link' => $output->project ? route('projects.show', $output->project) . '#output-' . $output->id : null,
                'special' => $special,
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
            $conference = collect($output->conferences ?? [])->first();
            $special = $buildSpecialPayload($typeCode, match ($typeCode) {
                'EA101' => [
                    ['label' => 'Journal', 'value' => $output->journal],
                    ['label' => 'Ranking', 'value' => $output->quartile],
                    ['label' => 'IF', 'value' => $formatImpactFactor($output->impact_factor)],
                ],
                'EA103' => [
                    ['label' => 'Conference', 'value' => $conference['name'] ?? null],
                    ['label' => 'Type', 'value' => $conference['presentation_type'] ?? null],
                    ['label' => 'Place', 'value' => $conference['location'] ?? null],
                ],
                'EA119' => [
                    ['label' => 'Book', 'value' => $output->book_title],
                    ['label' => 'Publisher', 'value' => $output->publisher_name],
                    ['label' => 'Pages', 'value' => $output->chapter_pages],
                    ['label' => 'ISBN', 'value' => $output->isbn],
                ],
                default => [],
            }, match ($typeCode) {
                'EA101' => $formatPeopleWithRoles($output->authors ?? []),
                'EA103' => $formatPeopleNames($conference['presenters'] ?? []),
                'EA118' => $formatPeopleNames($output->resolvedAwardRecipients()),
                'EA119' => $formatPeopleNames($output->book_authors ?? []),
                default => [],
            });
            $items->push((object) [
                'title' => $output->title ?: '-',
                'type' => $typeLabel,
                'year_be' => $yearBe,
                'work_status' => $workStatus,
                'project_label' => $output->task?->title,
                'source_badge' => 'Mission',
                'detail' => null,
                'related_link' => null,
                'link' => $output->task ? route('back_office.show', $output->task) : null,
                'special' => $special,
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
            $conference = $output->conferences->first();
            $special = $buildSpecialPayload($typeCode, match ($typeCode) {
                'EA101' => [
                    ['label' => 'Journal', 'value' => $output->journal],
                    ['label' => 'Ranking', 'value' => $output->quartile],
                    ['label' => 'IF', 'value' => $formatImpactFactor($output->impact_factor)],
                ],
                'EA103' => [
                    ['label' => 'Conference', 'value' => $conference?->name],
                    ['label' => 'Type', 'value' => $conference?->presentation_type],
                    ['label' => 'Place', 'value' => $conference?->location],
                ],
                'EA119' => [
                    ['label' => 'Book', 'value' => $output->book_title],
                    ['label' => 'Publisher', 'value' => $output->publisher_name],
                    ['label' => 'Pages', 'value' => $output->chapter_pages],
                    ['label' => 'ISBN', 'value' => $output->isbn],
                ],
                default => [],
            }, match ($typeCode) {
                'EA101' => $formatPeopleWithRoles($output->authors),
                'EA103' => $conference ? $formatPeopleNames($conference->presenters) : [],
                'EA118' => $formatPeopleNames($output->awardRecipients),
                'EA119' => $formatPeopleNames($output->bookAuthors),
                default => [],
            });

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
                'special' => $special,
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

        if ($request->input('export_scope') === 'range') {
            $exportStart = $this->normalizeThaiDateInput($request->input('export_start_date'));
            $exportEnd = $this->normalizeThaiDateInput($request->input('export_end_date'));

            if ($exportStart) {
                $startDate = Carbon::parse($exportStart, 'Asia/Bangkok')->startOfDay();
                $items = $items->filter(fn ($item) => $item->created_at && Carbon::parse($item->created_at)->greaterThanOrEqualTo($startDate))->values();
            }

            if ($exportEnd) {
                $endDate = Carbon::parse($exportEnd, 'Asia/Bangkok')->endOfDay();
                $items = $items->filter(fn ($item) => $item->created_at && Carbon::parse($item->created_at)->lessThanOrEqualTo($endDate))->values();
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

        return [
            'outputs' => $outputs,
            'items' => $items->values(),
            'typeOptions' => $typeOptions->unique()->sort()->values(),
            'yearOptions' => $yearOptions,
            'workStatusOptions' => collect([
                'in_progress' => 'อยู่ระหว่างการดำเนินงาน',
                'completed' => 'เสร็จแล้ว',
            ]),
        ];
    }

    private function buildOutputsExportSummary(Collection $rows): array
    {
        return [
            'total' => $rows->count(),
            'project' => $rows->filter(fn ($row) => blank($row->source_badge ?? null))->count(),
            'mission' => $rows->filter(fn ($row) => trim((string) ($row->source_badge ?? '')) === 'Mission')->count(),
            'extra' => $rows->filter(fn ($row) => trim((string) ($row->source_badge ?? '')) === 'Extra')->count(),
            'types' => $rows->pluck('type')->filter()->unique()->count(),
            'years' => $rows->pluck('year_be')->filter()->unique()->sortDesc()->values(),
        ];
    }

    private function buildOutputsExportFilterLabel(Request $request): string
    {
        $parts = [];
        if ($request->filled('search')) {
            $parts[] = 'ค้นหา: ' . trim((string) $request->input('search'));
        }
        if ($request->filled('type')) {
            $parts[] = 'ประเภท: ' . trim((string) $request->input('type'));
        }
        if ($request->filled('year')) {
            $parts[] = 'ปี: ' . trim((string) $request->input('year'));
        }
        if ($request->filled('work_status')) {
            $parts[] = 'สถานะ: ' . match (trim((string) $request->input('work_status'))) {
                'completed' => 'เสร็จแล้ว',
                'in_progress' => 'อยู่ระหว่างการดำเนินงาน',
                default => trim((string) $request->input('work_status')),
            };
        }
        if ($request->input('export_scope') === 'range') {
            $start = $this->normalizeThaiDateInput($request->input('export_start_date'));
            $end = $this->normalizeThaiDateInput($request->input('export_end_date'));
            $formatDate = static function (?string $date): ?string {
                if (! $date) {
                    return null;
                }
                $carbon = Carbon::parse($date);
                return $carbon->format('d/m/') . ($carbon->year + 543);
            };
            $parts[] = 'วันที่ลงข้อมูล: ' . (($formatDate($start) ?: 'เริ่มต้น') . ' - ' . ($formatDate($end) ?: 'ปัจจุบัน'));
        }

        return $parts ? implode(' | ', $parts) : 'ทั้งหมด';
    }

    public function visualization(Request $request)
    {
        $isPublicSeminar = (bool) $request->attributes->get('is_public_seminar', false);

        if ($request->query('__realtime_version') === '1') {
            return response()
                ->json([
                    'version' => $this->visualizationRealtimeVersion(),
                    'checked_at' => now()->setTimezone('Asia/Bangkok')->toIso8601String(),
                ])
                ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        }

        $useRenderedVisualizationCache = $request->isMethod('GET') && $request->routeIs('outputs.visualization');
        $renderedVisualizationCacheKey = null;
        if ($useRenderedVisualizationCache) {
            $renderedVisualizationCacheKey = $this->visualizationRenderedCacheKey($request);
            $cachedHtml = Cache::get($renderedVisualizationCacheKey);
            if (is_string($cachedHtml) && $cachedHtml !== '') {
                return response($cachedHtml);
            }
        }

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
            'back_office' => 'พันธกิจ',
            'external' => 'Extra',
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
                    'reference' => trim((string) ($output->task?->title ?? '')) ?: 'Mission',
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
            'EA117' => 'Memorandum of Understanding (MOU)',
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
        $communicationFeatureRows = $filtered
            ->where('type_code', 'EA108')
            ->filter(function ($row) {
                $title = Str::of((string) ($row->title ?? ''))->trim()->lower()->toString();
                return Str::startsWith($title, ['news:', 'article:']);
            })
            ->values();
        $networkingRows = $filtered
            ->whereIn('type_code', ['EA114', 'EA115', 'EA117'])
            ->values()
            ->merge($communicationFeatureRows)
            ->values();
        $categorizedTotal = (int) ($paperRows->count() + $presentationRows->count() + $textbookRows->count());

        $paperQuartileLabels = collect(['Q1', 'Q2', 'Q3', 'Q4', 'TCI 1', 'TCI 2', 'ไม่ระบุ']);
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
        $networkingYears = $networkingRows
            ->map(fn ($row) => is_numeric($row->year_be ?? null) ? (int) $row->year_be : null)
            ->filter()
            ->countBy()
            ->sortKeys();
        $isExternalFacultyParticipant = static function (?string $value): bool {
            $normalized = Str::lower(trim((string) $value));

            return $normalized !== ''
                && (Str::contains($normalized, 'ภายนอกคณะ')
                    || Str::contains($normalized, 'ภายนอกคณะแพทยศาสตร์ศิริราชพยาบาล')
                    || Str::contains($normalized, 'external'));
        };
        $isProjectInSelectedYears = function ($project) use ($yearFrom, $yearTo): bool {
            $year = $this->normalizeYearFilter($project?->start_year);

            if ($yearFrom !== null && ($year === null || $year < $yearFrom)) {
                return false;
            }
            if ($yearTo !== null && ($year === null || $year > $yearTo)) {
                return false;
            }

            return true;
        };
        $externalNetworkParticipantRows = collect();
        $externalPiSelect = ['id', 'start_year', 'pi_name', 'pi_affiliation'];
        if (Schema::hasColumn('projects', 'pi_person_type')) {
            $externalPiSelect[] = 'pi_person_type';
        }
        $externalNetworkParticipantRows = $externalNetworkParticipantRows
            ->merge(Project::query()
                ->select($externalPiSelect)
                ->get()
                ->filter(fn (Project $project) => $isProjectInSelectedYears($project))
                ->filter(fn (Project $project) => trim((string) ($project->pi_name ?? '')) !== '')
                ->filter(fn (Project $project) => $isExternalFacultyParticipant($project->pi_person_type ?? null)
                    || $isExternalFacultyParticipant($project->pi_affiliation ?? null))
                ->map(fn (Project $project) => (object) [
                    'project_id' => (int) $project->id,
                    'name' => $project->pi_name,
                    'role' => 'PI',
                ]));
        $externalCopiSelect = ['id', 'project_id', 'name', 'affiliation'];
        if (Schema::hasColumn('copis', 'person_type')) {
            $externalCopiSelect[] = 'person_type';
        }
        $externalNetworkParticipantRows = $externalNetworkParticipantRows
            ->merge(Copi::query()
                ->with('project:id,start_year')
                ->select($externalCopiSelect)
                ->when(Schema::hasColumn('copis', 'deleted_at'), fn ($query) => $query->whereNull('deleted_at'))
                ->get()
                ->filter(fn (Copi $copi) => $isProjectInSelectedYears($copi->project))
                ->filter(fn (Copi $copi) => trim((string) ($copi->name ?? '')) !== '')
                ->filter(fn (Copi $copi) => $isExternalFacultyParticipant($copi->person_type ?? null)
                    || $isExternalFacultyParticipant($copi->affiliation ?? null))
                ->map(fn (Copi $copi) => (object) [
                    'project_id' => (int) $copi->project_id,
                    'name' => $copi->name,
                    'role' => 'Co-PI',
                ]));
        $externalNetworkParticipantRows = $externalNetworkParticipantRows
            ->merge(CoResearch::query()
                ->with('project:id,start_year')
                ->select(['id', 'project_id', 'name', 'unit_status'])
                ->when(Schema::hasColumn('co_research', 'deleted_at'), fn ($query) => $query->whereNull('deleted_at'))
                ->where(function ($query) {
                    $query->where('unit_status', 'ภายนอกคณะแพทยศาสตร์ศิริราชพยาบาล')
                        ->orWhere('unit_status', 'like', '%ภายนอกคณะ%')
                        ->orWhere('unit_status', 'like', '%external%');
                })
                ->get()
                ->filter(fn (CoResearch $coResearch) => $isProjectInSelectedYears($coResearch->project))
                ->map(fn (CoResearch $coResearch) => (object) [
                    'project_id' => (int) $coResearch->project_id,
                    'name' => $coResearch->name,
                    'role' => 'Co-Research',
                ]))
            ->values();
        $externalSirirajNetworkPeopleCount = (int) $externalNetworkParticipantRows
            ->map(fn ($participant) => $this->normalizePersonNameForMatch($participant->name ?? null))
            ->filter()
            ->unique()
            ->count();
        $externalSirirajNetworkProjectCount = (int) $externalNetworkParticipantRows
            ->pluck('project_id')
            ->filter()
            ->unique()
            ->count();
        $externalSirirajNetworkRoleCounts = collect(['PI', 'Co-PI', 'Co-Research'])
            ->mapWithKeys(function (string $role) use ($externalNetworkParticipantRows) {
                return [
                    $role => (int) $externalNetworkParticipantRows
                        ->where('role', $role)
                        ->map(fn ($participant) => $this->normalizePersonNameForMatch($participant->name ?? null))
                        ->filter()
                        ->unique()
                        ->count(),
                ];
            });
        $networkingYearSeries = collect(['EA108', 'EA114', 'EA115', 'EA117'])->mapWithKeys(function ($typeCode) use ($networkingRows, $networkingYears) {
            return [
                $typeCode => $networkingYears->keys()
                    ->map(fn ($year) => (int) $networkingRows
                        ->where('type_code', $typeCode)
                        ->where('year_be', (int) $year)
                        ->count())
                    ->values(),
            ];
        });
        $buildChartOutputItems = function (Collection $rows) use ($sortRowsNewestFirst, $eaFullNameMap): Collection {
            return $sortRowsNewestFirst($rows->values())
                ->map(function ($row) use ($eaFullNameMap) {
                    $typeCode = strtoupper(trim((string) ($row->type_code ?? 'OTHER'))) ?: 'OTHER';

                    return [
                        'title' => trim((string) ($row->title ?? '')) ?: '-',
                        'meta' => trim((string) ($row->reference ?? '')) ?: trim((string) ($row->source_label ?? '')) ?: '-',
                        'type' => $typeCode . ' - ' . ($eaFullNameMap[$typeCode] ?? ('ประเภท ' . $typeCode)),
                        'year' => is_numeric($row->year_be ?? null) ? (int) $row->year_be : null,
                        'link' => trim((string) ($row->link ?? '')) ?: null,
                    ];
                })
                ->values();
        };
        $outputYearSourceItems = collect($sourceLabelMap)->mapWithKeys(function ($label, $sourceKey) use ($filtered, $yearAxis, $buildChartOutputItems) {
            return [$sourceKey => $yearAxis
                ->mapWithKeys(function ($yearKey) use ($filtered, $sourceKey, $buildChartOutputItems) {
                    $rowsForYear = $yearKey === '__unknown__'
                        ? $filtered->where('source', $sourceKey)->filter(fn ($row) => !is_numeric($row->year_be))->values()
                        : $filtered->where('source', $sourceKey)->where('year_be', (int) $yearKey)->values();

                    return [(string) $yearKey => $buildChartOutputItems($rowsForYear)];
                })
                ->all()];
        });
        $outputTypeChartItems = collect($typeCounts)
            ->mapWithKeys(fn ($count, $typeCode) => [
                strtoupper((string) $typeCode) => $buildChartOutputItems($filtered->where('type_code', strtoupper((string) $typeCode))->values()),
            ]);
        $paperRankingItems = $paperQuartileLabels
            ->mapWithKeys(function ($label) use ($paperRows, $buildChartOutputItems) {
                $rowsForLabel = $label === 'ไม่ระบุ'
                    ? $paperRows->filter(fn ($row) => trim((string) ($row->quartile ?? '')) === '')->values()
                    : $paperRows->where('quartile', $label)->values();

                return [(string) $label => $buildChartOutputItems($rowsForLabel)];
            });
        $paperYears = $paperRows
            ->map(fn ($row) => is_numeric($row->year_be ?? null) ? (int) $row->year_be : null)
            ->filter()
            ->unique()
            ->sort()
            ->values();
        $paperRankingYearSeries = $paperQuartileLabels
            ->mapWithKeys(function ($label) use ($paperRows, $paperYears) {
                return [(string) $label => $paperYears
                    ->map(function ($year) use ($paperRows, $label) {
                        $yearRows = $paperRows->where('year_be', (int) $year)->values();
                        return $label === 'ไม่ระบุ'
                            ? (int) $yearRows->filter(fn ($row) => trim((string) ($row->quartile ?? '')) === '')->count()
                            : (int) $yearRows->where('quartile', $label)->count();
                    })
                    ->values()];
            });
        $paperRankingYearItems = $paperQuartileLabels
            ->mapWithKeys(function ($label) use ($paperRows, $paperYears, $buildChartOutputItems) {
                return [(string) $label => $paperYears
                    ->mapWithKeys(function ($year) use ($paperRows, $label, $buildChartOutputItems) {
                        $yearRows = $paperRows->where('year_be', (int) $year)->values();
                        $rowsForLabel = $label === 'ไม่ระบุ'
                            ? $yearRows->filter(fn ($row) => trim((string) ($row->quartile ?? '')) === '')->values()
                            : $yearRows->where('quartile', $label)->values();

                        return [(string) $year => $buildChartOutputItems($rowsForLabel)];
                    })
                    ->all()];
            });
        $presentationActivityItems = $presentationActivity
            ->keys()
            ->mapWithKeys(fn ($label) => [
                (string) $label => $buildChartOutputItems($presentationRows
                    ->filter(fn ($row) => (trim((string) ($row->presentation_type ?? '')) ?: 'ไม่ระบุ') === (string) $label)
                    ->values()),
            ]);
        $presentationYears = $presentationRows
            ->map(fn ($row) => is_numeric($row->year_be ?? null) ? (int) $row->year_be : null)
            ->filter()
            ->unique()
            ->sort()
            ->values();
        $presentationActivityYearSeries = $presentationActivity
            ->keys()
            ->mapWithKeys(fn ($label) => [
                (string) $label => $presentationYears
                    ->map(fn ($year) => (int) $presentationRows
                        ->where('year_be', (int) $year)
                        ->filter(fn ($row) => (trim((string) ($row->presentation_type ?? '')) ?: 'ไม่ระบุ') === (string) $label)
                        ->count())
                    ->values(),
            ]);
        $presentationActivityYearItems = $presentationActivity
            ->keys()
            ->mapWithKeys(fn ($label) => [
                (string) $label => $presentationYears
                    ->mapWithKeys(fn ($year) => [
                        (string) $year => $buildChartOutputItems($presentationRows
                            ->where('year_be', (int) $year)
                            ->filter(fn ($row) => (trim((string) ($row->presentation_type ?? '')) ?: 'ไม่ระบุ') === (string) $label)
                            ->values()),
                    ])
                    ->all(),
            ]);
        $textbookYearItems = $textbookYears
            ->keys()
            ->mapWithKeys(fn ($year) => [
                (string) $year => $buildChartOutputItems($textbookRows->where('year_be', (int) $year)->values()),
            ]);
        $networkingYearItems = collect(['EA108', 'EA114', 'EA115', 'EA117'])->mapWithKeys(fn ($typeCode) => [
            $typeCode => $networkingYears
                ->keys()
                ->mapWithKeys(fn ($year) => [
                    (string) $year => $buildChartOutputItems($networkingRows
                        ->where('type_code', $typeCode)
                        ->where('year_be', (int) $year)
                        ->values()),
                ])
                ->all(),
        ]);

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
        $networkingSummary = [
            'count' => $networkingRows->count(),
            'latest_year' => $networkingYears->keys()->last(),
            'peak_year' => $networkingYears->sortDesc()->keys()->first(),
            'peak_year_count' => (int) ($networkingYears->max() ?? 0),
            'external_people_count' => $externalSirirajNetworkPeopleCount,
            'external_project_count' => $externalSirirajNetworkProjectCount,
            'external_role_counts' => $externalSirirajNetworkRoleCounts,
            'ea108_count' => (int) $communicationFeatureRows->count(),
            'ea114_count' => (int) $networkingRows->where('type_code', 'EA114')->count(),
            'ea115_count' => (int) $networkingRows->where('type_code', 'EA115')->count(),
            'ea117_count' => (int) $networkingRows->where('type_code', 'EA117')->count(),
            'latest' => $sortRowsNewestFirst($networkingRows)->take(5)->map($buildSummaryListItem)->values(),
            'communication_features' => $sortRowsNewestFirst($networkingRows)
                ->map(function ($row) use ($buildSummaryListItem) {
                    $item = $buildSummaryListItem($row);
                    $title = trim((string) ($item->title ?? ''));
                    $typeCode = strtoupper(trim((string) ($row->type_code ?? '')));
                    $kind = match ($typeCode) {
                        'EA114' => 'EA114',
                        'EA115' => 'EA115',
                        'EA117' => 'EA117',
                        default => Str::startsWith(Str::lower($title), 'article:') ? 'Article' : 'News',
                    };
                    $cleanTitle = trim((string) preg_replace('/^(news|article)\s*:\s*/iu', '', $title));
                    $item->content_kind = $kind;
                    $item->feature_type_code = $typeCode ?: 'EA108';
                    $item->clean_title = $cleanTitle !== '' ? $cleanTitle : $title;
                    $item->external_link = trim((string) ($item->modal_link ?? '')) ?: trim((string) ($item->link ?? '')) ?: null;
                    $preview = $item->external_link
                        ? OutputLinkPreview::query()
                            ->whereIn('url_hash', OutputLinkPreview::hashCandidates((string) $item->external_link))
                            ->where('fetch_status', 'ok')
                            ->orderByRaw("CASE WHEN preview_image_url IS NOT NULL AND preview_image_url <> '' THEN 0 WHEN provider <> 'screenshotone' THEN 1 ELSE 2 END")
                            ->latest('fetched_at')
                            ->first()
                        : null;
                    $item->link_preview = $preview ? [
                        'id' => $preview->id,
                        'title' => $preview->title,
                        'description' => $preview->description,
                        'preview_image_url' => $preview->preview_image_url,
                        'preview_image_path' => $preview->preview_image_path,
                        'thumbnail_url' => $preview->thumbnail_url,
                        'author_name' => $preview->author_name,
                        'provider_name' => $preview->provider_name,
                        'fetched_at' => $preview->fetched_at?->timezone('Asia/Bangkok')->format('d/m/Y H:i'),
                    ] : null;

                    return $item;
                })
                ->values(),
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

        $dataSourceOptions = collect([
            'Real-World Data',
            'Primary Data',
            'Real-World Data + Primary Data',
        ]);
        $normalizeDataSourceCategory = static function (mixed $value): ?string {
            $text = mb_strtolower(trim((string) $value), 'UTF-8');
            if ($text === '') {
                return null;
            }

            $hasRealWorld = Str::contains($text, [
                'real-world data',
                'real world data',
                'secondary / existing',
                'existing data',
                'raw data analysis',
                'contextualised / integrated',
                'contextualized / integrated',
                'integrated data',
                'improvised data',
            ]);
            $hasPrimary = Str::contains($text, [
                'primary data',
                'primary data collection',
                'primary / generated',
                'generated data',
                'generate data',
            ]);

            if ($hasRealWorld && $hasPrimary) {
                return 'Real-World Data + Primary Data';
            }

            if (Str::contains($text, [
                'primary data',
                'primary data collection',
                'primary / generated',
                'generated data',
                'generate data',
            ])) {
                return 'Primary Data';
            }

            if (Str::contains($text, [
                'real-world data',
                'real world data',
                'secondary / existing',
                'existing data',
                'raw data analysis',
                'contextualised / integrated',
                'contextualized / integrated',
                'integrated data',
                'improvised data',
            ])) {
                return 'Real-World Data';
            }

            return null;
        };
        $isSirirajPerson = static function (...$parts): bool {
            $text = mb_strtolower(trim(collect($parts)->filter()->implode(' ')), 'UTF-8');
            if ($text === '') {
                return false;
            }
            if (Str::contains($text, ['ภายนอก', 'external'])) {
                return false;
            }

            return Str::contains($text, [
                'siriraj',
                'ศิริราช',
                'คณะแพทยศาสตร์ศิริราช',
                'ภายในคณะ',
                'ภายในหน่วย',
            ]);
        };
        $projectSelectColumns = [
            'id',
            'project_code',
            'project_name_th',
            'project_type',
            'theme',
            'budget',
            'start_year',
            'pi_name',
            'pi_affiliation',
        ];
        foreach (['data_source', 'pi_user_id', 'pi_person_type'] as $optionalProjectColumn) {
            if (Schema::hasColumn('projects', $optionalProjectColumn)) {
                $projectSelectColumns[] = $optionalProjectColumn;
            }
        }
        $copiSelectColumns = ['id', 'project_id', 'name', 'affiliation'];
        foreach (['user_id', 'person_type'] as $optionalCopiColumn) {
            if (Schema::hasColumn('copis', $optionalCopiColumn)) {
                $copiSelectColumns[] = $optionalCopiColumn;
            }
        }
        $coResearchSelectColumns = ['id', 'project_id', 'name', 'affiliation', 'unit_status'];
        if (Schema::hasColumn('co_research', 'user_id')) {
            $coResearchSelectColumns[] = 'user_id';
        }
        $projectAnalysisAllRows = Project::query()
            ->with([
                'copis' => fn ($query) => $query->select($copiSelectColumns),
                'coResearches' => fn ($query) => $query->select($coResearchSelectColumns),
                'outputs:id,project_id,type,title,year,project_output',
            ])
            ->select($projectSelectColumns)
            ->get()
            ->values();
        $projectAnalysisRows = $projectAnalysisAllRows
            ->filter(function (Project $project) use ($yearFrom, $yearTo) {
                $year = $this->normalizeYearFilter($project->start_year);
                if ($yearFrom !== null && ($year === null || $year < $yearFrom)) {
                    return false;
                }
                if ($yearTo !== null && ($year === null || $year > $yearTo)) {
                    return false;
                }
                return true;
            })
            ->values();
        $buildParticipantRows = function (Project $project) use ($isSirirajPerson): Collection {
            $participants = collect();
            $piName = trim((string) ($project->pi_name ?? ''));
            if ($piName !== '') {
                $isInternal = (int) ($project->pi_user_id ?? 0) > 0
                    || $isSirirajPerson($project->pi_person_type, $project->pi_affiliation, $piName);
                $participants->push((object) [
                    'role' => 'PI',
                    'name' => $piName,
                    'affiliation' => trim((string) ($project->pi_affiliation ?? '')),
                    'user_id' => (int) ($project->pi_user_id ?? 0) ?: null,
                    'is_siriraj' => $isInternal,
                ]);
            }

            foreach ($project->copis as $copi) {
                $name = trim((string) ($copi->name ?? ''));
                if ($name === '') {
                    continue;
                }
                $participants->push((object) [
                    'role' => 'Co-PI',
                    'name' => $name,
                    'affiliation' => trim((string) ($copi->affiliation ?? '')),
                    'user_id' => (int) ($copi->user_id ?? 0) ?: null,
                    'is_siriraj' => (int) ($copi->user_id ?? 0) > 0
                        || $isSirirajPerson($copi->person_type, $copi->affiliation, $name),
                ]);
            }

            foreach ($project->coResearches as $coResearch) {
                $name = trim((string) ($coResearch->name ?? ''));
                if ($name === '') {
                    continue;
                }
                $participants->push((object) [
                    'role' => 'Co-Research',
                    'name' => $name,
                    'affiliation' => trim((string) ($coResearch->affiliation ?? '')),
                    'user_id' => (int) ($coResearch->user_id ?? 0) ?: null,
                    'is_siriraj' => (int) ($coResearch->user_id ?? 0) > 0
                        || $isSirirajPerson($coResearch->unit_status, $coResearch->affiliation, $name),
                ]);
            }

            return $participants;
        };
        $hasSirirajInvolvement = fn (Project $project): bool => $buildParticipantRows($project)
            ->contains(fn ($participant) => (bool) ($participant->is_siriraj ?? false));
        $matchesProjectTypeKeyword = static function (Project $project, array $keywords): bool {
            $text = mb_strtolower(trim(($project->project_type ?? '') . ' ' . ($project->theme ?? '') . ' ' . ($project->project_name_th ?? '')), 'UTF-8');
            return $text !== '' && Str::contains($text, $keywords);
        };
        $sirirajProjects = $projectAnalysisRows
            ->filter($hasSirirajInvolvement)
            ->sortByDesc(fn (Project $project) => (int) ($this->normalizeYearFilter($project->start_year) ?? 0))
            ->values();
        $sirirajParticipants = $projectAnalysisRows
            ->flatMap($buildParticipantRows)
            ->filter(fn ($participant) => (bool) ($participant->is_siriraj ?? false))
            ->values();
        $sirirajRoleCounts = [
            'pi' => (int) $projectAnalysisRows->filter(fn (Project $project) => $buildParticipantRows($project)
                ->contains(fn ($participant) => (bool) ($participant->is_siriraj ?? false) && ($participant->role ?? '') === 'PI'))->count(),
            'co_pi' => (int) $projectAnalysisRows->filter(fn (Project $project) => $buildParticipantRows($project)
                ->contains(fn ($participant) => (bool) ($participant->is_siriraj ?? false) && ($participant->role ?? '') === 'Co-PI'))->count(),
            'co_research' => (int) $projectAnalysisRows->filter(fn (Project $project) => $buildParticipantRows($project)
                ->contains(fn ($participant) => (bool) ($participant->is_siriraj ?? false) && ($participant->role ?? '') === 'Co-Research'))->count(),
        ];
        $personIdentityKey = function ($participant): string {
            $userId = (int) ($participant->user_id ?? 0);
            if ($userId > 0) {
                return 'user:' . $userId;
            }

            $nameKey = $this->normalizePersonNameForMatch($participant->name ?? null);

            return $nameKey !== '' ? 'name:' . $nameKey : '';
        };
        $sirirajRolePersonCounts = [
            'pi' => (int) $sirirajParticipants
                ->filter(fn ($participant) => ($participant->role ?? '') === 'PI')
                ->map($personIdentityKey)
                ->filter()
                ->unique()
                ->count(),
            'co_pi' => (int) $sirirajParticipants
                ->filter(fn ($participant) => ($participant->role ?? '') === 'Co-PI')
                ->map($personIdentityKey)
                ->filter()
                ->unique()
                ->count(),
            'co_research' => (int) $sirirajParticipants
                ->filter(fn ($participant) => ($participant->role ?? '') === 'Co-Research')
                ->map($personIdentityKey)
                ->filter()
                ->unique()
                ->count(),
        ];
        $classifyPeopleType = static function (?string $rawType, bool $isSiriraj): string {
            $text = mb_strtolower(trim((string) $rawType), 'UTF-8');
            if (Str::contains($text, ['ภายนอก', 'external'])) {
                return 'external';
            }
            if (Str::contains($text, ['ภายในหน่วย', 'unit'])) {
                return 'unit';
            }
            if ($isSiriraj || Str::contains($text, ['ศิริราช', 'siriraj', 'ภายในคณะ'])) {
                return 'siriraj';
            }

            return $isSiriraj ? 'siriraj' : 'external';
        };
        $buildProjectParticipantAnalysisRows = function (Collection $projects) use ($buildParticipantRows, $classifyPeopleType, $normalizeProjectType, $personIdentityKey): Collection {
            return $projects
                ->flatMap(function (Project $project) use ($buildParticipantRows, $classifyPeopleType, $normalizeProjectType, $personIdentityKey) {
                    $year = $this->normalizeYearFilter($project->start_year);

                    return $buildParticipantRows($project)->map(function ($participant) use ($project, $classifyPeopleType, $normalizeProjectType, $personIdentityKey, $year) {
                        $rawType = match ($participant->role ?? '') {
                            'PI' => $project->pi_person_type ?? null,
                            'Co-PI' => $project->copis->first(fn ($copi) => trim((string) $copi->name) === trim((string) $participant->name))?->person_type,
                            'Co-Research' => $project->coResearches->first(fn ($co) => trim((string) $co->name) === trim((string) $participant->name))?->unit_status,
                            default => null,
                        };
                        $participantPersonKey = $personIdentityKey($participant);
                        $hasUserId = (int) ($participant->user_id ?? 0) > 0;
                        $peopleType = $hasUserId
                            ? 'unit'
                            : $classifyPeopleType($rawType, (bool) ($participant->is_siriraj ?? false));
                        if (!$hasUserId && $peopleType === 'unit') {
                            $peopleType = (bool) ($participant->is_siriraj ?? false) ? 'siriraj' : 'external';
                        }

                        return (object) [
                            'project_id' => (int) $project->id,
                            'project_name' => trim((string) ($project->project_name_th ?? '')) ?: '-',
                            'project_link' => route('projects.show', $project),
                            'year' => $year,
                            'theme' => $normalizeProjectType($project->project_type),
                            'role' => $participant->role,
                            'name' => $participant->name,
                            'affiliation' => $participant->affiliation ?? '',
                            'user_id' => (int) ($participant->user_id ?? 0) ?: null,
                            'person_key' => $participantPersonKey,
                            'researcher_key' => ($normalizedResearcherName = $this->normalizePersonNameForMatch($participant->name ?? null)) !== ''
                                ? 'name:' . $normalizedResearcherName
                                : $participantPersonKey,
                            'people_type' => $peopleType,
                            'is_siriraj' => (bool) ($participant->is_siriraj ?? false),
                        ];
                    });
                })
                ->filter(fn ($row) => trim((string) $row->person_key) !== '')
                ->values();
        };
        $projectParticipantRows = $buildProjectParticipantAnalysisRows($projectAnalysisRows);
        $projectParticipantAllRows = $buildProjectParticipantAnalysisRows($projectAnalysisAllRows);
        $resolvedPeopleTypesByResearcher = $projectParticipantAllRows
                ->groupBy('researcher_key')
                ->map(function (Collection $personRows): string {
                    if ($personRows->contains(fn ($row) => (int) ($row->user_id ?? 0) > 0)) {
                        return 'unit';
                    }
                    if ($personRows->contains(fn ($row) => ($row->people_type ?? '') === 'siriraj' || (bool) ($row->is_siriraj ?? false))) {
                        return 'siriraj';
                    }

                    return 'external';
                });
        $resolvePeopleTypesByResearcher = static function (Collection $rows) use ($resolvedPeopleTypesByResearcher): Collection {
            return $rows
                ->map(function ($row) use ($resolvedPeopleTypesByResearcher) {
                    $row->people_type = $resolvedPeopleTypesByResearcher->get($row->researcher_key, $row->people_type);
                    return $row;
                })
                ->values();
        };
        $projectParticipantRows = $resolvePeopleTypesByResearcher($projectParticipantRows);
        $projectParticipantAllRows = $resolvePeopleTypesByResearcher($projectParticipantAllRows);

        $projectNatureProjects = $projectAnalysisAllRows
            ->filter(fn (Project $project) => trim((string) ($project->project_name_th ?? '')) !== '')
            ->values();
        $projectNatureTotal = max(1, $projectNatureProjects->count());
        $projectNatureThemeDefinitions = collect([
            [
                'key' => 'aging',
                'label' => 'Aging, Frailty และ care continuum',
                'short_label' => 'Aging / Frailty',
                'keywords' => ['ผู้สูงอายุ', 'สูงอายุ', 'frailty', 'เปราะบาง', 'aging', 'geriatric', 'สะโพก', 'hip', 'delirium', 'ฟื้นฟู', 'transitional', 'intermediate'],
                'reading' => 'โจทย์ไม่ได้หยุดที่โรคหรือหัตถการ แต่ขยับไปสู่ระบบดูแลผู้สูงอายุหลังเจ็บป่วย การฟื้นฟู และการดูแลต่อเนื่องจากโรงพยาบาลสู่ชุมชน',
                'work_done' => 'งานที่เห็นชัดคือภาวะเปราะบางในผู้สูงอายุ, hip fracture pathway, delirium หลังผ่าตัด, การฟื้นฟู, transitional/intermediate care และการใช้ทีมสหสาขาวิชาชีพเพื่อออกแบบระบบดูแลต่อเนื่อง',
                'next_step' => 'ผลลัพธ์ในหมวดนี้สามารถต่อยอดเป็น care pathway, service model หรือ policy brief ได้มากขึ้น โดยเฉพาะ output ที่วัด outcome ซ้ำได้ เช่น function, readmission, caregiver burden และ cost of care',
            ],
            [
                'key' => 'hta',
                'label' => 'HTA, economics และ reimbursement decision',
                'short_label' => 'HTA / Economics',
                'keywords' => ['คุ้มค่า', 'ต้นทุน', 'เศรษฐศาสตร์', 'cost', 'economic', 'budget', 'สิทธิประโยชน์', 'reimbursement', 'ความพร้อม'],
                'reading' => 'งานจำนวนมากตอบคำถามเชิงระบบว่าเทคโนโลยีหรือบริการใดควรเข้า benefit package ควร scale อย่างไร และงบประมาณรับได้หรือไม่',
                'work_done' => 'งานที่พบครอบคลุม cost-utility, economic evaluation, budget impact, ความพร้อมของระบบบริการ, การออกแบบสิทธิประโยชน์ และการประเมินเทคโนโลยีที่มีผลต่อการตัดสินใจระดับระบบ',
                'next_step' => 'ผลลัพธ์จากงาน HTA มีโอกาสต่อยอดเป็น policy recommendation, reimbursement brief หรือ implementation report พร้อมการติดตาม uptake, budget impact, equity impact และผลต่อ service delivery',
            ],
            [
                'key' => 'access_population',
                'label' => 'Access, equity และ population health',
                'short_label' => 'Access / Population',
                'keywords' => ['เข้าถึง', 'หลักประกัน', 'ข้ามเพศ', 'pmac', 'pm 2.5', 'พระสงฆ์', 'uhc', 'global', 'urban', 'สร้างเสริมสุขภาพ', 'ป้องกันโรค'],
                'reading' => 'มีสัญญาณของงานที่มองเกิน clinical outcome ไปสู่สิทธิประโยชน์ ความเป็นธรรม กลุ่มประชากรเฉพาะ และวาระสุขภาพระดับประเทศ/นานาชาติ',
                'work_done' => 'งานที่เห็นคือการเข้าถึงบริการภายใต้หลักประกันสุขภาพ, กลุ่มประชากรเฉพาะ เช่น transgender และพระสงฆ์, PM2.5, healthy aging ในเมือง, UHC conference และ PMAC ซึ่งเป็นงานเชิง agenda setting',
                'next_step' => 'ผลลัพธ์เชิง agenda setting ถูกต่อยอดเป็น policy brief, consensus statement, public communication หรือรายงานที่ติดตาม access gap, equity gap และ policy uptake แล้วหรือยัง',
            ],
            [
                'key' => 'device_innovation',
                'label' => 'Medical device และ innovation adoption',
                'short_label' => 'Device / Innovation',
                'keywords' => ['รากฟัน', 'กระดูกเทียม', 'เท้าเทียม', 'insulin pump', 'อุปกรณ์', 'implant', '3 มิติ', '3d', 'นวัตกรรม'],
                'reading' => 'นวัตกรรมถูกอ่านผ่านมุม implementation และระบบบริการจริง ไม่ใช่แค่การมีเทคโนโลยีใหม่ แต่ถามว่าจะนำเข้า ใช้ ติดตาม และจ่ายอย่างไร',
                'work_done' => 'งานที่พบมีทั้งกระดูกเทียมเฉพาะบุคคลจาก 3D printing, รากฟันเทียม, เท้าเทียมไดนามิก, insulin pump และ horizon scanning เพื่อเตรียมระบบรองรับนวัตกรรม',
                'next_step' => 'ผลลัพธ์ในระยะถัดไปควรสะท้อนช่วงหลังนำเทคโนโลยีไปใช้จริงมากขึ้น เช่น implementation report, monitoring dashboard, service readiness report และข้อเสนอเรื่องการกระจายเทคโนโลยี',
            ],
            [
                'key' => 'data_rwe',
                'label' => 'Data platform, RWE และ clinical cohort',
                'short_label' => 'Data / RWE',
                'keywords' => ['real-world', 'real world', 'rwe', 'cohort', 'registry', 'ฐานข้อมูล', 'คลังเก็บข้อมูล', 'อิเล็กทรอนิกส์', 'เวชระเบียน', 'longitudinal', 'ติดตามผล'],
                'reading' => 'ฐานข้อมูลและ cohort ทำหน้าที่เป็น evidence infrastructure เพื่ออ่าน outcome จริง ติดตามโรคเรื้อรัง และเชื่อมข้อมูลบริการกับการตัดสินใจ',
                'work_done' => 'งานที่เห็นคือฐานข้อมูลเบาหวานและความดัน, registry/ฐานข้อมูลอิเล็กทรอนิกส์, การเชื่อมเวชระเบียนกับฐานข้อมูลเบิกจ่าย, cohort โรคเรื้อรัง และการติดตามผลลัพธ์ระยะยาว',
                'next_step' => 'ผลลัพธ์จากฐานข้อมูลสามารถต่อยอดเป็น real-world evidence report, dashboard, data quality report หรือ policy-facing analysis เพื่อให้ฐานข้อมูลกลับไปช่วยปรับบริการจริง',
            ],
            [
                'key' => 'ai_digital',
                'label' => 'AI, digital health และ deployable technology',
                'short_label' => 'AI / Digital',
                'keywords' => ['ปัญญาประดิษฐ์', ' ai', '(ai)', 'telemedicine', 'แพทย์ทางไกล', 'digital', 'ระบบรายงาน', 'แสดงข้อมูล'],
                'reading' => 'AI และ digital health ถูกใช้เป็นเครื่องมือฝังใน workflow เช่น อ่านภาพรังสี รายงานข้อมูล และเชื่อมการดูแล ไม่ใช่งาน algorithm novelty เป็นหลัก',
                'work_done' => 'งานที่พบคือ AI อ่านภาพถ่ายรังสีทรวงอก, ระบบรายงานและแสดงข้อมูล, telemedicine ในช่วงโควิด และการถ่ายทอดความรู้เพื่อให้เทคโนโลยีใช้ได้จริงในโรงพยาบาลภาครัฐ',
                'next_step' => 'ผลลัพธ์หลัง deployment ของ AI/digital health ควรเห็นการประเมิน workflow fit, clinician adoption, safety monitoring, maintenance และความพร้อมของโรงพยาบาลปลายทาง',
            ],
            [
                'key' => 'drug_rdu',
                'label' => 'Drug governance และ medication safety',
                'short_label' => 'Drug / RDU',
                'keywords' => ['ใช้ยาอย่างสมเหตุผล', 'metformin', 'สั่งใช้ยา', 'ยาที่มีโอกาส', 'ยา metformin', 'medication', 'prescribing', 'rdu'],
                'reading' => 'กลุ่มนี้เล็กกว่าแต่คมชัด เน้น prescribing quality, medication safety และการทำให้การใช้ยาในโรงพยาบาลมี governance มากขึ้น',
                'work_done' => 'งานที่พบคือโครงการใช้ยาอย่างสมเหตุผลรายปี, ความเหมาะสมของการใช้ metformin ในผู้ป่วยไตเรื้อรัง และการติดตามยาที่อาจใช้อย่างไม่เหมาะสมในผู้สูงอายุ',
                'next_step' => 'ผลลัพธ์ของ RDU สามารถต่อเนื่องจากโครงการรายปีไปสู่ prescribing quality dashboard, medication safety report, clinical decision support brief หรือ feedback loop ให้ทีมรักษา',
            ],
        ]);
        $projectNatureMatches = static function (Project $project, array $keywords): bool {
            $title = mb_strtolower(trim((string) ($project->project_name_th ?? '')), 'UTF-8');

            return $title !== '' && Str::contains($title, $keywords);
        };
        $outputLabelForNature = static function (string $code): string {
            return match ($code) {
                'EA101' => 'บทความวิจัยและบทความวารสาร',
                'EA102' => 'รายงานหรือเอกสารวิชาการ',
                'EA103' => 'การนำเสนอผลงานทางวิชาการ',
                'EA104' => 'ชุดข้อมูลและฐานข้อมูล',
                'EA105' => 'เครื่องมือหรือระบบสนับสนุนการทำงาน',
                'EA106' => 'ข้อเสนอเชิงนโยบาย',
                'EA107' => 'สื่อหรือวัสดุเพื่อการเรียนรู้',
                'EA108' => 'ผลงานวิชาการที่เชื่อมกับการตัดสินใจเชิงระบบ',
                'EA109' => 'การเปลี่ยนแปลงแนวปฏิบัติ กระบวนการ หรือนโยบาย',
                'EA110' => 'จำนวนผู้เข้าร่วมกิจกรรม',
                'EA111' => 'ผลประเมินความพึงพอใจ',
                'EA112' => 'การสื่อสารหรือเผยแพร่สู่ผู้เกี่ยวข้อง',
                'EA113' => 'คำสั่งแต่งตั้งหรือบทบาทในคณะทำงาน',
                'EA114' => 'ผลงานสนับสนุนการใช้ประโยชน์เชิงนโยบาย',
                'EA115', 'EA117' => 'เครือข่ายหรือเวทีความร่วมมือ',
                'EA116' => 'โครงการวิจัยต่อเนื่อง',
                'EA118' => 'รางวัลหรือการยอมรับ',
                'EA119' => 'ตำรา คู่มือ หรือสื่อความรู้',
                'EA120' => 'คู่มือ แนวทาง หรือ instruction',
                'EA999' => 'ผลงานประเภทอื่น',
                default => 'ผลงานประเภทอื่น',
            };
        };
        $buildNatureOutputDirection = static function (array $theme, Collection $outputTypeMix) {
            if ($outputTypeMix->isEmpty()) {
                return 'ยังไม่พบ output ที่ผูกกับโครงการในหมวดนี้ การอ่านหมวดนี้จึงยังอิงจากสัญญาณในชื่อโครงการเป็นหลัก';
            }

            $codes = $outputTypeMix->pluck('code')->map(fn ($code) => strtoupper((string) $code))->values();
            $labels = $outputTypeMix->pluck('label')->filter()->unique()->values();
            $hasAny = static fn (array $targets): bool => $codes->intersect($targets)->isNotEmpty();
            $formatLabels = static function (Collection $items): string {
                $items = $items->values();
                if ($items->count() <= 1) {
                    return (string) ($items->first() ?? '');
                }
                if ($items->count() === 2) {
                    return $items->implode(' และ ');
                }

                return $items->slice(0, -1)->implode(', ') . ' และ ' . $items->last();
            };
            $has = static fn (string $code): bool => $codes->contains($code);

            $outputList = $formatLabels($labels);
            $themeKey = (string) ($theme['key'] ?? '');

            return match ($themeKey) {
                'aging' => 'ทิศทางของ output ในกลุ่มผู้สูงอายุควรอ่านเป็นเส้นทางจาก “ความเสี่ยงของคนไข้” ไปสู่ “การจัดระบบดูแล” มากกว่าการนับผลงานวิชาการอย่างเดียว ชุดผลงานที่พบ ได้แก่ ' . $outputList . ' ทำให้เห็นว่าหมวดนี้มีฐานสำหรับเล่าเรื่อง care continuum ตั้งแต่การคัดกรองภาวะเปราะบาง การฟื้นฟู ไปจนถึงการออกแบบบริการหลังออกจากโรงพยาบาล' . ($has('EA109') ? ' จุดที่มีน้ำหนักเชิงนโยบายคือเริ่มเห็น output ที่แตะการเปลี่ยนแนวปฏิบัติหรือกระบวนการ ซึ่งควรถูกใช้เป็นหลักฐานของ service redesign ได้' : ' จุดที่ควรเติมคือ output ที่แปลงผลวิจัยไปเป็น care pathway, service model หรือข้อเสนอเชิงระบบที่ผู้บริหารบริการสุขภาพนำไปใช้ได้') . '.',
                'hta' => 'สำหรับ HTA / Economics แกนของ output คือการทำให้ evidence อยู่ในรูปที่ผู้ตัดสินใจใช้ต่อได้ โดย output ที่พบสรุปเป็นกลุ่มหลัก ได้แก่ ' . $outputList . ' ซึ่งสะท้อนทั้งการสร้างหลักฐาน การสังเคราะห์ผล และการสื่อสารเข้าสู่ decision space เรื่องความคุ้มค่า งบประมาณ ความพร้อมของระบบบริการ และสิทธิประโยชน์' . ($has('EA106') ? ' การมีข้อเสนอเชิงนโยบายยิ่งทำให้เห็นเส้นทางจากการประเมินไปสู่ทางเลือกเชิงนโยบายชัดขึ้น' : ' หากยังไม่มีข้อเสนอเชิงนโยบายที่เด่นชัด ควรอ่านเป็น evidence base สำหรับการตัดสินใจ มากกว่าสรุปว่ามี policy uptake แล้ว') . '.',
                'access_population' => 'output ของกลุ่ม Access / Population มีบทบาทแบบ policy agenda มากกว่า clinical evidence โดยตรง ผลงานที่พบ เช่น ' . $outputList . ' ช่วยเปิดพื้นที่ให้ประเด็นการเข้าถึง ความเป็นธรรม และกลุ่มประชากรเฉพาะถูกมองเห็นในระดับระบบ' . ($hasAny(['EA112', 'EA114', 'EA115', 'EA117']) ? ' น้ำหนักสำคัญอยู่ที่เวที การประชุม และการสื่อสารกับผู้เกี่ยวข้อง ซึ่งเหมาะกับงานผลักดันวาระนโยบาย' : ' ถ้าจะให้แรงขึ้น ควรมี output ที่เชื่อมประเด็นเหล่านี้ไปถึงผู้กำหนดนโยบายหรือเวทีสาธารณะอย่างเป็นรูปธรรม') . '.',
                'device_innovation' => 'หมวด Device / Innovation ไม่ควรถูกอ่านเป็นแค่งานประดิษฐ์หรือเทคโนโลยีใหม่ แต่เป็นงานถามว่า “ระบบสุขภาพพร้อมรับนวัตกรรมหรือยัง” output ที่พบ ได้แก่ ' . $outputList . ' จึงควรถูกใช้เพื่อประเมิน readiness, adoption, financing และการกระจายเทคโนโลยี' . ($hasAny(['EA102', 'EA106']) ? ' หากมีรายงานหรือข้อเสนอเชิงนโยบายประกอบ จะทำให้การคุยเรื่องนำเทคโนโลยีเข้า benefit package มีฐานรองรับมากขึ้น' : ' สิ่งที่ยังควรผลักต่อคือรายงาน implementation หรือ service readiness ที่บอกได้ว่านวัตกรรมพร้อมใช้จริงในหน่วยบริการระดับใด') . '.',
                'data_rwe' => 'ทิศทางของ output ในกลุ่ม Data / RWE คือการสร้าง “โครงสร้างพื้นฐานของหลักฐาน” ผลงานอย่าง ' . $outputList . ' มีความหมายมากกว่าผลงานปลายทาง เพราะเป็นฐานสำหรับติดตาม outcome จริง ตรวจสอบ variation ของบริการ และทำให้ policy discussion อิงข้อมูล routine data ได้' . ($hasAny(['EA104', 'EA105']) ? ' จุดแข็งคือมี output ประเภทข้อมูลหรือระบบ ซึ่งเป็นต้นทุนของ learning health system' : ' จุดที่ควรเติมคือ output ประเภทฐานข้อมูล dashboard หรือ data quality report เพื่อให้เห็นว่าข้อมูลถูกนำกลับไปใช้ปรับระบบบริการจริง') . '.',
                'ai_digital' => 'สำหรับ AI / Digital Health น้ำหนักของ output ควรถูกอ่านที่ “การนำไปใช้ใน workflow” ไม่ใช่ความใหม่ของ algorithm เท่านั้น ผลงานที่พบ ได้แก่ ' . $outputList . ' จึงควรถามต่อว่าเทคโนโลยีนั้นช่วยลดภาระงาน เพิ่มคุณภาพการตัดสินใจ หรือเชื่อมการดูแลได้จริงเพียงใด' . ($hasAny(['EA105', 'EA112', 'EA115']) ? ' การมีเครื่องมือ การสื่อสาร หรือกิจกรรมถ่ายทอดความรู้ช่วยบอกว่าเริ่มขยับจาก prototype ไปสู่การใช้งานจริง' : ' สิ่งที่ยังควรเสริมคือ output ด้าน implementation evaluation, user adoption และ safety monitoring') . '.',
                'drug_rdu' => 'หมวด Drug / RDU มีลักษณะเป็นงาน policy-in-practice คือไม่ได้จบที่รู้ว่ายาใดเหมาะสม แต่ต้องทำให้การสั่งใช้ยาเปลี่ยนจริงในระบบบริการ output ที่พบ เช่น ' . $outputList . ' ควรถูกอ่านในฐานะเครื่องมือสร้าง feedback loop ให้ทีมรักษาและผู้บริหารคุณภาพ' . ($hasAny(['EA102', 'EA109']) ? ' ถ้ามีรายงานหรือการเปลี่ยนแนวปฏิบัติร่วมด้วย จะเป็นหลักฐานสำคัญว่างาน RDU เริ่มขยับจาก monitoring ไปสู่ governance' : ' จุดที่ควรเติมคือ prescribing quality dashboard, medication safety report หรือ decision support brief ที่ทำให้ทีมรักษาใช้ข้อมูลกลับไปปรับพฤติกรรมได้') . '.',
                default => 'output ที่พบในหมวดนี้ ได้แก่ ' . $outputList . ' ควรอ่านร่วมกับโจทย์ของโครงการและผู้ใช้ประโยชน์ปลายทาง เพื่อแยกให้ออกว่าสิ่งที่เกิดขึ้นเป็นองค์ความรู้ เครื่องมือสื่อสาร หลักฐานประกอบนโยบาย หรือกลไกเปลี่ยนการปฏิบัติ.',
            };
        };
        $projectNatureThemeRows = $projectNatureThemeDefinitions
            ->map(function (array $theme) use ($projectNatureProjects, $projectNatureTotal, $projectNatureMatches, $normalizeProjectType, $projectRows, $outputLabelForNature, $buildNatureOutputDirection) {
                $matches = $projectNatureProjects
                    ->filter(fn (Project $project) => $projectNatureMatches($project, $theme['keywords']))
                    ->values();
                $projectIds = $matches->pluck('id')->map(fn ($id) => (int) $id)->filter()->unique()->values();
                $themeOutputs = $projectRows
                    ->filter(fn ($row) => $projectIds->contains((int) ($row->project_id ?? 0)))
                    ->values();
                $outputTypeMix = $themeOutputs
                    ->groupBy(fn ($row) => strtoupper((string) ($row->type_code ?? 'OTHER')))
                    ->map(fn (Collection $group, string $code) => [
                        'code' => $code,
                        'label' => $outputLabelForNature($code),
                        'count' => $group->count(),
                    ])
                    ->sortByDesc('count')
                    ->values();
                $outputDirection = $buildNatureOutputDirection($theme, $outputTypeMix);
                $typeMix = $matches
                    ->groupBy(fn (Project $project) => $normalizeProjectType($project->project_type))
                    ->map(fn (Collection $group, string $type) => [
                        'type' => $type ?: 'ไม่ระบุกลุ่ม',
                        'count' => $group->count(),
                    ])
                    ->sortByDesc('count')
                    ->values();

                return array_merge($theme, [
                    'count' => $matches->count(),
                    'share' => round(($matches->count() / $projectNatureTotal) * 100, 1),
                    'output_count' => $themeOutputs->count(),
                    'output_type_mix' => $outputTypeMix->values()->all(),
                    'output_direction' => $outputDirection,
                    'output_examples' => $themeOutputs
                        ->sortByDesc(fn ($row) => (int) ($row->year_be ?? 0))
                        ->take(2)
                        ->map(fn ($row) => [
                            'title' => trim((string) ($row->title ?? '')) ?: '-',
                            'year' => is_numeric($row->year_be ?? null) ? (int) $row->year_be : null,
                            'type' => $outputLabelForNature(strtoupper((string) ($row->type_code ?? 'OTHER'))),
                        ])
                        ->values()
                        ->all(),
                    'type_mix' => $typeMix->take(3)->values()->all(),
                    'examples' => $matches
                        ->sortByDesc(fn (Project $project) => (int) ($this->normalizeYearFilter($project->start_year) ?? 0))
                        ->take(3)
                        ->map(fn (Project $project) => [
                            'code' => trim((string) ($project->project_code ?? '')),
                            'year' => $this->normalizeYearFilter($project->start_year),
                            'title' => trim((string) ($project->project_name_th ?? '')),
                            'type' => $normalizeProjectType($project->project_type),
                        ])
                        ->values()
                        ->all(),
                ]);
            })
            ->sortByDesc('count')
            ->values();
        $projectNatureTypeReadings = [
            '01 Aging and Frailty' => 'ฐานหลักของกลุ่มนี้คือ aging system: frailty, hip fracture, rehabilitation, transitional care และ outcome ของผู้สูงอายุ',
            '02 Health Systems and HTA' => 'ทำหน้าที่เป็น policy/economics engine: ประเมินความคุ้มค่า ความพร้อม การเข้าถึง และการนำเทคโนโลยีเข้าสู่ระบบ',
            '03 Rational Drug Use (RDU)' => 'เป็นกลุ่ม focused operational policy: prescribing quality, inappropriate medication และการใช้ยาอย่างสมเหตุผล',
            '04 Real-World Evidence (RWE) and Clinical–Technical Cohorts' => 'เป็น data infrastructure layer: ฐานข้อมูล, cohort, EMR linkage, AI report และ chronic disease tracking',
            '05 Global Health' => 'ทำหน้าที่ agenda setting และ policy dialogue: UHC, financing, PMAC, PM2.5 และ prevention',
            '06 Other Areas' => 'เป็นพื้นที่ทดลองโจทย์ข้ามกลุ่ม เช่น AI translation, community health, oncology support และ evaluation framework',
        ];
        $projectNatureByTypeRows = $projectNatureProjects
            ->groupBy(fn (Project $project) => $normalizeProjectType($project->project_type))
            ->map(function (Collection $group, string $type) use ($projectNatureThemeDefinitions, $projectNatureMatches, $projectNatureTypeReadings) {
                $themeSignals = $projectNatureThemeDefinitions
                    ->map(function (array $theme) use ($group, $projectNatureMatches) {
                        return [
                            'label' => $theme['short_label'],
                            'count' => $group->filter(fn (Project $project) => $projectNatureMatches($project, $theme['keywords']))->count(),
                        ];
                    })
                    ->filter(fn (array $theme) => (int) $theme['count'] > 0)
                    ->sortByDesc('count')
                    ->take(3)
                    ->values();

                return [
                    'type' => $type ?: 'ไม่ระบุกลุ่ม',
                    'count' => $group->count(),
                    'year_from' => $group->min(fn (Project $project) => $this->normalizeYearFilter($project->start_year) ?: null),
                    'year_to' => $group->max(fn (Project $project) => $this->normalizeYearFilter($project->start_year) ?: null),
                    'signals' => $themeSignals->all(),
                    'reading' => $projectNatureTypeReadings[$type] ?? 'เป็นกลุ่มที่ควรอ่านร่วมกับชื่อโครงการและ output ที่เกิดขึ้นจริง',
                    'examples' => $group
                        ->sortByDesc(fn (Project $project) => (int) ($this->normalizeYearFilter($project->start_year) ?? 0))
                        ->take(3)
                        ->map(fn (Project $project) => [
                            'code' => trim((string) ($project->project_code ?? '')),
                            'year' => $this->normalizeYearFilter($project->start_year),
                            'title' => trim((string) ($project->project_name_th ?? '')),
                        ])
                        ->values()
                        ->all(),
                ];
            })
            ->sortBy('type')
            ->values();
        $topProjectNatureThemes = $projectNatureThemeRows->take(3);
        $projectNatureAnalysis = [
            'project_total' => $projectNatureProjects->count(),
            'theme_rows' => $projectNatureThemeRows,
            'type_rows' => $projectNatureByTypeRows,
            'headline' => $topProjectNatureThemes->isNotEmpty()
                ? 'ภาพรวมความสนใจของหน่วย'
                : 'ยังไม่มีข้อมูลชื่อโครงการเพียงพอสำหรับอ่านความสนใจเชิงเนื้อหา',
        ];

        $htaProjects = $projectAnalysisRows
            ->filter(fn (Project $project) => $matchesProjectTypeKeyword($project, ['hta', 'health systems', 'health technology assessment', '02']))
            ->values();
        $clinicalLedProjects = $projectAnalysisRows
            ->filter(fn (Project $project) => $matchesProjectTypeKeyword($project, ['aging', 'ageing', 'frailty', 'real-world evidence', 'real world evidence', 'rwe', 'clinical']))
            ->values();
        $htaBudgetByYear = $htaProjects
            ->groupBy(fn (Project $project) => $this->normalizeYearFilter($project->start_year) ?: 'ไม่ระบุปี')
            ->map(fn (Collection $group) => (float) $group->sum(fn (Project $project) => $parseBudget($project->budget)))
            ->sortKeys()
            ->map(fn ($value, $year) => (object) ['year' => $year, 'budget' => (float) $value])
            ->values();
        $maxHtaBudget = (float) max(1, $htaBudgetByYear->max('budget') ?: 1);
        $impactCodes = collect(['EA101', 'EA109', 'EA102', 'EA106']);
        $includePolicyImpactProjectRow = static fn ($row): bool => ($row->source ?? '') === 'project'
            && $normalizeProjectType($row->project_type ?? null) !== $projectTypeLabelByCode['06'];
        $impactRows = $filtered
            ->filter($includePolicyImpactProjectRow)
            ->values();
        $impactAllRows = $rows
            ->filter($includePolicyImpactProjectRow)
            ->values();
        $impactOutputRows = $impactCodes->mapWithKeys(fn ($code) => [
            $code => (int) $impactRows->where('type_code', $code)->count(),
        ]);
        $impactOutputItems = $impactCodes->mapWithKeys(function ($code) use ($impactRows, $sortRowsNewestFirst, $eaFullNameMap) {
            return [
                $code => $sortRowsNewestFirst($impactRows->where('type_code', $code)->values())
                    ->take(8)
                    ->map(fn ($row) => (object) [
                        'title' => trim((string) ($row->title ?? '')) ?: '-',
                        'reference' => trim((string) ($row->reference ?? '')) ?: '-',
                        'year' => is_numeric($row->year_be ?? null) ? (int) $row->year_be : null,
                        'source_label' => trim((string) ($row->source_label ?? '')),
                        'type_code' => $code,
                        'type_label' => $eaFullNameMap[$code] ?? $code,
                        'link' => trim((string) ($row->link ?? '')) ?: null,
                    ])
                    ->values(),
            ];
        });
        $impactTrendYears = $impactRows
            ->whereIn('type_code', $impactCodes->all())
            ->pluck('year_be')
            ->filter(fn ($year) => is_numeric($year))
            ->map(fn ($year) => (int) $year)
            ->unique()
            ->sort()
            ->values();
        $impactTrendSeries = $impactCodes->mapWithKeys(function ($code) use ($impactRows, $impactTrendYears) {
            return [
                $code => $impactTrendYears
                    ->map(fn ($year) => (int) $impactRows
                        ->where('type_code', $code)
                        ->where('year_be', (int) $year)
                        ->count())
                    ->values(),
            ];
        });
        $impactTrendItems = $impactCodes->mapWithKeys(function ($code) use ($impactRows, $impactTrendYears, $sortRowsNewestFirst, $eaFullNameMap) {
            return [
                $code => $impactTrendYears
                    ->mapWithKeys(function ($year) use ($impactRows, $sortRowsNewestFirst, $code, $eaFullNameMap) {
                        return [(string) $year => $sortRowsNewestFirst($impactRows
                            ->where('type_code', $code)
                            ->where('year_be', (int) $year)
                            ->values())
                            ->map(fn ($row) => [
                                'title' => trim((string) ($row->title ?? '')) ?: '-',
                                'meta' => trim((string) ($row->reference ?? '')) ?: '-',
                                'type' => $code . ' ' . ($eaFullNameMap[$code] ?? $code),
                                'link' => trim((string) ($row->link ?? '')) ?: null,
                                'year' => (int) $year,
                                'people' => [],
                            ])
                            ->values()];
                    }),
            ];
        });
        $impactTrendItemsByYear = $impactTrendYears->mapWithKeys(function ($year) use ($impactRows, $impactCodes, $sortRowsNewestFirst, $eaFullNameMap) {
            return [(string) $year => $sortRowsNewestFirst($impactRows
                ->whereIn('type_code', $impactCodes->all())
                ->where('year_be', (int) $year)
                ->values())
                ->map(function ($row) use ($eaFullNameMap) {
                    $code = trim((string) ($row->type_code ?? ''));

                    return [
                        'title' => trim((string) ($row->title ?? '')) ?: '-',
                        'meta' => trim((string) ($row->reference ?? '')) ?: '-',
                        'type' => trim($code . ' ' . ($eaFullNameMap[$code] ?? $code)),
                        'link' => trim((string) ($row->link ?? '')) ?: null,
                        'year' => is_numeric($row->year_be ?? null) ? (int) $row->year_be : (int) $year,
                        'people' => [],
                    ];
                })
                ->values()];
        });
        $impactCompleteProjectRows = $impactRows
            ->where('source', 'project')
            ->filter(fn ($row) => is_numeric($row->project_id ?? null))
            ->whereIn('type_code', $impactCodes->all())
            ->groupBy(fn ($row) => (int) $row->project_id)
            ->map(function (Collection $projectRows) use ($impactCodes, $eaFullNameMap) {
                $codes = $projectRows
                    ->pluck('type_code')
                    ->map(fn ($code) => strtoupper((string) $code))
                    ->unique()
                    ->values();

                if ($impactCodes->diff($codes)->isNotEmpty()) {
                    return null;
                }

                $first = $projectRows->first();
                $years = $projectRows
                    ->pluck('year_be')
                    ->filter(fn ($year) => is_numeric($year))
                    ->map(fn ($year) => (int) $year)
                    ->unique()
                    ->sort()
                    ->values();
                $link = trim((string) ($first->link ?? ''));
                $projectLink = $link !== '' ? preg_replace('/#.*$/', '', $link) : null;

                return (object) [
                    'project_id' => (int) ($first->project_id ?? 0),
                    'project_name' => trim((string) ($first->project_name ?? '')) ?: trim((string) ($first->reference ?? '')) ?: '-',
                    'project_type' => trim((string) ($first->project_type ?? '')),
                    'link' => $projectLink,
                    'year_from' => $years->first(),
                    'year_to' => $years->last(),
                    'output_count' => (int) $projectRows->count(),
                    'counts' => $impactCodes
                        ->mapWithKeys(fn ($code) => [
                            $code => (int) $projectRows->where('type_code', $code)->count(),
                        ])
                        ->all(),
                    'type_labels' => $impactCodes
                        ->mapWithKeys(fn ($code) => [
                            $code => $eaFullNameMap[$code] ?? $code,
                        ])
                        ->all(),
                ];
            })
            ->filter()
            ->sortByDesc(fn ($row) => (int) ($row->output_count ?? 0))
            ->values();
        $impactThemeAnalysisRows = $impactRows
            ->where('source', 'project')
            ->filter(fn ($row) => is_numeric($row->project_id ?? null))
            ->whereIn('type_code', $impactCodes->all())
            ->groupBy(fn ($row) => $normalizeProjectType($row->project_type))
            ->map(function (Collection $themeRows, string $themeLabel) use ($impactCodes, $eaFullNameMap, $normalizeProjectType) {
                $projectCount = (int) $themeRows->pluck('project_id')->filter()->unique()->count();
                $counts = $impactCodes
                    ->mapWithKeys(fn ($code) => [
                        $code => (int) $themeRows->where('type_code', $code)->count(),
                    ]);
                $presentCodes = $counts
                    ->filter(fn ($count) => (int) $count > 0)
                    ->keys()
                    ->values();
                $missingCodes = $impactCodes
                    ->diff($presentCodes)
                    ->values();
                $dominantCode = $counts->sortDesc()->keys()->first();
                $completeProjectCount = $themeRows
                    ->groupBy(fn ($row) => (int) $row->project_id)
                    ->filter(function (Collection $projectRows) use ($impactCodes) {
                        $codes = $projectRows
                            ->pluck('type_code')
                            ->map(fn ($code) => strtoupper((string) $code))
                            ->unique();

                        return $impactCodes->diff($codes)->isEmpty();
                    })
                    ->count();
                $labelFor = fn ($code) => $eaFullNameMap[$code] ?? $code;
                $projectPresence = $impactCodes
                    ->mapWithKeys(function ($code) use ($themeRows, $projectCount) {
                        $projectsWithCode = (int) $themeRows
                            ->where('type_code', $code)
                            ->pluck('project_id')
                            ->filter()
                            ->unique()
                            ->count();

                        return [
                            $code => [
                                'with' => $projectsWithCode,
                                'missing' => max(0, $projectCount - $projectsWithCode),
                            ],
                        ];
                    });
                $weaknessRows = $projectPresence
                    ->map(fn ($stats, $code) => (object) [
                        'code' => $code,
                        'label' => $labelFor($code),
                        'with' => (int) ($stats['with'] ?? 0),
                        'missing' => (int) ($stats['missing'] ?? 0),
                    ])
                    ->sortByDesc(fn ($row) => (int) $row->missing)
                    ->values();
                $mainWeakness = $weaknessRows->first();
                $missingText = $missingCodes->isNotEmpty()
                    ? $missingCodes->map($labelFor)->implode(', ')
                    : ($mainWeakness && (int) $mainWeakness->missing > 0
                        ? ($mainWeakness->label . ' ใน ' . number_format((int) $mainWeakness->missing) . ' จาก ' . number_format($projectCount) . ' โครงการ')
                        : 'ยังไม่เห็น output ที่ขาดชัดเจนในธีมนี้');
                $presentText = $presentCodes->isNotEmpty()
                    ? $presentCodes->map($labelFor)->implode(', ')
                    : 'ยังไม่มี output ใน 4 กลุ่มนี้';
                $effectByCode = [
                    'EA101' => 'ผลคือฐาน publication สำหรับการอ้างอิงภายนอกและการสร้าง credibility ของ evidence ยังอาจไม่แข็งแรงพอ',
                    'EA102' => 'ผลคือการสังเคราะห์และสื่อสาร evidence เป็นรายงานหรือ white paper ยังอาจไม่เพียงพอสำหรับส่งต่อเชิงระบบ',
                    'EA106' => 'ผลคือ evidence ยังอาจไม่ถูกแปลงเป็นข้อเสนอเชิงนโยบายที่ใช้ประกอบการตัดสินใจได้ชัดเจน',
                    'EA109' => 'ผลคือการเปลี่ยนจากข้อเสนอหรือหลักฐานไปสู่ practice/policy implementation ยังอาจเกิดขึ้นจำกัด',
                ];
                $effectText = $mainWeakness
                    ? ($effectByCode[$mainWeakness->code] ?? 'ผลคือ pathway ของธีมนี้ยังมีบางช่วงที่ต้องเสริมเพื่อให้เกิด policy impact ชัดขึ้น')
                    : 'ผลคือธีมนี้ยังไม่มีจุดอ่อนเด่นจากข้อมูลชุดนี้';
                $overviewText = 'ธีมนี้มี ' . number_format($projectCount) . ' โครงการ และมี output ในกลุ่ม policy impact รวม ' . number_format((int) $themeRows->count()) . ' รายการ โดย output ที่เด่นที่สุดคือ ' . $labelFor($dominantCode) . ' (' . number_format((int) ($counts[$dominantCode] ?? 0)) . ' รายการ)';
                $gapText = $mainWeakness && (int) $mainWeakness->missing > 0
                    ? 'เมื่อดูรายโครงการ ส่วนที่ขาดมากที่สุดคือ ' . $mainWeakness->label . ' โดยมีเพียง ' . number_format((int) $mainWeakness->with) . ' จาก ' . number_format($projectCount) . ' โครงการที่มี output แบบนี้'
                    : 'เมื่อดูรายโครงการ ยังไม่พบจุดที่ขาดเด่นชัดจาก output ทั้ง 4 กลุ่ม';
                $interpretationText = $effectText . ' Output ที่พบแล้วในธีมนี้ ได้แก่ ' . $presentText . ' จึงควรเสริมจุดที่ขาดเป็นรายโครงการ ไม่ใช่อ่านเพียงว่าระดับธีมมีครบหรือไม่';
                $strengthText = 'จุดเด่นของหมวดนี้คือ ' . $labelFor($dominantCode) . ' ซึ่งมีจำนวนมากที่สุดในชุด output ของหมวดนี้ สะท้อนว่าหมวดนี้มีฐาน output ในขั้นนี้ค่อนข้างชัด.';
                $weaknessText = $mainWeakness && (int) $mainWeakness->missing > 0
                    ? 'จุดด้อยคือ ' . $mainWeakness->label . ' ยังพบในสัดส่วนจำกัด โดยขาดใน ' . number_format((int) $mainWeakness->missing) . ' จาก ' . number_format($projectCount) . ' โครงการ.'
                    : 'จุดด้อยยังไม่ชัดจากข้อมูลชุดนี้ เพราะ output ทั้ง 4 กลุ่มกระจายตัวค่อนข้างครบในระดับรายโครงการ.';
                $overviewText = Str::finish($overviewText, '.');
                $gapText = Str::finish($gapText, '.');
                $interpretationText = Str::finish($interpretationText, '.');

                return (object) [
                    'theme' => $themeLabel ?: $normalizeProjectType(null),
                    'project_count' => $projectCount,
                    'output_count' => (int) $themeRows->count(),
                    'complete_project_count' => (int) $completeProjectCount,
                    'dominant_code' => $dominantCode,
                    'dominant_label' => $labelFor($dominantCode),
                    'dominant_count' => (int) ($counts[$dominantCode] ?? 0),
                    'counts' => $counts->all(),
                    'missing_codes' => $missingCodes->all(),
                    'missing_text' => $missingText,
                    'effect_text' => $effectText,
                    'main_weakness_code' => $mainWeakness?->code,
                    'main_weakness_label' => $mainWeakness?->label,
                    'main_weakness_with' => (int) ($mainWeakness?->with ?? 0),
                    'main_weakness_missing' => (int) ($mainWeakness?->missing ?? 0),
                    'project_presence' => $projectPresence->all(),
                    'overview_text' => $overviewText,
                    'strength_text' => $strengthText,
                    'weakness_text' => $weaknessText,
                    'gap_text' => $gapText,
                    'interpretation_text' => $interpretationText,
                ];
            })
            ->sortByDesc(fn ($row) => (int) ($row->output_count ?? 0))
            ->values();
        $impactThemeSummaryTopTheme = $impactThemeAnalysisRows->first();
        $impactThemeProjectTotal = (int) $impactRows
            ->whereIn('type_code', $impactCodes->all())
            ->pluck('project_id')
            ->filter()
            ->unique()
            ->count();
        $impactThemeWeaknessSummary = $impactCodes
            ->map(function ($code) use ($impactRows, $impactThemeProjectTotal, $eaFullNameMap) {
                $projectsWithCode = (int) $impactRows
                    ->where('type_code', $code)
                    ->pluck('project_id')
                    ->filter()
                    ->unique()
                    ->count();

                return (object) [
                    'code' => $code,
                    'label' => $eaFullNameMap[$code] ?? $code,
                    'with' => $projectsWithCode,
                    'missing' => max(0, $impactThemeProjectTotal - $projectsWithCode),
                ];
            })
            ->sortByDesc(fn ($row) => (int) ($row->missing ?? 0))
            ->values();
        $impactThemeMainWeakness = $impactThemeWeaknessSummary->first();
        $impactThemeEffectByCode = [
            'EA101' => 'ส่งผลให้ฐาน publication และ credibility สำหรับการอ้างอิงภายนอกยังไม่แข็งแรงเท่าที่ควร.',
            'EA102' => 'ส่งผลให้การสังเคราะห์และสื่อสาร evidence เป็นรายงานหรือ white paper ยังไม่พอสำหรับส่งต่อเชิงระบบ.',
            'EA106' => 'ส่งผลให้ evidence ยังไม่ถูกแปลงเป็นข้อเสนอเชิงนโยบายที่ใช้ประกอบการตัดสินใจได้ชัดเจน.',
            'EA109' => 'ส่งผลให้การขยับจากหลักฐานหรือข้อเสนอไปสู่ practice/policy implementation ยังเกิดขึ้นจำกัด.',
        ];
        $impactThemeNarrative = (object) [
            'overview' => $impactThemeAnalysisRows->isNotEmpty()
                ? 'ภาพรวมตามธีมพบว่า ' . ($impactThemeSummaryTopTheme?->theme ?? '-') . ' เป็นธีมที่มี output ในกลุ่ม policy impact มากที่สุด โดยมี ' . number_format((int) ($impactThemeSummaryTopTheme?->output_count ?? 0)) . ' รายการ จาก ' . number_format((int) ($impactThemeSummaryTopTheme?->project_count ?? 0)) . ' โครงการ และ output ที่เด่นคือ ' . ($impactThemeSummaryTopTheme?->dominant_label ?? '-') . '.'
                : 'ยังไม่มีข้อมูล output ในระดับธีมสำหรับช่วงที่เลือก.',
            'gap' => $impactThemeMainWeakness && (int) ($impactThemeMainWeakness->missing ?? 0) > 0
                ? 'เมื่อดูรายโครงการในภาพรวม ส่วนที่ขาดมากที่สุดคือ ' . ($impactThemeMainWeakness->label ?? '-') . ' โดยมีเพียง ' . number_format((int) ($impactThemeMainWeakness->with ?? 0)) . ' จาก ' . number_format($impactThemeProjectTotal) . ' โครงการที่มี output แบบนี้.'
                : 'เมื่อดูรายโครงการในภาพรวม ยังไม่พบ output ที่ขาดเด่นชัดจาก 4 กลุ่มหลัก.',
            'effect' => $impactThemeMainWeakness && (int) ($impactThemeMainWeakness->missing ?? 0) > 0
                ? (($impactThemeEffectByCode[$impactThemeMainWeakness->code] ?? 'ส่งผลให้ pathway ของ policy impact ยังมีบางช่วงที่ต้องเสริมให้ชัดขึ้น.') . ' ดังนั้นการอ่านกราฟนี้ควรมองว่า output ที่มีอยู่ยังบอกได้ว่าธีมไหนผลิตหลักฐานหรือรายงานได้ดี แต่จุดที่ต้องเสริมคือการต่อยอดใน output ที่ยังขาดเป็นรายโครงการ.')
                : 'โดยรวมสะท้อนว่า output ทั้ง 4 กลุ่มเริ่มกระจายครบขึ้นในระดับโครงการ จึงควรติดตามคุณภาพและการนำไปใช้จริงในรอบถัดไป.',
        ];
        $themeNarrativePhrase = static function (string $theme): string {
            return match ($theme) {
                '01 Aging and Frailty' => 'หมวด Aging and Frailty',
                '02 Health Systems and HTA' => 'หมวด Health Systems and HTA',
                '04 Real-World Evidence (RWE) and Clinical-Technical Cohorts' => 'หมวด Real-World Evidence (RWE) and Clinical-Technical Cohorts',
                default => 'หมวด ' . preg_replace('/^\d+\s*/', '', $theme),
            };
        };
        $themeDominantSentence = static function (string $code, string $label, string $theme): string {
            if ($code === 'EA101' && Str::contains($theme, 'Real-World Evidence')) {
                return 'มีความโดดเด่นด้านการสร้างองค์ความรู้จากข้อมูลและ cohort เชิงคลินิก โดยเฉพาะในรูปแบบ ' . $label;
            }

            return match ($code) {
                'EA101' => 'มีจุดเด่นด้านการสร้างองค์ความรู้เชิงวิชาการ โดยพบ ' . $label . ' เป็น output หลักของหมวด สะท้อนความเข้มแข็งด้านฐานความรู้และงานวิจัย',
                'EA102' => 'มีความโดดเด่นด้าน ' . $label . ' ซึ่งสะท้อนบทบาทของหมวดในการสังเคราะห์และสื่อสารข้อมูลเชิงระบบได้ค่อนข้างชัด',
                'EA106' => 'มีจุดเด่นด้าน ' . $label . ' สะท้อนการต่อยอด evidence ไปสู่ข้อเสนอเชิงนโยบาย',
                'EA109' => 'มีจุดเด่นด้าน ' . $label . ' สะท้อนการขยับจากหลักฐานไปสู่การเปลี่ยนแปลงเชิงปฏิบัติหรือเชิงนโยบาย',
                default => 'มี output หลักคือ ' . $label . ' สะท้อนทิศทางการสร้างผลงานในหมวดนี้',
            };
        };
        $themeWeaknessEffect = static function (string $code, string $theme): string {
            if ($code === 'EA109' && Str::contains($theme, 'Aging and Frailty')) {
                return 'การเชื่อมโยงจากงานวิจัยไปสู่การเปลี่ยนแปลงเชิงระบบหรือเชิงปฏิบัติยังอยู่ในระยะเริ่มต้น';
            }
            if ($code === 'EA109' && Str::contains($theme, 'Real-World Evidence')) {
                return 'การนำข้อมูลเชิงประจักษ์ไปใช้ประโยชน์เชิงนโยบายหรือการขับเคลื่อน implementation ในระดับระบบยังต้องอาศัยกระบวนการต่อยอดเพิ่มเติม';
            }

            return match ($code) {
                'EA101' => 'ฐาน publication และ credibility สำหรับการอ้างอิงภายนอกยังอาจไม่แข็งแรงพอ',
                'EA102' => 'การสังเคราะห์และสื่อสาร evidence เป็นรายงานหรือ white paper ยังอาจไม่เพียงพอสำหรับส่งต่อเชิงระบบ',
                'EA106' => 'evidence ยังอาจไม่ถูกแปลงเป็นข้อเสนอเชิงนโยบายที่ใช้ประกอบการตัดสินใจได้ชัดเจน',
                'EA109' => 'การต่อยอดจากข้อเสนอหรือหลักฐานเชิงระบบไปสู่ implementation ในระดับปฏิบัติหรือเชิงนโยบายยังมีข้อจำกัดบางประการ',
                default => 'policy impact pathway ของหมวดนี้ยังมีบางช่วงที่ต้องเสริมให้ชัดขึ้น',
            };
        };
        $impactThemeNarrativeParagraph = $impactThemeAnalysisRows->isNotEmpty()
            ? $impactThemeAnalysisRows
                ->map(function ($themeRow, $index) use ($themeNarrativePhrase, $themeDominantSentence, $themeWeaknessEffect) {
                    $prefix = match ($index) {
                        0 => '',
                        1 => 'ขณะที่',
                        2 => 'ส่วน',
                        default => 'นอกจากนี้',
                    };
                    $themeName = $themeNarrativePhrase((string) ($themeRow->theme ?? '-'));
                    $themeLabel = (string) ($themeRow->theme ?? '');
                    $dominantLabel = (string) ($themeRow->dominant_label ?? '-');
                    $dominantCode = (string) ($themeRow->dominant_code ?? '');
                    $dominantText = $themeDominantSentence($dominantCode, $dominantLabel, $themeLabel);
                    $weaknessCode = (string) ($themeRow->main_weakness_code ?? '');
                    $weaknessLabel = (string) ($themeRow->main_weakness_label ?? '-');
                    $weaknessWith = (int) ($themeRow->main_weakness_with ?? 0);
                    $weaknessMissing = (int) ($themeRow->main_weakness_missing ?? 0);
                    $projectCount = max(1, (int) ($themeRow->project_count ?? 0));
                    $weaknessText = $weaknessMissing >= $projectCount
                        ? 'อย่างไรก็ตาม ยังไม่พบ output ด้าน ' . $weaknessLabel . ' ในทั้ง ' . number_format($projectCount) . ' โครงการ ซึ่งอาจบ่งชี้ว่า ' . $themeWeaknessEffect($weaknessCode, $themeLabel)
                        : ($weaknessMissing > 0
                            ? 'อย่างไรก็ตาม output ด้าน ' . $weaknessLabel . ' ยังพบในเพียง ' . number_format($weaknessWith) . ' จาก ' . number_format($projectCount) . ' โครงการ ซึ่งอาจสะท้อนว่า ' . $themeWeaknessEffect($weaknessCode, $themeLabel)
                            : 'ขณะเดียวกันยังไม่พบ output ที่ขาดเด่นชัดในระดับรายโครงการ จึงควรติดตามคุณภาพและการนำไปใช้จริงต่อเนื่อง');

                    return Str::finish(trim(($prefix ? $prefix . ' ' : '') . $themeName . ' ' . $dominantText . ' ' . $weaknessText), '.');
                })
                ->implode("\n\n")
            : 'ยังไม่มีข้อมูล output ในระดับธีมสำหรับช่วงที่เลือก.';
        if ($impactThemeAnalysisRows->isNotEmpty()) {
            $impactThemeNarrativeParagraph .= $impactThemeMainWeakness && (int) ($impactThemeMainWeakness->missing ?? 0) > 0
                ? "\n\nในภาพรวม ข้อมูลสะท้อนให้เห็นว่า output ส่วนใหญ่ยังอยู่ในระดับการสร้างองค์ความรู้และการสื่อสารเชิงระบบ ขณะที่ output ที่สะท้อนการเปลี่ยนแปลงเชิงปฏิบัติหรือเชิงนโยบายยังพบในสัดส่วนจำกัด โดยช่องว่างที่ควรติดตามมากที่สุดคือ " . ($impactThemeMainWeakness->label ?? '-') . ' ซึ่งอาจบ่งชี้ว่าการพัฒนา knowledge translation pathway จากงานวิจัยไปสู่ implementation และ policy adoption ยังอยู่ในระยะพัฒนา และอาจต้องอาศัยกลไกสนับสนุนเพิ่มเติมในอนาคต.'
                : "\n\nโดยรวม output ทั้ง 4 กลุ่มเริ่มกระจายตัวครบขึ้นในระดับธีม จึงควรติดตามต่อว่าหลักฐานและข้อเสนอเหล่านี้ถูกนำไปใช้จริงในระดับระบบมากขึ้นเพียงใด.";
        }
        $communicationOutputCount = (int) $filtered
            ->whereIn('type_code', ['EA108', 'EA112', 'EA114', 'EA115'])
            ->count();
        $adoptionOutputCount = (int) $filtered->where('type_code', 'EA109')->count();
        $buildOutputItems = fn (Collection $rows, int $limit = 6): Collection => $sortRowsNewestFirst($rows->values())
            ->take($limit)
            ->map(fn ($row) => (object) [
                'title' => trim((string) ($row->title ?? '')) ?: '-',
                'reference' => trim((string) ($row->reference ?? '')) ?: '-',
                'year' => is_numeric($row->year_be ?? null) ? (int) $row->year_be : null,
                'type_code' => trim((string) ($row->type_code ?? '')),
                'source_label' => trim((string) ($row->source_label ?? '')),
                'link' => trim((string) ($row->link ?? '')) ?: null,
            ]);
        $longCycleProjects = $projectAnalysisRows
            ->filter(fn (Project $project) => $matchesProjectTypeKeyword($project, ['cohort', 'prospective', 'longitudinal', 'ติดตาม', 'ระยะยาว']))
            ->take(6)
            ->map(fn (Project $project) => (object) [
                'name' => trim((string) ($project->project_name_th ?? '')) ?: '-',
                'code' => trim((string) ($project->project_code ?? '')),
                'year' => $this->normalizeYearFilter($project->start_year),
                'type' => $normalizeProjectType($project->project_type),
                'link' => route('projects.show', $project),
            ])
            ->values();
        $dataSourceCounts = $dataSourceOptions
            ->mapWithKeys(fn ($option) => [
                $option => (int) $projectAnalysisRows
                    ->filter(fn (Project $project) => $normalizeDataSourceCategory($project->data_source ?? null) === $option)
                    ->count(),
            ]);
        $dataSourceTrendYears = $projectAnalysisRows
            ->map(fn (Project $project) => $this->normalizeYearFilter($project->start_year))
            ->filter(fn ($year) => is_numeric($year))
            ->map(fn ($year) => (int) $year)
            ->unique()
            ->sort()
            ->values();
        $dataSourceTrend = [
            'years' => $dataSourceTrendYears,
            'series' => $dataSourceOptions->mapWithKeys(fn ($option) => [
                $option => $dataSourceTrendYears
                    ->map(fn ($year) => (int) $projectAnalysisRows
                        ->filter(fn (Project $project) => (int) $this->normalizeYearFilter($project->start_year) === (int) $year)
                        ->filter(fn (Project $project) => $normalizeDataSourceCategory($project->data_source ?? null) === $option)
                        ->count())
                    ->values(),
            ]),
            'items' => $dataSourceOptions->mapWithKeys(fn ($option) => [
                $option => $dataSourceTrendYears
                    ->mapWithKeys(fn ($year) => [
                        (string) $year => $projectAnalysisRows
                            ->filter(fn (Project $project) => (int) $this->normalizeYearFilter($project->start_year) === (int) $year)
                            ->filter(fn (Project $project) => $normalizeDataSourceCategory($project->data_source ?? null) === $option)
                            ->map(fn (Project $project) => [
                                'title' => trim((string) ($project->project_name_th ?? '')) ?: '-',
                                'meta' => trim((string) ($project->project_code ?? '')) ?: trim((string) ($project->data_source ?? '')),
                                'type' => $normalizeProjectType($project->project_type),
                                'year' => $this->normalizeYearFilter($project->start_year),
                                'link' => route('projects.show', $project),
                            ])
                            ->sortBy(fn ($item) => (string) ($item['title'] ?? ''))
                            ->values(),
                    ])
                    ->all(),
            ]),
        ];
        $missingDataSourceCount = (int) $projectAnalysisRows
            ->filter(fn (Project $project) => trim((string) ($project->data_source ?? '')) === '')
            ->count();
        $missingDataSourceProjects = $projectAnalysisRows
            ->filter(fn (Project $project) => trim((string) ($project->data_source ?? '')) === '')
            ->take(6)
            ->map(fn (Project $project) => (object) [
                'name' => trim((string) ($project->project_name_th ?? '')) ?: '-',
                'code' => trim((string) ($project->project_code ?? '')),
                'year' => $this->normalizeYearFilter($project->start_year),
                'type' => $normalizeProjectType($project->project_type),
                'link' => route('projects.show', $project),
            ])
            ->values();
        $projectAnalysisTotal = (int) $projectAnalysisRows->count();
        $analysisUsers = User::query()
            ->select(['id', 'prefix', 'name', 'prefix_en', 'name_en', 'email', 'role', 'position', 'profile_photo_path', 'updated_at'])
            ->orderBy('name')
            ->get()
            ->values();
        $personalStatsMap = $this->buildPersonalUserStatsMap($analysisUsers);
        $personHighlights = $analysisUsers
            ->map(function (User $user) use ($projectAnalysisRows, $buildParticipantRows, $normalizeProjectType, $personalStatsMap, $sirirajProjects, $yearFrom, $yearTo, $currentYearBe) {
                $aliases = collect([
                    $this->normalizePersonNameForMatch($user->full_name),
                    $this->normalizePersonNameForMatch((string) $user->name),
                    $this->normalizePersonNameForMatch($user->research_display_name),
                    $this->normalizePersonNameForMatch((string) ($user->name_en ?? '')),
                ])->filter()->unique()->values();

                $matchedProjects = $projectAnalysisRows->map(function (Project $project) use ($buildParticipantRows, $user, $aliases) {
                    $matchedRoles = $buildParticipantRows($project)
                        ->filter(function ($participant) use ($user, $aliases) {
                            if ((int) ($participant->user_id ?? 0) > 0 && (int) $participant->user_id === (int) $user->id) {
                                return true;
                            }

                            return $aliases->contains($this->normalizePersonNameForMatch($participant->name ?? null));
                        })
                        ->pluck('role')
                        ->unique()
                        ->values();

                    if ($matchedRoles->isEmpty()) {
                        return null;
                    }

                    return (object) [
                        'project' => $project,
                        'roles' => $matchedRoles,
                    ];
                })->filter()->values();

                $roleCounts = [
                    'pi' => (int) $matchedProjects->filter(fn ($row) => $row->roles->contains('PI'))->count(),
                    'co_pi' => (int) $matchedProjects->filter(fn ($row) => $row->roles->contains('Co-PI'))->count(),
                    'co_research' => (int) $matchedProjects->filter(fn ($row) => $row->roles->contains('Co-Research'))->count(),
                ];
                $totalProjects = array_sum($roleCounts);
                if ($totalProjects <= 0) {
                    return null;
                }

                $topTheme = $matchedProjects
                    ->map(fn ($row) => $normalizeProjectType($row->project->project_type))
                    ->filter()
                    ->countBy()
                    ->sortDesc()
                    ->keys()
                    ->first();
                $rangeStart = is_numeric($yearFrom) ? (int) $yearFrom : 2565;
                $rangeEnd = is_numeric($yearTo) ? (int) $yearTo : $currentYearBe;
                $rangeLabel = $rangeEnd === $currentYearBe
                    ? 'ปี ' . $rangeStart . '-ปัจจุบัน'
                    : 'ปี ' . $rangeStart . '-' . $rangeEnd;
                $denominator = max(1, (int) $sirirajProjects->count());
                $sharePct = round(($totalProjects / $denominator) * 100, 1);
                $trendText = $rangeLabel . ' มี ' . number_format($totalProjects) . ' โครงการ คิดเป็น ' . number_format($sharePct, 1) . '% ของโครงการที่มีคนศิริราชร่วม';
                $stats = $personalStatsMap->get((int) $user->id, $this->emptyPersonalStats());

                return (object) [
                    'user' => $user,
                    'name' => $user->full_name ?: $user->name,
                    'position' => trim((string) ($user->position ?? '')),
                    'profile_photo_url' => $this->resolveUserProfilePhotoUrl($user),
                    'role_counts' => $roleCounts,
                    'total_projects' => $totalProjects,
                    'total_outputs' => (int) ($stats['total_outputs'] ?? 0),
                    'top_theme' => $topTheme ?: '-',
                    'trend_text' => $trendText,
                    'link' => route('outputs.people.show', $this->personalOutputRouteKey($user)),
                ];
            })
            ->filter()
            ->sortByDesc(fn ($person) => ($person->total_projects * 10) + $person->total_outputs)
            ->take(8)
            ->values();
        $userById = $analysisUsers->keyBy('id');
        $userByNormalizedName = $analysisUsers
            ->flatMap(function (User $user) {
                return collect([
                    $this->normalizePersonNameForMatch($user->full_name),
                    $this->normalizePersonNameForMatch((string) $user->name),
                    $this->normalizePersonNameForMatch($user->research_display_name),
                    $this->normalizePersonNameForMatch((string) ($user->name_en ?? '')),
                ])
                    ->filter()
                    ->unique()
                    ->map(fn ($alias) => ['alias' => $alias, 'user' => $user]);
            })
            ->groupBy('alias')
            ->map(fn (Collection $matches) => $matches->pluck('user.id')->unique()->count() === 1 ? $matches->first()['user'] : null)
            ->filter();
        $resolveParticipantUser = function ($row) use ($userById, $userByNormalizedName): ?User {
            $userId = (int) ($row->user_id ?? 0);
            if ($userId > 0 && $userById->has($userId)) {
                return $userById->get($userId);
            }

            $nameKey = $this->normalizePersonNameForMatch($row->name ?? null);
            return $nameKey !== '' ? $userByNormalizedName->get($nameKey) : null;
        };
        $positionForParticipant = function ($row) use ($userById): string {
            $user = ((int) ($row->user_id ?? 0) > 0) ? $userById->get((int) $row->user_id) : null;

            return trim((string) ($user?->position ?? ''));
        };
        $clinicalAffiliationKeywords = [
            'department of medicine',
            'division of endocrinology',
            'endocrinology',
            'metabolism',
            'surgery',
            'pediatrics',
            'paediatrics',
            'orthopaedic',
            'orthopedic',
            'radiology',
            'anesthesiology',
            'anaesthesiology',
            'obstetrics',
            'gynecology',
            'gynaecology',
            'psychiatry',
            'dentistry',
            'dental',
            'ภาควิชาอายุรศาสตร์',
            'ภาควิชาศัลยศาสตร์',
            'ภาควิชากุมาร',
            'ภาควิชารังสี',
            'ภาควิชาวิสัญญี',
            'ภาควิชาจิตเวช',
            'ภาควิชาสูติ',
            'ทันต',
        ];
        $hasClinicalAffiliationSignal = static function ($row) use ($clinicalAffiliationKeywords): bool {
            $affiliation = mb_strtolower(trim((string) ($row->affiliation ?? '')), 'UTF-8');

            return $affiliation !== '' && Str::contains($affiliation, $clinicalAffiliationKeywords);
        };
        $hasNursingSignal = static function ($row): bool {
            $name = mb_strtolower(trim((string) ($row->name ?? '')), 'UTF-8');
            $affiliation = mb_strtolower(trim((string) ($row->affiliation ?? '')), 'UTF-8');
            if ($name !== '' && Str::contains($name, ['พว.', 'พยาบาล', 'nurse'])) {
                return true;
            }

            return $affiliation !== '' && Str::contains($affiliation, [
                'ฝ่ายการพยาบาล',
                'งานการพยาบาล',
                'คณะพยาบาล',
                'สำนักวิชาพยาบาล',
                'nursing department',
                'department of nursing',
                'school of nursing',
                'faculty of nursing',
            ]);
        };
        $professionGroup = function ($row) use ($positionForParticipant, $hasNursingSignal): string {
            $text = mb_strtolower(trim(($positionForParticipant($row) ?: '') . ' ' . ($row->name ?? '')), 'UTF-8');
            if (Str::contains($text, ['พยาบาล', 'nurse']) || $hasNursingSignal($row)) {
                return 'พยาบาล';
            }
            if (Str::contains($text, ['นพ.', 'พญ.', 'แพทย์', 'doctor', 'physician'])) {
                return 'แพทย์ / Clinical expert';
            }
            if (Str::contains($text, ['เภสัช', 'pharmac'])) {
                return 'เภสัชกร';
            }
            if (Str::contains($text, ['data', 'engineer', 'developer', 'analyst', 'สถิติ', 'วิเคราะห์'])) {
                return 'Data / Analyst';
            }

            return 'นักวิจัย / เจ้าหน้าที่วิจัย';
        };
        $professionSourceForParticipant = function ($row) use ($positionForParticipant, $hasNursingSignal): array {
            $position = $positionForParticipant($row);
            if ($position !== '') {
                return ['label' => 'จากข้อมูลตำแหน่ง', 'position' => $position, 'evidence' => null];
            }
            if ($hasNursingSignal($row)) {
                return [
                    'label' => 'จากชื่อ/affiliation พยาบาล',
                    'position' => null,
                    'evidence' => trim((string) ($row->affiliation ?? '')) ?: trim((string) ($row->name ?? '')),
                ];
            }

            return ['label' => 'จากคำนำหน้า/คำในชื่อ', 'position' => null, 'evidence' => null];
        };
        $isDoctorUser = static function (User $user): bool {
            $text = mb_strtolower(trim(($user->full_name ?? '') . ' ' . ($user->name ?? '') . ' ' . ($user->position ?? '')), 'UTF-8');

            return $text !== '' && Str::contains($text, ['นพ.', 'พญ.', 'แพทย์', 'doctor', 'physician']);
        };
        $isExcludedDoctorPosition = static function (User $user): bool {
            $position = mb_strtolower(trim((string) ($user->position ?? '')), 'UTF-8');

            return $position !== '' && Str::contains($position, ['คณบดี', 'ที่ปรึกษา']);
        };
        $doctorThemeEntries = $analysisUsers
            ->filter($isDoctorUser)
            ->reject($isExcludedDoctorPosition)
            ->flatMap(function (User $user) use ($projectAnalysisRows, $buildParticipantRows, $normalizeProjectType) {
                $aliases = collect([
                    $this->normalizePersonNameForMatch($user->full_name),
                    $this->normalizePersonNameForMatch((string) $user->name),
                    $this->normalizePersonNameForMatch($user->research_display_name),
                    $this->normalizePersonNameForMatch((string) ($user->name_en ?? '')),
                ])->filter()->unique()->values();

                $matchedProjects = $projectAnalysisRows
                    ->map(function (Project $project) use ($buildParticipantRows, $user, $aliases, $normalizeProjectType) {
                        $matchedRoles = $buildParticipantRows($project)
                            ->filter(function ($participant) use ($user, $aliases) {
                                if ((int) ($participant->user_id ?? 0) > 0 && (int) $participant->user_id === (int) $user->id) {
                                    return true;
                                }

                                return (int) ($participant->user_id ?? 0) <= 0
                                    && $aliases->contains($this->normalizePersonNameForMatch($participant->name ?? null));
                            })
                            ->pluck('role')
                            ->unique()
                            ->values();

                        if ($matchedRoles->isEmpty()) {
                            return null;
                        }

                        return (object) [
                            'project' => $project,
                            'year' => $this->normalizeYearFilter($project->start_year),
                            'theme' => $normalizeProjectType($project->project_type),
                            'roles' => $matchedRoles,
                        ];
                    })
                    ->filter(fn ($row) => $row && is_numeric($row->year ?? null))
                    ->sortBy([
                        fn ($row) => (int) ($row->year ?? 9999),
                        fn ($row) => trim((string) ($row->theme ?? '')),
                    ])
                    ->values();

                if ($matchedProjects->isEmpty()) {
                    return collect();
                }

                return $matchedProjects
                    ->groupBy(fn ($row) => (trim((string) ($row->theme ?? '')) ?: 'ไม่ระบุกลุ่มงาน') . '|' . (int) ($row->year ?? 0))
                    ->map(function (Collection $yearThemeRows) use ($user, $matchedProjects) {
                        $first = $yearThemeRows->first();
                        $theme = trim((string) ($first?->theme ?? '')) ?: 'ไม่ระบุกลุ่มงาน';
                        $year = (int) ($first?->year ?? 0);
                        $yearThemeRows = $yearThemeRows
                            ->sortBy(fn ($row) => trim((string) ($row->project?->project_name_th ?? '')))
                            ->values();
                        $roleCounts = collect(['PI', 'Co-PI', 'Co-Research'])
                            ->mapWithKeys(fn ($role) => [
                                $role => (int) $matchedProjects
                                    ->filter(fn ($row) => collect($row->roles ?? [])->contains($role))
                                    ->count(),
                            ]);
                        $projectsByRole = collect(['PI', 'Co-PI', 'Co-Research'])
                            ->mapWithKeys(fn ($role) => [
                                $role => $matchedProjects
                                    ->filter(fn ($row) => collect($row->roles ?? [])->contains($role))
                                    ->map(fn ($row) => (object) [
                                        'name' => trim((string) ($row->project?->project_name_th ?? '')) ?: '-',
                                        'year' => (int) ($row->year ?? 0),
                                        'theme' => trim((string) ($row->theme ?? '')) ?: 'ไม่ระบุกลุ่มงาน',
                                        'link' => $row->project ? route('projects.show', $row->project) : null,
                                    ])
                                    ->values(),
                            ]);

                        return (object) [
                            'name' => $user->full_name ?: $user->name,
                            'position' => trim((string) ($user->position ?? '')),
                            'profile_photo_url' => $this->resolveUserProfilePhotoUrl($user),
                            'first_year' => $year,
                            'theme' => $theme,
                            'first_themes' => collect([$theme]),
                            'first_roles' => $yearThemeRows->flatMap(fn ($row) => collect($row->roles ?? []))->filter()->unique()->values(),
                            'first_projects' => $yearThemeRows
                                ->map(fn ($row) => (object) [
                                    'name' => trim((string) ($row->project?->project_name_th ?? '')) ?: '-',
                                    'link' => $row->project ? route('projects.show', $row->project) : null,
                                ])
                                ->take(2)
                                ->values(),
                            'total_projects' => (int) $matchedProjects->count(),
                            'role_counts' => $roleCounts->all(),
                            'projects_by_role' => $projectsByRole->all(),
                            'link' => route('outputs.people.show', $this->personalOutputRouteKey($user)),
                        ];
                    })
                    ->values();
            })
            ->filter()
            ->sortBy([
                fn ($row) => (int) ($row->first_year ?? 9999),
                fn ($row) => (string) ($row->theme ?? ''),
                fn ($row) => (string) ($row->name ?? ''),
            ])
            ->values();
        $doctorContributionStarts = $doctorThemeEntries
            ->unique(fn ($row) => (string) ($row->name ?? ''))
            ->values();
        $doctorContributionTimeline = $doctorThemeEntries
            ->map(fn ($entry) => (object) [
                'year' => (int) ($entry->first_year ?? 0),
                'theme' => (string) ($entry->theme ?? ''),
                'doctor' => $entry,
            ])
            ->groupBy(fn ($row) => ((int) ($row->year ?? 0)) . '|' . trim((string) ($row->theme ?? '')))
            ->map(function (Collection $rows) {
                $first = $rows->first();

                return (object) [
                    'year' => (int) ($first?->year ?? 0),
                    'theme' => trim((string) ($first?->theme ?? '')),
                    'doctors' => $rows
                        ->pluck('doctor')
                        ->unique(fn ($doctor) => (string) ($doctor->name ?? ''))
                        ->values(),
                ];
            })
            ->sortBy([
                fn ($row) => (int) ($row->year ?? 9999),
                fn ($row) => (string) ($row->theme ?? ''),
            ])
            ->values();
        $doctorContributionRoleTables = collect([
            'pi' => ['label' => 'PI', 'role' => 'PI'],
            'co_pi' => ['label' => 'Co-PI', 'role' => 'Co-PI'],
            'co_research' => ['label' => 'Co-Research', 'role' => 'Co-Research'],
        ])->mapWithKeys(function (array $roleMeta, string $key) use ($doctorContributionStarts) {
            $rows = $doctorContributionStarts
                ->map(function ($doctor) use ($roleMeta) {
                    $role = $roleMeta['role'];
                    $projects = collect($doctor->projects_by_role[$role] ?? [])
                        ->sortByDesc(fn ($project) => (int) ($project->year ?? 0))
                        ->values();

                    if ($projects->isEmpty()) {
                        return null;
                    }

                    $themes = $projects
                        ->pluck('theme')
                        ->filter()
                        ->unique()
                        ->values();

                    return (object) [
                        'name' => (string) ($doctor->name ?? '-'),
                        'position' => (string) ($doctor->position ?? ''),
                        'profile_photo_url' => (string) ($doctor->profile_photo_url ?? ''),
                        'theme_text' => $themes->implode(', '),
                        'project_count' => (int) $projects->count(),
                        'year_text' => $projects->pluck('year')->filter()->unique()->sort()->implode(', '),
                        'project_names' => $projects->pluck('name')->filter()->take(3)->values(),
                        'link' => (string) ($doctor->link ?? ''),
                    ];
                })
                ->filter()
                ->sortByDesc(fn ($row) => (int) ($row->project_count ?? 0))
                ->values();

            return [$key => (object) [
                'key' => $key,
                'label' => $roleMeta['label'],
                'rows' => $rows,
                'people_count' => (int) $rows->count(),
                'project_count' => (int) $rows->sum(fn ($row) => (int) ($row->project_count ?? 0)),
            ]];
        });
        $unitContributionBaseYears = $projectParticipantRows
            ->where('people_type', 'unit')
            ->pluck('year')
            ->filter()
            ->map(fn ($year) => (int) $year)
            ->unique()
            ->sort()
            ->values();
        $unitContributionStartYear = filled($yearFrom)
            ? (int) $yearFrom
            : (int) ($unitContributionBaseYears->min() ?: 0);
        $unitContributionEndYear = filled($yearTo)
            ? (int) $yearTo
            : (int) ($unitContributionBaseYears->max() ?: 0);
        $unitContributionYears = ($unitContributionStartYear > 0 && $unitContributionEndYear >= $unitContributionStartYear)
            ? collect(range($unitContributionStartYear, $unitContributionEndYear))
            : $unitContributionBaseYears;
        $unitContributionRoleThemeTables = collect([
            'pi' => ['label' => 'PI', 'role' => 'PI'],
            'co_pi' => ['label' => 'Co-PI', 'role' => 'Co-PI'],
            'co_research' => ['label' => 'Co-Research', 'role' => 'Co-Research'],
        ])->mapWithKeys(function (array $roleMeta, string $key) use ($projectParticipantRows, $projectTypeLabelByCode, $unitContributionYears, $resolveParticipantUser) {
            $roleRows = $projectParticipantRows
                ->where('people_type', 'unit')
                ->where('role', $roleMeta['role'])
                ->values();
            $rows = collect($projectTypeLabelByCode)
                ->map(function ($themeLabel, $themeCode) use ($roleRows, $unitContributionYears, $resolveParticipantUser) {
                    $themeRows = $roleRows
                        ->where('theme', $themeLabel)
                        ->values();
                    $people = $themeRows
                        ->groupBy('researcher_key')
                        ->map(fn (Collection $personRows) => trim((string) ($personRows->first()->name ?? '')) ?: '-')
                        ->filter()
                        ->sort()
                        ->values();
                    $projects = $themeRows
                        ->groupBy('project_id')
                        ->map(function (Collection $projectRows) {
                            $first = $projectRows->first();

                            return (object) [
                                'name' => trim((string) ($first->project_name ?? '')) ?: '-',
                                'year' => (int) ($first->year ?? 0),
                                'link' => trim((string) ($first->project_link ?? '')) ?: null,
                            ];
                        })
                        ->sortByDesc(fn ($project) => (int) ($project->year ?? 0))
                        ->values();
                    $byYear = $unitContributionYears
                        ->mapWithKeys(function ($year) use ($themeRows, $resolveParticipantUser) {
                            $yearRows = $themeRows
                                ->where('year', (int) $year)
                                ->values();
                            $people = $yearRows
                                ->groupBy('researcher_key')
                                ->map(function (Collection $personRows) use ($resolveParticipantUser) {
                                    $first = $personRows->first();
                                    $user = $resolveParticipantUser($first);
                                    $name = trim((string) ($user?->full_name ?: $first->name)) ?: '-';

                                    return (object) [
                                        'name' => $name,
                                        'position' => trim((string) ($user?->position ?? '')),
                                        'profile_photo_url' => $user
                                            ? $this->resolveUserProfilePhotoUrl($user)
                                            : asset('images/default-avatar.png'),
                                        'project_count' => (int) $personRows->pluck('project_id')->unique()->count(),
                                    ];
                                })
                                ->sortBy(fn ($person) => (string) ($person->name ?? ''))
                                ->values();

                            return [(string) ((int) $year) => (object) [
                                'year' => (int) $year,
                                'people' => $people,
                                'people_count' => (int) $people->count(),
                            ]];
                        });

                    return (object) [
                        'theme_code' => (string) $themeCode,
                        'theme' => (string) $themeLabel,
                        'people_count' => (int) $people->count(),
                        'project_count' => (int) $projects->count(),
                        'people_names' => $people->take(4)->values(),
                        'project_names' => $projects->pluck('name')->filter()->take(3)->values(),
                        'year_text' => $projects->pluck('year')->filter()->unique()->sort()->implode(', '),
                        'by_year' => $byYear->all(),
                    ];
                })
                ->values();

            return [$key => (object) [
                'key' => $key,
                'label' => $roleMeta['label'],
                'years' => $unitContributionYears->values(),
                'rows' => $rows,
                'people_count' => (int) $rows->sum(fn ($row) => (int) ($row->people_count ?? 0)),
                'project_count' => (int) $rows->sum(fn ($row) => (int) ($row->project_count ?? 0)),
            ]];
        });
        $peopleTypeLabels = [
            'unit' => 'คนในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ',
            'siriraj' => 'คนภายในคณะแพทยศาสตร์ศิริราชพยาบาล',
            'external' => 'คนนอกคณะฯ',
        ];
        $professionPeopleTypeLabels = collect($peopleTypeLabels)->only(['unit', 'siriraj'])->all();
        $peopleTypeMix = collect($peopleTypeLabels)->map(function ($label, $key) use ($projectParticipantRows) {
            $rows = $projectParticipantRows->where('people_type', $key)->values();

            return (object) [
                'key' => $key,
                'label' => $label,
                'projects' => (int) $rows->pluck('project_id')->unique()->count(),
                'people' => (int) $rows->pluck('researcher_key')->filter()->unique()->count(),
                'role_entries' => (int) $rows->count(),
                'pi_projects' => (int) $rows->where('role', 'PI')->pluck('project_id')->unique()->count(),
                'co_pi_projects' => (int) $rows->where('role', 'Co-PI')->pluck('project_id')->unique()->count(),
                'co_research_projects' => (int) $rows->where('role', 'Co-Research')->pluck('project_id')->unique()->count(),
            ];
        })->values();
        $contributionTotalResearchers = (int) $projectParticipantRows
            ->whereIn('people_type', ['unit', 'siriraj', 'external'])
            ->pluck('researcher_key')
            ->filter()
            ->unique()
            ->count();
        $sirirajContextProjectIds = $projectParticipantRows
            ->whereIn('people_type', ['unit', 'siriraj'])
            ->groupBy('project_id')
            ->filter(fn (Collection $projectRows) => $projectRows->contains(fn ($row) => ($row->people_type ?? '') === 'unit')
                && $projectRows->contains(fn ($row) => ($row->people_type ?? '') === 'siriraj'))
            ->keys()
            ->map(fn ($projectId) => (int) $projectId)
            ->values();
        $sirirajContextAllProjectIds = $projectParticipantAllRows
            ->whereIn('people_type', ['unit', 'siriraj'])
            ->groupBy('project_id')
            ->filter(fn (Collection $projectRows) => $projectRows->contains(fn ($row) => ($row->people_type ?? '') === 'unit')
                && $projectRows->contains(fn ($row) => ($row->people_type ?? '') === 'siriraj'))
            ->keys()
            ->map(fn ($projectId) => (int) $projectId)
            ->values();
        $sirirajContextParticipantRows = $projectParticipantRows
            ->whereIn('project_id', $sirirajContextProjectIds->all())
            ->whereIn('people_type', ['unit', 'siriraj'])
            ->values();
        $sirirajContextParticipantAllRows = $projectParticipantAllRows
            ->whereIn('project_id', $sirirajContextAllProjectIds->all())
            ->whereIn('people_type', ['unit', 'siriraj'])
            ->values();
        $sirirajExpertRows = $projectParticipantRows
            ->whereIn('project_id', $sirirajContextProjectIds->all())
            ->filter(fn ($row) => in_array($row->people_type ?? '', ['unit', 'siriraj'], true))
            ->filter(fn ($row) => $professionGroup($row) === 'แพทย์ / Clinical expert')
            ->filter(fn ($row) => Str::startsWith(trim((string) ($row->theme ?? '')), ['01', '02', '04']))
            ->values();
        $sirirajExpertAllRows = $projectParticipantAllRows
            ->whereIn('project_id', $sirirajContextAllProjectIds->all())
            ->filter(fn ($row) => in_array($row->people_type ?? '', ['unit', 'siriraj'], true))
            ->filter(fn ($row) => $professionGroup($row) === 'แพทย์ / Clinical expert')
            ->filter(fn ($row) => Str::startsWith(trim((string) ($row->theme ?? '')), ['01', '02', '04']))
            ->values();
        $sirirajClinicalRows = $projectParticipantRows
            ->filter(fn ($row) => in_array($row->people_type ?? '', ['unit', 'siriraj'], true))
            ->filter(fn ($row) => $professionGroup($row) === 'แพทย์ / Clinical expert')
            ->values();
        $expertYears = $sirirajClinicalRows
            ->pluck('year')
            ->filter()
            ->unique()
            ->sort()
            ->values();
        $firstYearByExpert = $sirirajClinicalRows
            ->groupBy('researcher_key')
            ->map(fn ($rows) => (int) $rows->pluck('year')->filter()->min());
        $expertTrend = [
            'years' => $expertYears,
            'people' => $expertYears->map(fn ($year) => (int) $sirirajClinicalRows->where('year', (int) $year)->pluck('researcher_key')->filter()->unique()->count())->values(),
            'new_people' => $expertYears->map(fn ($year) => (int) $firstYearByExpert->filter(fn ($firstYear) => (int) $firstYear === (int) $year)->count())->values(),
            'projects' => $expertYears->map(fn ($year) => (int) $sirirajClinicalRows->where('year', (int) $year)->pluck('project_id')->unique()->count())->values(),
            'items_by_year' => $expertYears
                ->mapWithKeys(function ($year) use ($sirirajClinicalRows, $userById) {
                    return [(string) $year => $sirirajClinicalRows
                        ->where('year', (int) $year)
                        ->groupBy('project_id')
                        ->map(function (Collection $projectRows) use ($userById) {
                            $first = $projectRows->first();
                            $people = $projectRows
                                ->unique('researcher_key')
                                ->map(function ($row) use ($userById) {
                                    $user = ((int) ($row->user_id ?? 0) > 0) ? $userById->get((int) $row->user_id) : null;
                                    $name = trim((string) ($user?->full_name ?: $row->name)) ?: '-';

                                    return [
                                        'name' => $name,
                                        'role' => trim((string) ($row->role ?? '')),
                                        'affiliation' => trim((string) ($row->affiliation ?? '')),
                                        'profile_photo_url' => $user
                                            ? $this->resolveUserProfilePhotoUrl($user)
                                            : asset('images/default-avatar.png'),
                                    ];
                                })
                                ->values();

                            return [
                                'title' => trim((string) ($first->project_name ?? '')) ?: '-',
                                'meta' => $people->pluck('name')->filter()->take(4)->implode(', '),
                                'type' => trim((string) ($first->theme ?? '')),
                                'year' => (int) ($first->year ?? 0) ?: null,
                                'link' => trim((string) ($first->project_link ?? '')) ?: null,
                                'people' => $people,
                            ];
                        })
                        ->sortBy(fn ($item) => (string) ($item['title'] ?? ''))
                        ->values()];
                })
                ->all(),
            'total_people' => (int) $sirirajClinicalRows->pluck('researcher_key')->filter()->unique()->count(),
            'total_projects' => (int) $sirirajClinicalRows->pluck('project_id')->unique()->count(),
        ];
        $newExpertsByYear = $expertYears
            ->reverse()
            ->take(4)
            ->map(function ($year) use ($sirirajClinicalRows, $firstYearByExpert, $userById, $professionGroup) {
                $rows = $sirirajClinicalRows
                    ->filter(fn ($row) => (int) ($firstYearByExpert[$row->researcher_key] ?? 0) === (int) $year)
                    ->unique('researcher_key')
                    ->take(6)
                    ->map(function ($row) use ($userById, $professionGroup) {
                        $user = ((int) ($row->user_id ?? 0) > 0) ? $userById->get((int) $row->user_id) : null;

                        return (object) [
                            'name' => $user?->full_name ?: $row->name,
                            'position' => trim((string) ($user?->position ?? '')),
                            'profile_photo_url' => $user ? $this->resolveUserProfilePhotoUrl($user) : asset('images/default-avatar.png'),
                            'role' => $row->role,
                            'theme' => $row->theme,
                        ];
                    })
                    ->values();

                return (object) [
                    'year' => (int) $year,
                    'count' => (int) $rows->count(),
                    'people' => $rows,
                ];
            })
            ->values();
        $clinicalLedThemeMeta = [
            'ageing' => [
                'label' => 'Ageing and Frailty',
                'prefixes' => ['01'],
                'color' => '#4f46e5',
            ],
            'hta' => [
                'label' => 'Health Systems and HTA',
                'prefixes' => ['02'],
                'color' => '#d97706',
            ],
            'rwe' => [
                'label' => 'Real-World Evidence',
                'prefixes' => ['04'],
                'color' => '#0f766e',
            ],
        ];
        $clinicalLedThemeKey = static function (?string $theme) use ($clinicalLedThemeMeta): ?string {
            $text = trim((string) $theme);
            foreach ($clinicalLedThemeMeta as $key => $meta) {
                if (Str::startsWith($text, $meta['prefixes'])) {
                    return $key;
                }
            }

            return null;
        };
        $clinicalLedIntegrationRows = $sirirajContextParticipantRows
            ->filter(fn ($row) => $clinicalLedThemeKey($row->theme ?? null) !== null)
            ->values();
        $clinicalLedIntegrationYears = $clinicalLedIntegrationRows
            ->pluck('year')
            ->filter()
            ->unique()
            ->sort()
            ->values();
        $clinicalLedIntegrationTrend = [
            'years' => $clinicalLedIntegrationYears,
            'people' => $clinicalLedIntegrationYears
                ->map(fn ($year) => (int) $clinicalLedIntegrationRows
                    ->where('year', (int) $year)
                    ->pluck('researcher_key')
                    ->filter()
                    ->unique()
                    ->count())
                ->values(),
            'projects' => $clinicalLedIntegrationYears
                ->map(fn ($year) => (int) $clinicalLedIntegrationRows
                    ->where('year', (int) $year)
                    ->pluck('project_id')
                    ->unique()
                    ->count())
                ->values(),
            'ageing_projects' => $clinicalLedIntegrationYears
                ->map(fn ($year) => (int) $clinicalLedIntegrationRows
                    ->where('year', (int) $year)
                    ->filter(fn ($row) => $clinicalLedThemeKey($row->theme ?? null) === 'ageing')
                    ->pluck('project_id')
                    ->unique()
                    ->count())
                ->values(),
            'ageing_people' => $clinicalLedIntegrationYears
                ->map(fn ($year) => (int) $clinicalLedIntegrationRows
                    ->where('year', (int) $year)
                    ->filter(fn ($row) => $clinicalLedThemeKey($row->theme ?? null) === 'ageing')
                    ->pluck('researcher_key')
                    ->filter()
                    ->unique()
                    ->count())
                ->values(),
            'rwe_projects' => $clinicalLedIntegrationYears
                ->map(fn ($year) => (int) $clinicalLedIntegrationRows
                    ->where('year', (int) $year)
                    ->filter(fn ($row) => $clinicalLedThemeKey($row->theme ?? null) === 'rwe')
                    ->pluck('project_id')
                    ->unique()
                    ->count())
                ->values(),
            'rwe_people' => $clinicalLedIntegrationYears
                ->map(fn ($year) => (int) $clinicalLedIntegrationRows
                    ->where('year', (int) $year)
                    ->filter(fn ($row) => $clinicalLedThemeKey($row->theme ?? null) === 'rwe')
                    ->pluck('researcher_key')
                    ->filter()
                    ->unique()
                    ->count())
                ->values(),
            'hta_projects' => $clinicalLedIntegrationYears
                ->map(fn ($year) => (int) $clinicalLedIntegrationRows
                    ->where('year', (int) $year)
                    ->filter(fn ($row) => $clinicalLedThemeKey($row->theme ?? null) === 'hta')
                    ->pluck('project_id')
                    ->unique()
                    ->count())
                ->values(),
            'hta_people' => $clinicalLedIntegrationYears
                ->map(fn ($year) => (int) $clinicalLedIntegrationRows
                    ->where('year', (int) $year)
                    ->filter(fn ($row) => $clinicalLedThemeKey($row->theme ?? null) === 'hta')
                    ->pluck('researcher_key')
                    ->filter()
                    ->unique()
                    ->count())
                ->values(),
        ];
        $clinicalLedIntegrationRoleStats = collect([
            ['key' => 'pi', 'label' => 'PI', 'role' => 'PI'],
            ['key' => 'co_pi', 'label' => 'Co-PI', 'role' => 'Co-PI'],
            ['key' => 'co_research', 'label' => 'Co-Researcher', 'role' => 'Co-Research'],
        ])->map(function ($roleMeta) use ($clinicalLedIntegrationRows, $clinicalLedThemeKey, $clinicalLedThemeMeta) {
            $rows = $clinicalLedIntegrationRows->where('role', $roleMeta['role'])->values();
            $topTheme = $rows
                ->groupBy(fn ($row) => $clinicalLedThemeKey($row->theme ?? null) ?: 'unknown')
                ->map(fn (Collection $themeRows, $themeKey) => (object) [
                    'key' => $themeKey,
                    'label' => $clinicalLedThemeMeta[$themeKey]['label'] ?? (string) $themeKey,
                    'people' => (int) $themeRows->pluck('researcher_key')->filter()->unique()->count(),
                    'projects' => (int) $themeRows->pluck('project_id')->unique()->count(),
                ])
                ->sortByDesc('people')
                ->first();

            return (object) [
                'key' => $roleMeta['key'],
                'label' => $roleMeta['label'],
                'projects' => (int) $rows->pluck('project_id')->unique()->count(),
                'people' => (int) $rows->pluck('researcher_key')->filter()->unique()->count(),
                'top_theme' => $topTheme,
            ];
        })->values();
        $clinicalLedIntegrationRoleTrend = collect([
            'PI' => 'pi',
            'Co-PI' => 'co_pi',
            'Co-Research' => 'co_research',
        ])->mapWithKeys(fn ($key, $role) => [
            $key => $clinicalLedIntegrationYears
                ->map(fn ($year) => (int) $clinicalLedIntegrationRows
                    ->where('year', (int) $year)
                    ->where('role', $role)
                    ->pluck('researcher_key')
                    ->filter()
                    ->unique()
                    ->count())
                ->values(),
        ]);
        $clinicalLedIntegrationItemsByThemeYear = collect($clinicalLedThemeMeta)
            ->mapWithKeys(function ($meta, $themeKey) use ($clinicalLedIntegrationRows, $clinicalLedThemeKey, $userById) {
                $themeRows = $clinicalLedIntegrationRows
                    ->filter(fn ($row) => $clinicalLedThemeKey($row->theme ?? null) === $themeKey)
                    ->values();

                $itemsByYear = $themeRows
                    ->groupBy(fn ($row) => (string) ((int) ($row->year ?? 0)))
                    ->map(function (Collection $yearRows) use ($meta, $userById) {
                        return $yearRows
                            ->groupBy('project_id')
                            ->map(function (Collection $projectRows) use ($meta, $userById) {
                                $first = $projectRows->first();
                                $people = $projectRows
                                    ->groupBy('person_key')
                                    ->map(function (Collection $personRows) use ($userById) {
                                        $personFirst = $personRows->first();
                                        $user = ((int) ($personFirst->user_id ?? 0) > 0)
                                            ? $userById->get((int) $personFirst->user_id)
                                            : null;
                                        $name = trim((string) ($user?->full_name ?: $personFirst->name)) ?: '-';

                                        return (object) [
                                            'name' => $name,
                                            'role' => $personRows->pluck('role')->filter()->unique()->sort()->implode(' / '),
                                            'affiliation' => trim((string) ($personFirst->affiliation ?? '')),
                                            'profile_photo_url' => $user
                                                ? $this->resolveUserProfilePhotoUrl($user)
                                                : asset('images/default-avatar.png'),
                                        ];
                                    })
                                    ->sortBy(fn ($person) => (string) ($person->role ?? ''))
                                    ->values();

                                return (object) [
                                    'title' => trim((string) ($first->project_name ?? '')) ?: '-',
                                    'meta' => 'คนในหน่วยฯ/คณะศิริราชร่วม ' . number_format((int) $people->count()) . ' คน',
                                    'type' => $meta['label'],
                                    'year' => (int) ($first->year ?? 0),
                                    'link' => trim((string) ($first->project_link ?? '')) ?: null,
                                    'people' => $people,
                                ];
                            })
                            ->sortByDesc(fn ($item) => (int) ($item->year ?? 0))
                            ->values();
                    })
                    ->all();

                return [$themeKey => $itemsByYear];
            });
        $clinicalLedIntegrationPeopleByYear = $clinicalLedIntegrationYears
            ->map(function ($year) use ($clinicalLedIntegrationRows, $clinicalLedThemeKey, $clinicalLedThemeMeta, $userById) {
                $yearRows = $clinicalLedIntegrationRows
                    ->where('year', (int) $year)
                    ->values();
                $people = $yearRows
                    ->groupBy('researcher_key')
                    ->map(function (Collection $personRows) use ($clinicalLedThemeKey, $clinicalLedThemeMeta, $userById) {
                        $first = $personRows->first();
                        $user = ((int) ($first->user_id ?? 0) > 0) ? $userById->get((int) $first->user_id) : null;
                        $name = trim((string) ($user?->full_name ?: $first->name)) ?: '-';
                        $themeKeys = $personRows
                            ->map(fn ($row) => $clinicalLedThemeKey($row->theme ?? null))
                            ->filter()
                            ->unique()
                            ->values();

                        return (object) [
                            'name' => $name,
                            'position' => trim((string) ($user?->position ?? '')),
                            'affiliation' => trim((string) ($first->affiliation ?? '')),
                            'profile_photo_url' => $user
                                ? $this->resolveUserProfilePhotoUrl($user)
                                : asset('images/default-avatar.png'),
                            'roles' => $personRows->pluck('role')->filter()->unique()->sort()->values(),
                            'theme_labels' => $themeKeys
                                ->map(fn ($key) => $clinicalLedThemeMeta[$key]['label'] ?? $key)
                                ->values(),
                            'projects' => (int) $personRows->pluck('project_id')->unique()->count(),
                        ];
                    })
                    ->sortByDesc(fn ($person) => (int) $person->projects)
                    ->take(10)
                    ->values();

                return (object) [
                    'year' => (int) $year,
                    'people' => $people,
                    'people_count' => (int) $yearRows->pluck('researcher_key')->filter()->unique()->count(),
                    'project_count' => (int) $yearRows->pluck('project_id')->unique()->count(),
                    'ageing_people_count' => (int) $yearRows
                        ->filter(fn ($row) => $clinicalLedThemeKey($row->theme ?? null) === 'ageing')
                        ->pluck('researcher_key')
                        ->filter()
                        ->unique()
                        ->count(),
                    'ageing_project_count' => (int) $yearRows
                        ->filter(fn ($row) => $clinicalLedThemeKey($row->theme ?? null) === 'ageing')
                        ->pluck('project_id')
                        ->unique()
                        ->count(),
                    'rwe_people_count' => (int) $yearRows
                        ->filter(fn ($row) => $clinicalLedThemeKey($row->theme ?? null) === 'rwe')
                        ->pluck('researcher_key')
                        ->filter()
                        ->unique()
                        ->count(),
                    'rwe_project_count' => (int) $yearRows
                        ->filter(fn ($row) => $clinicalLedThemeKey($row->theme ?? null) === 'rwe')
                        ->pluck('project_id')
                        ->unique()
                        ->count(),
                    'hta_people_count' => (int) $yearRows
                        ->filter(fn ($row) => $clinicalLedThemeKey($row->theme ?? null) === 'hta')
                        ->pluck('researcher_key')
                        ->filter()
                        ->unique()
                        ->count(),
                    'hta_project_count' => (int) $yearRows
                        ->filter(fn ($row) => $clinicalLedThemeKey($row->theme ?? null) === 'hta')
                        ->pluck('project_id')
                        ->unique()
                        ->count(),
                    'pi_count' => (int) $yearRows->where('role', 'PI')->pluck('researcher_key')->filter()->unique()->count(),
                    'co_pi_count' => (int) $yearRows->where('role', 'Co-PI')->pluck('researcher_key')->filter()->unique()->count(),
                    'co_research_count' => (int) $yearRows->where('role', 'Co-Research')->pluck('researcher_key')->filter()->unique()->count(),
                ];
            })
            ->values();
        $clinicalLedIntegrationThemes = collect($clinicalLedThemeMeta)
            ->map(function ($meta, $key) use ($clinicalLedIntegrationRows, $clinicalLedThemeKey, $userById) {
                $rows = $clinicalLedIntegrationRows
                    ->filter(fn ($row) => $clinicalLedThemeKey($row->theme ?? null) === $key)
                    ->values();
                $people = $rows
                    ->groupBy('researcher_key')
                    ->map(function (Collection $personRows) use ($userById) {
                        $first = $personRows->first();
                        $user = ((int) ($first->user_id ?? 0) > 0) ? $userById->get((int) $first->user_id) : null;
                        $name = trim((string) ($user?->full_name ?: $first->name)) ?: '-';

                        return (object) [
                            'name' => $name,
                            'position' => trim((string) ($user?->position ?? '')),
                            'affiliation' => trim((string) ($first->affiliation ?? '')),
                            'profile_photo_url' => $user
                                ? $this->resolveUserProfilePhotoUrl($user)
                                : asset('images/default-avatar.png'),
                            'roles' => $personRows->pluck('role')->filter()->unique()->sort()->values(),
                            'projects' => (int) $personRows->pluck('project_id')->unique()->count(),
                            'years' => $personRows->pluck('year')->filter()->unique()->sort()->values(),
                            'project_names' => $personRows->groupBy('project_id')
                                ->map(fn ($projectRows) => trim((string) ($projectRows->first()->project_name ?? '')) ?: '-')
                                ->take(3)
                                ->values(),
                        ];
                    })
                    ->sortByDesc(fn ($person) => (int) $person->projects)
                    ->values();

                return (object) [
                    'key' => $key,
                    'label' => $meta['label'],
                    'color' => $meta['color'],
                    'projects' => (int) $rows->pluck('project_id')->unique()->count(),
                    'people' => (int) $rows->pluck('researcher_key')->filter()->unique()->count(),
                    'people_list' => $people,
                ];
            })
            ->values();
        $clinicalLedIntegration = [
            'trend' => $clinicalLedIntegrationTrend,
            'items_by_theme_year' => $clinicalLedIntegrationItemsByThemeYear,
            'role_trend' => $clinicalLedIntegrationRoleTrend,
            'role_stats' => $clinicalLedIntegrationRoleStats,
            'people_by_year' => $clinicalLedIntegrationPeopleByYear,
            'themes' => $clinicalLedIntegrationThemes,
            'total_people' => (int) $clinicalLedIntegrationRows->pluck('researcher_key')->filter()->unique()->count(),
            'total_projects' => (int) $clinicalLedIntegrationRows->pluck('project_id')->unique()->count(),
        ];
        $frameworkTrendYears = $projectAnalysisRows
            ->map(fn (Project $project) => $this->normalizeYearFilter($project->start_year))
            ->merge($filtered->pluck('year_be'))
            ->filter(fn ($year) => is_numeric($year))
            ->map(fn ($year) => (int) $year)
            ->unique()
            ->sort()
            ->values();
        $frameworkTrendSeries = [
            'trust' => $frameworkTrendYears
                ->map(fn ($year) => (int) $sirirajContextParticipantRows
                    ->where('year', (int) $year)
                    ->pluck('project_id')
                    ->unique()
                    ->count())
                ->values(),
            'truth' => $frameworkTrendYears
                ->map(fn ($year) => (int) $sirirajExpertRows
                    ->where('year', (int) $year)
                    ->pluck('project_id')
                    ->unique()
                    ->count())
                ->values(),
            'policy_impact' => $frameworkTrendYears
                ->map(fn ($year) => (int) $impactRows
                    ->whereIn('type_code', $impactCodes->all())
                    ->where('year_be', (int) $year)
                    ->count())
                ->values(),
        ];
        $latestFrameworkMetric = static function (Collection $rows, callable $countForYear) use ($currentYearBe): array {
            $latestYear = $rows
                ->pluck('year')
                ->filter(fn ($year) => is_numeric($year))
                ->map(fn ($year) => (int) $year)
                ->filter(fn ($year) => $year <= (int) $currentYearBe)
                ->max();

            return [
                'year' => $latestYear ? (int) $latestYear : null,
                'value' => $latestYear ? (int) $countForYear((int) $latestYear) : 0,
            ];
        };
        $latestImpactOutputYear = $impactAllRows
            ->whereIn('type_code', $impactCodes->all())
            ->pluck('year_be')
            ->filter(fn ($year) => is_numeric($year))
            ->map(fn ($year) => (int) $year)
            ->filter(fn ($year) => $year <= (int) $currentYearBe)
            ->max();
        $frameworkLatestMetrics = [
            'trust' => $latestFrameworkMetric(
                $sirirajContextParticipantAllRows,
                fn (int $year) => $sirirajContextParticipantAllRows
                    ->where('year', $year)
                    ->pluck('project_id')
                    ->unique()
                    ->count()
            ),
            'truth' => $latestFrameworkMetric(
                $sirirajExpertAllRows,
                fn (int $year) => $sirirajExpertAllRows
                    ->where('year', $year)
                    ->pluck('project_id')
                    ->unique()
                    ->count()
            ),
            'policy_impact' => [
                'year' => $latestImpactOutputYear ? (int) $latestImpactOutputYear : null,
                'value' => $latestImpactOutputYear
                    ? (int) $impactAllRows
                        ->whereIn('type_code', $impactCodes->all())
                        ->where('year_be', (int) $latestImpactOutputYear)
                        ->count()
                    : 0,
            ],
        ];
        $frameworkPeopleForRows = function (Collection $rows) use ($userById): Collection {
            return $rows
                ->unique('researcher_key')
                ->take(6)
                ->map(function ($row) use ($userById) {
                    $user = ((int) ($row->user_id ?? 0) > 0) ? $userById->get((int) $row->user_id) : null;
                    $name = trim((string) ($user?->full_name ?: $row->name)) ?: '-';

                    return [
                        'name' => $name,
                        'role' => trim((string) ($row->role ?? '')),
                        'affiliation' => trim((string) ($row->affiliation ?? '')),
                        'profile_photo_url' => $user
                            ? $this->resolveUserProfilePhotoUrl($user)
                            : asset('images/default-avatar.png'),
                    ];
                })
                ->values();
        };
        $frameworkTrendItems = [
            'trust' => $frameworkTrendYears
                ->mapWithKeys(function ($year) use ($sirirajContextParticipantRows, $frameworkPeopleForRows) {
                    return [(string) $year => $sirirajContextParticipantRows
                        ->where('year', (int) $year)
                        ->groupBy('project_id')
                        ->map(function ($rows) use ($frameworkPeopleForRows) {
                            $first = $rows->first();

                            return [
                                'title' => $first->project_name,
                                'meta' => collect($rows)->pluck('name')->filter()->unique()->take(4)->implode(', '),
                                'type' => $first->theme,
                                'link' => $first->project_link,
                                'year' => (int) ($first->year ?? 0) ?: null,
                                'people' => $frameworkPeopleForRows(collect($rows)),
                            ];
                        })
                        ->values()];
                }),
            'truth' => $frameworkTrendYears
                ->mapWithKeys(function ($year) use ($sirirajExpertRows, $frameworkPeopleForRows) {
                    return [(string) $year => $sirirajExpertRows
                        ->where('year', (int) $year)
                        ->groupBy('project_id')
                        ->map(function ($rows) use ($frameworkPeopleForRows) {
                            $first = $rows->first();

                            return [
                                'title' => $first->project_name,
                                'meta' => collect($rows)->pluck('name')->filter()->unique()->take(4)->implode(', '),
                                'type' => $first->theme,
                                'link' => $first->project_link,
                                'year' => (int) ($first->year ?? 0) ?: null,
                                'people' => $frameworkPeopleForRows(collect($rows)),
                            ];
                        })
                        ->values()];
                }),
            'policy_impact' => $frameworkTrendYears
                ->mapWithKeys(function ($year) use ($impactRows, $normalizeProjectType, $sortRowsNewestFirst, $impactCodes) {
                    return [(string) $year => $sortRowsNewestFirst($impactRows
                        ->whereIn('type_code', $impactCodes->all())
                        ->where('year_be', (int) $year)
                        ->values())
                        ->map(fn ($row) => [
                            'title' => trim((string) ($row->title ?? '')) ?: '-',
                            'meta' => trim((string) ($row->reference ?? '')) ?: '-',
                            'type' => trim((string) ($row->project_type ?? '')) !== ''
                                ? $normalizeProjectType($row->project_type)
                                : (trim((string) ($row->source_label ?? '')) ?: trim((string) ($row->type_code ?? ''))),
                            'link' => trim((string) ($row->link ?? '')) ?: null,
                            'year' => is_numeric($row->year_be ?? null) ? (int) $row->year_be : null,
                        ])
                        ->values()];
                }),
        ];
        $frameworkAllItems = collect($frameworkTrendItems)
            ->mapWithKeys(fn ($yearGroups, $key) => [
                $key => collect($yearGroups)
                    ->flatMap(fn ($items) => collect($items))
                    ->unique(fn ($item) => ($item['title'] ?? '') . '|' . ($item['meta'] ?? ''))
                    ->sortByDesc(fn ($item) => (int) ($item['year'] ?? 0))
                    ->values(),
            ]);
        $facultySirirajRoleStats = collect([
            ['key' => 'pi', 'label' => 'PI', 'role' => 'PI'],
            ['key' => 'co_pi', 'label' => 'Co-PI', 'role' => 'Co-PI'],
            ['key' => 'co_research', 'label' => 'Co-Researcher', 'role' => 'Co-Research'],
        ])->map(function ($roleMeta) use ($sirirajContextParticipantRows) {
            $rows = $sirirajContextParticipantRows
                ->where('role', $roleMeta['role'])
                ->values();

            return (object) [
                'key' => $roleMeta['key'],
                'label' => $roleMeta['label'],
                'projects' => (int) $rows->pluck('project_id')->unique()->count(),
                'people' => (int) $rows->pluck('researcher_key')->filter()->unique()->count(),
            ];
        })->values();
        $frameworkInterpretRows = collect([
            [
                'key' => 'trust',
                'label' => 'Credibility',
                'title' => 'Trend in same-project collaboration between SHP unit and Siriraj faculty',
                'unit' => 'โครงการ',
                'detail' => "แนวโน้มโครงการที่มีทั้งคนในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ และคนภายในคณะแพทยศาสตร์ศิริราชพยาบาล (แพทย์, บุคลากรทางการแพทย์) ทำงานร่วมกัน\n\nจำนวนโครงการวิจัยที่เกิดการทำงานร่วมกันระหว่างสองกลุ่มนี้ และเข้ามาอยู่ในบทบาทใดบ้าง (PI, Co-PI และ Co-Researcher)",
                'role_stats' => $facultySirirajRoleStats,
            ],
            [
                'key' => 'truth',
                'label' => 'Validation',
                'title' => 'Trends in same-project collaboration for research-to-policy work',
                'unit' => 'โครงการ',
                'detail' => "แนวโน้มโครงการที่มีทั้งคนในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ และคนภายในคณะแพทยศาสตร์ศิริราชพยาบาล (แพทย์, บุคลากรทางการแพทย์) ทำงานร่วมกันในประเด็นการขับเคลื่อนนโยบายและระบบสุขภาพ (Health Policy, Health System)\n\nจำนวนโครงการวิจัยที่นำไปสู่การขับเคลื่อนนโยบาย โดยต้องมีทั้งสองกลุ่มเป็นส่วนร่วมในโครงการเดียวกัน และแบ่งตามประเภท Theme/เนื้อหาของงานวิจัย\nAgeing and Frailty, Health System and HTA, Real-World Evidence and Clinical-Technical Cohorts\n\n*Theme/เนื้อหา Rational Drug Use (RDU) และ Global Health อาจยังถูกมองเป็น Operational project ภายในคณะแพทยศาสตร์ศิริราชพยาบาล มากกว่า Research model จึงยังไม่ถูกรวมในการวิเคราะห์",
                'role_stats' => $clinicalLedIntegrationRoleStats,
            ],
            [
                'key' => 'policy_impact',
                'label' => 'Impact',
                'title' => 'Trend in outputs contributing to Policy-Impact or Practice Change',
                'unit' => 'รายการ',
                'detail' => 'แนวโน้มผลงานที่มีส่วนในการผลักดันการเปลี่ยนแปลงในเชิงนโยบายหรือการปฏิบัติในระบบสุขภาพ (Research Paper and Journal Article, Change in Practices, Procedure, Reports and White Paper, Policy Recommendation)',
            ],
        ])->map(function ($row) use ($frameworkTrendYears, $frameworkTrendSeries, $frameworkLatestMetrics) {
            $values = collect($frameworkTrendSeries[$row['key']] ?? [])->map(fn ($value) => (int) $value)->values();
            $latestMetric = $frameworkLatestMetrics[$row['key']] ?? ['year' => null, 'value' => 0];
            $latest = (int) ($latestMetric['value'] ?? 0);
            $latestYear = (int) ($latestMetric['year'] ?? 0);
            $peak = (int) ($values->max() ?? 0);
            $peakIndex = $values->search($peak);
            $peakYear = $peakIndex !== false ? (int) ($frameworkTrendYears->values()->get($peakIndex) ?? 0) : null;

            return (object) array_merge($row, [
                'latest' => $latest,
                'latest_year' => $latestYear,
                'total' => (int) $values->sum(),
                'peak' => $peak,
                'peak_year' => $peakYear,
            ]);
        })->values();
        $frameworkTrendSummaryRows = $frameworkTrendYears
            ->values()
            ->map(function ($year, $index) use ($frameworkTrendSeries) {
                $trust = (int) (collect($frameworkTrendSeries['trust'] ?? [])->values()->get($index) ?? 0);
                $truth = (int) (collect($frameworkTrendSeries['truth'] ?? [])->values()->get($index) ?? 0);
                $policyImpact = (int) (collect($frameworkTrendSeries['policy_impact'] ?? [])->values()->get($index) ?? 0);

                return (object) [
                    'year' => (int) $year,
                    'trust' => $trust,
                    'truth' => $truth,
                    'policy_impact' => $policyImpact,
                    'total' => $trust + $truth + $policyImpact,
                ];
            })
            ->filter(fn ($row) => (int) ($row->year ?? 0) < (int) $currentYearBe)
            ->values();
        if ($frameworkTrendSummaryRows->isEmpty()) {
            $frameworkTrendSummaryRows = $frameworkTrendYears
                ->values()
                ->map(function ($year, $index) use ($frameworkTrendSeries) {
                    $trust = (int) (collect($frameworkTrendSeries['trust'] ?? [])->values()->get($index) ?? 0);
                    $truth = (int) (collect($frameworkTrendSeries['truth'] ?? [])->values()->get($index) ?? 0);
                    $policyImpact = (int) (collect($frameworkTrendSeries['policy_impact'] ?? [])->values()->get($index) ?? 0);

                    return (object) [
                        'year' => (int) $year,
                        'trust' => $trust,
                        'truth' => $truth,
                        'policy_impact' => $policyImpact,
                        'total' => $trust + $truth + $policyImpact,
                    ];
                })
                ->values();
        }
        $frameworkFirst = $frameworkTrendSummaryRows->first();
        $frameworkLast = $frameworkTrendSummaryRows->last();
        $frameworkPeak = $frameworkTrendSummaryRows
            ->sortByDesc(fn ($row) => (int) ($row->total ?? 0))
            ->first();
        $frameworkDelta = (int) ($frameworkLast?->total ?? 0) - (int) ($frameworkFirst?->total ?? 0);
        $frameworkDirection = $frameworkDelta > 0 ? 'เพิ่มขึ้น' : ($frameworkDelta < 0 ? 'ลดลง' : 'ทรงตัว');
        $frameworkDimensionTotals = collect([
            'trust' => (int) $frameworkTrendSummaryRows->sum('trust'),
            'truth' => (int) $frameworkTrendSummaryRows->sum('truth'),
            'policy_impact' => (int) $frameworkTrendSummaryRows->sum('policy_impact'),
        ]);
        $frameworkTopKey = $frameworkDimensionTotals->sortDesc()->keys()->first();
        $frameworkTopLabels = [
            'trust' => 'Credibility',
            'truth' => 'Validation',
            'policy_impact' => 'Impact',
        ];
        $frameworkTopLabel = $frameworkTopLabels[$frameworkTopKey] ?? '-';
        $frameworkPolicyTotal = (int) ($frameworkDimensionTotals['policy_impact'] ?? 0);
        $frameworkTrustTotal = (int) ($frameworkDimensionTotals['trust'] ?? 0);
        $frameworkTruthTotal = (int) ($frameworkDimensionTotals['truth'] ?? 0);
        $frameworkTruthPeak = $frameworkTrendSummaryRows
            ->sortByDesc(fn ($row) => (int) ($row->truth ?? 0))
            ->first();
        $frameworkPolicyPeak = $frameworkTrendSummaryRows
            ->sortByDesc(fn ($row) => (int) ($row->policy_impact ?? 0))
            ->first();
        $frameworkShape = static function (Collection $rows, string $key): string {
            $values = $rows->pluck($key)->map(fn ($value) => (int) $value)->values();
            if ($values->count() < 2) {
                return 'ยังมีข้อมูลไม่พอสำหรับอ่านแนวโน้ม';
            }

            $first = (int) $values->first();
            $last = (int) $values->last();
            $nonDecreasing = true;
            $nonIncreasing = true;
            for ($index = 1; $index < $values->count(); $index += 1) {
                if ((int) $values[$index] < (int) $values[$index - 1]) {
                    $nonDecreasing = false;
                }
                if ((int) $values[$index] > (int) $values[$index - 1]) {
                    $nonIncreasing = false;
                }
            }

            if ($nonDecreasing && $last > $first) {
                return 'เพิ่มขึ้นต่อเนื่อง';
            }
            if ($nonIncreasing && $last < $first) {
                return 'ลดลงต่อเนื่อง';
            }
            if ($last > $first) {
                return 'เพิ่มขึ้นโดยรวมแต่มีความผันผวนระหว่างปี';
            }
            if ($last < $first) {
                return 'ลดลงโดยรวมแต่มีความผันผวนระหว่างปี';
            }

            return 'ทรงตัวโดยรวม';
        };
        $frameworkTruthShape = $frameworkShape($frameworkTrendSummaryRows, 'truth');
        $frameworkPolicyShape = $frameworkShape($frameworkTrendSummaryRows, 'policy_impact');
        $frameworkHeadlineLead = (str_contains($frameworkTruthShape, 'เพิ่ม') || str_contains($frameworkPolicyShape, 'เพิ่ม'))
            ? 'จำนวนโครงการที่มีทั้งคนในหน่วยวิจัยฯ และคนภายในคณะฯ ร่วมกันเพิ่มขึ้นในภาพรวม'
            : 'จำนวนโครงการที่มีทั้งคนในหน่วยวิจัยฯ และคนภายในคณะฯ ร่วมกันยังไม่เพิ่มขึ้นชัดเจนในภาพรวม';
        $frameworkBody = str_contains($frameworkTruthShape, 'เพิ่ม')
            ? 'แนวโน้มสะท้อนการเพิ่มขึ้นของโครงการที่เชื่อมคนในหน่วยวิจัยฯ กับคนภายในคณะแพทยศาสตร์ศิริราชพยาบาลในงานวิจัยเชิงระบบและงานที่เชื่อมกับบริการจริงมากขึ้น'
            : 'แนวโน้มยังไม่สะท้อนการเพิ่มขึ้นของ Validation แบบโครงการร่วมสองกลุ่มอย่างชัดเจน จึงควรอ่านร่วมกับจำนวนโครงการและ output ในแต่ละปี';
        $frameworkBody .= str_contains($frameworkPolicyShape, 'เพิ่ม')
            ? ' รวมถึงเริ่มเห็น output ที่ต่อยอดไปสู่ Impact ต่อระบบสุขภาพและการตัดสินใจเชิงนโยบายมากขึ้นในช่วงหลัง'
            : ' ส่วน Impact ยังไม่เพิ่มชัดเจนในช่วงข้อมูลนี้';
        $frameworkFocus = (str_contains($frameworkTruthShape, 'เพิ่ม') && str_contains($frameworkPolicyShape, 'เพิ่ม'))
            ? 'ภาพรวมแสดงให้เห็นว่า การเติบโตของโครงการไม่ได้เพิ่มเฉพาะจำนวนงานวิจัย แต่รวมถึงการทำงานร่วมกันระหว่างคนในหน่วยวิจัยฯ และคนภายในคณะฯ พร้อมการต่อยอดไปสู่ผลลัพธ์ที่เชื่อมกับระบบสุขภาพมากขึ้น โดยปี ' . ($frameworkPeak?->year ?: '-') . ' เป็นจุดสูงสุดทั้งในด้านโครงการร่วมสองกลุ่มและ Impact ต่อระบบสุขภาพ'
            : 'ภาพรวมยังควรอ่านแบบแยกเส้น เพราะ Validation และ Impact ไม่ได้เพิ่มพร้อมกันอย่างชัดเจนในทุกปี';
        $frameworkHasCurrentYearData = $frameworkTrendYears
            ->contains(fn ($year) => (int) $year === (int) $currentYearBe);
        $frameworkTrendSummary = (object) [
            'headline' => 'ช่วงปี ' . ($frameworkFirst?->year ?: '-') . ' - ' . ($frameworkLast?->year ?: '-') . ' ' . $frameworkHeadlineLead . ' โดย Validation ของโครงการร่วมสองกลุ่ม ' . $frameworkTruthShape . ' จาก ' . number_format((int) ($frameworkFirst?->truth ?? 0)) . ' เป็น ' . number_format((int) ($frameworkLast?->truth ?? 0)) . ' โครงการ ขณะที่ Impact ' . $frameworkPolicyShape . ' จาก ' . number_format((int) ($frameworkFirst?->policy_impact ?? 0)) . ' เป็น ' . number_format((int) ($frameworkLast?->policy_impact ?? 0)) . ' รายการ และสูงสุดในปี ' . ($frameworkPolicyPeak?->year ?: '-'),
            'body' => $frameworkBody,
            'focus' => $frameworkFocus,
            'note' => $frameworkHasCurrentYearData ? 'ปี ' . $currentYearBe . ' ยังเป็นปีที่ข้อมูลกำลังเข้า ไม่ควรสรุปว่า trend ลดลงถาวร' : null,
        ];
        $professionMix = $projectParticipantRows
            ->filter(fn ($row) => in_array($row->people_type ?? '', ['unit', 'siriraj'], true))
            ->groupBy(fn ($row) => $professionGroup($row))
            ->map(fn ($rows, $label) => (object) [
                'label' => $label,
                'people' => (int) $rows->pluck('researcher_key')->filter()->unique()->count(),
                'projects' => (int) $rows->pluck('project_id')->unique()->count(),
                'role_entries' => (int) $rows->count(),
            ])
            ->sortByDesc('people')
            ->values();
        $professionTypeMatrix = $projectParticipantRows
            ->filter(fn ($row) => in_array($row->people_type ?? '', ['unit', 'siriraj'], true))
            ->groupBy(fn ($row) => $professionGroup($row))
            ->map(function ($rows, $professionLabel) use ($professionPeopleTypeLabels, $positionForParticipant, $professionSourceForParticipant) {
                $cells = collect($professionPeopleTypeLabels)->mapWithKeys(function ($typeLabel, $typeKey) use ($rows, $positionForParticipant, $professionSourceForParticipant) {
                    $typeRows = $rows->where('people_type', $typeKey)->values();
                    $peopleList = $typeRows
                        ->groupBy('researcher_key')
                        ->map(function ($personRows) use ($positionForParticipant, $professionSourceForParticipant) {
                            $first = $personRows->first();
                            $position = $positionForParticipant($first);
                            $professionSource = $professionSourceForParticipant($first);

                            return (object) [
                                'name' => trim((string) ($first->name ?? '-')) ?: '-',
                                'position' => $position,
                                'profession_source' => $professionSource['label'],
                                'profession_evidence' => $professionSource['evidence'],
                                'affiliation' => trim((string) ($first->affiliation ?? '')),
                                'roles' => $personRows->pluck('role')->filter()->unique()->sort()->values(),
                                'projects' => (int) $personRows->pluck('project_id')->unique()->count(),
                                'years' => $personRows->pluck('year')->filter()->unique()->sort()->values(),
                                'themes' => $personRows->pluck('theme')->filter()->unique()->take(2)->values(),
                            ];
                        })
                        ->sortBy('name', SORT_NATURAL)
                        ->values();

                    return [$typeKey => (object) [
                        'label' => $typeLabel,
                        'people' => (int) $typeRows->pluck('researcher_key')->filter()->unique()->count(),
                        'projects' => (int) $typeRows->pluck('project_id')->unique()->count(),
                        'people_list' => $peopleList,
                    ]];
                });

                return (object) [
                    'label' => $professionLabel,
                    'cells' => $cells,
                    'total_people' => (int) $rows->pluck('researcher_key')->filter()->unique()->count(),
                    'total_projects' => (int) $rows->pluck('project_id')->unique()->count(),
                ];
            })
            ->sortByDesc('total_people')
            ->values();
        $contributionNetwork = [
            'people_type_mix' => $peopleTypeMix,
            'total_researchers' => $contributionTotalResearchers,
            'expert_trend' => $expertTrend,
            'new_experts_by_year' => $newExpertsByYear,
            'clinical_led_integration' => $clinicalLedIntegration,
            'profession_mix' => $professionMix,
            'profession_type_matrix' => $professionTypeMatrix,
        ];
        $policyImpact = [
            'project_count' => $projectAnalysisTotal,
            'siriraj_project_count' => (int) $sirirajProjects->count(),
            'siriraj_participant_count' => (int) $sirirajParticipants->count(),
            'siriraj_role_counts' => $sirirajRoleCounts,
            'siriraj_role_person_counts' => $sirirajRolePersonCounts,
            'siriraj_projects' => $sirirajProjects->take(8)->map(function (Project $project) use ($buildParticipantRows, $normalizeProjectType, $parseBudget) {
                $participants = $buildParticipantRows($project)
                    ->filter(fn ($participant) => (bool) ($participant->is_siriraj ?? false))
                    ->map(fn ($participant) => trim(($participant->role ?? '') . ': ' . ($participant->name ?? '')))
                    ->values();

                return (object) [
                    'name' => trim((string) ($project->project_name_th ?? '')) ?: '-',
                    'code' => trim((string) ($project->project_code ?? '')),
                    'type' => $normalizeProjectType($project->project_type),
                    'year' => $this->normalizeYearFilter($project->start_year),
                    'budget' => $parseBudget($project->budget),
                    'people' => $participants,
                    'link' => route('projects.show', $project),
                ];
            })->values(),
            'impact_outputs' => $impactOutputRows,
            'impact_output_items' => $impactOutputItems,
            'impact_complete_projects' => $impactCompleteProjectRows,
            'impact_theme_analysis' => $impactThemeAnalysisRows,
            'impact_theme_narrative' => $impactThemeNarrative,
            'impact_theme_narrative_paragraph' => $impactThemeNarrativeParagraph,
            'impact_trend' => [
                'years' => $impactTrendYears,
                'series' => $impactTrendSeries,
                'items' => $impactTrendItems,
                'items_by_year' => $impactTrendItemsByYear,
            ],
            'framework_trend' => [
                'years' => $frameworkTrendYears,
                'series' => $frameworkTrendSeries,
                'items' => $frameworkTrendItems,
                'all_items' => $frameworkAllItems,
                'interpretation' => $frameworkInterpretRows,
                'summary' => $frameworkTrendSummary,
            ],
            'people_highlights' => $personHighlights,
            'doctor_contribution_starts' => $doctorContributionStarts,
            'doctor_contribution_timeline' => $doctorContributionTimeline,
            'doctor_contribution_role_tables' => $doctorContributionRoleTables,
            'unit_contribution_role_theme_tables' => $unitContributionRoleThemeTables,
            'contribution_network' => $contributionNetwork,
            'data_source_counts' => $dataSourceCounts,
            'data_source_trend' => $dataSourceTrend,
            'data_source_missing_count' => $missingDataSourceCount,
            'hta' => [
                'project_count' => (int) $htaProjects->count(),
                'budget_total' => (float) $htaProjects->sum(fn (Project $project) => $parseBudget($project->budget)),
                'siriraj_project_count' => (int) $htaProjects->filter($hasSirirajInvolvement)->count(),
                'siriraj_participant_count' => (int) $htaProjects
                    ->flatMap($buildParticipantRows)
                    ->filter(fn ($participant) => (bool) ($participant->is_siriraj ?? false))
                    ->count(),
                'budget_by_year' => $htaBudgetByYear,
                'max_budget' => $maxHtaBudget,
                'projects' => $htaProjects->filter($hasSirirajInvolvement)->take(6)->map(fn (Project $project) => (object) [
                    'name' => trim((string) ($project->project_name_th ?? '')) ?: '-',
                    'code' => trim((string) ($project->project_code ?? '')),
                    'year' => $this->normalizeYearFilter($project->start_year),
                    'link' => route('projects.show', $project),
                ])->values(),
            ],
            'clinical_led' => [
                'project_count' => (int) $clinicalLedProjects->count(),
                'siriraj_project_count' => (int) $clinicalLedProjects->filter($hasSirirajInvolvement)->count(),
                'siriraj_participant_count' => (int) $clinicalLedProjects
                    ->flatMap($buildParticipantRows)
                    ->filter(fn ($participant) => (bool) ($participant->is_siriraj ?? false))
                    ->count(),
            ],
            'weakness' => [
                'publication_count' => (int) $paperRows->count(),
                'policy_output_count' => (int) $impactOutputRows->sum(),
                'communication_output_count' => $communicationOutputCount,
                'adoption_output_count' => $adoptionOutputCount,
                'data_source_missing_pct' => $projectAnalysisTotal > 0 ? round(($missingDataSourceCount / $projectAnalysisTotal) * 100, 1) : 0.0,
                'publication_items' => $buildOutputItems($paperRows),
                'communication_items' => $buildOutputItems($filtered->whereIn('type_code', ['EA108', 'EA112', 'EA114', 'EA115'])->values()),
                'adoption_items' => $buildOutputItems($filtered->where('type_code', 'EA109')->values()),
                'long_cycle_projects' => $longCycleProjects,
                'missing_data_source_projects' => $missingDataSourceProjects,
            ],
        ];

        $themeGapFacts = $this->visualizationThemeGapFacts($impactThemeAnalysisRows, $impactThemeWeaknessSummary);
        $themeGapHash = $this->aiVisualizationInputHash([
            'facts' => $themeGapFacts,
            'fallback' => $impactThemeNarrativeParagraph,
        ]);
        $cachedThemeGapInsight = $this->findAiVisualizationInsight('theme_output_gap', 'global', $themeGapHash);
        if ($cachedThemeGapInsight && trim((string) $cachedThemeGapInsight->summary) !== '') {
            $policyImpact['impact_theme_narrative_paragraph'] = trim((string) $cachedThemeGapInsight->summary);
            $impactThemeNarrativeParagraph = $policyImpact['impact_theme_narrative_paragraph'];
        }

        $fteSkillAnalysis = $this->buildFteSkillAnalysis($yearFrom, $yearTo);
        $aiProjectNatureInsight = $this->latestAiVisualizationInsight('project_nature', 'global');

        $view = view('outputs.visualization', [
            'rows' => $filtered,
            'sourceLabelMap' => $sourceLabelMap,
            'sourceCounts' => $sourceCounts,
            'typeCounts' => $typeCounts,
            'yearAxisLabels' => $yearAxisLabels,
            'yearBySource' => $yearBySource,
            'outputYearSourceItems' => $outputYearSourceItems,
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
            'outputTypeChartItems' => $outputTypeChartItems,
            'paperSummary' => $paperSummary,
            'presentationSummary' => $presentationSummary,
            'textbookSummary' => $textbookSummary,
            'networkingSummary' => $networkingSummary,
            'paperQuartileLabels' => $paperQuartileLabels,
            'paperQuartileValues' => $paperQuartileValues,
            'paperRankingItems' => $paperRankingItems,
            'paperRankingLabels' => $paperQuartileLabels,
            'paperRankingValues' => $paperQuartileValues,
            'presentationActivityLabels' => $presentationActivity->keys()->values(),
            'presentationActivityValues' => $presentationActivity->values()->values(),
            'presentationActivityItems' => $presentationActivityItems,
            'paperYearLabels' => $paperYears,
            'paperRankingYearSeries' => $paperRankingYearSeries,
            'paperRankingYearItems' => $paperRankingYearItems,
            'presentationYearLabels' => $presentationYears,
            'presentationActivityYearSeries' => $presentationActivityYearSeries,
            'presentationActivityYearItems' => $presentationActivityYearItems,
            'textbookYearLabels' => $textbookYears->keys()->values(),
            'textbookYearValues' => $textbookYears->values()->values(),
            'textbookYearItems' => $textbookYearItems,
            'networkingYearLabels' => $networkingYears->keys()->values(),
            'networkingYearValues' => $networkingYears->values()->values(),
            'networkingYearSeries' => $networkingYearSeries,
            'networkingYearItems' => $networkingYearItems,
            'policyImpact' => $policyImpact,
            'projectNatureAnalysis' => $projectNatureAnalysis,
            'fteSkillAnalysis' => $fteSkillAnalysis,
            'aiProjectNatureInsight' => $aiProjectNatureInsight,
            'aiThemeGapInsight' => $cachedThemeGapInsight,
            'visualizationRealtimeVersion' => $this->visualizationRealtimeVersion(),
            'isPublicSeminar' => $isPublicSeminar,
            'visualizationRouteName' => $isPublicSeminar ? 'seminar' : 'outputs.visualization',
        ]);

        if ($useRenderedVisualizationCache && $renderedVisualizationCacheKey) {
            $html = $view->render();
            Cache::put($renderedVisualizationCacheKey, $html, now()->addMinutes(10));
            return response($html);
        }

        return $view;
    }

    public function seminar(Request $request)
    {
        $request->attributes->set('is_public_seminar', true);

        return $this->visualization($request);
    }

    public function linkPreviewImage(OutputLinkPreview $preview)
    {
        $imageUrl = trim((string) ($preview->preview_image_url ?: $preview->thumbnail_url));
        if ($imageUrl === '' || ! filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            abort(404);
        }

        $host = strtolower((string) (parse_url($imageUrl, PHP_URL_HOST) ?? ''));
        $path = strtolower((string) (parse_url($imageUrl, PHP_URL_PATH) ?? ''));
        $isGoogleSitesImage = str_contains($host, 'googleusercontent.com');
        $isSirirajNewsImage = str_contains($host, 'si.mahidol.ac.th') && str_contains($path, 'admin/news_images/');

        if (! $isGoogleSitesImage && ! $isSirirajNewsImage) {
            abort(404);
        }

        $referer = (string) ($preview->url ?: 'https://www.google.com/');
        $response = $this->fetchPreviewImageResponse($imageUrl, $referer);

        if (! $response && str_contains($host, 'googleusercontent.com')) {
            foreach ($this->googleSitesImageCandidates($referer) as $candidateUrl) {
                $response = $this->fetchPreviewImageResponse($candidateUrl, $referer);
                if (! $response) {
                    continue;
                }

                if ($candidateUrl !== $imageUrl) {
                    $preview->forceFill([
                        'preview_image_url' => $candidateUrl,
                        'thumbnail_url' => $candidateUrl,
                        'provider_name' => 'google_sites_image',
                        'fetch_status' => 'ok',
                        'fetched_at' => now(),
                    ])->save();
                }

                break;
            }
        }

        if (! $response) {
            abort(404);
        }

        $contentType = $response->header('Content-Type') ?: 'image/jpeg';

        return response($response->body(), 200, [
            'Content-Type' => $contentType,
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    private function fetchPreviewImageResponse(string $imageUrl, string $referer): ?\Illuminate\Http\Client\Response
    {
        try {
            $response = Http::timeout(20)
                ->connectTimeout(10)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36',
                    'Accept' => 'image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8',
                    'Referer' => $referer ?: 'https://www.google.com/',
                ])
                ->get($imageUrl);
        } catch (\Throwable) {
            return null;
        }

        $contentType = strtolower((string) $response->header('Content-Type'));
        if (! $response->successful() || ! str_starts_with($contentType, 'image/')) {
            return null;
        }

        if (str_contains($contentType, 'gif') || strlen((string) $response->body()) < 12000) {
            return null;
        }

        return $response;
    }

    private function googleSitesImageCandidates(string $pageUrl): array
    {
        if ($pageUrl === '' || ! filter_var($pageUrl, FILTER_VALIDATE_URL)) {
            return [];
        }

        try {
            $response = Http::timeout(20)
                ->connectTimeout(10)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                    'Accept-Language' => 'th-TH,th;q=0.9,en-US;q=0.8,en;q=0.7',
                ])
                ->withOptions(['allow_redirects' => true])
                ->get($pageUrl);
        } catch (\Throwable) {
            return [];
        }

        if (! $response->successful()) {
            return [];
        }

        $html = html_entity_decode((string) $response->body(), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if ($html === '') {
            return [];
        }

        preg_match_all('/https:\/\/lh3\.googleusercontent\.com\/sitesv\/[^"\'<>\s\\\\)]+/iu', $html, $matches);

        return collect($matches[0] ?? [])
            ->map(fn ($url) => rtrim((string) $url, " \t\n\r\0\x0B);,"))
            ->filter(fn ($url) => filter_var($url, FILTER_VALIDATE_URL))
            ->reject(fn ($url) => str_contains(strtolower((string) parse_url($url, PHP_URL_PATH)), 'favicon'))
            ->unique()
            ->values()
            ->all();
    }

    private function buildFteSkillAnalysis(?int $yearFrom, ?int $yearTo): array
    {
        $normalizeText = static fn ($value): string => Str::of((string) $value)->lower()->replace(["\r", "\n", "\t"], ' ')->replaceMatches('/\s+/u', ' ')->trim()->toString();
        $displayText = static fn ($value): string => Str::of((string) $value)->replace(["\r", "\n", "\t"], ' ')->replaceMatches('/\s+/u', ' ')->trim()->toString();
        $fteYearFor = function (FteRole $role): ?int {
            foreach ([$role->start_date, $role->end_date] as $value) {
                $date = $this->parseBackOfficeDate($value);
                if ($date) {
                    return $date->year + 543;
                }
                $year = $this->normalizeYearFilter($value);
                if ($year !== null) {
                    return $year;
                }
            }

            return $this->extractProjectYearBe($role->project);
        };
        $inRange = static function (?int $year) use ($yearFrom, $yearTo): bool {
            if ($yearFrom !== null && ($year === null || $year < $yearFrom)) {
                return false;
            }
            if ($yearTo !== null && ($year === null || $year > $yearTo)) {
                return false;
            }

            return true;
        };
        $stripNamePrefix = static function ($value) use ($normalizeText): string {
            $text = $normalizeText($value);
            $text = preg_replace('/^(นาย|นางสาว|นาง|คุณ|ดร\.?|ศ\.?|รศ\.?|ผศ\.?|อ\.?|นพ\.?|พญ\.?)\s+/u', '', $text) ?? $text;

            return trim($text);
        };
        $activeUnitUsers = User::query()
            ->select(['id', 'prefix', 'name', 'role', 'position', 'skill_role', 'is_borrowed', 'borrowed_department'])
            ->get()
            ->reject(function (User $user) use ($normalizeText) {
                return in_array($normalizeText($user->name), [
                    'ชริตา พูลเพิ่ม',
                ], true);
            })
            ->values();
        $activeUnitUserIds = $activeUnitUsers->pluck('id')->map(fn ($id) => (int) $id)->flip();
        $activeUnitUserById = $activeUnitUsers->keyBy(fn ($user) => (int) $user->id);
        $userSkillRole = static function (?User $user): string {
            $storedRole = trim((string) ($user?->skill_role ?? ''));
            if ($storedRole !== '' && in_array($storedRole, User::SKILL_ROLES, true)) {
                return $storedRole;
            }

            $position = mb_strtolower(trim((string) ($user?->position ?? '')), 'UTF-8');
            $text = mb_strtolower(trim(((string) ($user?->name ?? '')) . ' ' . $position), 'UTF-8');
            if ($text === '') {
                return 'ไม่ระบุสายงาน';
            }
            if (Str::contains($text, ['หัวหน้าหน่วย', 'รองหัวหน้าหน่วย', 'health policy', 'policy unit'])) {
                return 'Scientific Research';
            }
            if (Str::contains($text, ['ประชาสัมพันธ์', 'communication', 'สื่อสาร', 'ผลิตสื่อ', 'ออกแบบ', 'branding'])) {
                return 'Communication & Public Relations';
            }
            if (Str::contains($text, ['สารสนเทศ', 'นักวิเคราะห์ข้อมูล', 'data', 'สถิติ', 'วิเคราะห์ข้อมูล', 'developer', 'engineer', 'เทคโนโลยีสารสนเทศ'])) {
                return 'IT & Data Management Systems';
            }
            if (Str::contains($text, ['นักบริหารงานวิจัย', 'บริหารงานวิจัย', 'project manager', 'research administrator', 'research management'])) {
                return 'Project Management & Coordination';
            }
            if (Str::contains($text, ['พนักงานทั่วไป'])) {
                return 'General';
            }
            if (Str::contains($text, ['บริหารงานทั่วไป', 'ธุรการ', 'administration', 'admin', 'เลขานุการ'])) {
                return 'Operations & Administrative Support';
            }
            if (Str::contains($text, [
                'นักวิจัย',
                'ผู้ช่วยวิจัย',
                'เจ้าหน้าที่วิจัย',
                'แพทย์',
                'อาจารย์แพทย์',
                'พยาบาล',
                'เภสัช',
                'นักจิตวิทยา',
                'นักโภชนาการ',
                'นักสุขศึกษา',
                'research consultant',
                'consultant',
                'ที่ปรึกษา',
                'คณบดี',
            ])) {
                return 'Scientific Research';
            }

            return 'General';
        };
        $userPositionGroup = static fn (?User $user): string => User::inferFtePositionGroup($user?->position, $user?->name);
        $positionGroupOrder = collect([
            'แพทย์',
            'แพทย์ Intern',
            'นักวิจัย',
            'ผู้ช่วยวิจัย',
            'เจ้าหน้าที่วิจัย',
            'สหสาขาวิชาชีพ',
            'บริหารงานวิจัย',
            'นักสถิติ/นักวิเคราะห์ข้อมูล',
            'นักวิชาการสารสนเทศ',
            'นักประชาสัมพันธ์',
            'เจ้าหน้าที่บริหารงานทั่วไป',
            'ที่ปรึกษา',
            'ไม่ระบุตำแหน่ง',
        ])->values()->flip();
        $positionGroupTotals = $activeUnitUsers
            ->groupBy(fn (User $user) => $userPositionGroup($user))
            ->map(fn (Collection $users) => (int) $users->pluck('id')->unique()->count());
        $skillRoleOrder = collect(User::SKILL_ROLES)->values()->flip();
        $fteCapacityPositionRows = $activeUnitUsers
            ->map(function (User $user) use ($userSkillRole, $userPositionGroup) {
                return (object) [
                    'skill_role' => $userSkillRole($user),
                    'position_group' => $userPositionGroup($user),
                    'is_borrowed' => (bool) ($user->is_borrowed ?? false),
                ];
            })
            ->filter(fn ($row) => ! in_array((string) $row->skill_role, [User::SKILL_ROLE_UNSPECIFIED, User::SKILL_ROLE_GENERAL], true))
            ->groupBy(fn ($row) => (string) $row->skill_role . '|' . (string) $row->position_group)
            ->map(function (Collection $users, string $key) {
                [$skillRole, $positionGroup] = explode('|', $key, 2);

                return (object) [
                    'skill_role' => $skillRole,
                    'position_group' => $positionGroup,
                    'people' => (int) $users->reject(fn ($row) => (bool) $row->is_borrowed)->count(),
                    'borrowed_people' => (int) $users->filter(fn ($row) => (bool) $row->is_borrowed)->count(),
                ];
            })
            ->sortBy(fn ($row) => sprintf(
                '%03d-%03d-%s',
                (int) ($positionGroupOrder[(string) ($row->position_group ?? '')] ?? 999),
                (int) ($skillRoleOrder[(string) ($row->skill_role ?? '')] ?? 999),
                (string) ($row->position_group ?? '')
            ))
            ->values();
        $fteCapacitySkillRows = $activeUnitUsers
            ->map(fn (User $user) => (object) [
                'skill_role' => $userSkillRole($user),
                'is_borrowed' => (bool) ($user->is_borrowed ?? false),
            ])
            ->filter(fn ($row) => ! in_array((string) $row->skill_role, [User::SKILL_ROLE_UNSPECIFIED, User::SKILL_ROLE_GENERAL], true))
            ->groupBy('skill_role')
            ->map(function (Collection $users, string $skillRole) {
                return (object) [
                    'skill_role' => $skillRole,
                    'people' => (int) $users->count(),
                    'regular_people' => (int) $users->reject(fn ($row) => (bool) $row->is_borrowed)->count(),
                    'borrowed_people' => (int) $users->filter(fn ($row) => (bool) $row->is_borrowed)->count(),
                ];
            })
            ->sortBy(fn ($row) => (int) ($skillRoleOrder[(string) ($row->skill_role ?? '')] ?? 999))
            ->values();
        $fteCapacityTotal = max(1, (int) $fteCapacitySkillRows->sum('people'));
        $fteCapacitySkillRows = $fteCapacitySkillRows
            ->map(function ($row) use ($fteCapacityTotal) {
                $row->pct = round(((int) ($row->people ?? 0) / $fteCapacityTotal) * 100, 1);
                return $row;
            })
            ->values();
        $unitUserByName = collect();
        foreach ($activeUnitUsers as $user) {
            foreach ([$user->name, $user->full_name, trim(((string) $user->prefix) . ' ' . ((string) $user->name))] as $nameVariant) {
                $normalized = $normalizeText($nameVariant);
                $stripped = $stripNamePrefix($nameVariant);
                if ($normalized !== '') {
                    $unitUserByName[$normalized] = $user;
                }
                if ($stripped !== '') {
                    $unitUserByName[$stripped] = $user;
                }
            }
        }
        $resolveUnitUser = static function ($personName) use ($normalizeText, $stripNamePrefix, $unitUserByName): ?User {
            $normalized = $normalizeText($personName);
            $stripped = $stripNamePrefix($personName);

            return $unitUserByName->get($normalized) ?: $unitUserByName->get($stripped);
        };
        $fteSkillTaxonomy = [
            'scientific' => [
                'label' => 'งานวิชาการและหลักฐาน',
                'tone' => 'scientific',
                'items' => [
                    ['key' => 'review_literature', 'label' => 'review literature', 'keywords' => ['review literature', 'literature review', 'ทบทวนวรรณกรรม', 'systematic', 'meta']],
                    ['key' => 'proposal', 'label' => 'เขียนproposal', 'keywords' => ['proposal', 'โครงร่าง', 'ข้อเสนอโครงการ']],
                    ['key' => 'data_tool', 'label' => 'พัฒนาเครื่องมือและแนวทางในการเก็บข้อมูล', 'keywords' => ['พัฒนาเครื่องมือ', 'เครื่องมือ', 'แบบสอบถาม', 'แนวทางในการเก็บข้อมูล', 'เครื่องมือเก็บข้อมูล']],
                    ['key' => 'capacity_building', 'label' => 'อบรมพัฒนาศักยภาพ', 'keywords' => ['อบรม', 'พัฒนาศักยภาพ', 'training', 'workshop']],
                    ['key' => 'data_collection', 'label' => 'เก็บข้อมูล', 'keywords' => ['เก็บข้อมูล', 'ขอข้อมูล', 'field', 'survey', 'interview']],
                    ['key' => 'data_analysis', 'label' => 'วิเคราะห์ข้อมูล', 'keywords' => ['วิเคราะห์ข้อมูล', 'analysis', 'model', 'decision tree', 'markov', 'ต้นทุน', 'อรรถประโยชน์']],
                    ['key' => 'report_writing', 'label' => 'เขียนเล่มรายงาน', 'keywords' => ['เขียนเล่ม', 'รายงาน', 'เขียนรายงาน']],
                    ['key' => 'manuscript', 'label' => 'เขียนmanuscript', 'keywords' => ['manuscript', 'ต้นฉบับ', 'บทความ']],
                    ['key' => 'submit_journal', 'label' => 'submit journal', 'keywords' => ['submit journal', 'submission', 'journal', 'ตีพิมพ์']],
                    ['key' => 'presentation', 'label' => 'นำเสนอผลงานวิจัย Oral/Poster presentation', 'keywords' => ['presentation', 'oral', 'poster', 'นำเสนอ']],
                    ['key' => 'consultant', 'label' => 'ที่ปรึกษาโครงการ', 'keywords' => ['ที่ปรึกษาโครงการ', 'consultant', 'consultation']],
                    ['key' => 'concept_note', 'label' => 'พัฒนา Scientific concept note / training program', 'keywords' => ['scientific concept', 'concept note', 'training program']],
                    ['key' => 'research_consult_service', 'label' => 'งานบริการวิชาการให้คำปรึกษางานวิจัย', 'keywords' => ['บริการวิชาการ', 'ให้คำปรึกษางานวิจัย']],
                    ['key' => 'curriculum_design', 'label' => 'งานออกแบบหลักสูตรและการเรียนการสอน', 'keywords' => ['ออกแบบหลักสูตร', 'หลักสูตร']],
                    ['key' => 'teaching', 'label' => 'งานจัดการเรียนการสอน', 'keywords' => ['จัดการเรียนการสอน', 'การเรียนการสอน', 'lecture', 'สอน']],
                    ['key' => 'mentorship', 'label' => 'งานพัฒนาศักยภาพและให้คำปรึกษานักศึกษา (Mentorship)', 'keywords' => ['mentorship', 'นักศึกษา', 'ให้คำปรึกษานักศึกษา']],
                    ['key' => 'child_development', 'label' => 'งานด้านการพัฒนาเด็ก', 'keywords' => ['พัฒนาเด็ก']],
                ],
            ],
            'management' => [
                'label' => 'งานบริหารโครงการและกำกับเอกสาร',
                'tone' => 'management',
                'items' => [
                    ['key' => 'coordination', 'label' => 'ติดต่อประสานงาน', 'keywords' => ['ติดต่อประสานงาน', 'ประสานงาน', 'coordinator']],
                    ['key' => 'stakeholder_meeting', 'label' => 'ประชุมผู้มีส่วนได้ส่วนเสีย (Stakeholder meeting)', 'keywords' => ['stakeholder', 'ผู้มีส่วนได้ส่วนเสีย']],
                    ['key' => 'funding_submit', 'label' => 'ดำเนินการเอกสารขอทุน/submit,ทุน', 'keywords' => ['ขอทุน', 'submit ทุน', 'submit,ทุน', 'ทุน']],
                    ['key' => 'irb_submit', 'label' => 'ดำเนินการเอกสารขอirb/submit irb', 'keywords' => ['irb', 'จริยธรรม']],
                    ['key' => 'progress_report', 'label' => 'จัดทำรายงานความก้าวหน้า', 'keywords' => ['รายงานความก้าวหน้า', 'progress report']],
                    ['key' => 'extension', 'label' => 'ยื่นขอขยายระยะเวลาโครงการ', 'keywords' => ['ขยายระยะเวลา']],
                    ['key' => 'revise_funder_report', 'label' => 'ส่งเล่มรายงาน และ แก้ไขตามข้อเสนอแนะจากผู้ให้ทุน', 'keywords' => ['แก้ไขตามข้อเสนอแนะ', 'ผู้ให้ทุน', 'ส่งเล่มรายงาน']],
                    ['key' => 'final_report', 'label' => 'ส่งส่งรายงานฉบับสมบูรณ์', 'keywords' => ['รายงานฉบับสมบูรณ์', 'final report']],
                    ['key' => 'budget_close', 'label' => 'สรุปงบประมาณที่ใช้จริง และปิดโครงการ', 'keywords' => ['สรุปงบประมาณ', 'ปิดโครงการ']],
                    ['key' => 'material_publication_payment', 'label' => 'เบิกใบตรวจวัสดีพิมพ์/ค่าตอบแทนตีพิมพ์', 'keywords' => ['วัสดุพิมพ์', 'ค่าตอบแทนตีพิมพ์']],
                    ['key' => 'grant_disbursement', 'label' => 'เบิกงบประมาณทุน', 'keywords' => ['เบิกงบประมาณทุน', 'เบิกทุน']],
                    ['key' => 'internal_support', 'label' => 'งานบริการ สนับสนุน และประสานงานภายในหน่วย', 'keywords' => ['สนับสนุน', 'ภายในหน่วย']],
                    ['key' => 'admin_coordination', 'label' => 'งานติดต่อประสานงาน', 'keywords' => ['งานติดต่อประสานงาน']],
                    ['key' => 'meeting_minutes', 'label' => 'งานด้านการลงวาระและรายงานการประชุม', 'keywords' => ['วาระ', 'รายงานการประชุม', 'minutes']],
                ],
            ],
            'it_communication' => [
                'label' => 'งานสื่อสาร ระบบข้อมูล และเทคนิค',
                'tone' => 'it',
                'items' => [
                    ['key' => 'public_relations', 'label' => 'ประชาสัมพันธ์', 'keywords' => ['ประชาสัมพันธ์', 'pr']],
                    ['key' => 'communication_assets', 'label' => 'จัดทำสื่อ / Communication Assets', 'keywords' => ['communication assets', 'จัดทำสื่อ', 'สื่อ']],
                    ['key' => 'research_system', 'label' => 'สร้างโปรแกรม ฐานข้อมูล และ โปรแกรมที่เกี่ยวข้องกับงานวิจัย', 'keywords' => ['โปรแกรม', 'ฐานข้อมูล', 'database', 'website', 'ระบบ']],
                    ['key' => 'technical_support', 'label' => 'IT and Technical support', 'keywords' => ['it', 'technical', 'full stack', 'ux/ui', 'support']],
                    ['key' => 'communication_work', 'label' => 'งานด้านการสื่อสาร และประชาสัมพันธ์', 'keywords' => ['การสื่อสาร', 'สื่อสาร']],
                    ['key' => 'media_design', 'label' => 'งานด้านออกแบบ และผลิตสื่อ', 'keywords' => ['ออกแบบ', 'ผลิตสื่อ', 'branding']],
                    ['key' => 'technology_information', 'label' => 'งานด้านการพัฒนาเทคโนโลยีและสารสนเทศ', 'keywords' => ['เทคโนโลยี', 'สารสนเทศ']],
                ],
            ],
            'logistics_admin' => [
                'label' => 'งานสนับสนุนปฏิบัติการ',
                'tone' => 'logistics',
                'items' => [
                    ['key' => 'knowledge_sharing', 'label' => 'จัดกิจกรรมการแลกเปลี่ยนเรียนรู้ (Knowledge Sharing)', 'keywords' => ['knowledge sharing', 'แลกเปลี่ยนเรียนรู้']],
                    ['key' => 'non_research_meeting', 'label' => 'งานจัดประชุมที่ไม่เกี่ยวข้องกับงานวิจัย', 'keywords' => ['จัดประชุม']],
                    ['key' => 'support_documents', 'label' => 'งานบริการสิ่งสนับสนุนและเอกสาร', 'keywords' => ['สิ่งสนับสนุน', 'เอกสาร']],
                    ['key' => 'document_service', 'label' => 'งานบริการด้านเอกสาร', 'keywords' => ['บริการด้านเอกสาร', 'หนังสือ']],
                    ['key' => 'human_resources', 'label' => 'งานบริการด้านทรัพยากรบุคคล', 'keywords' => ['ทรัพยากรบุคคล', 'hr']],
                    ['key' => 'event_exhibition', 'label' => 'งานจัดงานหรือเข้าร่วมงานนิทรรศการ', 'keywords' => ['นิทรรศการ', 'จัดงาน', 'event']],
                ],
            ],
        ];
        $skillFor = static function (string $task, string $job, string $sourceCategory = '', string $personSkillRole = '') use ($normalizeText, $fteSkillTaxonomy): array {
            $taskText = $normalizeText($task);
            $text = $normalizeText($task . ' ' . $job . ' ' . $sourceCategory);
            $fallback = ['key' => 'logistics_admin', 'label' => 'งานสนับสนุนปฏิบัติการ', 'thai' => 'งานสนับสนุนปฏิบัติการ', 'item_key' => 'logistics_admin_support_documents', 'item_label' => 'งานบริการสิ่งสนับสนุนและเอกสาร', 'tone' => 'logistics'];
            $makeSkill = static function (string $groupKey, string $itemKey) use ($fteSkillTaxonomy): ?array {
                $group = $fteSkillTaxonomy[$groupKey] ?? null;
                if (! $group) {
                    return null;
                }

                foreach ($group['items'] as $index => $item) {
                    if ((string) ($item['key'] ?? '') !== $itemKey) {
                        continue;
                    }

                    return [
                        'key' => $groupKey,
                        'label' => $group['label'],
                        'thai' => $group['label'],
                        'item_key' => $groupKey . '_' . $item['key'],
                        'item_label' => $item['label'],
                        'item_order' => $index,
                        'tone' => $group['tone'],
                    ];
                }

                return null;
            };
            $categoryText = $normalizeText($sourceCategory . ' ' . $job);
            $isResearchProject = $normalizeText($sourceCategory) === 'research project';
            if (! $isResearchProject) {
                if (Str::contains($categoryText, ['เทคโนโลยีสารสนเทศ', 'สารสนเทศ', 'ฐานข้อมูล', 'it & information', 'information technology'])
                    || ($personSkillRole === 'IT & Data Management Systems' && Str::contains($categoryText, ['it', 'information', 'เทคโนโลยี', 'ข้อมูล']))) {
                    return $makeSkill('it_communication', 'technology_information') ?? $fallback;
                }
                if (Str::contains($categoryText, ['ประชาสัมพันธ์', 'corporate communication', 'communication'])) {
                    return $makeSkill('it_communication', 'communication_work') ?? $fallback;
                }
                if (Str::contains($categoryText, ['ทรัพยากรบุคคล', 'human resources', 'administration', 'บริหารงานทั่วไป', 'เลขานุการ'])) {
                    return $makeSkill('logistics_admin', 'human_resources') ?? $fallback;
                }
            }

            foreach ($fteSkillTaxonomy as $groupKey => $group) {
                foreach ($group['items'] as $index => $item) {
                    if ($taskText !== '' && Str::contains($taskText, $item['keywords'])) {
                        return [
                            'key' => $groupKey,
                            'label' => $group['label'],
                            'thai' => $group['label'],
                            'item_key' => $groupKey . '_' . $item['key'],
                            'item_label' => $item['label'],
                            'item_order' => $index,
                            'tone' => $group['tone'],
                        ];
                    }
                }
            }

            foreach ($fteSkillTaxonomy as $groupKey => $group) {
                foreach ($group['items'] as $index => $item) {
                    if (Str::contains($text, $item['keywords'])) {
                        return [
                            'key' => $groupKey,
                            'label' => $group['label'],
                            'thai' => $group['label'],
                            'item_key' => $groupKey . '_' . $item['key'],
                            'item_label' => $item['label'],
                            'item_order' => $index,
                            'tone' => $group['tone'],
                        ];
                    }
                }
            }

            return $fallback + ['item_order' => 999];
        };

        $researchRows = FteRole::query()
            ->with(['project:id,project_code,project_name_th,start_year,start_date'])
            ->get()
            ->map(function (FteRole $role) use ($displayText, $normalizeText, $skillFor, $fteYearFor, $resolveUnitUser, $userSkillRole, $userPositionGroup) {
                $task = $displayText($role->task_detail);
                $job = $displayText($role->job_detail);
                $person = $displayText($role->name) ?: '-';
                $unitUser = $resolveUnitUser($person);
                $personSkillRole = $userSkillRole($unitUser);
                $skill = $skillFor($task, $job, 'research project', $personSkillRole);

                return (object) [
                    'user_id' => $unitUser?->id,
                    'user_skill_role' => $personSkillRole,
                    'user_position_group' => $userPositionGroup($unitUser),
                    'source' => 'research',
                    'source_label' => 'Project FTE',
                    'year' => $fteYearFor($role),
                    'person' => $person,
                    'person_key' => 'user:' . (int) $unitUser?->id,
                    'container_id' => 'project:' . (int) ($role->project_id ?? 0),
                    'container_name' => $displayText($role->project?->project_name_th) ?: ($role->project?->project_code ?: 'ไม่ระบุโครงการ'),
                    'task' => $task !== '' ? $task : ($job ?: 'ไม่ระบุภาระงาน'),
                    'job' => $job,
                    'skill' => $skill,
                    'hours' => (float) ($role->hours ?? 0),
                    'fte' => (float) ($role->fte_value ?? 0),
                ];
            })
            ->filter(fn ($row) => $row->user_id !== null && $row->person_key !== '' && trim((string) $row->task) !== '');

        $backOfficeRows = BackOfficeEntry::query()
            ->with(['task:id,backoffice_id,title,start_date,end_date', 'user:id,prefix,name,role,position,skill_role'])
            ->get()
            ->map(function (BackOfficeEntry $entry) use ($displayText, $normalizeText, $skillFor, $activeUnitUserIds, $activeUnitUserById, $resolveUnitUser, $userSkillRole, $userPositionGroup) {
                $task = $displayText($entry->job_detail) ?: $displayText($entry->job_name);
                $job = $displayText($entry->job_name) ?: $displayText($entry->category);
                $person = $displayText($entry->person_name) ?: $displayText($entry->user?->name) ?: '-';
                $entryUserId = (int) ($entry->user_id ?? 0);
                $unitUser = $entryUserId > 0 && $activeUnitUserIds->has($entryUserId)
                    ? $activeUnitUserById->get($entryUserId)
                    : $resolveUnitUser($person);
                $personSkillRole = $userSkillRole($unitUser);
                $skill = $skillFor($task, $job, $displayText($entry->category), $personSkillRole);

                return (object) [
                    'user_id' => $unitUser?->id,
                    'user_skill_role' => $personSkillRole,
                    'user_position_group' => $userPositionGroup($unitUser),
                    'source' => 'backoffice',
                    'source_label' => 'Back Office FTE',
                    'year' => $this->extractBackOfficeEntryFiscalYearBe($entry),
                    'person' => $person,
                    'person_key' => 'user:' . (int) $unitUser?->id,
                    'container_id' => 'backoffice:' . (int) ($entry->task_id ?? 0),
                    'container_name' => $displayText($entry->task?->title) ?: ($entry->task?->backoffice_id ?: 'Back Office'),
                    'task' => $task !== '' ? $task : ($job ?: 'ไม่ระบุภาระงาน'),
                    'job' => $job,
                    'skill' => $skill,
                    'hours' => (float) ($entry->hours ?? 0),
                    'fte' => (float) ($entry->fte_value ?? 0),
                ];
            })
            ->filter(fn ($row) => $row->user_id !== null && $row->person_key !== '' && trim((string) $row->task) !== '');

        $rows = $researchRows->merge($backOfficeRows)->filter(fn ($row) => $inRange($row->year))->values();
        $uniqueRows = $rows
            ->groupBy(fn ($row) => implode('|', [$row->source, $row->container_id, $row->person_key, (string) data_get($row->skill, 'key'), $normalizeText($row->task)]))
            ->map(function (Collection $group) {
                $first = $group->first();
                $first->hours = (float) $group->max(fn ($row) => (float) ($row->hours ?? 0));
                $first->fte = (float) $group->max(fn ($row) => (float) ($row->fte ?? 0));

                return $first;
            })
            ->values();

        $skillRows = $uniqueRows
            ->groupBy(fn ($row) => (string) data_get($row->skill, 'key'))
            ->map(function (Collection $group) {
                $first = $group->first();

                return (object) [
                    'key' => (string) data_get($first->skill, 'key'),
                    'label' => (string) data_get($first->skill, 'label'),
                    'thai' => (string) data_get($first->skill, 'thai'),
                    'tone' => (string) data_get($first->skill, 'tone'),
                    'unique_tasks' => (int) $group->count(),
                    'people_count' => (int) $group->pluck('person_key')->unique()->count(),
                    'project_count' => (int) $group->where('source', 'research')->pluck('container_id')->unique()->count(),
                    'backoffice_count' => (int) $group->where('source', 'backoffice')->pluck('container_id')->unique()->count(),
                    'hours' => round((float) $group->sum(fn ($row) => (float) ($row->hours ?? 0)), 1),
                    'fte' => round((float) $group->sum(fn ($row) => (float) ($row->fte ?? 0)), 3),
                    'top_tasks' => $group
                        ->groupBy(fn ($row) => (string) ($row->task ?? '-'))
                        ->map(fn (Collection $taskRows, string $task) => (object) ['label' => $task, 'count' => $taskRows->count()])
                        ->sortByDesc('count')
                        ->take(4)
                        ->values(),
                    'people' => $group->pluck('person')->filter()->unique()->sort()->take(8)->values(),
                ];
            })
            ->sortByDesc('unique_tasks')
            ->values();
        $maxSkillTasks = max(1, (int) $skillRows->max('unique_tasks'));
        $fteSkillGroupOrder = collect($fteSkillTaxonomy)->keys()->values()->flip();
        $taskRows = $uniqueRows
            ->groupBy(fn ($row) => (string) data_get($row->skill, 'item_key', Str::of((string) ($row->task ?? '-'))->lower()->squish()->toString()))
            ->map(function (Collection $group) {
                $first = $group->first();
                $skillGroups = $group
                    ->groupBy(fn ($row) => (string) data_get($row->skill, 'thai'))
                    ->map(fn (Collection $skillGroup, string $label) => (object) [
                        'label' => $label,
                        'count' => $skillGroup->count(),
                    ])
                    ->sortByDesc('count')
                    ->values();
                $containers = $group
                    ->pluck('container_name')
                    ->filter()
                    ->unique()
                    ->take(4)
                    ->values();

                return (object) [
                    'key' => (string) data_get($first->skill, 'item_key', Str::slug(Str::limit((string) ($first->task ?? 'task'), 80, ''))),
                    'task_label' => (string) data_get($first->skill, 'item_label', (string) ($first->task ?? '-')),
                    'raw_task_labels' => $group
                        ->pluck('task')
                        ->filter()
                        ->unique()
                        ->sort()
                        ->take(6)
                        ->values(),
                    'group_key' => (string) data_get($first->skill, 'key'),
                    'group_order' => (int) data_get($first->skill, 'item_order', 999),
                    'skill_label' => (string) data_get($first->skill, 'thai'),
                    'skill_en' => (string) data_get($first->skill, 'label'),
                    'tone' => (string) data_get($first->skill, 'tone'),
                    'unique_tasks' => (int) $group->count(),
                    'people_count' => (int) $group->pluck('person_key')->unique()->count(),
                    'project_count' => (int) $group->where('source', 'research')->pluck('container_id')->unique()->count(),
                    'backoffice_count' => (int) $group->where('source', 'backoffice')->pluck('container_id')->unique()->count(),
                    'hours' => round((float) $group->sum(fn ($row) => (float) ($row->hours ?? 0)), 1),
                    'fte' => round((float) $group->sum(fn ($row) => (float) ($row->fte ?? 0)), 3),
                    'skill_groups' => $skillGroups->take(3),
                    'containers' => $containers,
                    'people' => $group->pluck('person')->filter()->unique()->sort()->take(8)->values(),
                ];
            })
            ->sortBy(fn ($row) => sprintf(
                '%02d-%03d-%s',
                (int) ($fteSkillGroupOrder[(string) ($row->group_key ?? '')] ?? 99),
                (int) ($row->group_order ?? 999),
                (string) ($row->task_label ?? '')
            ))
            ->values();
        $maxTaskRows = max(1, (int) $taskRows->max('unique_tasks'));
        $groupedTaskRows = collect($fteSkillTaxonomy)
            ->map(function (array $group, string $groupKey) use ($taskRows) {
                return (object) [
                    'key' => $groupKey,
                    'label' => $group['label'],
                    'tone' => $group['tone'],
                    'rows' => $taskRows->where('group_key', $groupKey)->values(),
                ];
            })
            ->filter(fn ($group) => $group->rows->isNotEmpty())
            ->values();
        $yearlyGroupRows = $uniqueRows
            ->filter(fn ($row) => $row->year !== null)
            ->groupBy(fn ($row) => (string) $row->year)
            ->map(function (Collection $yearRows, string $year) use ($fteSkillTaxonomy) {
                return [
                    'year' => (int) $year,
                    'groups' => collect($fteSkillTaxonomy)
                        ->map(function (array $group, string $groupKey) use ($yearRows) {
                            $rows = $yearRows->filter(fn ($row) => (string) data_get($row->skill, 'key') === $groupKey);

                            return [
                                'key' => $groupKey,
                                'label' => $group['label'],
                                'tone' => $group['tone'],
                                'unique_tasks' => (int) $rows->count(),
                                'people_count' => (int) $rows->pluck('person_key')->unique()->count(),
                                'hours' => round((float) $rows->sum(fn ($row) => (float) ($row->hours ?? 0)), 1),
                                'fte' => round((float) $rows->sum(fn ($row) => (float) ($row->fte ?? 0)), 3),
                            ];
                        })
                        ->values(),
                ];
            })
            ->sortKeys()
            ->values();
        $radarAxes = collect([
            'scientific_research' => [
                'label' => 'Scientific Research',
                'thai' => 'งานวิจัยและวิชาการ',
                'match' => fn ($row): bool => (string) data_get($row->skill, 'key') === 'scientific',
            ],
            'project_management' => [
                'label' => 'Project Management & Coordination',
                'thai' => 'บริหารโครงการและประสานงาน',
                'match' => fn ($row): bool => (string) data_get($row->skill, 'key') === 'management',
            ],
            'operations_admin' => [
                'label' => 'Operations & Administrative Support',
                'thai' => 'ปฏิบัติการและธุรการสนับสนุน',
                'match' => fn ($row): bool => (string) data_get($row->skill, 'key') === 'logistics_admin',
            ],
            'it_data_systems' => [
                'label' => 'IT & Data Management Systems',
                'thai' => 'ระบบข้อมูลและเทคโนโลยี',
                'match' => fn ($row): bool => (string) data_get($row->skill, 'key') === 'it_communication'
                    && ! in_array((string) data_get($row->skill, 'item_key'), [
                        'it_communication_public_relations',
                        'it_communication_communication_assets',
                        'it_communication_communication_work',
                        'it_communication_media_design',
                    ], true),
            ],
            'communication_pr' => [
                'label' => 'Communication & Public Relations',
                'thai' => 'สื่อสารและประชาสัมพันธ์',
                'match' => fn ($row): bool => (string) data_get($row->skill, 'key') === 'it_communication'
                    && in_array((string) data_get($row->skill, 'item_key'), [
                        'it_communication_public_relations',
                        'it_communication_communication_assets',
                        'it_communication_communication_work',
                        'it_communication_media_design',
                    ], true),
            ],
        ]);
        $radarRawRows = $radarAxes
            ->map(function (array $axis, string $key) use ($uniqueRows, $positionGroupOrder, $positionGroupTotals) {
                $rows = $uniqueRows->filter($axis['match']);
                $peopleByRole = $rows
                    ->groupBy(fn ($row) => (string) ($row->user_position_group ?? 'ไม่ระบุตำแหน่ง'))
                    ->map(fn (Collection $groupRows, string $positionGroup) => (object) [
                        'position_group' => $positionGroup,
                        'people' => (int) $groupRows->pluck('person_key')->unique()->count(),
                        'total_people' => (int) ($positionGroupTotals[$positionGroup] ?? 0),
                        'tasks' => (int) $groupRows->count(),
                    ])
                    ->sortBy(fn ($row) => sprintf(
                        '%03d-%03d-%s',
                        (int) ($positionGroupOrder[(string) ($row->position_group ?? '')] ?? 999),
                        999 - min(999, (int) ($row->people ?? 0)),
                        (string) ($row->position_group ?? '')
                    ))
                    ->values();

                return (object) [
                    'key' => $key,
                    'label' => $axis['label'],
                    'thai' => $axis['thai'],
                    'hours' => round((float) $rows->sum(fn ($row) => (float) ($row->hours ?? 0)), 1),
                    'tasks' => (int) $rows->count(),
                    'people' => (int) $rows->pluck('person_key')->unique()->count(),
                    'project_count' => (int) $rows->where('source', 'research')->pluck('container_id')->unique()->count(),
                    'backoffice_count' => (int) $rows->where('source', 'backoffice')->pluck('container_id')->unique()->count(),
                    'role_breakdown' => $peopleByRole,
                ];
            })
            ->values();
        $radarMaxHours = max(1.0, (float) $radarRawRows->max('hours'));
        $radarMaxTasks = max(1, (int) $radarRawRows->max('tasks'));
        $radarMaxPeople = max(1, (int) $radarRawRows->max('people'));
        $radarScale = 90;
        $radarRows = $radarRawRows
            ->map(fn ($row) => (object) [
                'key' => $row->key,
                'label' => $row->label,
                'thai' => $row->thai,
                'hours' => $row->hours,
                'tasks' => $row->tasks,
                'people' => $row->people,
                'project_count' => $row->project_count,
                'backoffice_count' => $row->backoffice_count,
                'role_breakdown' => $row->role_breakdown,
                'hours_pct' => round(((float) $row->hours / $radarMaxHours) * $radarScale, 1),
                'tasks_pct' => round(((int) $row->tasks / $radarMaxTasks) * $radarScale, 1),
                'people_pct' => round(((int) $row->people / $radarMaxPeople) * $radarScale, 1),
            ])
            ->values();
        $radarNonZeroRows = $radarRows
            ->filter(fn ($row) => (int) ($row->tasks ?? 0) > 0)
            ->values();
        $topTaskAxis = $radarNonZeroRows->sortByDesc('tasks_pct')->first();
        $topHourAxis = $radarNonZeroRows->sortByDesc('hours_pct')->first();
        $topPeopleAxis = $radarNonZeroRows->sortByDesc('people_pct')->first();
        $secondaryTaskAxis = $radarNonZeroRows->sortByDesc('tasks_pct')->skip(1)->first();
        $radarLowRows = $radarNonZeroRows
            ->sortBy('tasks_pct')
            ->take(2)
            ->values();
        $fteRadarChartInsight = null;
        if ($radarNonZeroRows->isNotEmpty()) {
            $lowAxisText = $radarLowRows->pluck('thai')->implode(' และ ');
            $fteRadarChartInsight = sprintf(
                'รูปทรงกราฟสะท้อนว่า capacity ของหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพยังวางน้ำหนักไว้ที่การผลิตหลักฐานและงานวิชาการเป็นหลัก โดยภาระงานสูงสุดอยู่ที่ %s (%s%%), เวลาทำงานสูงสุดอยู่ที่ %s (%s%%) และกำลังคนสูงสุดอยู่ที่ %s (%s%%). แกนรองที่ตามมาคือ %s ซึ่งเป็นงานที่ช่วยประคองให้โครงการวิจัยเดินได้จริง ตั้งแต่ประสานผู้เกี่ยวข้อง จัดการเอกสาร กำกับเวลา ไปจนถึงส่งต่อผลลัพธ์ให้ใช้ประโยชน์เชิงนโยบาย. ขณะเดียวกัน แกนที่ยังมีสัดส่วนต่ำคือ %s หากสองแกนนี้เป็นทักษะยุทธศาสตร์ของหน่วย ควรหาแผนเพิ่มงานที่มองเห็นได้ชัดขึ้น หรือกระจายคน/บทบาทให้เกิด capacity ด้านระบบข้อมูล เทคโนโลยี งานปฏิบัติการ และธุรการสนับสนุน ไม่ให้เป็นเพียงงานแฝงในงานวิจัยหรือการประสานโครงการ.',
                (string) ($topTaskAxis->label ?? '-'),
                number_format((float) ($topTaskAxis->tasks_pct ?? 0), 1),
                (string) ($topHourAxis->label ?? '-'),
                number_format((float) ($topHourAxis->hours_pct ?? 0), 1),
                (string) ($topPeopleAxis->label ?? '-'),
                number_format((float) ($topPeopleAxis->people_pct ?? 0), 1),
                (string) ($secondaryTaskAxis->label ?? 'แกนบริหารและประสานงาน'),
                $lowAxisText !== '' ? $lowAxisText : 'ยังไม่พบแกนรอง'
            );
        }
        $positionBreakdownText = static function ($roleBreakdown, ?int $limit = null): string {
            $items = collect($roleBreakdown ?? [])
                ->filter(fn ($row) => (int) ($row->people ?? 0) > 0)
                ->values();
            if ($items->isEmpty()) {
                return 'ยังไม่พบข้อมูลตำแหน่ง';
            }

            $visibleItems = $limit === null ? $items : $items->take($limit);
            $mainItems = $visibleItems
                ->map(function ($row) {
                    $people = (int) ($row->people ?? 0);
                    $totalPeople = (int) ($row->total_people ?? 0);
                    if ($totalPeople > 0 && $people >= $totalPeople) {
                        return (string) ($row->position_group ?? 'ไม่ระบุตำแหน่ง') . ' ทั้งหมด ' . number_format($people) . ' คน';
                    }

                    $denominator = $totalPeople > 0
                        ? ' จากทั้งหมด ' . number_format($totalPeople) . ' คน'
                        : ' คน';

                    return (string) ($row->position_group ?? 'ไม่ระบุตำแหน่ง') . ' ' . number_format($people) . $denominator;
                })
                ->values();
            if ($limit !== null) {
                $remaining = $items->slice($limit);
                if ($remaining->isNotEmpty()) {
                    $mainItems->push('กลุ่มอื่นอีก ' . number_format((int) $remaining->sum('people')) . ' คน');
                }
            }

            return $mainItems->implode(', ');
        };
        $insightNarrative = static function ($row, string $breakdown): string {
            $tasks = number_format((int) ($row->tasks ?? 0));
            $people = number_format((int) ($row->people ?? 0));
            $hours = number_format((float) ($row->hours ?? 0), 1);
            $tasksPct = number_format((float) ($row->tasks_pct ?? 0), 1);
            $peoplePct = number_format((float) ($row->people_pct ?? 0), 1);
            $hoursPct = number_format((float) ($row->hours_pct ?? 0), 1);
            $trendText = "รูปทรงกราฟอยู่ที่ภาระงาน {$tasksPct}%, เวลาทำงาน {$hoursPct}% และกำลังคน {$peoplePct}%";

            return match ((string) ($row->key ?? '')) {
                'scientific_research' => "Scientific Research เป็นแกนที่ดันกราฟออกมากที่สุด {$trendText} แปลว่างานหลักของหน่วยยังอยู่ที่งานวิจัยและวิชาการ โดยมีหลายตำแหน่งเข้ามาร่วมขับเคลื่อน เช่น {$breakdown}.",
                'project_management' => "Project Management & Coordination เป็นแกนรองที่ตาม Scientific Research ชัดเจน {$trendText} แนวโน้มนี้สะท้อนว่างานบริหารโครงการและประสานงานเป็นฐานสำคัญที่ทำให้งานวิจัยเดินต่อได้ โดยมี {$breakdown}.",
                'it_data_systems' => "IT & Data Management Systems ยังเป็นแกนขนาดเล็กกว่าแกนวิจัยและบริหารโครงการ {$trendText} จึงควรดูต่อว่าระบบข้อมูลเป็น capacity หลักแล้วหรือยัง หรือยังถูกบันทึกแฝงอยู่ในงานวิจัย/งานประสาน.",
                'communication_pr' => "Communication & Public Relations อยู่ในกลุ่มแกนรองของกราฟ {$trendText} แนวโน้มนี้บอกว่างานสื่อสารมีบทบาทต่อการเผยแพร่และต่อยอดงานวิจัย แต่ยังไม่ได้ขยายเท่าแกนวิจัยและบริหารโครงการ โดยมี {$breakdown}.",
                'operations_admin' => "Operations & Administrative Support เป็นแกนที่ยังต่ำในรูปทรงกราฟ {$trendText} ไม่ได้แปลว่าไม่สำคัญ แต่อาจสะท้อนว่างานสนับสนุนจำนวนหนึ่งถูกบันทึกอยู่ในหมวดประสานงานหรือวิชาการ.",
                default => "{$row->label}: {$trendText}.",
            };
        };
        $fteSkillInsights = collect();
        $highlightedRadarRows = $radarRows
            ->filter(fn ($row) => (int) ($row->tasks ?? 0) > 0)
            ->sortByDesc('tasks')
            ->take(3)
            ->values();
        $highlightedRadarRows
            ->each(function ($row) use ($fteSkillInsights, $positionBreakdownText, $insightNarrative) {
                $fteSkillInsights->push($insightNarrative($row, $positionBreakdownText($row->role_breakdown)));
            });
        $unmentionedRadarRows = $radarRows
            ->filter(fn ($row) => (int) ($row->tasks ?? 0) > 0)
            ->reject(fn ($row) => $highlightedRadarRows->pluck('key')->contains((string) ($row->key ?? '')))
            ->sortBy('tasks_pct')
            ->values();
        if ($unmentionedRadarRows->isNotEmpty()) {
            $fteSkillInsights->push(
                'ส่วนแกนที่ยังไม่ได้ถูกหยิบมาเล่าข้างต้นคือ ' .
                $unmentionedRadarRows->pluck('label')->implode(' และ ') .
                ' ซึ่งยังมีสัดส่วนต่ำเมื่อเทียบกับแกนหลัก หากเป็นทักษะยุทธศาสตร์ควรหาแผนเพิ่มงานหรือกระจายคนเพื่อสร้าง capacity'
            );
        } elseif ($radarLowRows->isNotEmpty()) {
            $fteSkillInsights->push(
                'ส่วนแกนที่ยังมีสัดส่วนต่ำคือ ' .
                $radarLowRows->pluck('label')->implode(' และ ') .
                ' หากเป็นทักษะยุทธศาสตร์ควรหาแผนเพิ่มงานหรือกระจายคนเพื่อสร้าง capacity'
            );
        }

        return [
            'total_unique_tasks' => (int) $uniqueRows->count(),
            'raw_rows' => (int) $rows->count(),
            'deduped_rows' => max(0, (int) $rows->count() - (int) $uniqueRows->count()),
            'people_count' => (int) $uniqueRows->pluck('person_key')->unique()->count(),
            'skill_count' => (int) $skillRows->count(),
            'total_hours' => round((float) $uniqueRows->sum(fn ($row) => (float) ($row->hours ?? 0)), 1),
            'total_fte' => round((float) $uniqueRows->sum(fn ($row) => (float) ($row->fte ?? 0)), 3),
            'max_skill_tasks' => $maxSkillTasks,
            'skill_rows' => $skillRows,
            'max_task_rows' => $maxTaskRows,
            'task_rows' => $taskRows,
            'grouped_task_rows' => $groupedTaskRows,
            'yearly_group_rows' => $yearlyGroupRows,
            'radar_rows' => $radarRows,
            'radar_chart_insight' => $fteRadarChartInsight,
            'radar_insights' => $fteSkillInsights->take(4)->values(),
            'capacity_position_rows' => $fteCapacityPositionRows,
            'capacity_skill_rows' => $fteCapacitySkillRows,
            'capacity_total' => (int) $fteCapacitySkillRows->sum('people'),
            'capacity_regular_total' => (int) $fteCapacitySkillRows->sum('regular_people'),
            'capacity_borrowed_total' => (int) $fteCapacitySkillRows->sum('borrowed_people'),
            'source_mix' => collect(['research' => 'Project FTE', 'backoffice' => 'Back Office FTE'])
                ->map(fn ($label, $key) => (object) [
                    'key' => $key,
                    'label' => $label,
                    'unique_tasks' => (int) $uniqueRows->where('source', $key)->count(),
                    'people_count' => (int) $uniqueRows->where('source', $key)->pluck('person_key')->unique()->count(),
                    'hours' => round((float) $uniqueRows->where('source', $key)->sum(fn ($row) => (float) ($row->hours ?? 0)), 1),
                ])
                ->values(),
        ];
    }

    private function latestAiVisualizationInsight(string $type, string $scopeKey = 'global'): ?AiVisualizationInsight
    {
        if (! Schema::hasTable('ai_visualization_insights')) {
            return null;
        }

        return AiVisualizationInsight::query()
            ->where('insight_type', $type)
            ->where('scope_key', $scopeKey)
            ->latest('generated_at')
            ->latest('id')
            ->first();
    }

    private function findAiVisualizationInsight(string $type, string $scopeKey, string $inputHash): ?AiVisualizationInsight
    {
        if (! Schema::hasTable('ai_visualization_insights')) {
            return null;
        }

        return AiVisualizationInsight::query()
            ->where('insight_type', $type)
            ->where('scope_key', $scopeKey)
            ->where('input_hash', $inputHash)
            ->latest('generated_at')
            ->latest('id')
            ->first();
    }

    private function aiVisualizationInputHash(array $payload): string
    {
        return hash('sha256', json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    private function visualizationThemeGapFacts(Collection $themeAnalysisRows, Collection $weaknessRows): array
    {
        return [
            'themes' => $themeAnalysisRows
                ->map(fn ($theme) => [
                    'theme' => (string) ($theme->theme ?? ''),
                    'project_count' => (int) ($theme->project_count ?? 0),
                    'output_count' => (int) ($theme->output_count ?? 0),
                    'dominant_code' => (string) ($theme->dominant_code ?? ''),
                    'dominant_label' => (string) ($theme->dominant_label ?? ''),
                    'dominant_count' => (int) ($theme->dominant_count ?? 0),
                    'main_weakness_code' => (string) ($theme->main_weakness_code ?? ''),
                    'main_weakness_label' => (string) ($theme->main_weakness_label ?? ''),
                    'main_weakness_with' => (int) ($theme->main_weakness_with ?? 0),
                    'main_weakness_missing' => (int) ($theme->main_weakness_missing ?? 0),
                ])
                ->values()
                ->all(),
            'overall_gap' => $weaknessRows
                ->map(fn ($row) => [
                    'code' => (string) ($row->code ?? ''),
                    'label' => (string) ($row->label ?? ''),
                    'with' => (int) ($row->with ?? 0),
                    'missing' => (int) ($row->missing ?? 0),
                    'project_total' => (int) ($row->project_total ?? 0),
                ])
                ->values()
                ->all(),
        ];
    }

    public function visualizationPdf(Request $request)
    {
        ini_set('pcre.backtrack_limit', '5000000');

        $view = $this->visualization($request);
        $data = method_exists($view, 'getData') ? $view->getData() : [];
        $user = $request->user();
        $reporter = trim((string) (($user?->full_name ?? '') ?: ($user?->name ?? ''))) ?: 'ผู้ใช้ระบบ';
        $generatedAt = now()->setTimezone('Asia/Bangkok');
        $filters = collect([
            $request->filled('source') ? 'แหล่งข้อมูล: ' . trim((string) $request->input('source')) : null,
            $request->filled('type') ? 'ประเภทผลงาน: ' . trim((string) $request->input('type')) : null,
            $request->filled('year_from') ? 'ปีเริ่ม: ' . trim((string) $request->input('year_from')) : null,
            $request->filled('year_to') ? 'ปีสิ้นสุด: ' . trim((string) $request->input('year_to')) : null,
        ])->filter()->values();

        $pdf = PDF::loadView('outputs.visualization-pdf', array_merge($data, [
            'generatedAt' => $generatedAt,
            'reporter' => $reporter,
            'filters' => $filters,
        ]), [], [
            'format' => 'A4-L',
        ]);

        $filename = $this->buildOutputPdfFilename('OUTPUT_VISUALIZATION');
        AuditLogger::logFileDownload($filename, [
            'subject_type' => 'output_visualization_pdf',
            'subject_label' => 'Outputs Visualization',
        ], $request);

        return $pdf->stream($filename);
    }

    private function visualizationRenderedCacheKey(Request $request): string
    {
        $user = $request->user();
        $query = collect($request->query())
            ->sortKeys()
            ->map(fn ($value) => is_array($value) ? array_values($value) : $value)
            ->all();

        return 'outputs_visualization:rendered:' . md5(json_encode([
            'user_id' => (int) ($user?->id ?? 0),
            'is_admin_like' => (bool) ($user?->isAdminLike() ?? false),
            'query' => $query,
            'version' => $this->visualizationDataVersion(),
            'view_version' => $this->visualizationViewVersion(),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    private function visualizationViewVersion(): string
    {
        $paths = [
            resource_path('views/outputs/visualization.blade.php'),
            __FILE__,
        ];

        return md5(collect($paths)
            ->filter(fn ($path) => is_file($path))
            ->map(fn ($path) => $path . ':' . filemtime($path))
            ->implode('|'));
    }

    private function visualizationRealtimeVersion(): string
    {
        return md5($this->visualizationDataVersion() . '|' . $this->visualizationViewVersion());
    }

    private function visualizationDataVersion(): string
    {
        return Cache::remember('outputs_visualization:data_version', now()->addSeconds(5), function () {
            $tables = [
                'outputs',
                'output_authors',
                'output_award_recipients',
                'output_book_authors',
                'output_conferences',
                'output_conference_presenters',
                'back_office_tasks',
                'back_office_task_outputs',
                'back_office_entries',
                'external_outputs',
                'external_output_authors',
                'external_output_award_recipients',
                'external_output_book_authors',
                'external_output_conferences',
                'external_output_conference_presenters',
                'projects',
                'copis',
                'co_research',
                'users',
            ];
            if (Schema::hasTable('ai_visualization_insights')) {
                $tables[] = 'ai_visualization_insights';
            }

            $parts = [];
            foreach ($tables as $table) {
                if (!Schema::hasTable($table)) {
                    continue;
                }

                $count = DB::table($table)->count();
                $updatedAt = Schema::hasColumn($table, 'updated_at')
                    ? (string) DB::table($table)->max('updated_at')
                    : '';
                $parts[] = $table . ':' . $count . ':' . $updatedAt;
            }

            return md5(implode('|', $parts));
        });
    }

    public function rewriteVisualizationThemeGap(Request $request)
    {
        $validated = $request->validate([
            'facts' => ['nullable', 'array'],
            'facts.themes' => ['nullable', 'array'],
            'facts.overall_gap' => ['nullable', 'array'],
            'fallback' => ['required', 'string', 'max:6000'],
        ]);

        $fallback = trim((string) $validated['fallback']);
        $facts = $validated['facts'] ?? [];
        $inputHash = $this->aiVisualizationInputHash([
            'facts' => $facts,
            'fallback' => $fallback,
        ]);
        $cachedInsight = $this->findAiVisualizationInsight('theme_output_gap', 'global', $inputHash);
        if ($cachedInsight && trim((string) $cachedInsight->summary) !== '') {
            return response()->json([
                'ok' => true,
                'cached' => true,
                'paragraph' => trim((string) $cachedInsight->summary),
            ]);
        }

        $prompt = $this->buildThemeGapRewritePrompt($facts, $fallback);
        $preferredProvider = mb_strtolower(trim((string) env('AI_PROVIDER', 'ollama')), 'UTF-8');
        $model = trim((string) config('services.gemini.model', 'gemini-2.5-flash')) ?: 'gemini-2.5-flash';
        $apiKey = trim((string) config('services.gemini.key', ''));
        $timeout = max(3, (int) config('services.gemini.insight_timeout', 8));
        $text = '';
        $provider = 'gemini';

        if ($preferredProvider === 'ollama') {
            $ollama = $this->callOllamaInsight($prompt);
            if (! ($ollama['ok'] ?? false)) {
                return response()->json([
                    'ok' => false,
                    'reason' => $ollama['reason'] ?? 'ollama_unavailable',
                ]);
            }

            $text = (string) ($ollama['text'] ?? '');
            $provider = 'ollama';
            $model = trim((string) ($ollama['model'] ?? '')) ?: trim((string) config('services.ollama.model', 'qwen2.5:7b'));
        } elseif ($apiKey !== '') {
            $url = rtrim((string) config('services.gemini.base_url', 'https://generativelanguage.googleapis.com/v1beta'), '/')
                . '/models/' . $model . ':generateContent';

            try {
                $response = Http::withHeaders(['x-goog-api-key' => $apiKey])
                    ->acceptJson()
                    ->timeout($timeout)
                    ->post($url, [
                        'contents' => [
                            [
                                'role' => 'user',
                                'parts' => [
                                    ['text' => $prompt],
                                ],
                            ],
                        ],
                        'generationConfig' => [
                            'temperature' => 0.2,
                            'maxOutputTokens' => 1200,
                            'responseMimeType' => 'application/json',
                        ],
                    ]);
            } catch (\Throwable $e) {
                return response()->json(['ok' => false, 'reason' => 'request_exception']);
            }

            if ($response->failed()) {
                return response()->json([
                    'ok' => false,
                    'reason' => 'gemini_http_' . $response->status(),
                ]);
            }

            $text = collect($response->json('candidates.0.content.parts', []))
                ->map(fn ($part) => $part['text'] ?? '')
                ->filter()
                ->implode("\n");
        } else {
            $gateway = $this->callGeminiInsightGateway($model, $prompt, $timeout);
            if (! ($gateway['ok'] ?? false)) {
                return response()->json([
                    'ok' => false,
                    'reason' => $gateway['reason'] ?? 'gateway_unavailable',
                ]);
            }

            $text = (string) ($gateway['text'] ?? '');
            $provider = 'gemini_gateway';
            $model = trim((string) ($gateway['model'] ?? $model)) ?: $model;
        }

        $decoded = $this->decodeGeminiInsightJson($text);
        $paragraph = trim((string) ($decoded['paragraph'] ?? ''));

        if (! $this->isValidThemeGapParagraph($paragraph)) {
            return response()->json(['ok' => false, 'reason' => 'invalid_ai_output']);
        }

        if (Schema::hasTable('ai_visualization_insights')) {
            AiVisualizationInsight::query()->updateOrCreate(
                [
                    'insight_type' => 'theme_output_gap',
                    'scope_key' => 'global',
                    'input_hash' => $inputHash,
                ],
                [
                    'provider' => $provider,
                    'model' => $model,
                    'summary' => $paragraph,
                    'payload' => ['paragraph' => $paragraph],
                    'input_snapshot' => [
                        'facts' => $facts,
                        'fallback' => $fallback,
                    ],
                    'generated_at' => now(),
                ]
            );
        }

        return response()->json([
            'ok' => true,
            'cached' => false,
            'paragraph' => $paragraph,
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
            'exportMinDate' => $this->exportMinDate($papers),
        ]);
    }

    public function papersPdf(Request $request)
    {
        $sort = in_array($request->get('sort'), ['created_at', 'year', 'title', 'journal', 'quartile'], true)
            ? $request->get('sort')
            : 'created_at';
        $dir = $request->get('dir') === 'asc' ? 'asc' : 'desc';
        $payload = $this->buildPapersPayload($request, $sort, $dir);
        $payload['items'] = $this->filterRowsByExportDateRange($payload['items'], $request);
        $payload['summary'] = $this->summarizeCategoryRows($payload['items'], 'papers');

        $user = auth()->user();
        $reporter = trim(($user->prefix ?? '') . ' ' . ($user->name ?? ''));
        $filters = collect([
            $request->filled('search') ? 'ค้นหา: ' . trim((string) $request->input('search')) : null,
            $request->filled('author') ? 'Author: ' . trim((string) $request->input('author')) : null,
            $request->filled('role') ? 'Role: ' . trim((string) $request->input('role')) : null,
            $request->filled('quartile') ? 'Ranking: ' . trim((string) $request->input('quartile')) : null,
            $request->filled('impact_max')
                ? 'Impact Factor <= ' . trim((string) $request->input('impact_max'))
                : null,
            $request->filled('year') ? 'ปี: ' . trim((string) $request->input('year')) : null,
            $this->exportDateRangeFilterLabel($request),
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

    public function papersExcel(Request $request)
    {
        $sort = in_array($request->get('sort'), ['created_at', 'year', 'title', 'journal', 'quartile'], true)
            ? $request->get('sort')
            : 'created_at';
        $dir = $request->get('dir') === 'asc' ? 'asc' : 'desc';
        $payload = $this->buildPapersPayload($request, $sort, $dir);
        $rows = $this->filterRowsByExportDateRange($payload['items'], $request);
        $filename = $this->buildOutputExportFilename('OUTPUT_PAPERS', 'xlsx');
        $columns = [
            'title' => 'ชื่อผลงาน',
            'type' => 'ประเภท',
            'source_badge' => 'ที่มา',
            'year_be' => 'ปี พ.ศ.',
            'project_label' => 'รายการอ้างอิง',
            'journal' => 'Journal',
            'quartile' => 'Ranking',
            'impact_factor' => 'Impact Factor',
            'authors_text' => 'Authors',
            'detail' => 'รายละเอียด',
            'project_link' => 'ลิงก์ผลงาน',
        ];

        AuditLogger::logFileDownload($filename, [
            'subject_type' => 'output_papers_excel',
            'subject_label' => 'Research Papers and Journal Articles',
        ], $request);

        return $this->xlsxResponse(
            $filename,
            'Research Papers',
            array_merge(['#' => '#'], $columns),
            $rows->map(fn ($row, $index) => $this->categoryExcelRow($row, $columns, (int) $index + 1))->values(),
            $this->categoryExcelMetaRows('Research Papers and Journal Articles', $rows, $request)
        );
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
            'exportMinDate' => $this->exportMinDate($rows),
        ]);
    }

    public function presentationsPdf(Request $request)
    {
        $sort = in_array($request->get('sort'), ['created_at', 'year', 'title', 'conference', 'activity'], true)
            ? $request->get('sort')
            : 'created_at';
        $dir = $request->get('dir') === 'asc' ? 'asc' : 'desc';
        $payload = $this->buildPresentationsPayload($request, $sort, $dir);
        $payload['items'] = $this->filterRowsByExportDateRange($payload['items'], $request);
        $payload['summary'] = $this->summarizeCategoryRows($payload['items'], 'presentations');

        $user = auth()->user();
        $reporter = trim(($user->prefix ?? '') . ' ' . ($user->name ?? ''));
        $filters = collect([
            $request->filled('search') ? 'ค้นหา: ' . trim((string) $request->input('search')) : null,
            $request->filled('presenter') ? 'ผู้นำเสนอ: ' . trim((string) $request->input('presenter')) : null,
            $request->filled('activity') ? 'กิจกรรม: ' . trim((string) $request->input('activity')) : null,
            $request->filled('year') ? 'ปี: ' . trim((string) $request->input('year')) : null,
            $this->exportDateRangeFilterLabel($request),
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

    public function presentationsExcel(Request $request)
    {
        $sort = in_array($request->get('sort'), ['created_at', 'year', 'title', 'conference', 'activity'], true)
            ? $request->get('sort')
            : 'created_at';
        $dir = $request->get('dir') === 'asc' ? 'asc' : 'desc';
        $payload = $this->buildPresentationsPayload($request, $sort, $dir);
        $rows = $this->filterRowsByExportDateRange($payload['items'], $request);
        $filename = $this->buildOutputExportFilename('OUTPUT_PRESENTATIONS', 'xlsx');
        $columns = [
            'title' => 'ชื่อผลงาน',
            'source_badge' => 'ที่มา',
            'year_be' => 'ปี พ.ศ.',
            'project_label' => 'รายการอ้างอิง',
            'conference_name' => 'Conference',
            'presentation_type' => 'ประเภทการนำเสนอ',
            'presenter_name' => 'ผู้นำเสนอ',
            'location' => 'สถานที่',
            'detail' => 'รายละเอียด',
            'project_link' => 'ลิงก์ผลงาน',
        ];

        AuditLogger::logFileDownload($filename, [
            'subject_type' => 'output_presentations_excel',
            'subject_label' => 'Presentations in Academic Conferences',
        ], $request);

        return $this->xlsxResponse(
            $filename,
            'Presentations',
            array_merge(['#' => '#'], $columns),
            $rows->map(fn ($row, $index) => $this->categoryExcelRow($row, $columns, (int) $index + 1))->values(),
            $this->categoryExcelMetaRows('Presentations in Academic Conferences', $rows, $request)
        );
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
            'exportMinDate' => $this->exportMinDate($payload['items']),
        ]);
    }

    public function textbooksPdf(Request $request)
    {
        $sort = in_array($request->get('sort'), ['created_at', 'year', 'title', 'book_title', 'publisher'], true)
            ? $request->get('sort')
            : 'created_at';
        $dir = $request->get('dir') === 'asc' ? 'asc' : 'desc';
        $payload = $this->buildTextbooksPayload($request, $sort, $dir);
        $payload['items'] = $this->filterRowsByExportDateRange($payload['items'], $request);
        $payload['summary'] = $this->summarizeCategoryRows($payload['items'], 'textbooks');

        $user = auth()->user();
        $reporter = trim(($user->prefix ?? '') . ' ' . ($user->name ?? ''));
        $filters = collect([
            $request->filled('search') ? 'ค้นหา: ' . trim((string) $request->input('search')) : null,
            $request->filled('author') ? 'Author: ' . trim((string) $request->input('author')) : null,
            $request->filled('year') ? 'ปี: ' . trim((string) $request->input('year')) : null,
            $this->exportDateRangeFilterLabel($request),
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

    public function textbooksExcel(Request $request)
    {
        $sort = in_array($request->get('sort'), ['created_at', 'year', 'title', 'book_title', 'publisher'], true)
            ? $request->get('sort')
            : 'created_at';
        $dir = $request->get('dir') === 'asc' ? 'asc' : 'desc';
        $payload = $this->buildTextbooksPayload($request, $sort, $dir);
        $rows = $this->filterRowsByExportDateRange($payload['items'], $request);
        $filename = $this->buildOutputExportFilename('OUTPUT_TEXTBOOKS', 'xlsx');
        $columns = [
            'title' => 'ชื่อผลงาน',
            'source_badge' => 'ที่มา',
            'year_be' => 'ปี พ.ศ.',
            'project_label' => 'รายการอ้างอิง',
            'book_title' => 'Book',
            'publisher_name' => 'Publisher',
            'chapter_pages' => 'Pages',
            'isbn' => 'ISBN',
            'authors_text' => 'Authors',
            'detail' => 'รายละเอียด',
            'project_link' => 'ลิงก์ผลงาน',
        ];

        AuditLogger::logFileDownload($filename, [
            'subject_type' => 'output_textbooks_excel',
            'subject_label' => 'Textbook / Book Chapter',
        ], $request);

        return $this->xlsxResponse(
            $filename,
            'Textbooks',
            array_merge(['#' => '#'], $columns),
            $rows->map(fn ($row, $index) => $this->categoryExcelRow($row, $columns, (int) $index + 1))->values(),
            $this->categoryExcelMetaRows('Textbook / Book Chapter', $rows, $request)
        );
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

    private function buildOutputExportFilename(string $prefix, string $extension): string
    {
        $dateStr = now()->setTimezone('Asia/Bangkok')->format('Ymd_Hi');
        return strtoupper(trim($prefix)) . '_' . $dateStr . '.' . ltrim($extension, '.');
    }

    private function xlsxResponse(string $filename, string $sheetName, array $columns, Collection $rows, array $metaRows = [])
    {
        $content = $this->buildXlsxWorkbook($sheetName, array_values($columns), $rows, $metaRows);

        return response($content, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Content-Length' => (string) strlen($content),
        ]);
    }

    private function buildXlsxWorkbook(string $sheetName, array $headers, Collection $rows, array $metaRows = []): string
    {
        $tmpPath = tempnam(sys_get_temp_dir(), 'outputs_xlsx_');
        $zip = new \ZipArchive();
        $zip->open($tmpPath, \ZipArchive::OVERWRITE);

        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '</Types>');

        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>');

        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="' . $this->xlsxEscapeAttribute(Str::limit($sheetName, 31, '')) . '" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>');

        $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>');

        $zip->addFromString('xl/styles.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><name val="Calibri"/></font></fonts>'
            . '<fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FFDDEBFF"/><bgColor indexed="64"/></patternFill></fill></fills>'
            . '<borders count="2"><border><left/><right/><top/><bottom/><diagonal/></border><border><left style="thin"><color rgb="FFCBD5E1"/></left><right style="thin"><color rgb="FFCBD5E1"/></right><top style="thin"><color rgb="FFCBD5E1"/></top><bottom style="thin"><color rgb="FFCBD5E1"/></bottom><diagonal/></border></borders>'
            . '<cellXfs count="3"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1"><alignment wrapText="1" vertical="top"/></xf></cellXfs>'
            . '</styleSheet>');

        $zip->addFromString('xl/worksheets/sheet1.xml', $this->buildXlsxWorksheet($headers, $rows, $metaRows));
        $zip->close();

        $content = (string) file_get_contents($tmpPath);
        @unlink($tmpPath);

        return $content;
    }

    private function buildXlsxWorksheet(array $headers, Collection $rows, array $metaRows): string
    {
        $rowIndex = 1;
        $xmlRows = [];

        foreach ($metaRows as $metaRow) {
            $xmlRows[] = $this->xlsxRow($rowIndex++, collect($metaRow)->values()->all(), 2);
        }

        if (!empty($metaRows)) {
            $xmlRows[] = '<row r="' . $rowIndex++ . '"/>';
        }

        $headerRowIndex = $rowIndex;
        $xmlRows[] = $this->xlsxRow($rowIndex++, $headers, 1);

        foreach ($rows as $row) {
            $xmlRows[] = $this->xlsxRow($rowIndex++, array_values((array) $row), 2);
        }

        $columnCount = max(count($headers), 1);
        $lastColumn = $this->xlsxColumnName($columnCount);

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheetViews><sheetView workbookViewId="0"><pane ySplit="' . ($headerRowIndex) . '" topLeftCell="A' . ($headerRowIndex + 1) . '" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
            . '<cols>' . collect(range(1, $columnCount))->map(fn ($i) => '<col min="' . $i . '" max="' . $i . '" width="' . ($i === 1 ? 8 : 24) . '" customWidth="1"/>')->implode('') . '</cols>'
            . '<sheetData>' . implode('', $xmlRows) . '</sheetData>'
            . '<autoFilter ref="A' . $headerRowIndex . ':' . $lastColumn . max($headerRowIndex, $rowIndex - 1) . '"/>'
            . '</worksheet>';
    }

    private function xlsxRow(int $rowIndex, array $values, int $style = 0): string
    {
        $cells = [];
        foreach (array_values($values) as $columnIndex => $value) {
            $cell = $this->xlsxColumnName($columnIndex + 1) . $rowIndex;
            $styleAttribute = $style > 0 ? ' s="' . $style . '"' : '';
            $cells[] = '<c r="' . $cell . '" t="inlineStr"' . $styleAttribute . '><is><t xml:space="preserve">' . $this->xlsxEscapeText($value) . '</t></is></c>';
        }

        return '<row r="' . $rowIndex . '">' . implode('', $cells) . '</row>';
    }

    private function xlsxColumnName(int $index): string
    {
        $name = '';
        while ($index > 0) {
            $index--;
            $name = chr(65 + ($index % 26)) . $name;
            $index = intdiv($index, 26);
        }

        return $name;
    }

    private function xlsxEscapeText($value): string
    {
        $text = trim((string) ($value ?? ''));
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $text) ?? '';

        return htmlspecialchars($text, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }

    private function xlsxEscapeAttribute(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }

    private function outputsExcelColumns(): array
    {
        return [
            '#',
            'ชื่อผลงาน',
            'ประเภท',
            'ปี พ.ศ.',
            'ที่มา',
            'รายการอ้างอิง',
            'รายละเอียด',
            'ลิงก์ผลงาน',
            'Journal',
            'Ranking',
            'Impact Factor',
            'Authors',
            'Conference',
            'ประเภทการนำเสนอ',
            'สถานที่',
            'Presenter',
            'Awardee',
            'Book',
            'Publisher',
            'Pages',
            'ISBN',
            'วันที่ลงข้อมูล',
        ];
    }

    private function outputExcelRow($row, int $index): array
    {
        $created = $row->created_at ? Carbon::parse($row->created_at) : null;
        $createdLabel = $created ? $created->locale('th')->translatedFormat('j F') . ' ' . ($created->year + 543) : '-';
        $type = (string) ($row->type ?? '');

        return [
            $index,
            $row->title ?: '-',
            $row->type ?: '-',
            $row->year_be ?: '-',
            trim((string) ($row->source_badge ?? '')) !== '' ? $row->source_badge : 'Project',
            $row->project_label ?: '-',
            $row->detail ?: '-',
            $row->related_link ?: ($row->link ?: '-'),
            $this->specialFieldValue($row, 'Journal') ?: '-',
            $this->specialFieldValue($row, 'Ranking') ?: '-',
            $this->specialFieldValue($row, 'IF') ?: '-',
            str_starts_with($type, 'EA101') || str_contains($type, 'Research Paper') ? ($this->specialPeopleValue($row) ?: '-') : '-',
            $this->specialFieldValue($row, 'Conference') ?: '-',
            $this->specialFieldValue($row, 'Type') ?: '-',
            $this->specialFieldValue($row, 'Place') ?: '-',
            str_starts_with($type, 'EA103') || str_contains($type, 'Presentations') ? ($this->specialPeopleValue($row) ?: '-') : '-',
            str_starts_with($type, 'EA118') || str_contains($type, 'Award') ? ($this->specialPeopleValue($row) ?: '-') : '-',
            $this->specialFieldValue($row, 'Book') ?: '-',
            $this->specialFieldValue($row, 'Publisher') ?: '-',
            $this->specialFieldValue($row, 'Pages') ?: '-',
            $this->specialFieldValue($row, 'ISBN') ?: '-',
            $createdLabel,
        ];
    }

    private function specialFieldValue($row, string $label): string
    {
        $field = collect(data_get($row, 'special.fields', []))->firstWhere('label', $label);

        return trim((string) ($field['value'] ?? ''));
    }

    private function specialPeopleValue($row): string
    {
        return collect(data_get($row, 'special.people', []))->filter()->implode(', ');
    }

    private function categoryExcelRow($row, array $columns, int $index): array
    {
        $values = ['#' => $index];
        foreach ($columns as $key => $label) {
            $values[$key] = $this->categoryExcelValue($row, (string) $key) ?: '-';
        }

        return $values;
    }

    private function categoryExcelValue($row, string $key): string
    {
        if ($key === 'source_badge') {
            return trim((string) ($row->source_badge ?? '')) !== '' ? (string) $row->source_badge : 'Project';
        }

        if ($key === 'authors_text') {
            if (!empty($row->authors_text)) {
                return (string) $row->authors_text;
            }

            if (!empty($row->authors_by_role) && is_iterable($row->authors_by_role)) {
                $roleLabels = [
                    'first' => 'First-author',
                    'co' => 'Co-author',
                    'corresponding' => 'Corresponding-author',
                ];

                return collect($row->authors_by_role)
                    ->flatMap(function ($names, $role) use ($roleLabels) {
                        $roleLabel = $roleLabels[$role] ?? ucfirst((string) $role);

                        return collect($names)
                            ->filter()
                            ->map(fn ($name) => "{$name} ({$roleLabel})");
                    })
                    ->values()
                    ->implode(', ');
            }

            if (!empty($row->authors) && is_iterable($row->authors)) {
                return collect($row->authors)->filter()->implode(', ');
            }
        }

        return trim((string) data_get($row, $key, ''));
    }

    private function categoryExcelMetaRows(string $title, Collection $rows, Request $request): array
    {
        $generatedAt = now()->setTimezone('Asia/Bangkok');
        $generatedLabel = $generatedAt->locale('th')->translatedFormat('j F') . ' ' . ($generatedAt->year + 543) . $generatedAt->translatedFormat(' เวลา H:i');
        $filters = $this->buildCategoryExportFilters($request);

        return [
            [$title],
            ['ออกรายงาน', $generatedLabel],
            ['เงื่อนไข', $filters->isNotEmpty() ? $filters->implode(' | ') : 'ทั้งหมด'],
            ['ทั้งหมด', number_format($rows->count()) . ' รายการ'],
        ];
    }

    private function exportMinDate(Collection $rows): ?string
    {
        $date = $rows
            ->pluck('created_at')
            ->filter()
            ->map(fn ($value) => Carbon::parse($value)->format('Y-m-d'))
            ->sort()
            ->first();

        return $date ?: null;
    }

    private function filterRowsByExportDateRange(Collection $rows, Request $request): Collection
    {
        if ($request->input('export_scope') !== 'range') {
            return $rows->values();
        }

        $start = $this->normalizeThaiDateInput($request->input('export_start_date'));
        $end = $this->normalizeThaiDateInput($request->input('export_end_date'));

        if ($start) {
            $startDate = Carbon::parse($start, 'Asia/Bangkok')->startOfDay();
            $rows = $rows->filter(fn ($row) => !empty($row->created_at) && Carbon::parse($row->created_at)->greaterThanOrEqualTo($startDate));
        }

        if ($end) {
            $endDate = Carbon::parse($end, 'Asia/Bangkok')->endOfDay();
            $rows = $rows->filter(fn ($row) => !empty($row->created_at) && Carbon::parse($row->created_at)->lessThanOrEqualTo($endDate));
        }

        return $rows->values();
    }

    private function exportDateRangeFilterLabel(Request $request): ?string
    {
        if ($request->input('export_scope') !== 'range') {
            return null;
        }

        $format = static function (?string $date): ?string {
            if (!$date) {
                return null;
            }
            $carbon = Carbon::parse($date);
            return $carbon->format('d/m/') . ($carbon->year + 543);
        };

        $start = $format($this->normalizeThaiDateInput($request->input('export_start_date')));
        $end = $format($this->normalizeThaiDateInput($request->input('export_end_date')));

        return 'วันที่ลงข้อมูล: ' . (($start ?: 'เริ่มต้น') . ' - ' . ($end ?: 'ปัจจุบัน'));
    }

    private function buildCategoryExportFilters(Request $request): Collection
    {
        return collect([
            $request->filled('search') ? 'ค้นหา: ' . trim((string) $request->input('search')) : null,
            $request->filled('author') ? 'Author: ' . trim((string) $request->input('author')) : null,
            $request->filled('presenter') ? 'ผู้นำเสนอ: ' . trim((string) $request->input('presenter')) : null,
            $request->filled('activity') ? 'กิจกรรม: ' . trim((string) $request->input('activity')) : null,
            $request->filled('year') ? 'ปี: ' . trim((string) $request->input('year')) : null,
            $this->exportDateRangeFilterLabel($request),
        ])->filter()->values();
    }

    private function summarizeCategoryRows(Collection $rows, string $kind): array
    {
        return match ($kind) {
            'presentations' => [
                'total_presentations' => $rows->count(),
                'unique_presenters' => $rows->pluck('presenter_name')->filter(fn ($name) => $name !== '-' && trim((string) $name) !== '')->unique()->count(),
                'top_presenter' => $this->topLabel($rows->pluck('presenter_name')->filter(fn ($name) => $name !== '-' && trim((string) $name) !== '')->countBy()),
                'top_conference' => $this->topLabel($rows->pluck('conference_name')->filter(fn ($name) => $name !== '-' && trim((string) $name) !== '')->countBy()),
            ],
            'textbooks' => [
                'total_textbooks' => $rows->count(),
                'unique_authors' => $rows->flatMap(fn ($row) => collect($row->authors ?? []))->filter()->unique()->count(),
                'top_publisher' => $this->topLabel($rows->pluck('publisher_name')->filter(fn ($name) => $name !== '-' && trim((string) $name) !== '')->countBy()),
            ],
            default => [
                'total_papers' => $rows->count(),
                'unique_authors' => $rows->flatMap(fn ($paper) => collect($paper->authors ?? []))->filter()->unique()->count(),
                'top_journal' => $this->topLabel($rows->pluck('journal')->filter(fn ($name) => $name !== '-' && trim((string) $name) !== '')->countBy()),
                'top_quartile' => $this->topLabel($rows->pluck('quartile')->filter(fn ($name) => $name !== '-' && trim((string) $name) !== '')->countBy()),
            ],
        };
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
                $conferences = $output->conferences->isNotEmpty()
                    ? $output->conferences
                    : collect([(object) [
                        'id' => 'missing-' . $output->id,
                        'name' => '-',
                        'presentation_type' => '-',
                        'location' => '-',
                        'created_at' => null,
                    ]]);

                return $conferences->map(function ($conference) use ($output, $yearBe) {
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
                        'presenter_name' => method_exists($conference, 'presenterDisplay') ? ($conference->presenterDisplay() ?: '-') : '-',
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
                        'source_badge' => 'Mission',
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
                $conferences = $output->conferences->isNotEmpty()
                    ? $output->conferences
                    : collect([(object) [
                        'id' => 'external-missing-' . $output->id,
                        'name' => '-',
                        'presentation_type' => '-',
                        'location' => '-',
                        'created_at' => null,
                    ]]);

                return $conferences->map(function ($conference) use ($output, $yearBe) {
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
                        'presenter_name' => method_exists($conference, 'presenterDisplay') ? ($conference->presenterDisplay() ?: '-') : '-',
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
            'Moderator',
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
        $query = BackOfficeTaskOutput::query()->with([
            'task:id,title,created_by',
            'contributorLinks.user',
            'authorLinks.user',
            'awardRecipientLinks.user',
            'bookAuthorLinks.user',
            'conferenceLinks.presenters.user',
        ]);
        $user = auth()->user();
        $fullName = trim(($user->prefix ?? '') . ' ' . $user->name);
        $canViewAll = $user->isAdminLike();

        if (!$canViewAll) {
            $query->where(function (Builder $scope) use ($user, $fullName) {
                $scope->where('owner_user_id', $user->id)
                    ->orWhere('owner_name', $fullName)
                    ->orWhereHas('contributorLinks', function (Builder $q) use ($user, $fullName) {
                        $q->where('user_id', $user->id)
                            ->orWhere('name', $fullName);
                    })
                    ->orWhereHas('task', function (Builder $q) use ($user, $fullName) {
                        $q->where('created_by', $user->id)
                            ->orWhereHas('entries', function (Builder $subQ) use ($fullName) {
                                $subQ->where('person_name', $fullName);
                            });
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
                    'source_badge' => 'Mission',
                    'title' => $output->title ?: '-',
                    'type' => $output->type ?: '-',
                    'year_be' => $yearBe,
                    'journal' => $output->journal ?: '-',
                    'quartile' => $output->quartile ?: '-',
                    'impact_factor' => $output->impact_factor,
                    'detail' => $output->project_output ?: null,
                    'project_label' => $output->task?->title ?: 'Mission',
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
                    'project_label' => $output->task?->title ?: 'Mission',
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
            ->with([
                'entries:id,task_id,person_name',
                'outputs:id,task_id,type,owner_user_id,owner_name',
                'outputs.contributorLinks.user',
                'outputs.authorLinks.user',
                'outputs.awardRecipientLinks.user',
                'outputs.bookAuthorLinks.user',
                'outputs.conferenceLinks.presenters.user',
            ])
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
                        default => 'other_outputs',
                    };

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
            $stats['total_outputs'] = $stats['research_outputs'] + $stats['presentation_outputs'] + $stats['textbook_outputs'] + $stats['award_outputs'] + $stats['other_outputs'];

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
        $matchedUserIds = $output->resolvedContributors()
            ->pluck('user_id')
            ->filter()
            ->concat($this->extractBackOfficeOutputContributorNames($output)
            ->flatMap(fn ($name) => $this->resolveMatchedUserIds($name, $nameUserIdsMap))
            )
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
        $ownerNames = method_exists($output, 'resolvedContributors')
            ? $output->resolvedContributors()->pluck('name')->map(fn ($name) => trim((string) $name))
            : collect([$output->owner_name ?? null])->map(fn ($name) => trim((string) $name));

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

        return $ownerNames
            ->concat($authorNames)
            ->concat($bookAuthorNames)
            ->concat($presenterNames)
            ->filter()
            ->unique()
            ->values();
    }

    private function buildPersonalOutputPayload(User $user): array
    {
        $projectRows = $this->buildPersonalProjectRows($user);
        $missionRows = $this->buildPersonalMissionRows($user);
        $researchRows = $this->buildPersonalResearchRows($user);
        $presentationRows = $this->buildPersonalPresentationRows($user);
        $textbookRows = $this->buildPersonalTextbookRows($user);
        $awardRows = $this->buildPersonalAwardRows($user);
        $otherRows = $this->buildPersonalOtherRows($user);
        $projectStatRows = $projectRows->where('source_label', 'Project')->values();

        $stats = [
            'pi_projects' => (int) $projectStatRows->filter(fn ($row) => in_array('PI', $row->roles, true))->count(),
            'co_pi_projects' => (int) $projectStatRows->filter(fn ($row) => in_array('Co-PI', $row->roles, true))->count(),
            'co_research_projects' => (int) $projectStatRows->filter(fn ($row) => in_array('Co-Researcher', $row->roles, true))->count(),
            'research_outputs' => (int) $researchRows->count(),
            'presentation_outputs' => (int) $presentationRows->count(),
            'textbook_outputs' => (int) $textbookRows->count(),
            'award_outputs' => (int) $awardRows->count(),
            'other_outputs' => (int) $otherRows->count(),
            'missions' => (int) $missionRows->count(),
            'mission_entries' => (int) $missionRows->sum('entry_count'),
            'mission_hours' => (float) $missionRows->sum('hours'),
            'mission_fte' => (float) $missionRows->sum('fte_value'),
        ];
        $stats['total_outputs'] = $stats['research_outputs'] + $stats['presentation_outputs'] + $stats['textbook_outputs'] + $stats['award_outputs'] + $stats['other_outputs'];
        $stats['total_projects'] = (int) $projectRows->count();

        return [
            'subject' => $user,
            'subjectRouteKey' => $this->personalOutputRouteKey($user),
            'stats' => $stats,
            'projectRows' => $projectRows,
            'missionRows' => $missionRows,
            'researchRows' => $researchRows,
            'presentationRows' => $presentationRows,
            'textbookRows' => $textbookRows,
            'awardRows' => $awardRows,
            'otherRows' => $otherRows,
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

        return $projectRows
            ->sortByDesc(fn ($row) => $row->sort_year)
            ->values();
    }

    private function buildPersonalMissionRows(User $user): Collection
    {
        $entries = BackOfficeEntry::query()
            ->with('task:id,backoffice_id,title')
            ->get()
            ->filter(fn (BackOfficeEntry $entry) => $this->matchesUserThaiName($entry->person_name ?? null, $user))
            ->map(function (BackOfficeEntry $entry) {
                $yearBe = $this->extractBackOfficeEntryFiscalYearBe($entry);

                return (object) [
                    'task_id' => (int) $entry->task_id,
                    'backoffice_id' => $entry->task?->backoffice_id,
                    'mission_title' => trim((string) ($entry->task?->title ?? '')) ?: 'Mission',
                    'category' => trim((string) ($entry->category ?? '')) ?: '-',
                    'job_name' => trim((string) ($entry->job_name ?? '')) ?: '-',
                    'job_detail' => trim((string) ($entry->job_detail ?? '')) ?: null,
                    'start_date' => trim((string) ($entry->start_date ?? '')) ?: null,
                    'end_date' => trim((string) ($entry->end_date ?? '')) ?: null,
                    'start_date_be' => $this->formatBackOfficeDateBe($entry->start_date),
                    'end_date_be' => $this->formatBackOfficeDateBe($entry->end_date),
                    'hours' => (float) ($entry->hours ?? 0),
                    'fte_value' => (float) ($entry->fte_value ?? 0),
                    'year_be' => $yearBe,
                    'link' => $entry->task ? route('back_office.show', $entry->task) : null,
                    'sort_year' => $yearBe ?? 0,
                ];
            });

        return $entries
            ->groupBy(fn ($entry) => ($entry->task_id ?: 0) . '|' . ($entry->year_be ?: 'unknown'))
            ->map(function (Collection $group) {
                $first = $group->first();
                $jobNames = $group->pluck('job_name')->filter(fn ($value) => trim((string) $value) !== '' && $value !== '-')->unique()->values();
                $categories = $group->pluck('category')->filter(fn ($value) => trim((string) $value) !== '' && $value !== '-')->unique()->values();

                return (object) [
                    'task_id' => $first->task_id,
                    'backoffice_id' => $first->backoffice_id,
                    'mission_title' => $first->mission_title,
                    'year_be' => $first->year_be,
                    'entry_count' => (int) $group->count(),
                    'hours' => (float) $group->sum('hours'),
                    'fte_value' => (float) $group->sum('fte_value'),
                    'job_summary' => $jobNames->take(3)->implode(', '),
                    'category_summary' => $categories->take(3)->implode(', '),
                    'link' => $first->link,
                    'detail_rows' => $group
                        ->sortBy(fn ($entry) => ($entry->start_date ?? '') . '|' . ($entry->job_name ?? ''))
                        ->values(),
                    'sort_year' => $first->sort_year,
                ];
            })
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
            ->with(['task:id,backoffice_id,title', 'task.entries:id,task_id,person_name', 'contributorLinks.user'])
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
                    'source_label' => 'Mission',
                    'reference_label' => trim((string) ($output->task?->title ?? '')) ?: 'Mission',
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
            ->with(['task:id,backoffice_id,title', 'task.entries:id,task_id,person_name', 'contributorLinks.user'])
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
                            'source_label' => 'Mission',
                            'reference_label' => trim((string) ($output->task?->title ?? '')) ?: 'Mission',
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
            ->with(['task:id,backoffice_id,title', 'task.entries:id,task_id,person_name', 'contributorLinks.user'])
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
                    'source_label' => 'Mission',
                    'reference_label' => trim((string) ($output->task?->title ?? '')) ?: 'Mission',
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
            ->with(['task:id,backoffice_id,title', 'task.entries:id,task_id,person_name', 'contributorLinks.user'])
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
                    'source_label' => 'Mission',
                    'reference_label' => trim((string) ($output->task?->title ?? '')) ?: 'Mission',
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

        $backOfficeRows = BackOfficeTaskOutput::query()
            ->with(['task:id,backoffice_id,title', 'task.entries:id,task_id,person_name', 'contributorLinks.user', 'authorLinks.user'])
            ->get()
            ->filter(function (BackOfficeTaskOutput $output) use ($user, $excludedTypeCodes) {
                $typeCode = $this->extractOutputTypeCode($output->type);
                if ($typeCode === null || in_array($typeCode, $excludedTypeCodes, true)) {
                    return false;
                }

                $matchedContributor = $this->extractBackOfficeOutputContributorNames($output)
                    ->contains(fn ($name) => $this->matchesUserOutputContributor($name, $user));

                $matchedTaskPerson = collect($output->task?->entries ?? [])
                    ->contains(fn ($entry) => $this->matchesUserThaiName($entry->person_name ?? null, $user));

                return $matchedContributor || $matchedTaskPerson;
            })
            ->map(function (BackOfficeTaskOutput $output) {
                $typeCode = $this->extractOutputTypeCode($output->type) ?? 'OTHER';
                $contributors = collect($output->resolvedAuthors())
                    ->map(function ($author) {
                        if (is_array($author)) {
                            return trim((string) ($author['name'] ?? ''));
                        }

                        return trim((string) $author);
                    })
                    ->filter()
                    ->values();

                if ($contributors->isEmpty()) {
                    $contributors = $output->resolvedContributors()
                        ->pluck('name')
                        ->map(fn ($name) => trim((string) $name))
                        ->filter()
                        ->values();
                }

                return (object) [
                    'title' => trim((string) ($output->title ?? '')) ?: '-',
                    'type_code' => $typeCode,
                    'type_label' => $this->resolveOutputTypeLabel($typeCode),
                    'year_be' => $this->normalizeYearFilter($output->year),
                    'contributors' => $contributors->implode(', '),
                    'source_label' => 'Mission',
                    'reference_label' => trim((string) ($output->task?->title ?? '')) ?: 'Mission',
                    'link' => $this->extractFirstUrlFromText($output->project_output)
                        ?: ($output->task ? route('back_office.show', $output->task) : null),
                    'sort_year' => $this->normalizeYearFilter($output->year) ?? 0,
                ];
            });

        return $backOfficeRows
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
            'EA117' => 'Memorandum of Understanding (MOU)',
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

    private function extractBackOfficeEntryFiscalYearBe(BackOfficeEntry $entry): ?int
    {
        $date = $this->parseBackOfficeDate($entry->start_date)
            ?: $this->parseBackOfficeDate($entry->end_date)
            ?: $this->parseBackOfficeDate($entry->task?->start_date)
            ?: $this->parseBackOfficeDate($entry->task?->end_date);

        if (! $date) {
            return null;
        }

        $year = (int) $date->format('Y');
        if ((int) $date->format('n') >= 10) {
            $year++;
        }

        return $year + 543;
    }

    private function parseBackOfficeDate(?string $value): ?Carbon
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        foreach (['d-m-Y', 'Y-m-d', 'd/m/Y', 'Y/m/d'] as $format) {
            try {
                $date = Carbon::createFromFormat($format, $value);
                if ($date !== false) {
                    return $date->startOfDay();
                }
            } catch (\Throwable $exception) {
                // Try the next supported date format.
            }
        }

        return null;
    }

    private function formatBackOfficeDateBe(?string $value): ?string
    {
        $date = $this->parseBackOfficeDate($value);
        if (! $date) {
            return null;
        }

        return $date->format('d-m-') . ($date->year + 543);
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
            'other_outputs' => 0,
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
        $value = preg_replace('/[\s.\x{200B}\x{200C}\x{200D}\x{FEFF}]+/u', '', $value) ?? '';
        $titlePattern = '/^(?:ศาสตราจารย์|รองศาสตราจารย์|ผู้ช่วยศาสตราจารย์|อาจารย์|พันเอกหญิง|นายแพทย์|แพทย์หญิง|ทันตแพทย์หญิง|ทันตแพทย์|ดร|รศ|ผศ|ศ|อ|นพ|พญ|ทพญ|ทพ|ภกญ|ภก|คุณ|นาย|นางสาว|นาง|professor|associateprofessor|assistantprofessor|assocprof|asstprof|prof|doctor|dr|mr|mrs|ms)+/u';
        $value = preg_replace($titlePattern, '', $value) ?? $value;

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

        return asset('images/default-avatar.png');
    }

    private function buildThemeGapRewritePrompt(array $facts, string $fallback): string
    {
        $factsJson = json_encode($facts, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $fallbackJson = json_encode(['paragraph' => $fallback], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return <<<PROMPT
คุณคือผู้ช่วยเรียบเรียงข้อความ Theme output gap analysis สำหรับ dashboard policy impact

งานของคุณ:
- เรียบเรียงภาษาไทยให้ลื่นและเป็นธรรมชาติขึ้น โดยยึด facts และ fallback เท่านั้น
- ห้ามแต่งตัวเลข จำนวนโครงการ ชื่อหมวด หรือชื่อ output ใหม่
- ห้ามใช้ bullet list หรือหัวข้อย่อย
- เขียนเป็น paragraph ต่อเนื่อง แต่แยกแต่ละหมวดด้วยบรรทัดว่างหนึ่งบรรทัด
- แต่ละหมวดต้องบอก 3 เรื่องใน paragraph เดียว: จุดเด่นคือ output ใด, ขาด/อ่อน output ใด, ส่งผลอย่างไร
- คงคำสำคัญ Research Paper and Journal Article, Reports and White Papers, Policy Recommendations, Change in Practices / Procedures / Policy ตาม facts
- ใช้น้ำเสียงเชิงวิเคราะห์แบบระมัดระวัง เช่น "อาจสะท้อนว่า", "อาจบ่งชี้ว่า", "ยังมีข้อจำกัดบางประการ", "ยังอยู่ในระยะพัฒนา"
- ถ้า main_weakness_missing เท่ากับ project_count ให้เขียนว่า "ยังไม่พบ output ด้าน ... ในทั้ง ... โครงการ"
- ถ้า main_weakness_with มากกว่า 0 แต่น้อยกว่า project_count ให้เขียนว่า "ยังพบในเพียง ... จาก ... โครงการ"
- ภาพรวมควรเชื่อมกับ knowledge translation pathway, implementation และ policy adoption
- ปิดท้ายด้วย paragraph ภาพรวม 1 ย่อหน้า ถ้า fallback มีภาพรวม
- ทุก paragraph ควรจบด้วยจุด "."
- ถ้าข้อมูลไม่พอ ให้เขียนอย่างระมัดระวัง ไม่สรุปเกินข้อมูล

ตอบเป็น JSON object เท่านั้น ตาม schema นี้:
{
  "paragraph": "ข้อความทั้งหมด พร้อม \\n\\n เพื่อเว้นบรรทัดระหว่างย่อหน้า"
}

facts:
{$factsJson}

fallback:
{$fallbackJson}
PROMPT;
    }

    private function decodeGeminiInsightJson(string $text): array
    {
        $text = trim($text);
        if ($text === '') {
            return [];
        }

        $text = preg_replace('/^```(?:json)?\s*/u', '', $text) ?? $text;
        $text = preg_replace('/\s*```$/u', '', $text) ?? $text;
        $decoded = json_decode($text, true);

        if (is_array($decoded)) {
            return $decoded;
        }

        if (preg_match('/\{.*\}/su', $text, $matches)) {
            $decoded = json_decode($matches[0], true);
            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    private function callOllamaInsight(string $prompt): array
    {
        $model = trim((string) config('services.ollama.model', 'qwen2.5:7b')) ?: 'qwen2.5:7b';
        $baseUrl = rtrim((string) config('services.ollama.base_url', 'http://127.0.0.1:11434'), '/');
        $timeout = max(30, (int) config('services.ollama.timeout', 180));

        try {
            $response = Http::acceptJson()
                ->timeout($timeout)
                ->post($baseUrl . '/api/generate', [
                    'model' => $model,
                    'prompt' => $prompt,
                    'stream' => false,
                    'format' => 'json',
                    'options' => [
                        'temperature' => 0.1,
                    ],
                ]);
        } catch (\Throwable $e) {
            return ['ok' => false, 'reason' => 'ollama_exception'];
        }

        if (! $response->ok()) {
            return ['ok' => false, 'reason' => 'ollama_http_' . $response->status()];
        }

        $text = trim((string) ($response->json('response') ?? ''));
        if ($text === '') {
            return ['ok' => false, 'reason' => 'ollama_empty_response'];
        }

        return ['ok' => true, 'text' => $text, 'model' => $model];
    }

    private function callGeminiInsightGateway(string $model, string $prompt, int $timeout): array
    {
        $models = collect([$model, 'gemini-flash-latest', 'gemini-2.5-flash', 'gemini-2.5-pro'])
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->values();
        $payloadModes = ['minimal', 'inline'];
        $errors = [];

        foreach ($models as $candidateModel) {
            foreach ($payloadModes as $payloadMode) {
                try {
                    $response = Http::timeout(max(25, $timeout))
                        ->withHeaders(['Content-Type' => 'application/json'])
                        ->acceptJson()
                        ->post(self::GEMINI_REMOTE_GATEWAY_URL, [
                            'model' => $candidateModel,
                            'prompt' => trim($prompt),
                            'payload' => $payloadMode,
                        ]);
                } catch (\Throwable $e) {
                    $errors[] = $candidateModel . ':' . $payloadMode . ':gateway_exception';
                    continue;
                }

                if (! $response->ok()) {
                    $errors[] = $candidateModel . ':' . $payloadMode . ':gateway_http_' . $response->status();
                    continue;
                }

                $json = $response->json();
                if (($json['ok'] ?? false) && trim((string) ($json['text'] ?? '')) !== '') {
                    return ['ok' => true, 'text' => trim((string) $json['text']), 'model' => $candidateModel];
                }
                if (trim((string) ($json['reply'] ?? '')) !== '') {
                    return ['ok' => true, 'text' => trim((string) $json['reply']), 'model' => $candidateModel];
                }
                if (is_array($json['candidates'][0]['content']['parts'] ?? null)) {
                    $text = collect($json['candidates'][0]['content']['parts'])
                        ->map(fn ($part) => $part['text'] ?? '')
                        ->filter()
                        ->implode("\n");

                    if (trim($text) !== '') {
                        return ['ok' => true, 'text' => trim($text), 'model' => $candidateModel];
                    }
                }

                $guess = trim((string) (($json['message'] ?? '') ?: ($json['data'] ?? '')));
                if ($guess !== '') {
                    return ['ok' => true, 'text' => $guess, 'model' => $candidateModel];
                }

                $errors[] = $candidateModel . ':' . $payloadMode . ':gateway_empty_response';
            }
        }

        return ['ok' => false, 'reason' => implode(' | ', $errors) ?: 'gateway_unavailable'];
    }

    private function isValidThemeGapParagraph(string $paragraph): bool
    {
        $length = mb_strlen(trim($paragraph), 'UTF-8');
        if ($length < 40 || $length > 6000) {
            return false;
        }

        return ! Str::contains($paragraph, ['<script', '</script']);
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
