# AI Extractor

Web application for structured invoice data extraction powered by artificial intelligence. Users upload a PDF or image, track the processing status, and review the extracted data as JSON without manually filling out forms.

> This project is under active development. Gemini integration is ready for local use; payments and commercial usage limits depend on Lemon Squeezy configuration.

## Key features

- Authenticated uploads of PDF, JPG, JPEG, PNG, and WEBP files.
- Asynchronous queue processing to keep the interface responsive.
- Structured extraction with Google Gemini and JSON responses.
- Identification of vendor, total amount, date, invoice number, and line items.
- Document history and original file downloads.
- Per-user access control and persisted extraction results.
- Responsive interface built with Vue 3, Inertia, and Tailwind CSS.

## How it works

1. The user uploads an invoice of up to 20 MB.
2. The application stores the file and creates a processing job.
3. The job sends the document to Gemini with a defined response schema.
4. The result is saved to the document and made available for review.

## Stack

- **Backend:** PHP 8.3+, Laravel 13, Laravel Sanctum
- **Frontend:** Vue 3, Inertia.js 2, Vite, Tailwind CSS
- **Database and queue:** SQLite and database queue by default
- **AI:** Google Gemini API (`gemini-2.5-flash` by default)

## Requirements

- PHP 8.3 or higher
- Composer 2
- Node.js 20+ e npm
- A [Google AI Studio](https://aistudio.google.com/apikey) API key

## Installation

Clone the repository and enter the project directory:

```bash
git clone <REPOSITORY_URL>
cd ai-extractor
```

Install dependencies, prepare the environment, and build the assets:

```bash
composer run setup
```

Configure the Gemini key in the `.env` file:

```dotenv
GEMINI_API_KEY=your-api-key
GEMINI_MODEL=gemini-2.5-flash
```

The project uses SQLite by default. If the database file does not exist yet, create it before running the migrations:

```bash
touch database/database.sqlite
php artisan migrate
```

## Local development

Start the application and queue worker in separate terminals:

```bash
php artisan serve
php artisan queue:work
```

For frontend development with hot reload, also run:

```bash
npm run dev
```

Visit [http://localhost:8000](http://localhost:8000), create an account, and upload a document.

## Response structure

Each processed document stores the following fields:

```json
{
	"vendor_name": "Vendor name",
	"total_amount": 123.45,
	"date": "2026-08-27",
	"invoice_number": "000123",
	"line_items": [
		{
			"description": "Product or service",
			"quantity": 1,
			"price": 123.45
		}
	]
}
```

Fields that are not legible are returned as `null` where applicable.

## Testing

Run the automated test suite with:

```bash
composer test
```

## Additional configuration

Available variables are documented in `.env.example`. To enable checkout on the pricing page, set:

```dotenv
LEMON_SQUEEZY_CHECKOUT_URL=https://...
```

For production environments, configure a persistent queue, secure document storage, HTTPS, and appropriate values for `APP_ENV`, `APP_DEBUG`, and `APP_URL`.

## Contributing

Pull requests are welcome. Before submitting a change:

1. Keep the scope of the change clear.
2. Add or update related tests.
3. Run `composer test` and review the diff.

## License

Este projeto esta licenciado sob a [MIT License](https://opensource.org/licenses/MIT).
