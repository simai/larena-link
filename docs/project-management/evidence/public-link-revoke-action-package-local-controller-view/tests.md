# Tests

Package test coverage:

- `tests/Feature/PublicLinkRevokeActionRouteBoundaryTest.php`
- `tests/Unit/PublicLinkRevokeActionPreviewTest.php`

Root test coverage:

- `tests/Feature/PublicLinkRevokeActionFoundationTest.php`

The root test must prove that the route name
`larena.internal.public-link-revoke-action` resolves to the package controller
after the root route/controller/view are removed.
