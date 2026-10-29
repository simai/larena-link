# Larena Link

Shared system package for stable, short, temporary, signed, one-time and action links across Larena without exposing physical resources or bypassing auth/access/audit boundaries.

This repository is an active Larena package-owned link runtime foundation. It
currently contains contracts, enums, configuration, the public route file,
a service provider, a public link lookup migration, the read-only public-link
foundation reports (token storage, persistent lookup, delivery readiness,
delivery simulation, one-time consumption, real delivery adapter, link sharing)
and focused tests. The developer-preview screens under `/larena/internal` were
removed (owner decision 2026-10-26), and so was the `/larena/link/{token}`
runtime-hardening preview route with the report chain it composed (owner
decision 2026-10-29).

Current status: developer-testable foundation. Public route provider promotion,
operator parity smoke and guarded mutation launches are separate approval
tracks; this package is not yet a production public-link management UI.

Canonical specifications are in `simai/larena-specs`.
