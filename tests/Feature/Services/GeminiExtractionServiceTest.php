<?php

use App\Services\GeminiExtractionService;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

it('requests comprehensive structured invoice data', function () {
    config()->set('services.gemini', [
        'key' => 'test-key',
        'model' => 'gemini-test',
        'url' => 'https://api.test',
    ]);
    Http::preventStrayRequests();
    Http::fake([
        'https://api.test/v1beta/models/gemini-test:generateContent*' => Http::response([
            'candidates' => [[
                'content' => [
                    'parts' => [[
                        'text' => json_encode([
                            'invoice_number' => 'IN-76924332',
                            'currency' => 'USD',
                            'vendor' => ['name' => 'Cloudflare, Inc.'],
                            'customer' => ['name' => 'Renato Oliveira'],
                            'line_items' => [['description' => 'Registrar Registration Fee']],
                            'bank_details' => ['bank_name' => 'Wells Fargo'],
                        ], JSON_THROW_ON_ERROR),
                    ]],
                ],
            ]],
        ]),
    ]);
    $invoice = UploadedFile::fake()->createWithContent('invoice.pdf', '%PDF-1.4 test');

    $result = app(GeminiExtractionService::class)->extract($invoice->getPathname(), 'application/pdf');

    expect($result)
        ->invoice_number->toBe('IN-76924332')
        ->currency->toBe('USD')
        ->vendor->name->toBe('Cloudflare, Inc.')
        ->customer->name->toBe('Renato Oliveira')
        ->line_items->toHaveCount(1)
        ->bank_details->bank_name->toBe('Wells Fargo');
    Http::assertSent(function (Request $request): bool {
        $schema = data_get($request->data(), 'generationConfig.responseSchema');

        return $request->method() === 'POST'
            && data_get($request->data(), 'generationConfig.temperature') === 0.1
            && str_contains(data_get($request->data(), 'contents.0.parts.0.text'), 'Normalize dates to YYYY-MM-DD')
            && array_keys($schema['properties']) === [
                'document_type',
                'invoice_number',
                'purchase_order_number',
                'issue_date',
                'due_date',
                'currency',
                'payment_status',
                'payment_terms',
                'subtotal',
                'discount_amount',
                'tax_amount',
                'shipping_amount',
                'total_amount',
                'amount_paid',
                'amount_due',
                'vendor',
                'customer',
                'line_items',
                'payment_instructions',
                'bank_details',
                'notes',
            ]
            && array_keys($schema['properties']['line_items']['items']['properties']) === [
                'description',
                'sku',
                'quantity',
                'unit',
                'unit_price',
                'discount_amount',
                'tax_rate',
                'tax_amount',
                'amount',
                'service_period_start',
                'service_period_end',
            ];
    });
});

it('rejects extraction when the API key is missing', function () {
    config()->set('services.gemini.key');
    $invoice = UploadedFile::fake()->createWithContent('invoice.pdf', '%PDF-1.4 test');

    app(GeminiExtractionService::class)->extract($invoice->getPathname(), 'application/pdf');
})->throws(RuntimeException::class, 'GEMINI_API_KEY is not configured.');
