# Smoke

Passed commands:

```bash
COMPOSER_BIN=/Applications/ServBay/package/bin/composer PATH="/opt/homebrew/opt/php@8.3/bin:$PATH" /Applications/ServBay/package/bin/composer run quality:gate
```

```bash
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/php artisan route:list --path=larena/internal/public-link-guarded-real-delivery-adapter
```

Result:

- package quality gate passed;
- root route list confirms `/larena/internal/public-link-guarded-real-delivery-adapter`;
- portal report skipped because `SIMAI_PORTAL_BATCH_REPORT_WEBHOOK_URL` is not set.
