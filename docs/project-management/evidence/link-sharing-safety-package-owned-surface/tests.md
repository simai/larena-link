# Tests

## Commands Run

```bash
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/php -d memory_limit=512M /Applications/ServBay/package/bin/composer run quality:gate
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/php artisan test --filter=LinkSharingSafetyPreviewSmokeTest
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/composer run cockpit:check
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/composer run validate:cockpit-governance
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/composer run validate:entry-app-presentation-debt-inventory
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/composer run validate:developer-preview-ownership-model
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/composer run validate:developer-preview-architecture
git diff --check
```

## Result

Passed.

- Package quality gate passed with ServBay PHP: validate, lint, PHPStan, unit
  tests, metadata, evidence and scope checks completed.
- Entry app smoke passed: `2 tests, 40 assertions`.
- Root cockpit/debt/ownership/architecture validators passed.
- Root and package `git diff --check` passed.
- Inventory after the batch still reports `public_link=35`, so the parent track
  remains open.

Homebrew PHP was not usable in this shell because its ICU dylib was missing;
ServBay PHP was used for the reproducible validation path.
