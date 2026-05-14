<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class OutputPreviewImageStorageService
{
    public function storeFromUrl(?string $imageUrl, string $sourceUrl, string $urlHash): ?string
    {
        $imageUrl = trim((string) $imageUrl);
        if ($imageUrl === '' || ! filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            return null;
        }

        try {
            $response = Http::timeout(30)
                ->connectTimeout(12)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36',
                    'Accept' => 'image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8',
                    'Referer' => $sourceUrl ?: 'https://www.google.com/',
                ])
                ->get($imageUrl);
        } catch (Throwable) {
            return null;
        }

        $contentType = strtolower((string) $response->header('Content-Type'));
        $body = (string) $response->body();
        if (! $response->successful() || ! str_starts_with($contentType, 'image/') || strlen($body) < 1000) {
            return null;
        }

        $extension = $this->extensionFor($contentType, $imageUrl);
        $path = 'output-previews/' . $urlHash . '-' . substr(sha1($imageUrl), 0, 12) . '.' . $extension;

        Storage::disk('public')->put($path, $body);

        return $path;
    }

    private function extensionFor(string $contentType, string $imageUrl): string
    {
        return match (true) {
            str_contains($contentType, 'png') => 'png',
            str_contains($contentType, 'webp') => 'webp',
            str_contains($contentType, 'gif') => 'gif',
            str_contains($contentType, 'svg') => 'svg',
            default => $this->extensionFromUrl($imageUrl) ?: 'jpg',
        };
    }

    private function extensionFromUrl(string $imageUrl): ?string
    {
        $path = strtolower((string) parse_url($imageUrl, PHP_URL_PATH));
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        return in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'], true)
            ? ($extension === 'jpeg' ? 'jpg' : $extension)
            : null;
    }
}
