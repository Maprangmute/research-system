<?php

namespace App\Models;

use App\Services\OutputLinkPreviewCacheService;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BackOfficeTaskOutput extends Model
{
    private const UNIT_NAME = 'หน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ';

    protected $fillable = [
        'task_id',
        'owner_user_id',
        'owner_name',
        'title',
        'type',
        'project_output',
        'year',
        'journal',
        'quartile',
        'impact_factor',
        'book_title',
        'publisher_name',
        'chapter_pages',
        'isbn',
    ];

    protected static function booted(): void
    {
        static::saved(function (BackOfficeTaskOutput $output) {
            app(OutputLinkPreviewCacheService::class)->scheduleForSavedModel($output);
        });
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(BackOfficeTask::class, 'task_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function resolvedOwnerName(): string
    {
        $user = $this->relationLoaded('owner') ? $this->owner : $this->owner()->first();

        if ($user) {
            $name = trim((string) $user->full_name);
            if ($name !== '') {
                return $name;
            }
        }

        return trim((string) ($this->owner_name ?? ''));
    }

    public function resolvedContributors(): Collection
    {
        $links = $this->relationLoaded('contributorLinks')
            ? $this->contributorLinks
            : $this->contributorLinks()->with('user')->get();

        if ($links->isNotEmpty()) {
            return $links
                ->map(function (BackOfficeOutputContributor $contributor): array {
                    $user = $contributor->relationLoaded('user') ? $contributor->user : $contributor->user()->first();
                    $freshName = $user ? trim((string) $user->full_name) : '';
                    $snapshotName = trim((string) ($contributor->name ?? ''));

                    return [
                        'user_id' => $contributor->user_id ? (int) $contributor->user_id : null,
                        'name' => $freshName !== '' ? $freshName : $snapshotName,
                    ];
                })
                ->filter(fn (array $item) => $item['name'] !== '' || !empty($item['user_id']))
                ->values();
        }

        $contributors = collect($this->rawJsonArray('contributors'))
            ->map(function ($item): array {
                $item = is_array($item) ? $item : [];
                return [
                    'user_id' => !empty($item['user_id']) ? (int) $item['user_id'] : null,
                    'name' => trim((string) ($item['name'] ?? '')),
                ];
            })
            ->filter(fn (array $item) => $item['name'] !== '' || !empty($item['user_id']))
            ->values();

        if ($contributors->isNotEmpty()) {
            $usersById = User::query()
                ->whereIn('id', $contributors->pluck('user_id')->filter()->unique()->values())
                ->get()
                ->keyBy('id');

            return $contributors
                ->map(function (array $contributor) use ($usersById): array {
                    $userId = !empty($contributor['user_id']) ? (int) $contributor['user_id'] : null;
                    $user = $userId ? $usersById->get($userId) : null;
                    $freshName = $user ? trim((string) $user->full_name) : '';

                    return [
                        'user_id' => $userId,
                        'name' => $freshName !== '' ? $freshName : $contributor['name'],
                    ];
                })
                ->filter(fn (array $item) => $item['name'] !== '' || !empty($item['user_id']))
                ->values();
        }

        $ownerName = $this->resolvedOwnerName();
        if ($ownerName === '' && empty($this->owner_user_id)) {
            return collect();
        }

        return collect([[
            'user_id' => $this->owner_user_id ? (int) $this->owner_user_id : null,
            'name' => $ownerName,
        ]]);
    }

    public function authorLinks(): HasMany
    {
        return $this->hasMany(BackOfficeOutputAuthor::class, 'back_office_task_output_id');
    }

    public function contributorLinks(): HasMany
    {
        return $this->hasMany(BackOfficeOutputContributor::class, 'back_office_task_output_id');
    }

    public function awardRecipientLinks(): HasMany
    {
        return $this->hasMany(BackOfficeOutputAwardRecipient::class, 'back_office_task_output_id');
    }

    public function bookAuthorLinks(): HasMany
    {
        return $this->hasMany(BackOfficeOutputBookAuthor::class, 'back_office_task_output_id');
    }

    public function conferenceLinks(): HasMany
    {
        return $this->hasMany(BackOfficeOutputConference::class, 'back_office_task_output_id');
    }

    public function resolvedAuthors(): array
    {
        $links = $this->relationLoaded('authorLinks')
            ? $this->authorLinks
            : $this->authorLinks()->with('user')->get();

        if ($links->isNotEmpty()) {
            return $links
                ->map(function (BackOfficeOutputAuthor $author): array {
                    $role = trim((string) $author->role);
                    $personType = trim((string) $author->person_type);
                    $user = $author->relationLoaded('user') ? $author->user : $author->user()->first();

                    $name = trim((string) $author->name);
                    if ($user) {
                        $name = $role === 'awardee'
                            ? trim((string) $user->full_name)
                            : trim((string) $user->research_display_name);
                    }

                    return [
                        'user_id' => $author->user_id ? (int) $author->user_id : null,
                        'name' => $name,
                        'role' => $role,
                        'person_type' => $personType,
                    ];
                })
                ->filter(fn (array $item) => $item['name'] !== '' || $item['role'] !== '' || $item['person_type'] !== '' || !empty($item['user_id']))
                ->values()
                ->all();
        }

        return collect($this->rawJsonArray('authors'))
            ->map(fn ($item) => is_array($item) ? $item : [])
            ->values()
            ->all();
    }

    public function resolvedAwardRecipients(): array
    {
        $links = $this->relationLoaded('awardRecipientLinks')
            ? $this->awardRecipientLinks
            : $this->awardRecipientLinks()->with('user')->get();

        if ($links->isNotEmpty()) {
            return $links
                ->map(function (BackOfficeOutputAwardRecipient $recipient): array {
                    $personType = trim((string) $recipient->person_type);
                    $user = $recipient->relationLoaded('user') ? $recipient->user : $recipient->user()->first();

                    if ($personType === 'unit') {
                        $name = self::UNIT_NAME;
                    } elseif ($user) {
                        $name = trim((string) $user->full_name);
                    } else {
                        $name = trim((string) $recipient->name);
                    }

                    return [
                        'user_id' => $recipient->user_id ? (int) $recipient->user_id : null,
                        'name' => $name,
                        'person_type' => $personType,
                    ];
                })
                ->filter(fn (array $item) => $item['name'] !== '' || $item['person_type'] !== '' || !empty($item['user_id']))
                ->values()
                ->all();
        }

        return collect($this->rawJsonArray('authors'))
            ->filter(fn ($item) => is_array($item) && (($item['role'] ?? '') === 'awardee'))
            ->map(function (array $item): array {
                $personType = trim((string) ($item['person_type'] ?? ''));
                return [
                    'user_id' => !empty($item['user_id']) ? (int) $item['user_id'] : null,
                    'name' => $personType === 'unit'
                        ? self::UNIT_NAME
                        : trim((string) ($item['name'] ?? '')),
                    'person_type' => $personType,
                ];
            })
            ->values()
            ->all();
    }

    public function resolvedBookAuthors(): array
    {
        $links = $this->relationLoaded('bookAuthorLinks')
            ? $this->bookAuthorLinks
            : $this->bookAuthorLinks()->with('user')->get();

        if ($links->isNotEmpty()) {
            return $links
                ->map(function (BackOfficeOutputBookAuthor $author): array {
                    $personType = trim((string) $author->person_type);
                    $user = $author->relationLoaded('user') ? $author->user : $author->user()->first();
                    $name = $user ? trim((string) $user->full_name) : trim((string) $author->name);

                    return [
                        'user_id' => $author->user_id ? (int) $author->user_id : null,
                        'name' => $name,
                        'person_type' => $personType,
                    ];
                })
                ->filter(fn (array $item) => $item['name'] !== '' || $item['person_type'] !== '' || !empty($item['user_id']))
                ->values()
                ->all();
        }

        return collect($this->rawJsonArray('book_authors'))
            ->map(fn ($item) => is_array($item) ? $item : [])
            ->values()
            ->all();
    }

    public function resolvedConferences(): array
    {
        $links = $this->relationLoaded('conferenceLinks')
            ? $this->conferenceLinks
            : $this->conferenceLinks()->with(['user', 'presenters.user'])->get();

        if ($links->isNotEmpty()) {
            return $links
                ->map(function (BackOfficeOutputConference $conference): array {
                    $presenters = ($conference->relationLoaded('presenters')
                        ? $conference->presenters
                        : $conference->presenters()->with('user')->get())
                        ->map(function (BackOfficeOutputConferencePresenter $presenter): array {
                            $personType = trim((string) $presenter->person_type);
                            $user = $presenter->relationLoaded('user') ? $presenter->user : $presenter->user()->first();
                            $name = $user ? trim((string) $user->full_name) : trim((string) $presenter->name);

                            return [
                                'user_id' => $presenter->user_id ? (int) $presenter->user_id : null,
                                'name' => $name,
                                'person_type' => $personType,
                            ];
                        })
                        ->filter(fn (array $item) => $item['name'] !== '' || $item['person_type'] !== '' || !empty($item['user_id']))
                        ->values()
                        ->all();

                    return [
                        'name' => trim((string) $conference->name),
                        'presentation_type' => trim((string) $conference->presentation_type),
                        'location' => trim((string) $conference->location),
                        'presenter_person_type' => trim((string) $conference->presenter_person_type),
                        'note' => trim((string) $conference->note),
                        'presenters' => $presenters,
                    ];
                })
                ->filter(function (array $item): bool {
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

        return collect($this->rawJsonArray('conferences'))
            ->map(fn ($item) => is_array($item) ? $item : [])
            ->values()
            ->all();
    }

    public function getContributorsAttribute(): array
    {
        return $this->resolvedContributors()->all();
    }

    public function getAuthorsAttribute(): array
    {
        return $this->resolvedAuthors();
    }

    public function getBookAuthorsAttribute(): array
    {
        return $this->resolvedBookAuthors();
    }

    public function getConferencesAttribute(): array
    {
        return $this->resolvedConferences();
    }

    private function rawJsonArray(string $key): array
    {
        if (! array_key_exists($key, $this->attributes)) {
            return [];
        }

        $value = $this->attributes[$key] ?? null;

        if (is_array($value)) {
            return $value;
        }

        if (! is_string($value) || trim($value) === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }
}
