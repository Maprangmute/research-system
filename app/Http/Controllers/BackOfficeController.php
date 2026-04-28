<?php

namespace App\Http\Controllers;

use App\Models\BackOfficeEntry;
use App\Models\BackOfficeOutputAuthor;
use App\Models\BackOfficeOutputAwardRecipient;
use App\Models\BackOfficeOutputBookAuthor;
use App\Models\BackOfficeOutputConference;
use App\Models\BackOfficeOutputConferencePresenter;
use App\Models\BackOfficeTask;
use App\Models\BackOfficeTaskOutput;
use App\Models\User;
use App\Support\AuditLogger;
use Carbon\Carbon;
use PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class BackOfficeController extends Controller
{
    private const BACK_OFFICE_UNIT_NAME = 'หน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ';

    private const BACK_OFFICE_TASK_TITLE_ORDER = [
        'ภาระงานด้านการศึกษาและการจัดการเรียนการสอนด้านการวิจัย',
        'ภาระงานด้านการให้คำปรึกษางานวิจัย ด้าน Health Technology Assessment (HTA)',
        'ภาระงานด้านการให้คำปรึกษางานวิจัย ด้าน Systematic Review',
        'ภาระงานด้านเทคโนโลยีสารสนเทศและข้อมูล (IT & Information)',
        'ภาระงานด้านการสื่อสารและประชาสัมพันธ์ (Corporate Communication)',
        'ภาระงานด้านการบริหารงานทั่วไป (Administration)',
        'ภาระงานด้านเลขานุการและผู้ช่วยผู้บริหาร (Executive Assistant / Secretary)',
        'ภาระงานด้านทรัพยากรบุคคล',
    ];

    private function backOfficeOutputRelations(): array
    {
        return [
            'outputs.authorLinks.user',
            'outputs.awardRecipientLinks.user',
            'outputs.bookAuthorLinks.user',
            'outputs.conferenceLinks.user',
            'outputs.conferenceLinks.presenters.user',
        ];
    }

    private function backOfficeCategoryOptions(): array
    {
        return [
            'งานบริการ สนับสนุน และประสานงานภายในหน่วย',
            'งานด้านติดต่อประสานงาน',
            'งานด้านการลงตารางนัดหมายประชุม',
            'งานจัดประชุมที่ไม่เกี่ยวข้องกับงานวิจัย',
            'งานบริการส่งสินค้าและเอกสาร',
            'งานบริการด้านเอกสาร',
            'งานบริการด้านทรัพยากรบุคคล',
            'งานบริการด้านการให้คำปรึกษางานวิจัย',
            'งานออกแบบหลักสูตรและการเรียนการสอน',
            'งานจัดการเรียนการสอน',
            'งานพัฒนาศักยภาพและให้คำปรึกษานักศึกษา (Mentorship)',
            'งานด้านการสื่อสาร และประชาสัมพันธ์',
            'งานด้านออกแบบ และผลิตสื่อ',
            'งานจัดงานหรือเข้าร่วมงานนิทรรศการ',
            'งานด้านการพัฒนาเทคโนโลยีและสารสนเทศ',
        ];
    }

    private function ensureBackOfficeAdmin(): void
    {
        abort_unless(Auth::user()?->hasRole(User::ROLE_ADMIN), 403);
    }

    private function deriveBackOfficeTaskDateRange(array $entries): array
    {
        if (empty($entries)) {
            return [null, null];
        }

        $startDates = [];
        $endDates = [];

        foreach ($entries as $entry) {
            $startDates[] = Carbon::createFromFormat('d-m-Y', $entry['start_date'])->startOfDay();
            $endDates[] = Carbon::createFromFormat('d-m-Y', $entry['end_date'])->startOfDay();
        }

        usort($startDates, fn (Carbon $a, Carbon $b) => $a->getTimestamp() <=> $b->getTimestamp());
        usort($endDates, fn (Carbon $a, Carbon $b) => $b->getTimestamp() <=> $a->getTimestamp());

        return [
            $startDates[0]->format('d-m-Y'),
            $endDates[0]->format('d-m-Y'),
        ];
    }

    /**
     * @return Collection<string, array<int, int>>
     */
    private function buildBackOfficeUserLookup(): Collection
    {
        $lookup = collect();

        User::query()
            ->select(['id', 'prefix', 'name', 'prefix_en', 'name_en'])
            ->orderBy('id')
            ->chunkById(500, function ($users) use (&$lookup): void {
                foreach ($users as $user) {
                    $candidates = array_filter([
                        $this->normalizeBackOfficeNameKey(trim(((string) $user->prefix) . ' ' . ((string) $user->name))),
                        $this->normalizeBackOfficeNameKey((string) $user->name),
                        $this->normalizeBackOfficeNameKey(trim(((string) $user->prefix_en) . ' ' . ((string) $user->name_en))),
                        $this->normalizeBackOfficeNameKey((string) $user->name_en),
                    ]);

                    foreach (array_unique($candidates) as $candidate) {
                        $existing = $lookup->get($candidate, []);
                        $existing[] = (int) $user->id;
                        $lookup->put($candidate, array_values(array_unique($existing)));
                    }
                }
            });

        return $lookup;
    }

    private function normalizeBackOfficeNameKey(string $value): string
    {
        $value = preg_replace('/\s+/u', ' ', trim($value)) ?? '';

        return $value !== '' ? mb_strtolower($value) : '';
    }

    /**
     * @param  Collection<string, array<int, int>>|null  $lookup
     */
    private function resolveBackOfficeUserIdByStoredName(?Collection $lookup, string $name): ?int
    {
        $normalized = $this->normalizeBackOfficeNameKey($name);
        if ($normalized === '') {
            return null;
        }

        $lookup ??= $this->buildBackOfficeUserLookup();
        $matches = $lookup->get($normalized, []);

        return count($matches) === 1 ? (int) $matches[0] : null;
    }

    private function resolveBackOfficeUser(?int $userId, ?string $name = null, ?Collection $lookup = null): ?User
    {
        if ($userId && $userId > 0) {
            $user = User::query()->find($userId);
            if ($user) {
                return $user;
            }
        }

        $resolvedUserId = $this->resolveBackOfficeUserIdByStoredName($lookup, (string) $name);

        return $resolvedUserId ? User::query()->find($resolvedUserId) : null;
    }

    private function backOfficeInternalResearchName(?User $user, ?string $fallback = null): string
    {
        $fallback = trim((string) $fallback);
        if (! $user) {
            return $fallback;
        }

        $value = trim((string) $user->research_display_name);

        return $value !== '' ? $value : $fallback;
    }

    private function backOfficeInternalFullName(?User $user, ?string $fallback = null): string
    {
        $fallback = trim((string) $fallback);
        if (! $user) {
            return $fallback;
        }

        $value = trim((string) $user->full_name);

        return $value !== '' ? $value : $fallback;
    }

    private function normalizeFlexibleBackOfficeDate(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        foreach (['d-m-Y', 'Y-m-d'] as $format) {
            try {
                return Carbon::createFromFormat($format, $value)->format('d-m-Y');
            } catch (\Exception $e) {
            }
        }

        try {
            return Carbon::parse($value)->format('d-m-Y');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function normalizeLeaderList(array $leaders): array
    {
        return collect($leaders)
            ->map(fn ($value) => trim((string) $value))
            ->filter(fn ($value) => $value !== '')
            ->values()
            ->all();
    }

    private function normalizeBackOfficeAuthors(array $items, ?Collection $lookup = null): array
    {
        return collect($items)
            ->map(function ($item) use ($lookup) {
                $personType = trim((string) ($item['person_type'] ?? ''));
                $userId = (($item['user_id'] ?? null) !== null && (int) $item['user_id'] > 0)
                    ? (int) $item['user_id']
                    : null;
                $name = trim((string) ($item['name'] ?? ''));
                $user = $this->resolveBackOfficeUser($userId, $name, $lookup);

                if ($personType === 'internal' && $user) {
                    $userId = (int) $user->id;
                    $name = $this->backOfficeInternalResearchName($user, $name);
                } elseif ($personType !== 'internal') {
                    $userId = null;
                } elseif ($user) {
                    $userId = (int) $user->id;
                }

                return [
                    'user_id' => $userId,
                    'name' => $name,
                    'role' => trim((string) ($item['role'] ?? '')),
                    'person_type' => $personType,
                ];
            })
            ->filter(fn ($item) => $item['name'] !== '' || $item['role'] !== '' || $item['person_type'] !== '' || !empty($item['user_id']))
            ->values()
            ->all();
    }

    private function normalizeBackOfficeAwardRecipients(array $items, ?Collection $lookup = null): array
    {
        return collect($items)
            ->map(function ($item) use ($lookup) {
                $personType = trim((string) ($item['person_type'] ?? ''));
                $userId = (($item['user_id'] ?? null) !== null && (int) $item['user_id'] > 0)
                    ? (int) $item['user_id']
                    : null;
                $name = trim((string) ($item['name'] ?? ''));
                $user = $this->resolveBackOfficeUser($userId, $name, $lookup);

                if ($personType === 'unit') {
                    $userId = null;
                    $name = self::BACK_OFFICE_UNIT_NAME;
                } elseif ($personType === 'internal' && $user) {
                    $userId = (int) $user->id;
                    $name = $this->backOfficeInternalFullName($user, $name);
                } elseif ($personType !== 'internal') {
                    $userId = null;
                } elseif ($user) {
                    $userId = (int) $user->id;
                }

                return [
                    'user_id' => $userId,
                    'name' => $name,
                    'person_type' => $personType,
                ];
            })
            ->filter(fn ($item) => $item['name'] !== '' || $item['person_type'] !== '' || !empty($item['user_id']))
            ->values()
            ->all();
    }

    private function mapAwardRecipientsToBackOfficeAuthors(array $items, ?Collection $lookup = null): array
    {
        return collect($this->normalizeBackOfficeAwardRecipients($items, $lookup))
            ->map(function ($item) {
                return [
                    'user_id' => $item['user_id'] ?? null,
                    'name' => $item['name'],
                    'role' => 'awardee',
                    'person_type' => $item['person_type'],
                ];
            })
            ->values()
            ->all();
    }

    private function normalizeBackOfficeBookAuthors(array $items, ?Collection $lookup = null): array
    {
        return collect($items)
            ->map(function ($item) use ($lookup) {
                $personType = trim((string) ($item['person_type'] ?? ''));
                $userId = (($item['user_id'] ?? null) !== null && (int) $item['user_id'] > 0)
                    ? (int) $item['user_id']
                    : null;
                $name = trim((string) ($item['name'] ?? ''));
                $user = $this->resolveBackOfficeUser($userId, $name, $lookup);

                if ($personType === 'internal' && $user) {
                    $userId = (int) $user->id;
                    $name = $this->backOfficeInternalFullName($user, $name);
                } elseif ($personType !== 'internal') {
                    $userId = null;
                } elseif ($user) {
                    $userId = (int) $user->id;
                }

                return [
                    'user_id' => $userId,
                    'name' => $name,
                    'person_type' => $personType,
                ];
            })
            ->filter(fn ($item) => $item['name'] !== '' || $item['person_type'] !== '' || !empty($item['user_id']))
            ->values()
            ->all();
    }

    private function normalizeBackOfficeConferences(array $items, ?Collection $lookup = null): array
    {
        return collect($items)
            ->map(function ($item) use ($lookup) {
                $presenters = collect($item['presenters'] ?? [])
                    ->map(function ($presenter) use ($lookup) {
                        $personType = trim((string) ($presenter['person_type'] ?? ''));
                        $userId = (($presenter['user_id'] ?? null) !== null && (int) $presenter['user_id'] > 0)
                            ? (int) $presenter['user_id']
                            : null;
                        $name = trim((string) ($presenter['name'] ?? ''));
                        $user = $this->resolveBackOfficeUser($userId, $name, $lookup);

                        if ($personType === 'internal' && $user) {
                            $userId = (int) $user->id;
                            $name = $this->backOfficeInternalFullName($user, $name);
                        } elseif ($personType !== 'internal') {
                            $userId = null;
                        } elseif ($user) {
                            $userId = (int) $user->id;
                        }

                        return [
                            'user_id' => $userId,
                            'name' => $name,
                            'person_type' => $personType,
                        ];
                    })
                    ->filter(fn ($presenter) => $presenter['name'] !== '' || $presenter['person_type'] !== '' || !empty($presenter['user_id']))
                    ->values()
                    ->all();

                $fallbackName = trim((string) ($item['note'] ?? ''));
                $fallbackType = trim((string) ($item['presenter_person_type'] ?? ''));
                if (empty($presenters) && ($fallbackName !== '' || $fallbackType !== '')) {
                    $presenters[] = [
                        'name' => $fallbackName,
                        'person_type' => $fallbackType,
                    ];
                }

                return [
                    'name' => trim((string) ($item['name'] ?? '')),
                    'presentation_type' => trim((string) ($item['presentation_type'] ?? '')),
                    'location' => trim((string) ($item['location'] ?? '')),
                    'presenter_person_type' => trim((string) ($item['presenter_person_type'] ?? '')),
                    'note' => trim((string) ($item['note'] ?? '')),
                    'presenters' => $presenters,
                ];
            })
            ->filter(function ($item) {
                return $item['name'] !== ''
                    || $item['presentation_type'] !== ''
                    || $item['location'] !== ''
                    || $item['presenter_person_type'] !== ''
                    || $item['note'] !== ''
                    || !empty($item['presenters']);
            })
            ->values()
            ->all();
    }

    private function normalizeBackOfficeConferencePresenters(array $items, ?Collection $lookup = null): array
    {
        return collect($items)
            ->map(function ($item) use ($lookup) {
                $personType = trim((string) ($item['person_type'] ?? ''));
                $userId = (($item['user_id'] ?? null) !== null && (int) $item['user_id'] > 0)
                    ? (int) $item['user_id']
                    : null;
                $name = trim((string) ($item['name'] ?? ''));
                $user = $this->resolveBackOfficeUser($userId, $name, $lookup);

                if ($personType === 'internal' && $user) {
                    $userId = (int) $user->id;
                    $name = $this->backOfficeInternalFullName($user, $name);
                } elseif ($personType !== 'internal') {
                    $userId = null;
                } elseif ($user) {
                    $userId = (int) $user->id;
                }

                return [
                    'user_id' => $userId,
                    'name' => $name,
                    'person_type' => $personType,
                ];
            })
            ->filter(fn ($item) => $item['name'] !== '' || $item['person_type'] !== '' || !empty($item['user_id']))
            ->values()
            ->all();
    }

    private function normalizeDetailOutputAuthors(array $validated, string $type): array
    {
        $lookup = $this->buildBackOfficeUserLookup();

        if ($type === 'EA118 Award and Recognition') {
            $authors = !empty($validated['award_recipients'])
                ? $this->mapAwardRecipientsToBackOfficeAuthors((array) $validated['award_recipients'], $lookup)
                : [];

            if (empty($authors) && trim((string) ($validated['award_recipient_name'] ?? '')) !== '') {
                $personType = trim((string) ($validated['award_recipient_person_type'] ?? ''));
                $name = trim((string) $validated['award_recipient_name']);
                if ($personType === 'unit') {
                    $name = self::BACK_OFFICE_UNIT_NAME;
                }
                $user = $personType === 'internal'
                    ? $this->resolveBackOfficeUser(
                        (($validated['award_recipient_user_id'] ?? null) !== null && (int) $validated['award_recipient_user_id'] > 0)
                            ? (int) $validated['award_recipient_user_id']
                            : null,
                        $name,
                        $lookup
                    )
                    : null;
                $authors[] = [
                    'user_id' => $user?->id,
                    'name' => $personType === 'internal'
                        ? $this->backOfficeInternalFullName($user, $name)
                        : $name,
                    'role' => 'awardee',
                    'person_type' => $personType,
                ];
            }

            return $authors;
        }

        $authors = !empty($validated['authors'])
            ? $this->normalizeBackOfficeAuthors((array) $validated['authors'], $lookup)
            : [];

        if (empty($authors) && trim((string) ($validated['journal_author_name'] ?? '')) !== '') {
            $personType = trim((string) ($validated['journal_author_person_type'] ?? ''));
            $name = trim((string) $validated['journal_author_name']);
            $user = $personType === 'internal'
                ? $this->resolveBackOfficeUser(
                    (($validated['journal_author_user_id'] ?? null) !== null && (int) $validated['journal_author_user_id'] > 0)
                        ? (int) $validated['journal_author_user_id']
                        : null,
                    $name,
                    $lookup
                )
                : null;
            $authors[] = [
                'user_id' => $user?->id,
                'name' => $personType === 'internal'
                    ? $this->backOfficeInternalResearchName($user, $name)
                    : $name,
                'role' => trim((string) ($validated['journal_author_role'] ?? '')),
                'person_type' => $personType,
            ];
        }

        return $authors;
    }

    private function normalizeDetailOutputBookAuthors(array $validated): array
    {
        $lookup = $this->buildBackOfficeUserLookup();
        $bookAuthors = !empty($validated['book_authors'])
            ? $this->normalizeBackOfficeBookAuthors((array) $validated['book_authors'], $lookup)
            : [];

        if (empty($bookAuthors) && trim((string) ($validated['book_author_name'] ?? '')) !== '') {
            $personType = trim((string) ($validated['book_author_person_type'] ?? ''));
            $name = trim((string) $validated['book_author_name']);
            $user = $personType === 'internal'
                ? $this->resolveBackOfficeUser(
                    (($validated['book_author_user_id'] ?? null) !== null && (int) $validated['book_author_user_id'] > 0)
                        ? (int) $validated['book_author_user_id']
                        : null,
                    $name,
                    $lookup
                )
                : null;
            $bookAuthors[] = [
                'user_id' => $user?->id,
                'name' => $personType === 'internal'
                    ? $this->backOfficeInternalFullName($user, $name)
                    : $name,
                'person_type' => $personType,
            ];
        }

        return $bookAuthors;
    }

    private function normalizeDetailOutputConferences(array $validated): array
    {
        $lookup = $this->buildBackOfficeUserLookup();
        $conferencePresenters = !empty($validated['conference_presenters'])
            ? $this->normalizeBackOfficeConferencePresenters((array) $validated['conference_presenters'], $lookup)
            : [];

        $conferences = !empty($validated['conferences'])
            ? $this->normalizeBackOfficeConferences((array) $validated['conferences'], $lookup)
            : [];

        if (empty($conferences) && trim((string) ($validated['conference_name'] ?? '')) !== '') {
            $conferences[] = [
                'name' => trim((string) $validated['conference_name']),
                'presentation_type' => trim((string) ($validated['conference_presentation_type'] ?? '')),
                'location' => trim((string) ($validated['conference_location'] ?? '')),
                'presenter_person_type' => trim((string) ($validated['conference_presenter_person_type'] ?? '')),
                'note' => trim((string) ($validated['conference_note'] ?? '')),
                'presenters' => !empty($conferencePresenters)
                    ? $conferencePresenters
                    : (($validated['conference_note'] ?? '') !== '' || ($validated['conference_presenter_person_type'] ?? '') !== ''
                        ? [[
                            'name' => trim((string) ($validated['conference_note'] ?? '')),
                            'person_type' => trim((string) ($validated['conference_presenter_person_type'] ?? '')),
                        ]]
                        : []),
            ];
        }

        return $conferences;
    }

    private function buildBackOfficeComparableSnapshot(BackOfficeTask $task): array
    {
        $task->loadMissing(array_merge(['entries', 'outputs'], $this->backOfficeOutputRelations()));

        return [
            'title' => trim((string) $task->title),
            'start_date' => trim((string) $task->start_date),
            'end_date' => trim((string) $task->end_date),
            'description' => trim((string) ($task->description ?? '')),
            'purpose' => trim((string) ($task->purpose ?? '')),
            'leaders' => $this->normalizeLeaderList((array) ($task->leaders ?? [])),
            'entries' => $task->entries
                ->map(function ($entry) {
                    return [
                        'person_name' => trim((string) $entry->person_name),
                        'category' => trim((string) $entry->category),
                        'job_name' => trim((string) $entry->job_name),
                        'job_detail' => trim((string) $entry->job_detail),
                        'start_date' => trim((string) $entry->start_date),
                        'end_date' => trim((string) $entry->end_date),
                        'hours' => (string) ((float) $entry->hours),
                    ];
                })
                ->values()
                ->all(),
            'outputs' => $task->outputs
                ->map(function ($output) {
                    return [
                        'title' => trim((string) $output->title),
                        'type' => trim((string) ($output->type ?? '')),
                        'project_output' => trim((string) ($output->project_output ?? '')),
                        'year' => trim((string) ($output->year ?? '')),
                        'journal' => trim((string) ($output->journal ?? '')),
                        'quartile' => trim((string) ($output->quartile ?? '')),
                        'impact_factor' => $output->impact_factor === null ? '' : (string) ((float) $output->impact_factor),
                        'book_title' => trim((string) ($output->book_title ?? '')),
                        'publisher_name' => trim((string) ($output->publisher_name ?? '')),
                        'chapter_pages' => trim((string) ($output->chapter_pages ?? '')),
                        'book_authors' => $this->normalizeBackOfficeBookAuthors($output->resolvedBookAuthors()),
                        'authors' => $this->normalizeBackOfficeAuthors($output->resolvedAuthors()),
                        'conferences' => $this->normalizeBackOfficeConferences($output->resolvedConferences()),
                    ];
                })
                ->values()
                ->all(),
        ];
    }

    private function serializeBackOfficeOutputForForm(BackOfficeTaskOutput $output): array
    {
        $type = trim((string) ($output->type ?? ''));
        $authors = $this->normalizeBackOfficeAuthors($output->resolvedAuthors());
        $awardRecipients = $this->normalizeBackOfficeAwardRecipients($output->resolvedAwardRecipients());
        $bookAuthors = $this->normalizeBackOfficeBookAuthors($output->resolvedBookAuthors());
        $conferences = $this->normalizeBackOfficeConferences($output->resolvedConferences());

        return [
            'id' => $output->id,
            'title' => trim((string) $output->title),
            'type' => $type,
            'project_output' => trim((string) ($output->project_output ?? '')),
            'year' => trim((string) ($output->year ?? '')),
            'journal' => trim((string) ($output->journal ?? '')),
            'quartile' => trim((string) ($output->quartile ?? '')),
            'impact_factor' => $output->impact_factor === null ? '' : (string) ((float) $output->impact_factor),
            'book_title' => trim((string) ($output->book_title ?? '')),
            'publisher_name' => trim((string) ($output->publisher_name ?? '')),
            'chapter_pages' => trim((string) ($output->chapter_pages ?? '')),
            'authors' => str_starts_with($type, 'EA118') ? [] : $authors,
            'award_recipients' => str_starts_with($type, 'EA118') ? $awardRecipients : [],
            'book_authors' => $bookAuthors,
            'conferences' => $conferences,
        ];
    }

    private function buildBackOfficeComparableInput(array $data): array
    {
        return [
            'title' => trim((string) ($data['title'] ?? '')),
            'start_date' => trim((string) ($data['start_date'] ?? '')),
            'end_date' => trim((string) ($data['end_date'] ?? '')),
            'description' => trim((string) ($data['description'] ?? '')),
            'purpose' => trim((string) ($data['purpose'] ?? '')),
            'leaders' => $this->normalizeLeaderList((array) ($data['leaders'] ?? [])),
            'entries' => collect($data['entries'] ?? [])
                ->map(function ($entry) {
                    return [
                        'person_name' => trim((string) ($entry['person_name'] ?? '')),
                        'category' => trim((string) ($entry['category'] ?? '')),
                        'job_name' => trim((string) ($entry['job_name'] ?? '')),
                        'job_detail' => trim((string) ($entry['job_detail'] ?? '')),
                        'start_date' => trim((string) ($entry['start_date'] ?? '')),
                        'end_date' => trim((string) ($entry['end_date'] ?? '')),
                        'hours' => (string) ((float) ($entry['hours'] ?? 0)),
                    ];
                })
                ->values()
                ->all(),
            'outputs' => collect($data['outputs'] ?? [])
                ->map(function ($output) {
                    $type = trim((string) ($output['type'] ?? ''));
                    return [
                        'title' => trim((string) ($output['title'] ?? '')),
                        'type' => $type,
                        'project_output' => trim((string) ($output['project_output'] ?? '')),
                        'year' => trim((string) ($output['year'] ?? '')),
                        'journal' => trim((string) ($output['journal'] ?? '')),
                        'quartile' => trim((string) ($output['quartile'] ?? '')),
                        'impact_factor' => ($output['impact_factor'] ?? null) === null ? '' : (string) ((float) $output['impact_factor']),
                        'book_title' => trim((string) ($output['book_title'] ?? '')),
                        'publisher_name' => trim((string) ($output['publisher_name'] ?? '')),
                        'chapter_pages' => trim((string) ($output['chapter_pages'] ?? '')),
                        'book_authors' => $this->normalizeBackOfficeBookAuthors((array) ($output['book_authors'] ?? [])),
                        'authors' => str_starts_with($type, 'EA118')
                            ? $this->mapAwardRecipientsToBackOfficeAuthors((array) ($output['award_recipients'] ?? []))
                            : $this->normalizeBackOfficeAuthors((array) ($output['authors'] ?? [])),
                        'conferences' => $this->normalizeDetailOutputConferences($output),
                    ];
                })
                ->values()
                ->all(),
        ];
    }

    private function buildBackOfficeEntryPayload(array $entry, ?Collection $lookup = null): array
    {
        $lookup ??= $this->buildBackOfficeUserLookup();
        $user = $this->resolveBackOfficeUser(
            (($entry['user_id'] ?? null) !== null && (int) $entry['user_id'] > 0)
                ? (int) $entry['user_id']
                : null,
            trim((string) ($entry['person_name'] ?? '')),
            $lookup
        );
        $hours = (float) ($entry['hours'] ?? 0);

        return [
            'user_id' => $user?->id,
            'person_name' => $this->backOfficeInternalFullName($user, trim((string) ($entry['person_name'] ?? ''))),
            'category' => trim((string) ($entry['category'] ?? '')),
            'job_name' => trim((string) ($entry['job_name'] ?? '')),
            'job_detail' => trim((string) ($entry['job_detail'] ?? '')),
            'start_date' => trim((string) ($entry['start_date'] ?? '')),
            'end_date' => trim((string) ($entry['end_date'] ?? '')),
            'hours' => $hours,
            'fte_value' => round($hours * 0.000621, 8),
        ];
    }

    private function syncBackOfficeOutputLinks(
        BackOfficeTaskOutput $output,
        string $type,
        array $authors,
        array $awardRecipients,
        array $bookAuthors,
        array $conferences
    ): void {
        $this->syncBackOfficeOutputAuthors($output, str_starts_with($type, 'EA101') ? $authors : []);
        $this->syncBackOfficeOutputAwardRecipients($output, str_starts_with($type, 'EA118') ? $awardRecipients : []);
        $this->syncBackOfficeOutputBookAuthors($output, str_starts_with($type, 'EA119') ? $bookAuthors : []);
        $this->syncBackOfficeOutputConferences($output, str_starts_with($type, 'EA103') ? $conferences : []);
    }

    private function syncBackOfficeOutputAuthors(BackOfficeTaskOutput $output, array $authors): void
    {
        $output->authorLinks()->delete();

        if ($authors === []) {
            return;
        }

        $rows = collect($authors)
            ->map(function ($author) use ($output) {
                $name = trim((string) ($author['name'] ?? ''));
                $role = trim((string) ($author['role'] ?? ''));
                if ($name === '' || $role === '') {
                    return null;
                }

                return [
                    'back_office_task_output_id' => $output->id,
                    'user_id' => (($author['user_id'] ?? null) !== null && (int) $author['user_id'] > 0) ? (int) $author['user_id'] : null,
                    'role' => $role,
                    'person_type' => trim((string) ($author['person_type'] ?? '')) ?: null,
                    'name' => $name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })
            ->filter()
            ->values()
            ->all();

        if ($rows !== []) {
            BackOfficeOutputAuthor::insert($rows);
        }
    }

    private function syncBackOfficeOutputAwardRecipients(BackOfficeTaskOutput $output, array $recipients): void
    {
        $output->awardRecipientLinks()->delete();

        if ($recipients === []) {
            return;
        }

        $rows = collect($recipients)
            ->map(function ($recipient) use ($output) {
                $name = trim((string) ($recipient['name'] ?? ''));
                if ($name === '') {
                    return null;
                }

                return [
                    'back_office_task_output_id' => $output->id,
                    'user_id' => (($recipient['user_id'] ?? null) !== null && (int) $recipient['user_id'] > 0) ? (int) $recipient['user_id'] : null,
                    'person_type' => trim((string) ($recipient['person_type'] ?? '')) ?: null,
                    'name' => $name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })
            ->filter()
            ->values()
            ->all();

        if ($rows !== []) {
            BackOfficeOutputAwardRecipient::insert($rows);
        }
    }

    private function syncBackOfficeOutputBookAuthors(BackOfficeTaskOutput $output, array $authors): void
    {
        $output->bookAuthorLinks()->delete();

        if ($authors === []) {
            return;
        }

        $rows = collect($authors)
            ->map(function ($author) use ($output) {
                $name = trim((string) ($author['name'] ?? ''));
                if ($name === '') {
                    return null;
                }

                return [
                    'back_office_task_output_id' => $output->id,
                    'user_id' => (($author['user_id'] ?? null) !== null && (int) $author['user_id'] > 0) ? (int) $author['user_id'] : null,
                    'person_type' => trim((string) ($author['person_type'] ?? '')) ?: null,
                    'name' => $name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })
            ->filter()
            ->values()
            ->all();

        if ($rows !== []) {
            BackOfficeOutputBookAuthor::insert($rows);
        }
    }

    private function syncBackOfficeOutputConferences(BackOfficeTaskOutput $output, array $conferences): void
    {
        $output->conferenceLinks()->each(function (BackOfficeOutputConference $conference): void {
            $conference->presenters()->delete();
        });
        $output->conferenceLinks()->delete();

        if ($conferences === []) {
            return;
        }

        foreach ($conferences as $conference) {
            $createdConference = $output->conferenceLinks()->create([
                'user_id' => (($conference['user_id'] ?? null) !== null && (int) $conference['user_id'] > 0) ? (int) $conference['user_id'] : null,
                'name' => trim((string) ($conference['name'] ?? '')) ?: null,
                'presentation_type' => trim((string) ($conference['presentation_type'] ?? '')) ?: null,
                'location' => trim((string) ($conference['location'] ?? '')) ?: null,
                'presenter_person_type' => trim((string) ($conference['presenter_person_type'] ?? '')) ?: null,
                'note' => trim((string) ($conference['note'] ?? '')) ?: null,
            ]);

            $presenters = collect($conference['presenters'] ?? [])
                ->map(function ($presenter) use ($createdConference) {
                    $name = trim((string) ($presenter['name'] ?? ''));
                    $personType = trim((string) ($presenter['person_type'] ?? ''));
                    $userId = (($presenter['user_id'] ?? null) !== null && (int) $presenter['user_id'] > 0)
                        ? (int) $presenter['user_id']
                        : null;

                    if ($name === '' && $personType === '' && ! $userId) {
                        return null;
                    }

                    return [
                        'back_office_output_conference_id' => $createdConference->id,
                        'user_id' => $userId,
                        'person_type' => $personType !== '' ? $personType : null,
                        'name' => $name !== '' ? $name : null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                })
                ->filter()
                ->values()
                ->all();

            if ($presenters !== []) {
                $createdConference->presenters()->insert($presenters);
            }
        }
    }

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

        $currentSort = $request->input('sort', 'back_office_order');

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

        AuditLogger::logBackOfficeAction('view_index', [
            'subject_type' => 'back_office_task',
            'subject_label' => $request->ajax() ? 'เปิดรายการ Back Office (ajax)' : 'เปิดรายการ Back Office',
        ], $request);

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
        // Back Office เปิดให้ผู้ใช้ที่ล็อกอินทุกคนเข้าถึงข้อมูลร่วมกัน
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
            case 'back_office_order':
                $this->applyBackOfficeTaskTitleOrder($query);
                break;
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

    private function applyBackOfficeTaskTitleOrder($query): void
    {
        $caseSql = collect(self::BACK_OFFICE_TASK_TITLE_ORDER)
            ->keys()
            ->map(fn ($index) => 'WHEN ? THEN ' . ($index + 1))
            ->implode(' ');

        $query
            ->orderByRaw("CASE title {$caseSql} ELSE 999 END", self::BACK_OFFICE_TASK_TITLE_ORDER)
            ->orderBy('title')
            ->orderByDesc('created_at');
    }

    private function backOfficeSortLabel(string $sort): string
    {
        return match ($sort) {
            'back_office_order' => 'ลำดับ Back Office',
            'oldest' => 'เก่าสุด',
            'title' => 'ชื่องาน ก-ฮ',
            'hours' => 'ชั่วโมงมากที่สุด',
            'fte' => 'FTE มากที่สุด',
            'outputs' => 'ผลงานมากที่สุด',
            default => 'บันทึกล่าสุด',
        };
    }

    public function create()
    {
        $this->ensureBackOfficeAdmin();

        $users = User::select('prefix', 'name', 'prefix_en', 'name_en')->orderBy('name')->get();
        $personList = $users->map(fn($u) => trim($u->prefix . ' ' . $u->name));
        $researchPersonList = $users
            ->map(fn ($user) => trim((string) $user->research_display_name))
            ->filter()
            ->values();

        AuditLogger::logBackOfficeAction('view_create_form', [
            'subject_type' => 'back_office_task',
            'subject_label' => 'เปิดฟอร์มสร้าง Back Office',
        ]);

        return view('back-office.create', [
            'personList' => $personList,
            'researchPersonList' => $researchPersonList,
            'categoryOptions' => $this->backOfficeCategoryOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $this->ensureBackOfficeAdmin();

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'purpose' => ['nullable', 'string'],
            'leaders' => ['nullable', 'array'],
            'leaders.*' => ['nullable', 'string', 'max:255'],
            'outputs' => ['nullable', 'array'],
            'outputs.*.title' => ['required_with:outputs', 'string', 'max:500'],
            'outputs.*.type' => ['nullable', 'string', 'max:255'],
            'outputs.*.project_output' => ['nullable', 'string', 'max:500'],
            'outputs.*.year' => ['nullable', 'string', 'max:255'],
            'outputs.*.journal' => ['nullable', 'string', 'max:500'],
            'outputs.*.quartile' => ['nullable', 'in:Q1,Q2,Q3,Q4'],
            'outputs.*.impact_factor' => ['nullable', 'numeric'],
            'outputs.*.book_title' => ['nullable', 'string', 'max:1000'],
            'outputs.*.publisher_name' => ['nullable', 'string', 'max:1000'],
            'outputs.*.chapter_pages' => ['nullable', 'string', 'max:1000'],
            'outputs.*.book_authors' => ['nullable', 'array'],
            'outputs.*.book_authors.*.user_id' => ['nullable', 'integer', 'exists:users,id'],
            'outputs.*.book_authors.*.name' => ['nullable', 'string', 'max:500'],
            'outputs.*.authors' => ['nullable', 'array'],
            'outputs.*.authors.*.user_id' => ['nullable', 'integer', 'exists:users,id'],
            'outputs.*.authors.*.name' => ['nullable', 'string', 'max:500'],
            'outputs.*.authors.*.role' => ['nullable', 'string', 'max:50'],
            'outputs.*.authors.*.person_type' => ['nullable', 'string', 'max:50'],
            'outputs.*.award_recipients' => ['nullable', 'array'],
            'outputs.*.award_recipients.*.user_id' => ['nullable', 'integer', 'exists:users,id'],
            'outputs.*.award_recipients.*.name' => ['nullable', 'string', 'max:500'],
            'outputs.*.award_recipients.*.person_type' => ['nullable', 'in:unit,internal,external'],
            'outputs.*.conferences' => ['nullable', 'array'],
            'outputs.*.conferences.*.name' => ['nullable', 'string', 'max:500'],
            'outputs.*.conferences.*.presentation_type' => ['nullable', 'string', 'max:100'],
            'outputs.*.conferences.*.location' => ['nullable', 'string', 'max:500'],
            'outputs.*.conferences.*.note' => ['nullable', 'string', 'max:500'],
            'outputs.*.conferences.*.presenters' => ['nullable', 'array'],
            'outputs.*.conferences.*.presenters.*.user_id' => ['nullable', 'integer', 'exists:users,id'],
            'outputs.*.conferences.*.presenters.*.name' => ['nullable', 'string', 'max:500'],
            'outputs.*.conferences.*.presenters.*.person_type' => ['nullable', 'in:internal,external'],
            'outputs.*.conference_name' => ['nullable', 'string', 'max:500'],
            'outputs.*.conference_presentation_type' => ['nullable', 'string', 'max:100'],
            'outputs.*.conference_location' => ['nullable', 'string', 'max:500'],
            'outputs.*.conference_presenters' => ['nullable', 'array'],
            'outputs.*.conference_presenters.*.user_id' => ['nullable', 'integer', 'exists:users,id'],
            'outputs.*.conference_presenters.*.name' => ['nullable', 'string', 'max:500'],
            'outputs.*.conference_presenters.*.person_type' => ['nullable', 'in:internal,external'],
            'entries' => ['nullable', 'array'],
            'entries.*.user_id' => ['nullable', 'integer', 'exists:users,id'],
            'entries.*.person_name' => ['required', 'string', 'max:500'],
            'entries.*.category' => ['required', Rule::in($this->backOfficeCategoryOptions())],
            'entries.*.job_name' => ['required', 'string', 'max:500'],
            'entries.*.job_detail' => ['required', 'string', 'max:10000'],
            'entries.*.start_date' => ['required', 'date_format:d-m-Y'],
            'entries.*.end_date' => ['required', 'date_format:d-m-Y'],
            'entries.*.hours' => ['required', 'numeric', 'min:0'],
        ]);

        $task = null;

        $entries = $data['entries'] ?? [];

        foreach ($entries as $idx => $entry) {
            $entryStart = Carbon::createFromFormat('d-m-Y', $entry['start_date']);
            $entryEnd = Carbon::createFromFormat('d-m-Y', $entry['end_date']);
            if ($entryEnd->lt($entryStart)) {
                return back()->withErrors([
                    "entries.$idx.end_date" => 'วันที่สิ้นสุดต้องไม่ก่อนวันที่เริ่มต้น',
                ])->withInput();
            }
        }

        [$startDate, $endDate] = $this->deriveBackOfficeTaskDateRange($entries);

        DB::transaction(function () use ($data, $startDate, $endDate, &$task) {
            $lookup = $this->buildBackOfficeUserLookup();
            $task = BackOfficeTask::create([
                'title' => $data['title'],
                'start_date' => $startDate,
                'end_date' => $endDate,
                'description' => null,
                'purpose' => $data['purpose'] ?? null,
                'leaders' => $data['leaders'] ?? null,
                'created_by' => Auth::id(),
                'updated_by' => null,
            ]);

            if (!empty($data['outputs'])) {
                foreach ($data['outputs'] as $output) {
                    $type = trim((string) ($output['type'] ?? ''));
                    $awardRecipients = $this->normalizeBackOfficeAwardRecipients((array) ($output['award_recipients'] ?? []), $lookup);
                    $authors = str_starts_with($type, 'EA118')
                        ? $this->mapAwardRecipientsToBackOfficeAuthors((array) ($output['award_recipients'] ?? []), $lookup)
                        : $this->normalizeBackOfficeAuthors((array) ($output['authors'] ?? []), $lookup);
                    $bookAuthors = $this->normalizeBackOfficeBookAuthors((array) ($output['book_authors'] ?? []), $lookup);
                    $conferences = $this->normalizeDetailOutputConferences($output);
                    $createdOutput = $task->outputs()->create([
                        'title' => $output['title'],
                        'type' => $type ?: null,
                        'project_output' => $output['project_output'] ?? null,
                        'year' => $output['year'] ?? null,
                        'journal' => $output['journal'] ?? null,
                        'quartile' => $output['quartile'] ?? null,
                        'impact_factor' => $output['impact_factor'] ?? null,
                        'book_title' => $output['book_title'] ?? null,
                        'publisher_name' => $output['publisher_name'] ?? null,
                        'chapter_pages' => $output['chapter_pages'] ?? null,
                        'book_authors' => !empty($bookAuthors) ? $bookAuthors : null,
                        'authors' => !empty($authors) ? $authors : null,
                        'conferences' => !empty($conferences) ? $conferences : null,
                    ]);
                    $this->syncBackOfficeOutputLinks($createdOutput, $type, $authors, $awardRecipients, $bookAuthors, $conferences);
                }
            }

            foreach ($data['entries'] ?? [] as $entry) {
                BackOfficeEntry::create(array_merge(
                    ['task_id' => $task->id],
                    $this->buildBackOfficeEntryPayload($entry, $lookup)
                ));
            }
        });

        AuditLogger::logBackOfficeAction('create_task', [
            'task_id' => $task?->id,
            'subject_type' => 'back_office_task',
            'subject_label' => $task?->title,
            'task_title' => $task?->title,
            'changes' => [
                'entries_count' => count($data['entries'] ?? []),
                'outputs_count' => count($data['outputs'] ?? []),
            ],
        ], $request);

        return redirect()
            ->route('back_office.show', $task)
            ->with('success', 'บันทึกงานนอกโครงการเรียบร้อย');
    }

    public function show(Request $request, BackOfficeTask $task)
    {
        $task->load(array_merge(['entries', 'outputs', 'creator', 'updater'], $this->backOfficeOutputRelations()));
        $users = User::select('prefix', 'name', 'prefix_en', 'name_en')->orderBy('name')->get();
        $personList = $users->map(fn($u) => trim($u->prefix . ' ' . $u->name));
        $researchPersonList = $users
            ->map(function ($user) {
                $display = trim((string) ($user->research_display_name ?? ''));
                return $display !== '' ? $display : trim((string) ($user->name ?? ''));
            })
            ->filter()
            ->values();

        $summary = [
            'entries' => $task->entries->count(),
            'hours' => $task->entries->sum('hours'),
            'fte' => $task->entries->sum('fte_value'),
        ];

        if ($request->header('X-BackOffice-Refresh') !== '1') {
            AuditLogger::logBackOfficeAction('view_task', [
                'task_id' => $task->id,
                'subject_type' => 'back_office_task',
                'subject_label' => $task->title,
                'task_title' => $task->title,
            ], $request);
        }

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
            'personList' => $personList,
            'researchPersonList' => $researchPersonList,
            'categoryOptions' => $this->backOfficeCategoryOptions(),
        ]);
    }

    public function storeEntry(Request $request, BackOfficeTask $task)
    {
        $validated = $request->validate([
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'person_name' => ['required', 'string', 'max:500'],
            'category' => ['required', Rule::in($this->backOfficeCategoryOptions())],
            'job_name' => ['required', 'string', 'max:500'],
            'job_detail' => ['required', 'string', 'max:10000'],
            'start_date' => ['required', 'string', 'max:255'],
            'end_date' => ['required', 'string', 'max:255'],
            'hours' => ['required', 'numeric', 'min:0'],
        ]);

        $startDate = $this->normalizeFlexibleBackOfficeDate($validated['start_date']);
        $endDate = $this->normalizeFlexibleBackOfficeDate($validated['end_date']);

        if (!$startDate) {
            return back()->withErrors(['start_date' => 'วันที่เริ่มไม่ถูกต้อง'])->withInput($request->all() + ['_quick_add_entry' => 1]);
        }

        if (!$endDate) {
            return back()->withErrors(['end_date' => 'วันที่สิ้นสุดไม่ถูกต้อง'])->withInput($request->all() + ['_quick_add_entry' => 1]);
        }

        $start = Carbon::createFromFormat('d-m-Y', $startDate);
        $end = Carbon::createFromFormat('d-m-Y', $endDate);
        if ($end->lt($start)) {
            return back()->withErrors(['end_date' => 'วันที่สิ้นสุดต้องไม่ก่อนวันที่เริ่มต้น'])->withInput($request->all() + ['_quick_add_entry' => 1]);
        }

        $entry = BackOfficeEntry::create(array_merge(
            ['task_id' => $task->id],
            $this->buildBackOfficeEntryPayload(array_merge($validated, [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]))
        ));

        AuditLogger::logBackOfficeAction('add_entry_from_detail', [
            'task_id' => $task->id,
            'entry_id' => $entry->id,
            'subject_type' => 'back_office_entry',
            'subject_label' => $entry->job_name,
            'task_title' => $task->title,
            'entry_person_name' => $entry->person_name,
            'changes' => [
                'category' => $validated['category'],
                'hours' => $entry->hours,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ], $request);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'message' => 'เพิ่มภาระงานเรียบร้อย',
            ]);
        }

        return redirect()
            ->route('back_office.show', $task)
            ->with('success', 'เพิ่มภาระงานเรียบร้อย');
    }

    public function updateEntry(Request $request, BackOfficeTask $task, BackOfficeEntry $entry)
    {
        $this->ensureBackOfficeAdmin();

        if ((int) $entry->task_id !== (int) $task->id) {
            abort(404);
        }

        $validated = $request->validate([
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'person_name' => ['required', 'string', 'max:500'],
            'category' => ['required', Rule::in($this->backOfficeCategoryOptions())],
            'job_name' => ['required', 'string', 'max:500'],
            'job_detail' => ['required', 'string', 'max:10000'],
            'start_date' => ['required', 'string', 'max:255'],
            'end_date' => ['required', 'string', 'max:255'],
            'hours' => ['required', 'numeric', 'min:0'],
            '_edit_entry_id' => ['nullable'],
        ]);

        $startDate = $this->normalizeFlexibleBackOfficeDate($validated['start_date']);
        $endDate = $this->normalizeFlexibleBackOfficeDate($validated['end_date']);

        if (!$startDate) {
            return back()->withErrors(['start_date' => 'วันที่เริ่มไม่ถูกต้อง'])->withInput($request->all());
        }

        if (!$endDate) {
            return back()->withErrors(['end_date' => 'วันที่สิ้นสุดไม่ถูกต้อง'])->withInput($request->all());
        }

        $start = Carbon::createFromFormat('d-m-Y', $startDate);
        $end = Carbon::createFromFormat('d-m-Y', $endDate);
        if ($end->lt($start)) {
            return back()->withErrors(['end_date' => 'วันที่สิ้นสุดต้องไม่ก่อนวันที่เริ่มต้น'])->withInput($request->all());
        }

        $entry->update($this->buildBackOfficeEntryPayload(array_merge($validated, [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ])));

        AuditLogger::logBackOfficeAction('update_entry_from_detail', [
            'task_id' => $task->id,
            'entry_id' => $entry->id,
            'subject_type' => 'back_office_entry',
            'subject_label' => $entry->job_name,
            'task_title' => $task->title,
            'entry_person_name' => $entry->person_name,
            'changes' => [
                'category' => $validated['category'],
                'hours' => $entry->hours,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ], $request);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'message' => 'อัปเดตภาระงานเรียบร้อย',
            ]);
        }

        return redirect()
            ->route('back_office.show', $task)
            ->with('success', 'อัปเดตภาระงานเรียบร้อย');
    }

    public function storeOutput(Request $request, BackOfficeTask $task)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:500'],
            'type' => ['nullable', 'string', 'max:255'],
            'project_output' => ['nullable', 'string', 'max:1000'],
            'year' => ['nullable', 'string', 'max:255'],
            'journal' => ['nullable', 'string', 'max:500'],
            'quartile' => ['nullable', 'in:Q1,Q2,Q3,Q4'],
            'impact_factor' => ['nullable', 'numeric'],
            'book_title' => ['nullable', 'string', 'max:1000'],
            'publisher_name' => ['nullable', 'string', 'max:1000'],
            'chapter_pages' => ['nullable', 'string', 'max:1000'],
            'authors' => ['nullable', 'array'],
            'authors.*.user_id' => ['nullable', 'integer', 'exists:users,id'],
            'authors.*.name' => ['nullable', 'string', 'max:500'],
            'authors.*.role' => ['nullable', 'string', 'max:50'],
            'authors.*.person_type' => ['nullable', 'string', 'max:50'],
            'journal_author_name' => ['nullable', 'string', 'max:500'],
            'journal_author_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'journal_author_role' => ['nullable', 'string', 'max:50'],
            'award_recipient_name' => ['nullable', 'string', 'max:500'],
            'award_recipient_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'award_recipient_person_type' => ['nullable', 'in:unit,internal,external'],
            'award_recipients' => ['nullable', 'array'],
            'award_recipients.*.user_id' => ['nullable', 'integer', 'exists:users,id'],
            'award_recipients.*.name' => ['nullable', 'string', 'max:500'],
            'award_recipients.*.person_type' => ['nullable', 'in:unit,internal,external'],
            'book_authors' => ['nullable', 'array'],
            'book_authors.*.user_id' => ['nullable', 'integer', 'exists:users,id'],
            'book_authors.*.name' => ['nullable', 'string', 'max:500'],
            'book_authors.*.person_type' => ['nullable', 'in:internal,external'],
            'book_author_name' => ['nullable', 'string', 'max:500'],
            'book_author_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'conferences' => ['nullable', 'array'],
            'conferences.*.name' => ['nullable', 'string', 'max:500'],
            'conferences.*.presentation_type' => ['nullable', 'string', 'max:100'],
            'conferences.*.location' => ['nullable', 'string', 'max:500'],
            'conferences.*.note' => ['nullable', 'string', 'max:500'],
            'conferences.*.presenter_person_type' => ['nullable', 'in:internal,external'],
            'conferences.*.presenters' => ['nullable', 'array'],
            'conferences.*.presenters.*.user_id' => ['nullable', 'integer', 'exists:users,id'],
            'conferences.*.presenters.*.name' => ['nullable', 'string', 'max:500'],
            'conferences.*.presenters.*.person_type' => ['nullable', 'in:internal,external'],
            'conference_name' => ['nullable', 'string', 'max:500'],
            'conference_presentation_type' => ['nullable', 'string', 'max:100'],
            'conference_location' => ['nullable', 'string', 'max:500'],
            'conference_presenter_person_type' => ['nullable', 'in:internal,external'],
            'conference_note' => ['nullable', 'string', 'max:500'],
            'conference_presenters' => ['nullable', 'array'],
            'conference_presenters.*.user_id' => ['nullable', 'integer', 'exists:users,id'],
            'conference_presenters.*.name' => ['nullable', 'string', 'max:500'],
            'conference_presenters.*.person_type' => ['nullable', 'in:internal,external'],
        ]);

        $type = trim((string) ($validated['type'] ?? ''));
        $awardRecipients = $this->normalizeBackOfficeAwardRecipients((array) ($validated['award_recipients'] ?? []));
        $authors = $this->normalizeDetailOutputAuthors($validated, $type);
        $bookAuthors = $this->normalizeDetailOutputBookAuthors($validated);
        $conferences = $this->normalizeDetailOutputConferences($validated);

        $output = $task->outputs()->create([
            'title' => $validated['title'],
            'type' => $type ?: null,
            'project_output' => $validated['project_output'] ?? null,
            'year' => $validated['year'] ?? null,
            'journal' => $validated['journal'] ?? null,
            'quartile' => $validated['quartile'] ?? null,
            'impact_factor' => $validated['impact_factor'] ?? null,
            'book_title' => $validated['book_title'] ?? null,
            'publisher_name' => $validated['publisher_name'] ?? null,
            'chapter_pages' => $validated['chapter_pages'] ?? null,
            'authors' => !empty($authors) ? $authors : null,
            'book_authors' => !empty($bookAuthors) ? $bookAuthors : null,
            'conferences' => !empty($conferences) ? $conferences : null,
        ]);
        $this->syncBackOfficeOutputLinks($output, $type, $authors, $awardRecipients, $bookAuthors, $conferences);

        AuditLogger::logBackOfficeAction('add_output_from_detail', [
            'task_id' => $task->id,
            'output_id' => $output->id,
            'subject_type' => 'back_office_output',
            'subject_label' => $validated['title'],
            'task_title' => $task->title,
            'output_title' => $validated['title'],
            'changes' => [
                'type' => $type,
                'year' => $validated['year'] ?? null,
            ],
        ], $request);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'message' => 'เพิ่มผลงานเรียบร้อย',
            ]);
        }

        return redirect()
            ->route('back_office.show', $task)
            ->with('success', 'เพิ่มผลงานเรียบร้อย');
    }

    public function updateOutput(Request $request, BackOfficeTask $task, \App\Models\BackOfficeTaskOutput $output)
    {
        $this->ensureBackOfficeAdmin();

        if ((int) $output->task_id !== (int) $task->id) {
            abort(404);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:500'],
            'type' => ['nullable', 'string', 'max:255'],
            'project_output' => ['nullable', 'string', 'max:1000'],
            'year' => ['nullable', 'string', 'max:255'],
            'journal' => ['nullable', 'string', 'max:500'],
            'quartile' => ['nullable', 'in:Q1,Q2,Q3,Q4'],
            'impact_factor' => ['nullable', 'numeric'],
            'book_title' => ['nullable', 'string', 'max:1000'],
            'publisher_name' => ['nullable', 'string', 'max:1000'],
            'chapter_pages' => ['nullable', 'string', 'max:1000'],
            'authors' => ['nullable', 'array'],
            'authors.*.user_id' => ['nullable', 'integer', 'exists:users,id'],
            'authors.*.name' => ['nullable', 'string', 'max:500'],
            'authors.*.role' => ['nullable', 'string', 'max:50'],
            'authors.*.person_type' => ['nullable', 'string', 'max:50'],
            'journal_author_name' => ['nullable', 'string', 'max:500'],
            'journal_author_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'journal_author_role' => ['nullable', 'string', 'max:50'],
            'award_recipient_name' => ['nullable', 'string', 'max:500'],
            'award_recipient_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'award_recipient_person_type' => ['nullable', 'in:unit,internal,external'],
            'award_recipients' => ['nullable', 'array'],
            'award_recipients.*.user_id' => ['nullable', 'integer', 'exists:users,id'],
            'award_recipients.*.name' => ['nullable', 'string', 'max:500'],
            'award_recipients.*.person_type' => ['nullable', 'in:unit,internal,external'],
            'book_authors' => ['nullable', 'array'],
            'book_authors.*.user_id' => ['nullable', 'integer', 'exists:users,id'],
            'book_authors.*.name' => ['nullable', 'string', 'max:500'],
            'book_authors.*.person_type' => ['nullable', 'in:internal,external'],
            'book_author_name' => ['nullable', 'string', 'max:500'],
            'book_author_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'conferences' => ['nullable', 'array'],
            'conferences.*.name' => ['nullable', 'string', 'max:500'],
            'conferences.*.presentation_type' => ['nullable', 'string', 'max:100'],
            'conferences.*.location' => ['nullable', 'string', 'max:500'],
            'conferences.*.note' => ['nullable', 'string', 'max:500'],
            'conferences.*.presenter_person_type' => ['nullable', 'in:internal,external'],
            'conferences.*.presenters' => ['nullable', 'array'],
            'conferences.*.presenters.*.user_id' => ['nullable', 'integer', 'exists:users,id'],
            'conferences.*.presenters.*.name' => ['nullable', 'string', 'max:500'],
            'conferences.*.presenters.*.person_type' => ['nullable', 'in:internal,external'],
            'conference_name' => ['nullable', 'string', 'max:500'],
            'conference_presentation_type' => ['nullable', 'string', 'max:100'],
            'conference_location' => ['nullable', 'string', 'max:500'],
            'conference_presenter_person_type' => ['nullable', 'in:internal,external'],
            'conference_note' => ['nullable', 'string', 'max:500'],
            'conference_presenters' => ['nullable', 'array'],
            'conference_presenters.*.user_id' => ['nullable', 'integer', 'exists:users,id'],
            'conference_presenters.*.name' => ['nullable', 'string', 'max:500'],
            'conference_presenters.*.person_type' => ['nullable', 'in:internal,external'],
            '_edit_output_id' => ['nullable'],
        ]);

        $type = trim((string) ($validated['type'] ?? ''));
        $awardRecipients = $this->normalizeBackOfficeAwardRecipients((array) ($validated['award_recipients'] ?? []));
        $authors = $this->normalizeDetailOutputAuthors($validated, $type);
        $bookAuthors = $this->normalizeDetailOutputBookAuthors($validated);
        $conferences = $this->normalizeDetailOutputConferences($validated);

        $output->update([
            'title' => $validated['title'],
            'type' => $type ?: null,
            'project_output' => $validated['project_output'] ?? null,
            'year' => $validated['year'] ?? null,
            'journal' => $validated['journal'] ?? null,
            'quartile' => $validated['quartile'] ?? null,
            'impact_factor' => $validated['impact_factor'] ?? null,
            'book_title' => $validated['book_title'] ?? null,
            'publisher_name' => $validated['publisher_name'] ?? null,
            'chapter_pages' => $validated['chapter_pages'] ?? null,
            'authors' => !empty($authors) ? $authors : null,
            'book_authors' => !empty($bookAuthors) ? $bookAuthors : null,
            'conferences' => !empty($conferences) ? $conferences : null,
        ]);
        $this->syncBackOfficeOutputLinks($output, $type, $authors, $awardRecipients, $bookAuthors, $conferences);

        AuditLogger::logBackOfficeAction('update_output_from_detail', [
            'task_id' => $task->id,
            'output_id' => $output->id,
            'subject_type' => 'back_office_output',
            'subject_label' => $validated['title'],
            'task_title' => $task->title,
            'output_title' => $validated['title'],
            'changes' => [
                'type' => $type,
                'year' => $validated['year'] ?? null,
            ],
        ], $request);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'message' => 'อัปเดตผลงานเรียบร้อย',
            ]);
        }

        return redirect()
            ->route('back_office.show', $task)
            ->with('success', 'อัปเดตผลงานเรียบร้อย');
    }

    public function destroyOutput(Request $request, BackOfficeTask $task, \App\Models\BackOfficeTaskOutput $output)
    {
        if ((int) $output->task_id !== (int) $task->id) {
            abort(404);
        }

        $label = trim((string) $output->title) ?: 'ไม่ระบุชื่อผลงาน';
        $type = trim((string) ($output->type ?? ''));
        $year = trim((string) ($output->year ?? ''));

        AuditLogger::logBackOfficeAction('delete_output', [
            'task_id' => $task->id,
            'output_id' => $output->id,
            'subject_type' => 'back_office_output',
            'subject_label' => $label,
            'task_title' => $task->title,
            'output_title' => $label,
            'changes' => [
                'type' => $type,
                'year' => $year,
            ],
        ], $request);

        $output->delete();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'message' => 'ลบผลงานเรียบร้อย',
                'remaining_outputs' => $task->outputs()->count(),
            ]);
        }

        return redirect()
            ->route('back_office.show', $task)
            ->with('success', 'ลบผลงานเรียบร้อย');
    }

    public function destroyEntry(Request $request, BackOfficeTask $task, BackOfficeEntry $entry)
    {
        if ((int) $entry->task_id !== (int) $task->id) {
            abort(404);
        }

        $entryLabel = trim((string) $entry->job_name) ?: 'ไม่ระบุชื่อภาระงาน';
        $entryPerson = trim((string) $entry->person_name) ?: '-';
        $entryHours = (float) ($entry->hours ?? 0);
        $entryStart = (string) ($entry->start_date ?? '');
        $entryEnd = (string) ($entry->end_date ?? '');

        AuditLogger::logBackOfficeAction('delete_entry', [
            'task_id' => $task->id,
            'entry_id' => $entry->id,
            'subject_type' => 'back_office_entry',
            'subject_label' => $entryLabel,
            'task_title' => $task->title,
            'entry_person_name' => $entryPerson,
            'changes' => [
                'hours' => $entryHours,
                'start_date' => $entryStart,
                'end_date' => $entryEnd,
            ],
        ], $request);

        $entry->delete();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'message' => 'ลบภาระงานเรียบร้อย',
                'remaining_entries' => $task->entries()->count(),
            ]);
        }

        return redirect()
            ->route('back_office.show', $task)
            ->with('success', 'ลบภาระงานเรียบร้อย');
    }

    public function edit(BackOfficeTask $task)
    {
        $this->ensureBackOfficeAdmin();

        $task->load(array_merge(['entries', 'outputs', 'creator', 'updater'], $this->backOfficeOutputRelations()));

        $users = User::select('prefix', 'name', 'prefix_en', 'name_en')->orderBy('name')->get();
        $personList = $users->map(fn($u) => trim($u->prefix . ' ' . $u->name));
        $researchPersonList = $users
            ->map(fn ($user) => trim((string) $user->research_display_name))
            ->filter()
            ->values();

        AuditLogger::logBackOfficeAction('view_edit_form', [
            'task_id' => $task->id,
            'subject_type' => 'back_office_task',
            'subject_label' => $task->title,
            'task_title' => $task->title,
        ]);

        return view('back-office.edit', [
            'task' => $task,
            'personList' => $personList,
            'researchPersonList' => $researchPersonList,
            'categoryOptions' => $this->backOfficeCategoryOptions(),
            'initialTaskOutputs' => $task->outputs
                ->map(fn (BackOfficeTaskOutput $output) => $this->serializeBackOfficeOutputForForm($output))
                ->values()
                ->all(),
        ]);
    }

    public function update(Request $request, BackOfficeTask $task)
    {
        $this->ensureBackOfficeAdmin();

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'purpose' => ['nullable', 'string'],
            'leaders' => ['nullable', 'array'],
            'leaders.*' => ['nullable', 'string', 'max:255'],
            'outputs' => ['nullable', 'array'],
            'outputs.*.title' => ['required_with:outputs', 'string', 'max:500'],
            'outputs.*.type' => ['nullable', 'string', 'max:255'],
            'outputs.*.project_output' => ['nullable', 'string', 'max:500'],
            'outputs.*.year' => ['nullable', 'string', 'max:255'],
            'outputs.*.journal' => ['nullable', 'string', 'max:500'],
            'outputs.*.quartile' => ['nullable', 'in:Q1,Q2,Q3,Q4'],
            'outputs.*.impact_factor' => ['nullable', 'numeric'],
            'outputs.*.book_title' => ['nullable', 'string', 'max:1000'],
            'outputs.*.publisher_name' => ['nullable', 'string', 'max:1000'],
            'outputs.*.chapter_pages' => ['nullable', 'string', 'max:1000'],
            'outputs.*.book_authors' => ['nullable', 'array'],
            'outputs.*.book_authors.*.user_id' => ['nullable', 'integer', 'exists:users,id'],
            'outputs.*.book_authors.*.name' => ['nullable', 'string', 'max:500'],
            'outputs.*.authors' => ['nullable', 'array'],
            'outputs.*.authors.*.user_id' => ['nullable', 'integer', 'exists:users,id'],
            'outputs.*.authors.*.name' => ['nullable', 'string', 'max:500'],
            'outputs.*.authors.*.role' => ['nullable', 'string', 'max:50'],
            'outputs.*.authors.*.person_type' => ['nullable', 'string', 'max:50'],
            'outputs.*.award_recipients' => ['nullable', 'array'],
            'outputs.*.award_recipients.*.user_id' => ['nullable', 'integer', 'exists:users,id'],
            'outputs.*.award_recipients.*.name' => ['nullable', 'string', 'max:500'],
            'outputs.*.award_recipients.*.person_type' => ['nullable', 'in:unit,internal,external'],
            'outputs.*.conferences' => ['nullable', 'array'],
            'outputs.*.conferences.*.name' => ['nullable', 'string', 'max:500'],
            'outputs.*.conferences.*.presentation_type' => ['nullable', 'string', 'max:100'],
            'outputs.*.conferences.*.location' => ['nullable', 'string', 'max:500'],
            'outputs.*.conferences.*.note' => ['nullable', 'string', 'max:500'],
            'outputs.*.conferences.*.presenters' => ['nullable', 'array'],
            'outputs.*.conferences.*.presenters.*.user_id' => ['nullable', 'integer', 'exists:users,id'],
            'outputs.*.conferences.*.presenters.*.name' => ['nullable', 'string', 'max:500'],
            'outputs.*.conferences.*.presenters.*.person_type' => ['nullable', 'in:internal,external'],
            'outputs.*.conference_name' => ['nullable', 'string', 'max:500'],
            'outputs.*.conference_presentation_type' => ['nullable', 'string', 'max:100'],
            'outputs.*.conference_location' => ['nullable', 'string', 'max:500'],
            'outputs.*.conference_presenter_person_type' => ['nullable', 'in:internal,external'],
            'outputs.*.conference_note' => ['nullable', 'string', 'max:500'],
            'outputs.*.conference_presenters' => ['nullable', 'array'],
            'outputs.*.conference_presenters.*.user_id' => ['nullable', 'integer', 'exists:users,id'],
            'outputs.*.conference_presenters.*.name' => ['nullable', 'string', 'max:500'],
            'outputs.*.conference_presenters.*.person_type' => ['nullable', 'in:internal,external'],
            'entries' => ['nullable', 'array'],
            'entries.*.user_id' => ['nullable', 'integer', 'exists:users,id'],
            'entries.*.person_name' => ['required', 'string', 'max:500'],
            'entries.*.category' => ['required', Rule::in($this->backOfficeCategoryOptions())],
            'entries.*.job_name' => ['required', 'string', 'max:500'],
            'entries.*.job_detail' => ['required', 'string', 'max:10000'],
            'entries.*.start_date' => ['required', 'date_format:d-m-Y'],
            'entries.*.end_date' => ['required', 'date_format:d-m-Y'],
            'entries.*.hours' => ['required', 'numeric', 'min:0'],
        ]);

        $entries = $data['entries'] ?? [];

        foreach ($entries as $idx => $entry) {
            $entryStart = Carbon::createFromFormat('d-m-Y', $entry['start_date']);
            $entryEnd = Carbon::createFromFormat('d-m-Y', $entry['end_date']);
            if ($entryEnd->lt($entryStart)) {
                return back()->withErrors([
                    "entries.$idx.end_date" => 'วันที่สิ้นสุดต้องไม่ก่อนวันที่เริ่มต้น',
                ])->withInput();
            }
        }

        [$startDate, $endDate] = $this->deriveBackOfficeTaskDateRange($entries);
        $data['start_date'] = $startDate;
        $data['end_date'] = $endDate;
        $data['description'] = null;

        $originalSnapshot = $this->buildBackOfficeComparableSnapshot($task);
        $incomingSnapshot = $this->buildBackOfficeComparableInput($data);

        if ($originalSnapshot === $incomingSnapshot) {
            return redirect()
                ->route('back_office.show', $task)
                ->with('success', 'ไม่มีการเปลี่ยนแปลงข้อมูล');
        }

        DB::transaction(function () use ($data, $task, $startDate, $endDate) {
            $lookup = $this->buildBackOfficeUserLookup();
            $task->update([
                'title' => $data['title'],
                'start_date' => $startDate,
                'end_date' => $endDate,
                'description' => null,
                'purpose' => $data['purpose'] ?? null,
                'leaders' => $data['leaders'] ?? null,
                'updated_by' => Auth::id(),
            ]);

            $task->outputs()->delete();
            if (!empty($data['outputs'])) {
                foreach ($data['outputs'] as $output) {
                    $type = trim((string) ($output['type'] ?? ''));
                    $awardRecipients = $this->normalizeBackOfficeAwardRecipients((array) ($output['award_recipients'] ?? []), $lookup);
                    $authors = str_starts_with($type, 'EA118')
                        ? $this->mapAwardRecipientsToBackOfficeAuthors((array) ($output['award_recipients'] ?? []), $lookup)
                        : $this->normalizeBackOfficeAuthors((array) ($output['authors'] ?? []), $lookup);
                    $bookAuthors = $this->normalizeBackOfficeBookAuthors((array) ($output['book_authors'] ?? []), $lookup);
                    $conferences = $this->normalizeDetailOutputConferences($output);
                    $createdOutput = $task->outputs()->create([
                        'title' => $output['title'],
                        'type' => $type ?: null,
                        'project_output' => $output['project_output'] ?? null,
                        'year' => $output['year'] ?? null,
                        'journal' => $output['journal'] ?? null,
                        'quartile' => $output['quartile'] ?? null,
                        'impact_factor' => $output['impact_factor'] ?? null,
                        'book_title' => $output['book_title'] ?? null,
                        'publisher_name' => $output['publisher_name'] ?? null,
                        'chapter_pages' => $output['chapter_pages'] ?? null,
                        'book_authors' => !empty($bookAuthors) ? $bookAuthors : null,
                        'authors' => !empty($authors) ? $authors : null,
                        'conferences' => !empty($conferences) ? $conferences : null,
                    ]);
                    $this->syncBackOfficeOutputLinks($createdOutput, $type, $authors, $awardRecipients, $bookAuthors, $conferences);
                }
            }

            $task->entries()->delete();

            foreach ($data['entries'] ?? [] as $entry) {
                $task->entries()->create($this->buildBackOfficeEntryPayload($entry, $lookup));
            }
        });

        AuditLogger::logBackOfficeAction('update_task', [
            'task_id' => $task->id,
            'subject_type' => 'back_office_task',
            'subject_label' => $task->title,
            'task_title' => $task->title,
            'changes' => [
                'entries_count' => count($data['entries'] ?? []),
                'outputs_count' => count($data['outputs'] ?? []),
            ],
        ], $request);

        return redirect()
            ->route('back_office.show', $task)
            ->with('success', 'อัปเดตงานนอกโครงการเรียบร้อย');
    }

    public function destroy(BackOfficeTask $task)
    {
        $this->ensureBackOfficeAdmin();

        $user = Auth::user();
        if (!$this->canManageTask($task, $user)) {
            abort(403);
        }

        AuditLogger::logBackOfficeAction('delete_task', [
            'task_id' => $task->id,
            'subject_type' => 'back_office_task',
            'subject_label' => $task->title,
            'task_title' => $task->title,
        ]);

        DB::transaction(function () use ($task) {
            $task->entries()->delete();
            $task->outputs()->delete();
            $task->delete();
        });

        return redirect()
            ->route('back_office.index')
            ->with('success', 'ลบงานนอกโครงการเรียบร้อย');
    }

    private function canManageTask(BackOfficeTask $task, $user): bool
    {
        return $user?->hasRole(User::ROLE_ADMIN) ?? false;
    }

    public function pdf(BackOfficeTask $task)
    {
        $task->load(array_merge(['entries', 'outputs', 'creator', 'updater'], $this->backOfficeOutputRelations()));

        $summary = [
            'entries' => $task->entries->count(),
            'hours' => $task->entries->sum('hours'),
            'fte' => $task->entries->sum('fte_value'),
        ];

        $user = auth()->user();
        $dateStr = now()->setTimezone('Asia/Bangkok')->format('Ymd_Hi');
        $filename = 'BACK_OFFICE_' . ($task->backoffice_id ?: BackOfficeTask::formatBackOfficeId((int) $task->id)) . "_{$dateStr}.pdf";

        $pdf = PDF::loadView('back-office.pdf', [
            'task' => $task,
            'summary' => $summary,
            'user' => $user,
        ]);

        AuditLogger::logBackOfficeAction('export_pdf', [
            'task_id' => $task->id,
            'subject_type' => 'back_office_task',
            'subject_label' => $task->title,
            'task_title' => $task->title,
        ]);
        AuditLogger::logFileDownload($filename, [
            'subject_type' => 'back_office_pdf',
            'subject_label' => $task->title,
        ]);

        return $pdf->stream($filename);
    }
}
