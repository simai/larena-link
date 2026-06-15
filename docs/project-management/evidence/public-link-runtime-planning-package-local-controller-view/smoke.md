# Smoke

Smoke target:

- `GET /larena/internal/public-link-runtime-planning`
- `GET /larena/internal/public-link-runtime-planning?format=json`

Expected behavior:

- HTML still contains `Larena Public Link Runtime Planning`.
- JSON still exposes `larena.public_link_runtime_planning_preview.v1`.
- `safe_trace.public_route`, `safe_trace.token_storage_enabled_now` and `safe_trace.token_material_generated_now` remain false.
