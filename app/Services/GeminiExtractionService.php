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
            throw new RuntimeException('GEMINI_API_KEY is not configured.');
        }

        $response = $this->client($key)->post('/v1beta/models/'.config('services.gemini.model').':generateContent', [
            'contents' => [[
                'parts' => [
                    ['text' => implode(' ', [
                        'Extract all invoice data that is explicitly visible in the document.',
                        'Do not infer or calculate missing values.',
                        'Use null for unavailable scalar values and empty arrays when no list entries are present.',
                        'Normalize dates to YYYY-MM-DD, currency to an ISO 4217 code, and amounts to numbers without currency symbols.',
                        'Keep names, identifiers, addresses, references, and descriptions faithful to the source.',
                    ])],
                    ['inline_data' => ['mime_type' => $mimeType, 'data' => base64_encode((string) file_get_contents($path))]],
                ],
            ]],
            'generationConfig' => [
                'responseMimeType' => 'application/json',
                'responseSchema' => $this->responseSchema(),
                'temperature' => 0.1,
            ],
        ])->throw();

        $text = data_get($response->json(), 'candidates.0.content.parts.0.text');
        if (! is_string($text)) {
            throw new RuntimeException('The Gemini API did not return structured data.');
        }

        return json_decode($text, true, 512, JSON_THROW_ON_ERROR);
    }

    private function client(string $key): PendingRequest
    {
        return Http::baseUrl(config('services.gemini.url'))
            ->acceptJson()
            ->withQueryParameters(['key' => $key])
            ->connectTimeout(10)
            ->timeout(90);
    }

    /**
     * @return array<string, mixed>
     */
    private function responseSchema(): array
    {
        $properties = [
            'document_type' => $this->nullableString('Document type, such as invoice, receipt, or credit note.'),
            'invoice_number' => $this->nullableString('Invoice or document identifier.'),
            'purchase_order_number' => $this->nullableString('Purchase order number.'),
            'issue_date' => $this->nullableString('Issue date normalized to YYYY-MM-DD.'),
            'due_date' => $this->nullableString('Payment due date normalized to YYYY-MM-DD.'),
            'currency' => $this->nullableString('Three-letter ISO 4217 currency code.'),
            'payment_status' => $this->nullableString('Payment status explicitly stated by the document.'),
            'payment_terms' => $this->nullableString('Payment terms or due-period wording.'),
            'subtotal' => $this->nullableNumber('Subtotal before discounts, taxes, and shipping.'),
            'discount_amount' => $this->nullableNumber('Document-level discount amount.'),
            'tax_amount' => $this->nullableNumber('Total tax amount.'),
            'shipping_amount' => $this->nullableNumber('Shipping or delivery amount.'),
            'total_amount' => $this->nullableNumber('Final invoice total.'),
            'amount_paid' => $this->nullableNumber('Amount already paid.'),
            'amount_due' => $this->nullableNumber('Outstanding amount due.'),
            'vendor' => $this->partySchema(),
            'customer' => $this->partySchema(),
            'line_items' => [
                'type' => 'ARRAY',
                'description' => 'Every billed product, service, fee, or charge.',
                'items' => $this->lineItemSchema(),
            ],
            'payment_instructions' => $this->nullableString('Payment method instructions shown on the document.'),
            'bank_details' => $this->bankDetailsSchema(),
            'notes' => [
                'type' => 'ARRAY',
                'description' => 'Additional notes, support instructions, or legal text relevant to the invoice.',
                'items' => ['type' => 'STRING'],
            ],
        ];

        return [
            'type' => 'OBJECT',
            'properties' => $properties,
            'required' => array_keys($properties),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function partySchema(): array
    {
        $properties = [
            'name' => $this->nullableString('Display or contact name.'),
            'company_name' => $this->nullableString('Legal or company name.'),
            'email' => $this->nullableString('Email address.'),
            'phone' => $this->nullableString('Phone number.'),
            'website' => $this->nullableString('Website address.'),
            'tax_id' => $this->nullableString('Tax, VAT, EIN, or business registration identifier, including its label when shown.'),
            'address' => $this->addressSchema(),
        ];

        return [
            'type' => 'OBJECT',
            'nullable' => true,
            'properties' => $properties,
            'required' => array_keys($properties),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function addressSchema(): array
    {
        $properties = [
            'line_1' => $this->nullableString('First street-address line.'),
            'line_2' => $this->nullableString('Second street-address line.'),
            'city' => $this->nullableString('City or locality.'),
            'state' => $this->nullableString('State, province, or region.'),
            'postal_code' => $this->nullableString('Postal or ZIP code.'),
            'country' => $this->nullableString('Country name or code.'),
        ];

        return [
            'type' => 'OBJECT',
            'nullable' => true,
            'properties' => $properties,
            'required' => array_keys($properties),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function lineItemSchema(): array
    {
        $properties = [
            'description' => $this->nullableString('Complete item description.'),
            'sku' => $this->nullableString('SKU, product code, or item identifier.'),
            'quantity' => $this->nullableNumber('Quantity billed.'),
            'unit' => $this->nullableString('Unit of measure.'),
            'unit_price' => $this->nullableNumber('Price per unit.'),
            'discount_amount' => $this->nullableNumber('Line-level discount amount.'),
            'tax_rate' => $this->nullableNumber('Line-level tax percentage.'),
            'tax_amount' => $this->nullableNumber('Line-level tax amount.'),
            'amount' => $this->nullableNumber('Final line amount.'),
            'service_period_start' => $this->nullableString('Service-period start date normalized to YYYY-MM-DD.'),
            'service_period_end' => $this->nullableString('Service-period end date normalized to YYYY-MM-DD.'),
        ];

        return [
            'type' => 'OBJECT',
            'properties' => $properties,
            'required' => array_keys($properties),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function bankDetailsSchema(): array
    {
        $properties = [
            'account_holder' => $this->nullableString('Bank account holder.'),
            'bank_name' => $this->nullableString('Bank name.'),
            'routing_number' => $this->nullableString('Routing or sort-code identifier.'),
            'account_number' => $this->nullableString('Bank account number.'),
            'iban' => $this->nullableString('IBAN.'),
            'swift_bic' => $this->nullableString('SWIFT or BIC code.'),
            'payment_reference' => $this->nullableString('Reference required with payment.'),
        ];

        return [
            'type' => 'OBJECT',
            'nullable' => true,
            'properties' => $properties,
            'required' => array_keys($properties),
        ];
    }

    /**
     * @return array{type: string, nullable: true, description: string}
     */
    private function nullableString(string $description): array
    {
        return ['type' => 'STRING', 'nullable' => true, 'description' => $description];
    }

    /**
     * @return array{type: string, nullable: true, description: string}
     */
    private function nullableNumber(string $description): array
    {
        return ['type' => 'NUMBER', 'nullable' => true, 'description' => $description];
    }
}
