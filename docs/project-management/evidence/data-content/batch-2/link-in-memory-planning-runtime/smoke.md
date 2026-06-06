# Smoke

The package runtime slice is smoke-tested through package-local tests first.
Entry app smoke must only be updated after the package commit is accepted and
`/Users/rim/Documents/GitHub/larena/composer.lock` references the new package
commit.

Expected entry app checks after package commit:

```bash
php artisan larena:data-content-smoke --full
php artisan larena:cluster-smoke --full
```

The runtime slice must remain developer-testable foundation only.
