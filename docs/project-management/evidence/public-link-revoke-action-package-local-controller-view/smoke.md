# Smoke

Expected smoke checks:

- `composer run quality:gate` in `larena/link`.
- `php artisan test --filter=PublicLinkRevokeActionFoundationTest` in the entry app.
- `GET /larena/internal/public-link-revoke-action`.
- `GET /larena/internal/public-link-revoke-action?format=json`.

The JSON contract remains `larena.public_link_revoke_action_foundation.v1`.
