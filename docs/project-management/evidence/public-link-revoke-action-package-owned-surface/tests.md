# Tests

## Commands Planned

```bash
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/php -d memory_limit=512M /Applications/ServBay/package/bin/composer run quality:gate
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/php artisan test --filter=PublicLinkRevokeActionFoundationTest
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/composer run cockpit:check
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/composer run validate:cockpit-governance
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/composer run validate:entry-app-presentation-debt-inventory
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/composer run validate:developer-preview-ownership-model
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/composer run validate:developer-preview-architecture
git diff --check
```

## Result

Passed.

- Package quality gate passed, including `PublicLinkRevokeActionPreviewTest`.
- Package unit coverage includes `PublicLinkRevokeActionPreview::preview`.
- Root smoke passed: `PublicLinkRevokeActionFoundationTest`, 4 tests, 63 assertions.
- Root mutation ladder regression passed: `PublicLinkMutationLadderReviewFoundationTest`, 4 tests, 82 assertions.
- `composer run cockpit:check` passed.
- `composer run validate:cockpit-governance` passed with known cockpit-density warnings and no errors.
- `composer run validate:entry-app-presentation-debt-inventory` is expected to pass after root inventory update; target `public_link=17`.
- `composer run validate:developer-preview-ownership-model` passed.
- `composer run validate:developer-preview-architecture` passed; architecture acceptance score `100`.
- Root/package `git diff --check` passed.
