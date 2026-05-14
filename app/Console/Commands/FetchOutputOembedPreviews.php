<?php

namespace App\Console\Commands;

use App\Models\BackOfficeTaskOutput;
use App\Models\ExternalOutput;
use App\Models\Output;
use App\Models\OutputLinkPreview;
use App\Services\FacebookOembedService;
use App\Services\MicrolinkPreviewService;
use App\Services\OpenGraphPreviewService;
use App\Services\OutputPreviewImageStorageService;
use App\Services\ScreenshotPreviewService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class FetchOutputOembedPreviews extends Command
{
    protected $signature = 'outputs:fetch-oembed-previews
        {--limit=50 : Maximum number of links to process}
        {--refresh : Fetch again even when a successful preview already exists}
        {--dry-run : List candidate links without calling oEmbed or saving}
        {--driver=auto : Preview driver: auto, oembed, microlink, og-image, or screenshot}
        {--host= : Process only candidate links whose host contains this value}
        {--url= : Fetch a single URL instead of scanning networking outputs}';

    protected $description = 'Fetch link previews for EA108 News/Article, EA114, EA115, and EA117 links and cache them in output_link_previews';

    public function handle(
        FacebookOembedService $oembed,
        MicrolinkPreviewService $microlink,
        OpenGraphPreviewService $openGraph,
        ScreenshotPreviewService $screenshots,
        OutputPreviewImageStorageService $imageStorage,
    ): int {
        if (! Schema::hasTable('output_link_previews')) {
            $this->error('Missing output_link_previews table. Run: php artisan migrate');

            return self::FAILURE;
        }

        $limit = max(1, (int) $this->option('limit'));
        $candidates = $this->option('url')
            ? collect([(object) [
                'subject' => null,
                'title' => 'Manual URL',
                'url' => trim((string) $this->option('url')),
            ]])
            : $this->candidateLinks();

        $candidates = $candidates
            ->filter(fn ($candidate) => filter_var(OutputLinkPreview::normalizeUrlForHash((string) ($candidate->url ?? '')), FILTER_VALIDATE_URL))
            ->filter(fn ($candidate) => $this->matchesHostFilter((string) ($candidate->url ?? '')))
            ->unique(fn ($candidate) => OutputLinkPreview::hashUrl((string) $candidate->url))
            ->take($limit)
            ->values();

        if ($candidates->isEmpty()) {
            $this->info('No EA108/EA114/EA115/EA117 links found.');

            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->table(
                ['Title', 'URL', 'Subject'],
                $candidates->map(fn ($candidate) => [
                    Str::limit((string) $candidate->title, 70),
                    $candidate->url,
                    $candidate->subject ? $candidate->subject::class . '#' . $candidate->subject->getKey() : '-',
                ])->all()
            );

            return self::SUCCESS;
        }

        $saved = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($candidates as $candidate) {
            $url = (string) $candidate->url;
            $hash = OutputLinkPreview::hashUrl($url);
            $existing = OutputLinkPreview::query()->where('url_hash', $hash)->first();

            if ($existing && $existing->fetch_status === 'ok' && ! $this->option('refresh')) {
                $skipped++;
                continue;
            }

            try {
                $driver = $this->resolveDriver();
                if ($driveThumbnailUrl = $this->googleDriveThumbnailUrl($url)) {
                    $attributes = [
                        'provider' => 'google_drive',
                        'oembed_type' => 'drive_thumbnail',
                        'title' => $candidate->title ?? null,
                        'description' => null,
                        'embed_html' => null,
                        'preview_image_url' => $driveThumbnailUrl,
                        'thumbnail_url' => $driveThumbnailUrl,
                        'thumbnail_width' => null,
                        'thumbnail_height' => null,
                        'author_name' => null,
                        'author_url' => null,
                        'provider_name' => 'Google Drive',
                        'provider_url' => 'https://drive.google.com/',
                        'raw_payload' => $this->compactPayload('google_drive_thumbnail', $url, [
                            'image_url' => $driveThumbnailUrl,
                        ]),
                    ];
                } elseif ($youtubeThumbnailUrl = $this->youtubeThumbnailUrl($url)) {
                    $attributes = [
                        'provider' => 'youtube_thumbnail',
                        'oembed_type' => 'youtube_thumbnail',
                        'title' => $candidate->title ?? null,
                        'description' => null,
                        'embed_html' => null,
                        'preview_image_url' => $youtubeThumbnailUrl,
                        'thumbnail_url' => $youtubeThumbnailUrl,
                        'thumbnail_width' => null,
                        'thumbnail_height' => null,
                        'author_name' => null,
                        'author_url' => null,
                        'provider_name' => 'YouTube',
                        'provider_url' => 'https://www.youtube.com/',
                        'raw_payload' => $this->compactPayload('youtube_thumbnail', $url, [
                            'image_url' => $youtubeThumbnailUrl,
                        ]),
                    ];
                } elseif ($driver === 'oembed') {
                    $payload = $oembed->fetchPost($url);
                    $attributes = [
                        'provider' => 'facebook',
                        'oembed_type' => $payload['type'] ?? null,
                        'title' => $payload['title'] ?? $candidate->title ?? null,
                        'description' => $payload['description'] ?? null,
                        'embed_html' => $payload['html'] ?? null,
                        'thumbnail_url' => $payload['thumbnail_url'] ?? null,
                        'thumbnail_width' => isset($payload['thumbnail_width']) ? (int) $payload['thumbnail_width'] : null,
                        'thumbnail_height' => isset($payload['thumbnail_height']) ? (int) $payload['thumbnail_height'] : null,
                        'author_name' => $payload['author_name'] ?? null,
                        'author_url' => $payload['author_url'] ?? null,
                        'provider_name' => $payload['provider_name'] ?? null,
                        'provider_url' => $payload['provider_url'] ?? null,
                        'raw_payload' => $this->compactPayload('oembed', $url, [
                            'type' => $payload['type'] ?? null,
                            'thumbnail_url' => $payload['thumbnail_url'] ?? null,
                        ]),
                    ];
                } elseif ($driver === 'microlink') {
                    try {
                        $payload = $microlink->fetch($url);
                        $data = is_array($payload['data'] ?? null) ? $payload['data'] : [];
                        $imageUrl = $data['image']['url'] ?? null;
                        $screenshotUrl = $data['screenshot']['url'] ?? null;
                        if (! $imageUrl && ! $screenshotUrl) {
                            throw new RuntimeException('Microlink image not found.');
                        }

                        $attributes = $imageUrl && $this->isGoogleSitesImageUrl((string) $imageUrl)
                            ? [
                                'provider' => 'google_sites_image',
                                'oembed_type' => 'google_sites_image',
                                'title' => $data['title'] ?? $candidate->title ?? null,
                                'description' => $data['description'] ?? null,
                                'embed_html' => null,
                                'preview_image_url' => $imageUrl,
                                'thumbnail_url' => $imageUrl,
                                'thumbnail_width' => $data['image']['width'] ?? null,
                                'thumbnail_height' => $data['image']['height'] ?? null,
                                'author_name' => $data['author'] ?? null,
                                'author_url' => null,
                                'provider_name' => 'Google Sites Image',
                                'provider_url' => parse_url($url, PHP_URL_SCHEME) . '://' . parse_url($url, PHP_URL_HOST) . '/',
                                'raw_payload' => $this->compactPayload('google_sites_image', $url, [
                                    'image_url' => $imageUrl,
                                ]),
                            ]
                            : [
                            'provider' => 'microlink',
                            'oembed_type' => 'link_preview',
                            'title' => $data['title'] ?? $candidate->title ?? null,
                            'description' => $data['description'] ?? null,
                            'embed_html' => null,
                            'preview_image_url' => $imageUrl,
                            'thumbnail_url' => $screenshotUrl ?? $imageUrl,
                            'thumbnail_width' => $data['image']['width'] ?? null,
                            'thumbnail_height' => $data['image']['height'] ?? null,
                            'author_name' => $data['author'] ?? null,
                            'author_url' => null,
                            'provider_name' => $data['publisher'] ?? 'Microlink',
                            'provider_url' => 'https://microlink.io/',
                            'raw_payload' => $this->compactPayload('microlink', $url, [
                                'image_url' => $imageUrl,
                                'screenshot_url' => $screenshotUrl,
                            ]),
                        ];
                    } catch (Throwable $microlinkException) {
                        $screenshotUrl = $screenshots->screenshotUrl($url);
                        $attributes = [
                            'provider' => 'screenshotone',
                            'oembed_type' => 'screenshot',
                            'title' => $candidate->title ?? null,
                            'description' => null,
                            'embed_html' => null,
                            'preview_image_url' => null,
                            'thumbnail_url' => $screenshotUrl,
                            'thumbnail_width' => (int) config('services.screenshotone.viewport_width', 1200),
                            'thumbnail_height' => (int) config('services.screenshotone.viewport_height', 800),
                            'author_name' => null,
                            'author_url' => null,
                            'provider_name' => 'ScreenshotOne',
                            'provider_url' => 'https://screenshotone.com/',
                            'raw_payload' => $this->compactPayload('microlink_fallback_screenshotone', $url, [
                                'generated_url' => $screenshotUrl,
                                'microlink_error' => $microlinkException->getMessage(),
                            ]),
                        ];
                    }
                } elseif ($driver === 'og-image') {
                    try {
                        $payload = $openGraph->fetch($url);
                        $imageUrl = $payload['image'] ?? null;
                        $provider = $this->isFacebookUrl($url) ? 'facebook_og' : 'open_graph';
                        $providerName = $this->providerNameForUrl($url, $provider);
                        $providerUrl = parse_url($url, PHP_URL_SCHEME) . '://' . parse_url($url, PHP_URL_HOST) . '/';

                        $attributes = $imageUrl && $this->isGoogleSitesImageUrl((string) $imageUrl)
                            ? [
                                'provider' => 'google_sites_image',
                                'oembed_type' => 'google_sites_image',
                                'title' => $payload['title'] ?? $candidate->title ?? null,
                                'description' => $payload['description'] ?? null,
                                'embed_html' => null,
                                'preview_image_url' => $imageUrl,
                                'thumbnail_url' => $imageUrl,
                                'thumbnail_width' => null,
                                'thumbnail_height' => null,
                                'author_name' => null,
                                'author_url' => null,
                                'provider_name' => 'Google Sites Image',
                                'provider_url' => parse_url($url, PHP_URL_SCHEME) . '://' . parse_url($url, PHP_URL_HOST) . '/',
                                'raw_payload' => $this->compactPayload('google_sites_image', $url, [
                                    'image_url' => $imageUrl,
                                ]),
                            ]
                            : [
                            'provider' => $provider,
                            'oembed_type' => 'open_graph',
                            'title' => $payload['title'] ?? $candidate->title ?? null,
                            'description' => $payload['description'] ?? null,
                            'embed_html' => null,
                            'preview_image_url' => $imageUrl,
                            'thumbnail_url' => $imageUrl,
                            'thumbnail_width' => null,
                            'thumbnail_height' => null,
                            'author_name' => null,
                            'author_url' => null,
                            'provider_name' => $providerName,
                            'provider_url' => $providerUrl,
                            'raw_payload' => $this->compactPayload('open_graph', $url, [
                                'image_url' => $imageUrl,
                                'original_image_url' => $payload['image'] ?? null,
                            ]),
                        ];
                    } catch (Throwable $openGraphException) {
                        $screenshotUrl = $screenshots->screenshotUrl($url);
                        $attributes = [
                            'provider' => 'screenshotone',
                            'oembed_type' => 'screenshot',
                            'title' => $candidate->title ?? null,
                            'description' => null,
                            'embed_html' => null,
                            'preview_image_url' => null,
                            'thumbnail_url' => $screenshotUrl,
                            'thumbnail_width' => (int) config('services.screenshotone.viewport_width', 1200),
                            'thumbnail_height' => (int) config('services.screenshotone.viewport_height', 800),
                            'author_name' => null,
                            'author_url' => null,
                            'provider_name' => 'ScreenshotOne',
                            'provider_url' => 'https://screenshotone.com/',
                            'raw_payload' => $this->compactPayload('open_graph_fallback_screenshotone', $url, [
                                'generated_url' => $screenshotUrl,
                                'open_graph_error' => $openGraphException->getMessage(),
                            ]),
                        ];
                    }
                } else {
                    $screenshotUrl = $screenshots->screenshotUrl($url);
                    $attributes = [
                        'provider' => 'screenshotone',
                        'oembed_type' => 'screenshot',
                        'title' => $candidate->title ?? null,
                        'description' => null,
                        'embed_html' => null,
                        'thumbnail_url' => $screenshotUrl,
                        'thumbnail_width' => (int) config('services.screenshotone.viewport_width', 1200),
                        'thumbnail_height' => (int) config('services.screenshotone.viewport_height', 800),
                        'author_name' => null,
                        'author_url' => null,
                        'provider_name' => 'ScreenshotOne',
                        'provider_url' => 'https://screenshotone.com/',
                        'raw_payload' => $this->compactPayload('screenshotone', $url, [
                            'generated_url' => $screenshotUrl,
                        ]),
                    ];
                }

                $attributes = array_merge($attributes, $this->storedImageAttributes($attributes, $url, $hash, $imageStorage));

                $preview = OutputLinkPreview::query()->updateOrCreate(
                    ['url_hash' => $hash],
                    array_merge($this->subjectAttributes($candidate->subject), [
                        'url' => $url,
                        'fetch_status' => 'ok',
                        'error_message' => null,
                        'fetched_at' => now(),
                    ], $attributes)
                );

                $saved++;
                $this->line('Saved preview #' . $preview->id . ': ' . Str::limit($url, 92));
            } catch (Throwable $exception) {
                OutputLinkPreview::query()->updateOrCreate(
                    ['url_hash' => $hash],
                    array_merge($this->subjectAttributes($candidate->subject), [
                        'url' => $url,
                        'provider' => 'facebook',
                        'fetch_status' => 'failed',
                        'error_message' => Str::limit($exception->getMessage(), 1000, ''),
                        'fetched_at' => now(),
                    ])
                );

                $failed++;
                $this->warn('Failed: ' . Str::limit($url, 80) . ' :: ' . $exception->getMessage());
            }
        }

        $this->info("Done. saved={$saved}, skipped={$skipped}, failed={$failed}");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function resolveDriver(): string
    {
        $driver = strtolower(trim((string) $this->option('driver')));
        if (! in_array($driver, ['auto', 'oembed', 'microlink', 'og-image', 'screenshot'], true)) {
            return 'auto';
        }

        if ($driver === 'auto') {
            $hasOembedToken = trim((string) config('services.facebook_oembed.access_token', '')) !== ''
                || (
                    trim((string) config('services.facebook_oembed.app_id', '')) !== ''
                    && trim((string) config('services.facebook_oembed.client_token', '')) !== ''
                );

            return $hasOembedToken ? 'oembed' : 'screenshot';
        }

        return $driver;
    }

    private function matchesHostFilter(string $url): bool
    {
        $hostFilter = Str::lower(trim((string) $this->option('host')));
        if ($hostFilter === '') {
            return true;
        }

        $host = Str::lower((string) parse_url(OutputLinkPreview::normalizeUrlForHash($url), PHP_URL_HOST));

        return $host !== '' && Str::contains($host, $hostFilter);
    }

    private function candidateLinks(): Collection
    {
        return collect()
            ->merge($this->outputsFrom(Output::query()->get(), 'project_output'))
            ->merge($this->outputsFrom(BackOfficeTaskOutput::query()->get(), 'project_output'))
            ->merge($this->outputsFrom(ExternalOutput::query()->get(), 'note'))
            ->values();
    }

    private function outputsFrom(Collection $outputs, string $detailField): Collection
    {
        return $outputs
            ->filter(fn (Model $output) => $this->isNetworkingPreviewCandidate($output))
            ->map(function (Model $output) use ($detailField) {
                $detail = (string) ($output->{$detailField} ?? '');

                return (object) [
                    'subject' => $output,
                    'title' => (string) ($output->title ?? ''),
                    'url' => $this->firstUrl($detail),
                ];
            })
            ->filter(fn ($candidate) => $candidate->url !== null)
            ->values();
    }

    private function isNetworkingPreviewCandidate(Model $output): bool
    {
        $type = strtoupper(trim((string) ($output->type ?? '')));
        $title = Str::of((string) ($output->title ?? ''))->trim()->lower()->toString();

        if (Str::startsWith($type, ['EA114', 'EA115', 'EA117'])) {
            return true;
        }

        return Str::startsWith($type, 'EA108')
            && Str::startsWith($title, ['news:', 'article:']);
    }

    private function firstUrl(string $text): ?string
    {
        if (preg_match('/https?:\/\/[^\s<>"\'\]\)]+/iu', $text, $matches) !== 1) {
            return null;
        }

        return trim((string) ($matches[0] ?? '')) ?: null;
    }

    private function subjectAttributes(?Model $subject): array
    {
        if (! $subject) {
            return [
                'subject_type' => null,
                'subject_id' => null,
            ];
        }

        return [
            'subject_type' => $subject::class,
            'subject_id' => $subject->getKey(),
        ];
    }

    private function compactPayload(string $driver, string $sourceUrl, array $extra = []): array
    {
        return array_filter(array_merge([
            'driver' => $driver,
            'source_url' => $sourceUrl,
        ], $extra), fn ($value) => $value !== null && $value !== '');
    }

    private function storedImageAttributes(array $attributes, string $sourceUrl, string $urlHash, OutputPreviewImageStorageService $imageStorage): array
    {
        if (! Schema::hasColumn('output_link_previews', 'preview_image_path')) {
            return [];
        }

        $imageUrl = trim((string) (($attributes['preview_image_url'] ?? null) ?: ($attributes['thumbnail_url'] ?? null)));
        $path = $imageStorage->storeFromUrl($imageUrl, $sourceUrl, $urlHash);

        return $path ? ['preview_image_path' => $path] : [];
    }

    private function googleDriveThumbnailUrl(string $url): ?string
    {
        $host = strtolower((string) (parse_url($url, PHP_URL_HOST) ?? ''));
        if (! str_contains($host, 'drive.google.com')) {
            return null;
        }

        $fileId = null;
        if (preg_match('/drive\.google\.com\/file\/d\/([^\/\?]+)/i', $url, $matches) === 1) {
            $fileId = $matches[1];
        } elseif (preg_match('/[?&]id=([^&]+)/i', $url, $matches) === 1) {
            $fileId = $matches[1];
        }

        if (! $fileId) {
            return null;
        }

        return 'https://drive.google.com/thumbnail?id=' . rawurlencode($fileId) . '&sz=w1200';
    }

    private function youtubeThumbnailUrl(string $url): ?string
    {
        $host = strtolower((string) (parse_url($url, PHP_URL_HOST) ?? ''));
        $videoId = null;

        if (str_contains($host, 'youtu.be')) {
            $path = trim((string) (parse_url($url, PHP_URL_PATH) ?? ''), '/');
            $videoId = $path !== '' ? explode('/', $path)[0] : null;
        } elseif (str_contains($host, 'youtube.com')) {
            parse_str((string) (parse_url($url, PHP_URL_QUERY) ?? ''), $query);
            $videoId = isset($query['v']) ? (string) $query['v'] : null;
        }

        if (! $videoId || ! preg_match('/^[A-Za-z0-9_-]{6,}$/', $videoId)) {
            return null;
        }

        return 'https://i.ytimg.com/vi/' . rawurlencode($videoId) . '/hqdefault.jpg';
    }

    private function isGoogleSitesImageUrl(string $url): bool
    {
        $host = strtolower((string) (parse_url($url, PHP_URL_HOST) ?? ''));

        return str_contains($host, 'googleusercontent.com')
            && str_contains($url, '/sitesv/');
    }

    private function isFacebookUrl(string $url): bool
    {
        $host = strtolower((string) (parse_url($url, PHP_URL_HOST) ?? ''));

        return str_contains($host, 'facebook.com') || str_contains($host, 'fb.watch');
    }

    private function providerNameForUrl(string $url, string $fallback): string
    {
        $host = (string) (parse_url($url, PHP_URL_HOST) ?: '');

        return $host !== '' ? $host : $fallback;
    }

}
