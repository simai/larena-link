# Smoke

Planned smoke checks:

- `php artisan larena:public-link-persistent-lookup-smoke`
- `php artisan larena:public-link-runtime-hardening-smoke`
- `php artisan test --filter=PublicLinkPersistentLookupFoundationTest`

Expected result: report schema remains `larena.public_link_persistent_lookup_foundation.v1`, status remains `passed`, raw token values are not exposed, local/testing-only database preview remains marked as non-production, and file delivery remains blocked.
