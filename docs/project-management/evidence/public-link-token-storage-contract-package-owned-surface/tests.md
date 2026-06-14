# Tests

## Commands Run

```bash
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/php -d memory_limit=512M /Applications/ServBay/package/bin/composer run quality:gate
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/php artisan test --filter=PublicLinkTokenStorageContractFoundationTest
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/composer run cockpit:check
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/composer run validate:cockpit-governance
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/composer run validate:entry-app-presentation-debt-inventory
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/composer run validate:developer-preview-ownership-model
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/composer run validate:developer-preview-architecture
git diff --check
```

## Result

Passed.

- Package quality gate passed with lint, PHPStan, metadata/evidence/scope checks and the package unit suite, including `PublicLinkTokenStorageContractPreviewTest`.
- Root feature smoke passed: `PublicLinkTokenStorageContractFoundationTest`, 6 tests, 86 assertions.
- Root cockpit check passed.
- Root validators passed:
  - `validate:cockpit-governance` passed with known cockpit-density warnings and no errors.
  - `validate:entry-app-presentation-debt-inventory` passed with `tracked_file_count=128`, `public_link=35`, `unclassified_file_count=0`.
  - `validate:developer-preview-ownership-model` passed.
  - `validate:developer-preview-architecture` passed.
- Root and package syntax/JSON/diff checks passed.
- Portal batch report was skipped because `SIMAI_PORTAL_BATCH_REPORT_WEBHOOK_URL` was not set in this shell.
- Preflight secret-safety warning was reviewed as a naming/context warning; no secrets, credentials, `.env` values or private logs were read, written or committed.
- Project memory update with full workflow was refused by secret-safety heuristic because the workflow contains public-link token terminology; project memory update without full workflow succeeded through `current_focus`.
- Homebrew PHP remains unsuitable in this environment because of the known ICU issue; validation used ServBay PHP at `/Applications/ServBay/package/bin/php`.
