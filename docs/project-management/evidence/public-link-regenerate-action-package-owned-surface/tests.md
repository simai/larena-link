# Tests

## Commands Planned

```bash
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/php -d memory_limit=512M /Applications/ServBay/package/bin/composer run quality:gate
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/php artisan test --filter=PublicLinkRegenerateActionFoundationTest
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/composer run cockpit:check
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/composer run validate:cockpit-governance
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/composer run validate:entry-app-presentation-debt-inventory
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/composer run validate:developer-preview-ownership-model
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/composer run validate:developer-preview-architecture
git diff --check
```

## Result

Passed.

- Package quality gate passed, including `PublicLinkRegenerateActionPreviewTest`.
- Root smoke passed: `PublicLinkRegenerateActionFoundationTest`, 4 tests, 69 assertions.
- `composer run cockpit:check` passed.
- `composer run validate:cockpit-governance` passed with known cockpit-density warnings and no errors.
- `composer run validate:entry-app-presentation-debt-inventory` passed; `public_link=35`.
- `composer run validate:developer-preview-ownership-model` passed.
- `composer run validate:developer-preview-architecture` passed; architecture acceptance score `100`.
- Root/package `git diff --check` passed.
