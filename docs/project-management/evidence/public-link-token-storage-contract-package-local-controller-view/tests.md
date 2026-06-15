# Tests

Required:

- `composer run quality:gate`
- `php artisan test --filter=PublicLinkTokenStorageContractFoundationTest`
- cockpit/debt/ownership validators in the entry app after root bridge removal.

Observed:

- `composer run quality:gate`: passed.
- Root storage-contract foundation smoke: passed, 7 tests and 89 assertions.
- Root cockpit/debt/ownership/architecture validators: passed.
- Portal batch report: skipped because `SIMAI_PORTAL_BATCH_REPORT_WEBHOOK_URL` is not set.
