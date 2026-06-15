# Smoke

Planned smoke commands for this slice:

```bash
php -l src/Runtime/PublicLinkMutationLadderReviewPreview.php
php -l tests/Unit/PublicLinkMutationLadderReviewPreviewTest.php
composer run quality:gate
```

Root compatibility smoke:

```bash
php artisan test --filter=PublicLinkMutationLadderReviewFoundationTest
```

