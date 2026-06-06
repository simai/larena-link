# Implementation Summary

`larena/link` now has an in-memory planning runtime that can evaluate link
requests, revocation intent and diagnostics without mutating state.

Implemented runtime surface:

- `ArrayLinkPolicy`
- `ArrayLinkRequest`
- `ArrayLinkPlan`
- `ArrayLinkRevocationPlan`
- `ArrayLinkDiagnosticsReport`
- `InMemoryLinkRuntime`

The runtime fails closed when required safety context is missing:

- missing target identity;
- missing link policy;
- missing target access policy for protected/private/internal targets;
- missing access scope for non-public audiences;
- invalid TTL for temporary links;
- public link without explicit public-delivery policy;
- confirmation-required link without confirmation;
- revocation without link identity, actor, reason or confirmation.

Non-goals preserved:

- no routes/controllers;
- no token hashing or storage runtime;
- no one-time atomic consumption runtime;
- no migrations;
- no admin UI;
- no filesystem/file-manager integration;
- no production link delivery.
