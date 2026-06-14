# Tests

## Commands Run

```bash
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/php -d memory_limit=512M /Applications/ServBay/package/bin/composer run quality:gate
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/php artisan test --filter=PublicContentLinkFlowPreviewSmokeTest
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
- Entry app smoke passed: `4 tests, 64 assertions`.
- Root cockpit/debt/ownership/architecture validators passed.
- Root and package `git diff --check` passed.
- Inventory after the batch still reports `public_link=35`, so the parent track
  remains open.

Homebrew PHP was not usable in this shell because its ICU dylib was missing;
ServBay PHP was used for the reproducible validation path.

The package unit test verifies that the package-owned flow report keeps the
public route, token, delivery, file, database and release gates disabled.
