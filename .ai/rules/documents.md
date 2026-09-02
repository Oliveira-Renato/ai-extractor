---
paths:
  - 'app/Services/GeminiExtractionService.php,resources/js/Pages/Documents/Show.vue'
---

# Documents

## Keep invoice extraction structured and backward compatible
Invoice extraction must use explicit nullable schema fields, ISO 4217 currency codes, YYYY-MM-DD dates, numeric amounts, nested vendor/customer/payment data, and complete line items. The document result page should preserve fallbacks for legacy vendor_name, date, and line-item price fields.
