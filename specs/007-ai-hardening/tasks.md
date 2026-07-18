# 007-ai-hardening — Tasks

Harden the entire AI layer: fix error handling, queue resilience, JSON decoding, model config clarity, and billing-loop guards.

## Backend — P0 fixes

1. Fix `InvoiceExtractionService` JSON decoder
   - Replace primitive `json_decode()` with robust decoder handling thought parts, fences, truncated JSON.
   - Mirror `LeaseExtractionService::decodeJsonResponse()` / `GeminiClient`.
   - Tests must cover truncated JSON and fenced JSON.

2. Harden `ProcessInvoiceBatch` queue job
   - Change `$tries = 1` to `$tries = 3`.
   - Add `$backoff = [60, 300, 900]`.
   - In `handle()`, catch exceptions only to update batch status, then rethrow so Laravel routes to `failed()` / `failed_jobs`.

3. Harden `ProcessLeaseBatch` queue job
   - Add `$backoff` (same as invoice).
   - Catch-and-rethrow instead of swallowing exceptions.

## Backend — P1 shared infrastructure

4. Centralize Gemini HTTP client
   - Extend `GeminiClient` with `generateText()` for text-only calls.
   - Migrate text-only services (`ReportsNlService`, `HomeInsightService`, `InvoiceBatchSummarizer`, `LeaseForecastService`, `FinancialAiService`, `MoraslatAiExtractor` text path, `ViolationAiExtractor`) to use `GeminiClient`.
   - Unify retry/backoff logic; remove duplicated `while(true)` loops.

5. Add structured logging
   - Log model used, tokens, retry attempts, 429/503 hits, and final exceptions.
   - Use `Log::warning` for retries, `Log::error` for failures.

6. Add per-page deadline / timeout guard
   - In `InvoicePipeline` and `LeasePipeline`, abort gracefully if remaining job time is too low.
   - Add `gemini.page_timeout` config.

## Backend — P2–P3 refinements

7. Admin Settings expose more Gemini config
   - Add `gemini_rescan_model`, `gemini_thinking_hard`, `gemini_timeout`, `gemini_retries` to Settings UI and `Settings::applyToConfig()`.

8. Harden `PdfPageRasterizer`
   - Wrap `pdftoppm` with shell `timeout` to prevent hung processes.

9. Fix `ViolationAiExtractor::classify()` model parameter
   - Accept and pass through model parameter.

10. Concurrency guard for reprocessing
    - Bail if batch status already `processing`, or add Redis lock.

## QA

11. Write/update tests
    - JSON decoder tests for invoice service.
    - Queue job retry/backoff tests.
    - Billing retry-storm scenario test (max calls per page under failure).

12. Verification
    - Run `./vendor/bin/pest`.
    - Deploy to test server and re-run lease batch + invoice batch smoke tests.

## Reviewer fixes (round 2)

13. Make `failed_jobs.id` migration DB-portable
    - Use Laravel Schema helpers or DB-conditional SQL (MySQL/Oracle/SQLite).
    - Respect `config('queue.failed.table')`.

14. Centralize file extraction in `GeminiClient`
    - Move `callGemini()` + `decodeJsonResponse()` + `tryDecodeJson()` from `InvoiceExtractionService` and `LeaseExtractionService` into `GeminiClient::extract()`.
    - Remove duplicated retry/decoder loops.

15. Retry connection-level timeouts in `GeminiClient`
    - Catch `Illuminate\Http\Client\ConnectionException` (cURL 28/56) in retry loop.
    - Apply to both `extract()` and `generateText()`.

16. Add structured logging to `GeminiClient::generateText()`
    - Mirror logging in `extract()`: model, tokens, attempts, errors.

17. Make job retries resume-aware and exception-class aware
    - Skip pages with `status = done` on retry.
    - Use `$retryOn` to avoid retrying deterministic errors (bad JSON, auth, invalid schema).

18. Make reprocess guard atomic
    - Use `where('status', '!=', 'processing')->update(['status' => 'processing'])` and check affected rows.

19. Clarify `timeout` vs `page_timeout` in admin Settings
    - Either expose `gemini_page_timeout` or map the existing setting correctly.

20. Add missing tests
    - Connection-timeout retry test.
    - Billing retry-storm / max-calls-per-page test.
    - Per-page resume test.

21. Re-verify
    - Re-run targeted tests and deploy to test server for smoke test.
