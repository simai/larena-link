# Smoke

Root smoke passed:

```bash
PATH=/Applications/ServBay/package/bin:$PATH /Applications/ServBay/package/bin/php artisan test --filter=PublicLinkTokenStorageContractFoundationTest
```

Result: passed, 6 tests, 86 assertions.

The smoke confirms:

- command output remains available;
- internal review JSON remains available;
- raw candidate token does not leak in report output;
- package-backed wrapper preserves `lookup()` and `fingerprint()` compatibility;
- persistent token table, database migration, file delivery and release-ready flags remain disabled.
