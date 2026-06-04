# Tests

Status: `passed`

Executed commands:

```bash
PATH=/opt/homebrew/opt/php@8.3/bin:$PATH /Applications/ServBay/package/bin/composer validate --strict
PATH=/opt/homebrew/opt/php@8.3/bin:$PATH /Applications/ServBay/package/bin/composer dump-autoload
PATH=/opt/homebrew/opt/php@8.3/bin:$PATH /Applications/ServBay/package/bin/composer run quality:gate
git diff --check
```

Semantic checks:

- typed link targets expose owner package, target id, visibility and access
  policy without physical paths or raw routes;
- token policy exposes TTL, max uses, scope, raw-token-storage flag and safe
  diagnostics;
- unknown target, malformed token, expired token, revoked token, access denied
  and scope mismatch decisions fail closed;
- share link descriptor declares audience, scope, expiry and revocation policy.

Observed results:

- `composer.json is valid`
- Composer autoload files generated successfully.
- `validate-larena-package`: `Larena Link coding launch context is valid.`
- PHP lint checked scripts, tools, `src` and `tests` with no syntax errors.
- PHPStan analysed scripts, tools, `src` and `tests` with no errors.
- `LinkTargetContractTest passed.`
- `LinkFailsClosedTest passed.`
- Evidence contract passed for the current repository state.
- Scope check passed for launch allowed files and evidence path.
- `git diff --check` passed.
