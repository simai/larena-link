# Smoke

Package smoke:

```bash
php -l src/Runtime/PublicLinkRuntimeHardeningPreview.php
php -l tests/Unit/PublicLinkRuntimeHardeningPreviewTest.php
composer run quality:gate
```

Root compatibility smoke:

```bash
php artisan test --filter=PublicLinkRuntimeHardeningFoundationTest
```

