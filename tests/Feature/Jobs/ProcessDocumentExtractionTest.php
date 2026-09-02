<?php

use App\Jobs\ProcessDocumentExtraction;
use App\Models\User;
use App\Services\GeminiExtractionService;
use Illuminate\Support\Facades\Storage;

it('marks a document as processed after extracting its data', function () {
    Storage::fake('local');
    $user = User::factory()->create();
    Storage::disk('local')->put('documents/invoice.pdf', '%PDF-1.4 test');
    $document = $user->documents()->create([
        'file_path' => 'documents/invoice.pdf',
        'original_filename' => 'invoice.pdf',
    ]);
    $gemini = Mockery::mock(GeminiExtractionService::class);
    $gemini->shouldReceive('extract')
        ->once()
        ->with(Storage::disk('local')->path($document->file_path), 'application/pdf')
        ->andReturn(['vendor_name' => 'Acme Inc.']);

    (new ProcessDocumentExtraction($document))->handle($gemini);

    expect($document->refresh())
        ->status->toBe('processed')
        ->extracted_json->toBe(['vendor_name' => 'Acme Inc.']);
});

it('marks a document as failed when the queued job fails', function () {
    $user = User::factory()->create();
    $document = $user->documents()->create([
        'file_path' => 'documents/invoice.pdf',
        'original_filename' => 'invoice.pdf',
    ]);

    (new ProcessDocumentExtraction($document))->failed(new RuntimeException('Timed out'));

    expect($document->refresh()->status)->toBe('failed');
});
