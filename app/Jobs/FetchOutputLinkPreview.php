<?php

namespace App\Jobs;

use App\Services\OutputLinkPreviewCacheService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FetchOutputLinkPreview implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 2;

    public int $timeout = 45;

    public function __construct(
        public Model $output,
        public bool $force = false,
    ) {
        $this->onQueue('previews');
    }

    public function handle(OutputLinkPreviewCacheService $previews): void
    {
        $previews->cacheForModel($this->output, true);
    }
}
