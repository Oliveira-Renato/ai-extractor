<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessDocumentExtraction;
use App\Models\Document;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DocumentController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $request->validate(['file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:20480']]);
        $file = $request->file('file');
        $document = $request->user()->documents()->create([
            'file_path' => $file->store('documents'),
            'original_filename' => $file->getClientOriginalName(),
        ]);

        ProcessDocumentExtraction::dispatch($document)
            ->onConnection(config('services.document_extraction.queue_connection'));

        return to_route('documents.show', $document)->with('success', 'File uploaded. Extraction has started.');
    }

    public function show(Request $request, Document $document): Response
    {
        abort_unless($document->user_id === $request->user()->id, 404);

        return Inertia::render('Documents/Show', ['document' => $document]);
    }

    public function preview(Request $request, Document $document): BinaryFileResponse
    {
        abort_unless($document->user_id === $request->user()->id, 404);

        return response()->file(
            Storage::disk('local')->path($document->file_path),
            ['Content-Disposition' => 'inline'],
        );
    }
}
