<?php

namespace App\Console\Commands;

use App\Models\OutputLinkPreview;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class SetOutputPreviewImageUrl extends Command
{
    protected $signature = 'outputs:set-preview-image-url
        {preview : Preview id, subject id, or source URL}
        {image_url : Public image URL to use as the preview image}
        {--subject-type=App\\Models\\Output : Subject model class when preview is a subject id}';

    protected $description = 'Set a public preview image URL for a cached output link preview without uploading the image';

    public function handle(): int
    {
        if (! Schema::hasTable('output_link_previews')) {
            $this->error('Missing output_link_previews table. Run: php artisan migrate');

            return self::FAILURE;
        }

        $imageUrl = trim((string) $this->argument('image_url'));
        if (! filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            $this->error('image_url must be a valid public URL.');

            return self::FAILURE;
        }

        $preview = $this->resolvePreview((string) $this->argument('preview'));
        if (! $preview) {
            $this->error('Preview not found. Run outputs:fetch-oembed-previews first or pass an existing source URL.');

            return self::FAILURE;
        }

        $preview->forceFill([
            'preview_image_url' => $imageUrl,
        ])->save();

        $this->info('Updated preview #' . $preview->id . ' image URL.');

        return self::SUCCESS;
    }

    private function resolvePreview(string $value): ?OutputLinkPreview
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return OutputLinkPreview::query()
                ->where('url_hash', OutputLinkPreview::hashUrl($value))
                ->first();
        }

        if (ctype_digit($value)) {
            return OutputLinkPreview::query()->find((int) $value)
                ?: OutputLinkPreview::query()
                    ->where('subject_type', trim((string) $this->option('subject-type')))
                    ->where('subject_id', (int) $value)
                    ->first();
        }

        return null;
    }
}
