# Smoke

## Result

Passed on `2026-06-14T23:05:25Z`.

## Commands

```bash
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/php artisan test --filter=PublicLinkGuardedAdminMutationPlanningFoundationTest
```

Result:

```text
{"tool":"phpunit","result":"passed","tests":4,"passed":4,"assertions":81,"duration_ms":1443}
```

The root app wrapper preserves the internal route/controller/view and JSON
shape while delegating final planning report assembly to `larena/link`.
