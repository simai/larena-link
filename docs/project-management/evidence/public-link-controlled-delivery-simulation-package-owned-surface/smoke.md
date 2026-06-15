# Smoke

Expected smoke commands:

```bash
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/php artisan test --filter=PublicLinkControlledDeliverySimulationFoundationTest
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/php artisan larena:public-link-controlled-delivery-simulation-smoke --json
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/php artisan larena:public-link-runtime-hardening-smoke --json
```

Status: pending validation in this batch.

Expected result: report schema remains
`larena.public_link_controlled_delivery_simulation_foundation.v1`, status
remains `passed`, raw token values are not exposed, simulated response remains
metadata-only and file delivery remains blocked.
