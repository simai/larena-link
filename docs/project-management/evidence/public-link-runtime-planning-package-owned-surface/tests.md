# Tests

Commands run for this slice:

```bash
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/php -d memory_limit=512M /Applications/ServBay/package/bin/composer run quality:gate
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/php artisan test --filter=PublicLinkRuntimePlanningPreviewSmokeTest
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/composer run cockpit:check
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/composer run validate:cockpit-governance
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/composer run validate:entry-app-presentation-debt-inventory
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/composer run validate:developer-preview-ownership-model
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/composer run validate:developer-preview-architecture
git diff --check
```

Result: passed.

The root smoke test passed with 4 tests and 59 assertions. The presentation-debt
inventory validator still reports `public_link=35`, so the parent reduction
track remains open.

The package unit test verifies that the package-owned report keeps the public
route, token, delivery, file, database and release gates disabled.

Homebrew PHP was not usable in this shell because its ICU dylib was missing.
ServBay PHP completed the package quality gate with `memory_limit=512M`.
