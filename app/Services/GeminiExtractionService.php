<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiExtractionService
{
    public function extract(string $path, string $mimeType): array
    {
        $key = config('services.gemini.key');
        if (! $key) {
            throw new RuntimeException('GEMINI_API_KEY não configurada.');
        }

        $response = $this->client($key)->post('/v1beta/models/'.config('services.gemini.model').':generateContent', [
            'contents' => [[
                'parts' => [
                    ['text' => 'Extraia os dados desta nota fiscal. Retorne somente os campos solicitados. Use null quando um campo não estiver legível.'],
                    ['inline_data' => ['mime_type' => $mimeType, 'data' => base64_encode((string) file_get_contents($path))]],
                ],
            ]],
            'generationConfig' => [
                'responseMimeType' => 'application/json',
                'responseSchema' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'vendor_name' => ['type' => 'STRING', 'nullable' => true],
                        'total_amount' => ['type' => 'NUMBER', 'nullable' => true],
                        'date' => ['type' => 'STRING', 'nullable' => true],
                        'invoice_number' => ['type' => 'STRING', 'nullable' => true],
                        'line_items' => ['type' => 'ARRAY', 'items' => ['type' => 'OBJECT', 'properties' => [
                            'description' => ['type' => 'STRING'],
                            'quantity' => ['type' => 'NUMBER'],
                            'price' => ['type' => 'NUMBER'],
                        ]]],
                    ],
                ],
            ],
        ])->throw();

        $text = data_get($response->json(), 'candidates.0.content.parts.0.text');
        if (! is_string($text)) {
            throw new RuntimeException('A API do Gemini não retornou dados estruturados.');
        }

        return json_decode($text, true, 512, JSON_THROW_ON_ERROR);
    }

    private function client(string $key): PendingRequest
    {
        return Http::baseUrl(config('services.gemini.url'))
            ->acceptJson()
            ->withQueryParameters(['key' => $key])
            ->timeout(90);
    }
}