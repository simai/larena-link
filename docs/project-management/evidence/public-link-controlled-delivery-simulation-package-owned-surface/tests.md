# Tests

Expected package command:

```bash
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/php -d memory_limit=512M /Applications/ServBay/package/bin/composer run quality:gate
```

Expected root validators:

```bash
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/composer run cockpit:check
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/composer run validate:cockpit-governance
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/composer run validate:entry-app-presentation-debt-inventory
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/composer run validate:developer-preview-ownership-model
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/composer run validate:developer-preview-architecture
```

Status: passed.

Results:

- package quality gate: passed
- root `PublicLinkControlledDeliverySimulationFoundationTest`: passed, 5 tests, 104 assertions
- cockpit check: passed
- cockpit governance: passed with existing warnings
- entry app presentation debt inventory: passed, `public_link=35`
- developer preview ownership model: passed
- developer preview architecture: passed, score 100
