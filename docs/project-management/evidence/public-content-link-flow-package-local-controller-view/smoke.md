# Smoke

Smoke target:

- `GET /larena/internal/public-content-link-flow`
- `GET /larena/internal/public-content-link-flow?format=json`

Expected behavior:

- HTML still contains `Larena Public Content Link Flow`.
- JSON still exposes `larena.public_content_link_flow_preview.v1`.
- `safe_trace.public_route`, `safe_trace.real_public_url_generated` and `safe_trace.token_storage_runtime` remain false.
