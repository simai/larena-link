# Tests

## Commands

```bash
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/php -d memory_limit=512M /Applications/ServBay/package/bin/composer run quality:gate
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/php artisan test --filter=PublicLinkGuardedAdminMutationPlanningFoundationTest
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/composer run cockpit:check
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/composer run validate:cockpit-governance
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/composer run validate:entry-app-presentation-debt-inventory
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/composer run validate:developer-preview-ownership-model
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/composer run validate:developer-preview-architecture
git diff --check
```

## Result

Passed on `2026-06-14T23:05:25Z`.

- Package `quality:gate`: passed, including `PublicLinkGuardedAdminMutationPlanningPreviewTest`.
- Root `PublicLinkGuardedAdminMutationPlanningFoundationTest`: passed, 4 tests, 81 assertions.
- Root `cockpit:check`: passed.
- Root `validate:cockpit-governance`: passed with existing cockpit-density warnings and no errors.
- Root `validate:entry-app-presentation-debt-inventory`: passed; `public_link=35`.
- Root `validate:developer-preview-ownership-model`: passed.
- Root `validate:developer-preview-architecture`: passed; architecture acceptance score `100`.
- Root/package `git diff --check`: passed.
