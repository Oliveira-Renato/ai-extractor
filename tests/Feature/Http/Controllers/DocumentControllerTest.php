<?php

use App\Jobs\ProcessDocumentExtraction;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

it('stores an uploaded document and starts extraction in the background', function () {
    Storage::fake('local');
    Queue::fake([ProcessDocumentExtraction::class]);
    config()->set('services.document_extraction.queue_connection', 'background');
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('documents.store'), [
        'file' => UploadedFile::fake()->create('invoice.pdf', 100, 'application/pdf'),
    ]);

    $document = $user->documents()->sole();
    $response->assertRedirect(route('documents.show', $document));
    $this->assertDatabaseHas('documents', [
        'id' => $document->id,
        'original_filename' => 'invoice.pdf',
        'status' => 'pending',
    ]);
    Storage::disk('local')->assertExists($document->file_path);
    Queue::assertPushed(ProcessDocumentExtraction::class, fn (ProcessDocumentExtraction $job): bool => $job->document->is($document) && $job->connection === 'background'
    );
});

it('serves a PDF preview inline without forcing a download', function () {
    Storage::fake('local');
    $user = User::factory()->create();
    Storage::disk('local')->put('documents/invoice.pdf', '%PDF-1.4 test');
    $document = $user->documents()->create([
        'file_path' => 'documents/invoice.pdf',
        'original_filename' => 'invoice.pdf',
    ]);

    $response = $this->actingAs($user)->get(route('documents.preview', $document));

    $response->assertOk()->assertHeader('content-type', 'application/pdf');
    expect($response->headers->get('content-disposition'))->toStartWith('inline');
});

it('does not reveal another users document preview', function () {
    Storage::fake('local');
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    Storage::disk('local')->put('documents/invoice.pdf', '%PDF-1.4 test');
    $document = $owner->documents()->create([
        'file_path' => 'documents/invoice.pdf',
        'original_filename' => 'invoice.pdf',
    ]);

    $this->actingAs($otherUser)
        ->get(route('documents.preview', $document))
        ->assertNotFound();
});
