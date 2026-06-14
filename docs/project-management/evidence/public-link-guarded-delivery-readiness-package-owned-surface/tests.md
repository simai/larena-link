# Tests

## Commands Planned

```bash
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/php -d memory_limit=512M /Applications/ServBay/package/bin/composer run quality:gate
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/php artisan test --filter=PublicLinkGuardedDeliveryReadinessFoundationTest
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/composer run cockpit:check
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/composer run validate:cockpit-governance
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/composer run validate:entry-app-presentation-debt-inventory
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/composer run validate:developer-preview-ownership-model
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/composer run validate:developer-preview-architecture
git diff --check
```

## Result

- package quality gate: passed;
- root smoke `PublicLinkGuardedDeliveryReadinessFoundationTest`: passed,
  5 tests, 95 assertions;
- cockpit check: passed;
- cockpit governance: passed with known cockpit-density warnings and no errors;
- entry-app presentation debt inventory: passed, `public_link=35`;
- developer preview ownership model: passed;
- developer preview architecture: passed, architecture acceptance score 100;
- root/package `git diff --check`: passed.
