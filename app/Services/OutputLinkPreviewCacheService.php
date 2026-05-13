<?php

namespace App\Services;

use App\Models\BackOfficeTaskOutput;
use App\Models\ExternalOutput;
use App\Models\Output;
use App\Models\OutputLinkPreview;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Throwable;

class OutputLinkPreviewCacheService
{
    public function __construct(
        private readonly MicrolinkPreviewService $microlink,
        private readonly OpenGraphPreviewService $openGraph,
        private readonly ScreenshotPreviewService $screenshots,
    ) {
    }

    public function cacheForModel(Model $output, bool $force = false): ?OutputLinkPreview
    {
        if (! $this->isSupportedModel($output) || ! $this->isCandidate($output)) {
            return null;
        }

        if (! $force && ! $this->shouldRefreshForSavedModel($output)) {
            return null;
        }

        $url = $this->firstUrl($this->detailText($output));
        if (! $url || ! filter_var(OutputLinkPreview::normalizeUrlForHash($url), FILTER_VALIDATE_URL)) {
            return null;
        }

        $hash = OutputLinkPreview::hashUrl($url);
        $existing = OutputLinkPreview::query()->where('url_hash', $hash)->first();
        if ($existing && $existing->fetch_status === 'ok' && ! $force) {
            $this->attachSubjectIfMissing($existing, $output);

            return $existing;
        }

        try {
            $attributes = $this->fetchAttributes($url, (string) ($output->title ?? ''));

            return OutputLinkPreview::query()->updateOrCreate(
                ['url_hash' => $hash],
                array_merge($this->subjectAttributes($output), [
                    'url' => $url,
                    'fetch_status' => 'ok',
                    'error_message' => null,
                    'fetched_at' => now(),
                ], $attributes)
            );
        } catch (Throwable $exception) {
            return OutputLinkPreview::query()->updateOrCreate(
                ['url_hash' => $hash],
                array_merge($this->subjectAttributes($output), [
                    'url' => $url,
                    'provider' => 'auto_preview',
                    'fetch_status' => 'failed',
                    'error_message' => Str::limit($exception->getMessage(), 1000, ''),
                    'fetched_at' => now(),
                ])
            );
        }
    }

    public function shouldQueueForSavedModel(Model $output): bool
    {
        if (! $this->isSupportedModel($output) || ! $this->isCandidate($output)) {
            return false;
        }

        if (! $this->shouldRefreshForSavedModel($output)) {
            return false;
        }

        $url = $this->firstUrl($this->detailText($output));
        if (! $url || ! filter_var(OutputLinkPreview::normalizeUrlForHash($url), FILTER_VALIDATE_URL)) {
            return false;
        }

        return true;
    }

    public function scheduleForSavedModel(Model $output): void
    {
        if (! $this->shouldQueueForSavedModel($output)) {
            return;
        }

        $class = $output::class;
        $key = $output->getKey();

        app()->terminating(function () use ($class, $key): void {
            /** @var Model|null $freshOutput */
            $freshOutput = $class::query()->find($key);

            if ($freshOutput) {
                $this->cacheForModel($freshOutput, true);
            }
        });
    }

    public function isCandidate(Model $output): bool
    {
        $type = strtoupper(trim((string) ($output->type ?? '')));
        $title = Str::of((string) ($output->title ?? ''))->trim()->lower()->toString();

        if (Str::startsWith($type, ['EA114', 'EA115', 'EA117'])) {
            return true;
        }

        return Str::startsWith($type, 'EA108')
            && Str::startsWith($title, ['news:', 'article:']);
    }

    public function firstUrl(string $text): ?string
    {
        if (preg_match('/https?:\/\/[^\s<>"\'\]\)]+/i', $text, $matches) !== 1) {
            return null;
        }

        return trim((string) ($matches[0] ?? '')) ?: null;
    }

    private function fetchAttributes(string $url, string $fallbackTitle): array
    {
        if ($driveThumbnailUrl = $this->googleDriveThumbnailUrl($url)) {
            return [
                'provider' => 'google_drive',
                'oembed_type' => 'drive_thumbnail',
                'title' => $fallbackTitle ?: null,
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
        }

        if ($youtubeThumbnailUrl = $this->youtubeThumbnailUrl($url)) {
            return [
                'provider' => 'youtube_thumbnail',
                'oembed_type' => 'youtube_thumbnail',
                'title' => $fallbackTitle ?: null,
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
        }

        try {
            $openGraph = $this->openGraph->fetch($url);
            $imageUrl = $openGraph['image'] ?? null;

            if ($imageUrl) {
                $provider = $this->isFacebookUrl($url) ? 'facebook_og' : 'open_graph';
                $providerName = $this->providerNameForUrl($url, $provider);

                if ($this->isGoogleSitesImageUrl((string) $imageUrl)) {
                    $provider = 'google_sites_image';
                    $providerName = 'Google Sites Image';
                }

                return [
                    'provider' => $provider,
                    'oembed_type' => $provider === 'google_sites_image' ? 'google_sites_image' : 'open_graph',
                    'title' => $openGraph['title'] ?? $fallbackTitle ?: null,
                    'description' => $openGraph['description'] ?? null,
                    'embed_html' => null,
                    'preview_image_url' => $imageUrl,
                    'thumbnail_url' => $imageUrl,
                    'thumbnail_width' => null,
                    'thumbnail_height' => null,
                    'author_name' => null,
                    'author_url' => null,
                    'provider_name' => $providerName,
                    'provider_url' => parse_url($url, PHP_URL_SCHEME) . '://' . parse_url($url, PHP_URL_HOST) . '/',
                    'raw_payload' => $this->compactPayload($provider, $url, [
                        'image_url' => $imageUrl,
                    ]),
                ];
            }
        } catch (Throwable) {
            // Fall through to Microlink/ScreenshotOne fallback providers.
        }

        try {
            $payload = $this->microlink->fetch($url);
            $data = is_array($payload['data'] ?? null) ? $payload['data'] : [];
            $imageUrl = $data['image']['url'] ?? null;
            $screenshotUrl = $data['screenshot']['url'] ?? null;

            if ($imageUrl && $this->isGoogleSitesImageUrl((string) $imageUrl)) {
                return [
                    'provider' => 'google_sites_image',
                    'oembed_type' => 'google_sites_image',
                    'title' => $data['title'] ?? $fallbackTitle ?: null,
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
                ];
            }

            if (! $imageUrl && ! $screenshotUrl) {
                throw new \RuntimeException('Preview image not found.');
            }

            return [
                'provider' => 'microlink',
                'oembed_type' => 'link_preview',
                'title' => $data['title'] ?? $fallbackTitle ?: null,
                'description' => $data['description'] ?? null,
                'embed_html' => null,
                'preview_image_url' => $imageUrl,
                'thumbnail_url' => $screenshotUrl ?? $imageUrl,
                'thumbnail_width' => $data['image']['width'] ?? null,
                'thumbnail_height' => $data['image']['height'] ?? null,
                'author_name' => $data['author'] ?? null,
                'author_url' => null,
                'provider_name' => $data['publisher'] ?? parse_url($url, PHP_URL_HOST) ?: 'Link preview',
                'provider_url' => parse_url($url, PHP_URL_SCHEME) . '://' . parse_url($url, PHP_URL_HOST) . '/',
                'raw_payload' => $this->compactPayload('auto_preview', $url, [
                    'image_url' => $imageUrl,
                    'screenshot_url' => $screenshotUrl,
                ]),
            ];
        } catch (Throwable $previewException) {
            $screenshotUrl = $this->screenshots->screenshotUrl($url);

            return [
                'provider' => 'screenshotone',
                'oembed_type' => 'screenshot',
                'title' => $fallbackTitle ?: null,
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
                'raw_payload' => $this->compactPayload('auto_preview_fallback_screenshotone', $url, [
                    'generated_url' => $screenshotUrl,
                    'preview_error' => $previewException->getMessage(),
                ]),
            ];
        }
    }

    private function isSupportedModel(Model $output): bool
    {
        return $output instanceof Output
            || $output instanceof BackOfficeTaskOutput
            || $output instanceof ExternalOutput;
    }

    private function shouldRefreshForSavedModel(Model $output): bool
    {
        if ($output->wasRecentlyCreated) {
            return true;
        }

        return collect(array_keys($output->getChanges()))
            ->intersect(['type', 'title', 'project_output', 'note'])
            ->isNotEmpty();
    }

    private function detailText(Model $output): string
    {
        return $output instanceof ExternalOutput
            ? (string) ($output->note ?? '')
            : (string) ($output->project_output ?? '');
    }

    private function attachSubjectIfMissing(OutputLinkPreview $preview, Model $output): void
    {
        if ($preview->subject_type && $preview->subject_id) {
            return;
        }

        $preview->forceFill($this->subjectAttributes($output))->save();
    }

    private function subjectAttributes(Model $output): array
    {
        return [
            'subject_type' => $output::class,
            'subject_id' => $output->getKey(),
        ];
    }

    private function compactPayload(string $driver, string $sourceUrl, array $extra = []): array
    {
        return array_filter(array_merge([
            'driver' => $driver,
            'source_url' => $sourceUrl,
        ], $extra), fn ($value) => $value !== null && $value !== '');
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
