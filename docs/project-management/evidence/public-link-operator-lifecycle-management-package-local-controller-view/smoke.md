# Smoke

Passed commands:

```bash
COMPOSER_BIN=/Applications/ServBay/package/bin/composer PATH="/opt/homebrew/opt/php@8.3/bin:$PATH" /Applications/ServBay/package/bin/composer run quality:gate
```

```bash
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/php artisan route:list --path=larena/internal/public-link-operator-lifecycle-management
```

Result:

- package quality gate passed;
- root route list confirms /larena/internal/public-link-operator-lifecycle-management;
- portal report skipped because the batch report webhook URL is not configured.
