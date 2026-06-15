# Smoke

Package smoke:

```bash
php -l src/Runtime/PublicLinkDeliveryContractHardeningPreview.php
php -l tests/Unit/PublicLinkDeliveryContractHardeningPreviewTest.php
composer run quality:gate
```

Root compatibility smoke:

```bash
php artisan test --filter=PublicLinkDeliveryContractHardeningFoundationTest
```

