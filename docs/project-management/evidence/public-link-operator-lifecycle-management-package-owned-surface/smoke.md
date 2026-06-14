# Smoke

## Result

Passed on `2026-06-14T22:56:29Z`.

## Commands

```bash
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/php artisan test --filter=PublicLinkOperatorLifecycleManagementFoundationTest
```

Result:

```text
{"tool":"phpunit","result":"passed","tests":5,"passed":5,"assertions":113,"duration_ms":1751}
```

The root app wrapper preserves the internal route/controller/view and JSON
shape while delegating final report assembly to `larena/link`.
