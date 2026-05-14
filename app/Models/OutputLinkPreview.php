<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class OutputLinkPreview extends Model
{
    protected $fillable = [
        'subject_type',
        'subject_id',
        'url_hash',
        'url',
        'provider',
        'oembed_type',
        'title',
        'description',
        'embed_html',
        'thumbnail_url',
        'preview_image_url',
        'preview_image_path',
        'thumbnail_width',
        'thumbnail_height',
        'author_name',
        'author_url',
        'provider_name',
        'provider_url',
        'fetch_status',
        'error_message',
        'raw_payload',
        'fetched_at',
    ];

    protected $casts = [
        'raw_payload' => 'array',
        'fetched_at' => 'datetime',
    ];

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public static function hashUrl(string $url): string
    {
        return hash('sha256', self::normalizeUrlForHash($url));
    }

    public static function hashCandidates(string $url): array
    {
        $normalized = self::normalizeUrlForHash($url);
        $candidates = [$normalized];

        $parts = parse_url($normalized);
        $host = $parts['host'] ?? null;
        if ($host) {
            $withWww = str_starts_with(strtolower($host), 'www.')
                ? preg_replace('/:\/\/www\./i', '://', $normalized, 1)
                : preg_replace('/:\/\/([^\/:@]+)/i', '://www.$1', $normalized, 1);

            if (is_string($withWww) && $withWww !== '') {
                $candidates[] = $withWww;
            }
        }

        return collect($candidates)
            ->filter()
            ->unique()
            ->map(fn (string $candidate) => hash('sha256', $candidate))
            ->values()
            ->all();
    }

    public static function normalizeUrlForHash(string $url): string
    {
        $url = trim($url);
        $parts = parse_url($url);
        $host = $parts['host'] ?? null;
        if (! $host || ! function_exists('idn_to_ascii')) {
            return $url;
        }

        $asciiHost = idn_to_ascii($host, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
        if (! $asciiHost || $asciiHost === $host) {
            return $url;
        }

        $scheme = $parts['scheme'] ?? 'https';
        $user = isset($parts['user']) ? $parts['user'] . (isset($parts['pass']) ? ':' . $parts['pass'] : '') . '@' : '';
        $port = isset($parts['port']) ? ':' . $parts['port'] : '';
        $path = $parts['path'] ?? '';
        $query = isset($parts['query']) ? '?' . $parts['query'] : '';
        $fragment = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';

        return "{$scheme}://{$user}{$asciiHost}{$port}{$path}{$query}{$fragment}";
    }
}
