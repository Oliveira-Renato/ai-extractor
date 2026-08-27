<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessDocumentExtraction;
use App\Models\Document;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

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

        ProcessDocumentExtraction::dispatch($document);

        return to_route('documents.show', $document)->with('success', 'Arquivo enviado. A extração começou.');
    }

    public function show(Request $request, Document $document): Response
    {
        abort_unless($document->user_id === $request->user()->id, 404);
        return Inertia::render('Documents/Show', ['document' => $document]);
    }

    public function download(Request $request, Document $document): mixed
    {
        abort_unless($document->user_id === $request->user()->id, 404);
        return response()->download(
            Storage::disk('local')->path($document->file_path),
            $document->original_filename,
        );
    }
}