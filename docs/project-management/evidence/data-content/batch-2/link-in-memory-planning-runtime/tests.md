# Tests

Commands run with ServBay PHP:

```bash
composer validate --strict
composer run validate:larena
composer run lint
composer run analyse
composer test
composer run quality:gate
```

Initial `quality:gate` stopped at missing evidence files, after code/lint/static
analysis/tests had passed. This evidence package was then added and the gate is
expected to be rerun before acceptance.

Test files:

- `tests/Unit/LinkTargetContractTest.php`
- `tests/Unit/LinkFailsClosedTest.php`
- `tests/Unit/InMemoryLinkRuntimeTest.php`
- `tests/Unit/InMemoryLinkRuntimeFailsClosedTest.php`
