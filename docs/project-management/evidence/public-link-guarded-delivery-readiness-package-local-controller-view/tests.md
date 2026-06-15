# Tests

Required:

- `composer run quality:gate`
- `php artisan test --filter=PublicLinkGuardedDeliveryReadinessFoundationTest`
- cockpit/debt/ownership validators in the entry app after root bridge removal.

Observed on 2026-06-15:

- `COMPOSER_BIN=/Applications/ServBay/package/bin/composer PATH="/opt/homebrew/opt/php@8.3/bin:$PATH" /Applications/ServBay/package/bin/composer run quality:gate`: passed.
- `PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/php artisan test --filter=PublicLinkGuardedDeliveryReadinessFoundationTest`: passed, 6 tests, 98 assertions.
- `PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/php scripts/generate-cockpit-reports.php`: passed.
- `composer run cockpit:check`: passed.
- `composer run validate:cockpit-governance`: passed.
- `composer run validate:entry-app-presentation-debt-inventory`: passed with `public_link=28`, `tracked_file_count=121`, `unclassified_file_count=0`.
- `composer run validate:developer-preview-ownership-model`: passed.
- `composer run validate:developer-preview-architecture`: passed.
- `git diff --check`: passed for package and entry app.
