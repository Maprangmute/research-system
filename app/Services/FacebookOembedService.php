<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class FacebookOembedService
{
    public function fetchPost(string $url): array
    {
        $accessToken = $this->accessToken();
        $version = trim((string) config('services.facebook_oembed.graph_version', 'v24.0')) ?: 'v24.0';
        $timeout = (int) config('services.facebook_oembed.timeout', 15);

        try {
            return Http::timeout($timeout)
                ->retry(2, 350)
                ->get("https://graph.facebook.com/{$version}/oembed_post", [
                    'url' => $url,
                    'access_token' => $accessToken,
                    'omitscript' => 'true',
                ])
                ->throw()
                ->json();
        } catch (RequestException $exception) {
            $message = $exception->response?->json('error.message')
                ?: $exception->response?->body()
                ?: $exception->getMessage();

            throw new RuntimeException(trim((string) $message), previous: $exception);
        }
    }

    public function accessToken(): string
    {
        $token = trim((string) config('services.facebook_oembed.access_token', ''));
        if ($token !== '') {
            return $token;
        }

        $appId = trim((string) config('services.facebook_oembed.app_id', ''));
        $clientToken = trim((string) config('services.facebook_oembed.client_token', ''));
        if ($appId !== '' && $clientToken !== '') {
            return $appId . '|' . $clientToken;
        }

        throw new RuntimeException('Missing FACEBOOK_OEMBED_ACCESS_TOKEN or FACEBOOK_OEMBED_APP_ID/FACEBOOK_OEMBED_CLIENT_TOKEN.');
    }
}
