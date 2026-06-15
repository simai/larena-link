# Tests

Passed commands:

```bash
COMPOSER_BIN=/Applications/ServBay/package/bin/composer PATH="/opt/homebrew/opt/php@8.3/bin:$PATH" /Applications/ServBay/package/bin/composer run quality:gate
```

```bash
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/php artisan test --filter=PublicLinkOperatorLifecycleManagementFoundationTest
```

Results:

- package quality gate passed;
- root feature test passed with 5 tests and 115 assertions;
- cockpit/debt/ownership validators passed with public_link=24 and tracked_file_count=117;
- settings/property package-owned presentation reduction pattern validator passed as a regression guard.
