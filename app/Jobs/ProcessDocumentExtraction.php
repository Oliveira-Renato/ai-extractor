<?php

namespace App\Jobs;

use App\Models\Document;
use App\Services\GeminiExtractionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProcessDocumentExtraction implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 120;

    public bool $failOnTimeout = true;

    public function __construct(public Document $document) {}

    public function handle(GeminiExtractionService $gemini): void
    {
        $path = Storage::disk('local')->path($this->document->file_path);
        $data = $gemini->extract($path, mime_content_type($path) ?: 'application/octet-stream');

        $this->document->update(['extracted_json' => $data, 'status' => 'processed']);
    }

    public function failed(?Throwable $exception): void
    {
        Document::query()
            ->whereKey($this->document->getKey())
            ->update(['status' => 'failed']);
    }
}
