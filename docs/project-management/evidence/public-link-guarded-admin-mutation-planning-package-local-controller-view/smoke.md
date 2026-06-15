# Smoke

Expected smoke checks:

- `composer run quality:gate` in `larena/link`.
- `php artisan test --filter=PublicLinkGuardedAdminMutationPlanningFoundationTest` in the entry app.
- `GET /larena/internal/public-link-guarded-admin-mutation-planning`.
- `GET /larena/internal/public-link-guarded-admin-mutation-planning?format=json`.

The JSON contract remains
`larena.public_link_guarded_admin_mutation_planning_foundation.v1`.
