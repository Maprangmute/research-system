<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class MicrolinkPreviewService
{
    public function fetch(string $url): array
    {
        try {
            return Http::timeout(25)
                ->retry(2, 350)
                ->get('https://api.microlink.io/', [
                    'url' => $url,
                    'screenshot' => 'true',
                    'meta' => 'true',
                ])
                ->throw()
                ->json();
        } catch (RequestException $exception) {
            $message = $exception->response?->json('message')
                ?: $exception->response?->body()
                ?: $exception->getMessage();

            throw new RuntimeException(trim((string) $message), previous: $exception);
        }
    }
}
