<?php

namespace App\Services;

use RuntimeException;

class ScreenshotPreviewService
{
    public function screenshotUrl(string $url, array $overrides = []): string
    {
        $accessKey = trim((string) config('services.screenshotone.access_key', ''));
        if ($accessKey === '') {
            throw new RuntimeException('Missing SCREENSHOTONE_ACCESS_KEY.');
        }

        $baseUrl = trim((string) config('services.screenshotone.base_url', 'https://api.screenshotone.com/take'));
        $query = [
            'access_key' => $accessKey,
            'url' => $url,
            'format' => 'jpg',
            'viewport_width' => (int) config('services.screenshotone.viewport_width', 1200),
            'viewport_height' => (int) config('services.screenshotone.viewport_height', 800),
            'device_scale_factor' => 1,
            'block_ads' => 'true',
            'block_cookie_banners' => 'true',
            'block_banners_by_heuristics' => 'false',
            'block_trackers' => 'true',
            'delay' => 0,
            'timeout' => 60,
            'response_type' => 'by_format',
            'image_quality' => 80,
        ];
        $query = array_filter(array_merge($query, $overrides), fn ($value) => $value !== null && $value !== '');

        $queryString = http_build_query($query, '', '&', PHP_QUERY_RFC3986);
        $signingKey = trim((string) config('services.screenshotone.signing_key', ''));
        if ($signingKey !== '') {
            $queryString .= '&signature=' . hash_hmac('sha256', $queryString, $signingKey);
        }

        return rtrim($baseUrl, '?') . '?' . $queryString;
    }

    public function googleSitesContentScreenshotUrl(string $url): string
    {
        return $this->screenshotUrl($url, [
            'viewport_width' => 1280,
            'viewport_height' => 900,
            'delay' => 2,
            'image_quality' => 85,
            'selector' => 'img[src*="googleusercontent.com"][src*="sitesv"]',
            'selector_algorithm' => 'clip',
            'selector_scroll_into_view' => 'true',
        ]);
    }
}
