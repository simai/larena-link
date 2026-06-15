# Tests

Required:

- `composer run quality:gate`
- `php artisan test --filter=PublicLinkRuntimeHardeningFoundationTest`
- cockpit/debt/ownership validators in the entry app after root bridge removal.

Observed:

- `composer run quality:gate`: passed.
- Root `PublicLinkRuntimeHardeningFoundationTest`: passed, 6 tests and 105 assertions.
- Root cockpit/debt/ownership/architecture validators: passed.
- Portal batch report: skipped because `SIMAI_PORTAL_BATCH_REPORT_WEBHOOK_URL` is not set.
