# Tests

Required package checks:

- `composer run quality:gate`

Required entry-app checks:

- `php artisan test --filter=PublicLinkPersistentLookupFoundationTest`
- `php artisan test --filter=PublicLinkRuntimeHardeningFoundationTest`
- `php artisan test --filter=PublicLinkDeliveryContractHardeningFoundationTest`
- `php artisan test --filter=PublicLinkTokenStorageContractFoundationTest`

Required cockpit/governance checks:

- `composer run cockpit:generate`
- `composer run cockpit:check`
- `composer run validate:entry-app-presentation-debt-inventory`
- `composer run validate:developer-preview-ownership-model`
- `composer run validate:settings-property-package-owned-presentation-reduction-pattern`
- `composer run validate:cockpit-governance`
- `composer run validate:developer-preview-architecture`
- `composer run validate:package-runtime-composition-ownership`
- `git diff --check`
