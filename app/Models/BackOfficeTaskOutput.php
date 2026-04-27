<?php

namespace App\Models;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BackOfficeTaskOutput extends Model
{
    private const UNIT_NAME = 'หน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ';

    protected $fillable = [
        'task_id',
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
        'book_authors',
        'authors',
        'conferences',
    ];

    protected $casts = [
        'book_authors' => 'array',
        'authors' => 'array',
        'conferences' => 'array',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(BackOfficeTask::class, 'task_id');
    }

    public function authorLinks(): HasMany
    {
        return $this->hasMany(BackOfficeOutputAuthor::class, 'back_office_task_output_id');
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

        return collect($this->authors ?? [])
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

        return collect($this->authors ?? [])
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

        return collect($this->book_authors ?? [])
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

        return collect($this->conferences ?? [])
            ->map(fn ($item) => is_array($item) ? $item : [])
            ->values()
            ->all();
    }
}
