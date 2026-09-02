---
paths:
  - 'app/Jobs/**,app/Http/Controllers/DocumentController.php,config/{queue,services}.php'
---

# Controllers

## Document extraction runs without a queue worker
Dispatch document extraction through services.document_extraction.queue_connection, which defaults to Laravel's background driver so `php artisan serve` does not leave uploads pending. Keep the job timeout below the database queue retry_after value, and preserve failed() status handling for hard timeouts.
