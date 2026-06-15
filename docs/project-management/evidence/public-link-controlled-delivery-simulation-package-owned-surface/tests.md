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

Planned results for this composition reduction batch:

- package quality gate
- root `PublicLinkControlledDeliverySimulationFoundationTest`
- root `PublicLinkRuntimeHardeningFoundationTest`
- root `PublicLinkDeliveryContractHardeningFoundationTest`
- root `PublicLinkOneTimeConsumptionLifecycleFoundationTest`
- root `PublicLinkTokenStorageContractFoundationTest`
- root `PublicLinkPersistentLookupFoundationTest`
- public link smoke commands
- cockpit/debt/ownership/pattern validators
