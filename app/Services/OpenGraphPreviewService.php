<?php

namespace App\Services;

use DOMDocument;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class OpenGraphPreviewService
{
    public function fetch(string $url): array
    {
        $headers = [
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
            'Accept-Language' => 'th-TH,th;q=0.9,en-US;q=0.8,en;q=0.7',
        ];
        try {
            [$response, $resolvedRequestUrl] = $this->requestHtml($url, $headers);
        } catch (Throwable $exception) {
            if ($this->isFacebookUrl($url) && ($facebookMeta = $this->extractFacebookPostMeta($url, $headers))) {
                return $facebookMeta;
            }

            throw $exception;
        }

        $html = (string) $response->body();
        if (trim($html) === '') {
            throw new RuntimeException('Empty HTML response.');
        }

        $meta = $this->extractMeta($html);
        if (empty($meta['image'])) {
            $meta['image'] = $this->extractFirstContentImage($html, $resolvedRequestUrl);
        }
        if (empty($meta['image'])) {
            $redirectUrl = $this->extractRedirectUrl($html);
            if ($redirectUrl && $redirectUrl !== $resolvedRequestUrl) {
                [$redirectResponse, $resolvedRedirectUrl] = $this->requestHtml($redirectUrl, $headers);
                $redirectHtml = (string) $redirectResponse->body();
                $meta = $this->extractMeta($redirectHtml);
                if (empty($meta['image'])) {
                    $meta['image'] = $this->extractFirstContentImage($redirectHtml, $resolvedRedirectUrl);
                }
                $meta['resolved_url'] = $resolvedRedirectUrl;
            }
        }
        if (empty($meta['image']) && $this->isFacebookUrl($url)) {
            $facebookMeta = $this->extractFacebookPostMeta($url, $headers);
            if ($facebookMeta) {
                $meta['title'] = $meta['title'] ?? ($facebookMeta['title'] ?? null);
                $meta['description'] = $meta['description'] ?? ($facebookMeta['description'] ?? null);
                $meta['image'] = $meta['image'] ?? ($facebookMeta['image'] ?? null);
            }
        } elseif ($this->isFacebookUrl($url) && empty($meta['description'])) {
            $facebookMeta = $this->extractFacebookPostMeta($url, $headers);
            if ($facebookMeta) {
                $meta['description'] = $facebookMeta['description'] ?? null;
            }
        }

        if (empty($meta['image'])) {
            throw new RuntimeException('Open Graph image not found.');
        }

        return $meta;
    }

    private function requestHtml(string $url, array $headers): array
    {
        $normalizedUrl = $this->normalizeUrlForHttp($url);
        $attempts = collect([$normalizedUrl]);
        if ($wwwUrl = $this->withWwwHost($normalizedUrl)) {
            $attempts->push($wwwUrl);
        }

        foreach ($attempts->values() as $attemptUrl) {
            if (str_starts_with(strtolower($attemptUrl), 'https://')) {
                $attempts->push(preg_replace('/^https:\/\//i', 'http://', $attemptUrl));
            }
        }

        $lastException = null;
        foreach ($attempts->unique()->values() as $attemptUrl) {
            foreach ([false, true] as $withoutVerifying) {
                if ($withoutVerifying && ! str_starts_with(strtolower($attemptUrl), 'https://')) {
                    continue;
                }

                try {
                    $request = Http::timeout(20)
                        ->withHeaders($headers)
                        ->connectTimeout(10)
                        ->withOptions([
                            'allow_redirects' => true,
                        ]);

                    if ($withoutVerifying) {
                        $request = $request->withoutVerifying();
                    }

                    return [$request->get($attemptUrl)->throw(), $attemptUrl];
                } catch (Throwable $exception) {
                    $lastException = $exception;
                }
            }
        }

        throw new RuntimeException($lastException?->getMessage() ?: 'Unable to fetch URL.');
    }

    private function normalizeUrlForHttp(string $url): string
    {
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

    private function withWwwHost(string $url): ?string
    {
        $parts = parse_url($url);
        $host = $parts['host'] ?? null;
        if (! $host || str_starts_with(strtolower($host), 'www.')) {
            return null;
        }

        $scheme = $parts['scheme'] ?? 'https';
        $user = isset($parts['user']) ? $parts['user'] . (isset($parts['pass']) ? ':' . $parts['pass'] : '') . '@' : '';
        $port = isset($parts['port']) ? ':' . $parts['port'] : '';
        $path = $parts['path'] ?? '';
        $query = isset($parts['query']) ? '?' . $parts['query'] : '';
        $fragment = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';

        return "{$scheme}://{$user}www.{$host}{$port}{$path}{$query}{$fragment}";
    }

    private function extractFirstContentImage(string $html, string $baseUrl): ?string
    {
        if (preg_match('/((?:https?:)?\/\/[^"\'>\s\)]+admin\/news_images\/[^"\'>\s\)]*\.(?:jpe?g|png|webp)(?:\?[^"\'>\s\)]*)?|\/?[^"\'>\s\)]*admin\/news_images\/[^"\'>\s\)]*\.(?:jpe?g|png|webp)(?:\?[^"\'>\s\)]*)?)/i', $html, $match) === 1) {
            return $this->absoluteUrl(html_entity_decode(trim((string) $match[1])), $baseUrl);
        }

        if (preg_match('/(?:href|src|url\()=["\']?([^"\')]*admin\/news_images\/[^"\')]*\.(?:jpe?g|png|webp)(?:\?[^"\')]*)?)["\']?/i', $html, $match) === 1) {
            return $this->absoluteUrl(html_entity_decode(trim((string) $match[1])), $baseUrl);
        }

        if (preg_match_all('/(?:href|src)=["\']([^"\']*\.(?:jpe?g|png|webp)(?:\?[^"\']*)?)["\']/i', $html, $matches) > 0) {
            foreach ($matches[1] as $candidate) {
                if ($this->isDecorativeImagePath((string) $candidate)) {
                    continue;
                }

                return $this->absoluteUrl(html_entity_decode(trim((string) $candidate)), $baseUrl);
            }
        }

        if (preg_match_all('/background-image\s*:\s*url\((["\']?)([^"\')]+)(\1)\)/i', $html, $matches) > 0) {
            foreach ($matches[2] as $candidate) {
                if ($this->isDecorativeImagePath((string) $candidate)) {
                    continue;
                }

                return $this->absoluteUrl(html_entity_decode(trim((string) $candidate)), $baseUrl);
            }
        }

        return null;
    }

    private function extractFacebookPostImage(string $url, array $headers): ?string
    {
        return $this->extractFacebookPostMeta($url, $headers)['image'] ?? null;
    }

    private function extractFacebookPostMeta(string $url, array $headers): ?array
    {
        $mobileUrl = $this->facebookMobileUrl($url);
        [$response] = $this->requestHtml($mobileUrl, array_merge($headers, [
            'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 Mobile/15E148',
        ]));

        $html = (string) $response->body();
        if (trim($html) === '') {
            return null;
        }

        $meta = $this->extractMeta($html);

        if (empty($meta['image']) && preg_match_all('/https?:\\\\?\/\\\\?\/[^"\'<>\s\\\\]+(?:scontent|fbcdn)[^"\'<>\s\\\\]+\.(?:jpe?g|png|webp)(?:\?[^"\'<>\s\\\\]+)?/iu', $html, $matches) > 0) {
            foreach ($matches[0] as $candidate) {
                $imageUrl = $this->cleanFacebookImageUrl((string) $candidate);
                $host = strtolower((string) (parse_url($imageUrl, PHP_URL_HOST) ?? ''));
                if ($imageUrl === '' || str_contains($host, 'static.xx.fbcdn.net')) {
                    continue;
                }

                $meta['image'] = $imageUrl;
                break;
            }
        }

        return empty($meta['image']) && empty($meta['description']) ? null : $meta;
    }

    private function isFacebookUrl(string $url): bool
    {
        $host = strtolower((string) (parse_url($url, PHP_URL_HOST) ?? ''));

        return str_contains($host, 'facebook.com') || str_contains($host, 'fb.watch');
    }

    private function facebookMobileUrl(string $url): string
    {
        $parts = parse_url($url);
        $path = $parts['path'] ?? '/';
        $query = isset($parts['query']) ? '?' . $parts['query'] : '';
        $fragment = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';

        return 'https://m.facebook.com' . $path . $query . $fragment;
    }

    private function cleanFacebookImageUrl(string $url): string
    {
        $url = html_entity_decode($url, ENT_QUOTES | ENT_HTML5);
        $url = str_replace(['\\/', '\\u0025', '\\u0026'], ['/', '%', '&'], $url);
        $url = preg_replace('/\\\\+/', '', $url) ?: $url;

        return trim($url);
    }

    private function isDecorativeImagePath(string $path): bool
    {
        $path = strtolower($path);

        return str_contains($path, 'favicon')
            || str_contains($path, 'apple-touch-icon')
            || str_contains($path, 'bg_footer');
    }

    private function absoluteUrl(string $url, string $baseUrl): string
    {
        if (preg_match('/^https?:\/\//iu', $url) === 1) {
            return $url;
        }

        $base = parse_url($baseUrl);
        $scheme = $base['scheme'] ?? 'https';
        $host = $base['host'] ?? '';
        $path = $base['path'] ?? '/';

        if (str_starts_with($url, '/')) {
            return $scheme . '://' . $host . $url;
        }

        $directory = rtrim((string) preg_replace('/\/[^\/]*$/', '/', $path), '/');

        return $scheme . '://' . $host . $directory . '/' . ltrim($url, '/');
    }

    private function extractRedirectUrl(string $html): ?string
    {
        if (preg_match('/<meta[^>]+http-equiv=["\']refresh["\'][^>]+content=["\'][^"\']*url=([^"\']+)["\']/iu', $html, $match) === 1) {
            return html_entity_decode(trim((string) $match[1]));
        }

        if (preg_match('/href=["\'](https?:\/\/www\.facebook\.com\/[^"\']+)["\']/iu', $html, $match) === 1) {
            return html_entity_decode(trim((string) $match[1]));
        }

        return null;
    }

    private function extractMeta(string $html): array
    {
        $previous = libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $meta = [];
        foreach ($dom->getElementsByTagName('meta') as $node) {
            $property = strtolower(trim($node->getAttribute('property') ?: $node->getAttribute('name')));
            $content = trim(html_entity_decode($node->getAttribute('content'), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            if ($property === '' || $content === '') {
                continue;
            }

            match ($property) {
                'og:title', 'twitter:title' => $meta['title'] ??= $content,
                'og:description', 'description', 'twitter:description' => $meta['description'] ??= $content,
                'og:image', 'og:image:url', 'twitter:image' => $meta['image'] ??= $content,
                'og:url' => $meta['url'] ??= $content,
                default => null,
            };
        }

        return $meta;
    }
}
