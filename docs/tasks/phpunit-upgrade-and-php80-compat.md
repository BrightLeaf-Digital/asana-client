# PHPUnit Upgrade + Drop PHP 7.4 (Keep Runtime Support for PHP 8.0+)

## Goal

Upgrade PHPUnit from `9.6.x` to either:

- `12.5.22` (recommended for lower-risk migration), or
- `13.1.6` (newer major).

At the same time:

- drop PHP `7.4` support,
- keep library runtime compatibility for PHP `8.0+`,
- accept that PHPUnit test execution will run only on a higher PHP version required by the chosen PHPUnit major.

## Current Constraints in This Repo

- Runtime PHP constraint still allows 7.4: [composer.json](/var/www/html/asana-client/composer.json:13)
- PHPUnit is currently 9.6 line: [composer.json](/var/www/html/asana-client/composer.json:22), [composer.lock](/var/www/html/asana-client/composer.lock:1648)
- PHPStan is configured for PHP 7.4 syntax/semantics: [phpstan.neon.dist](/var/www/html/asana-client/phpstan.neon.dist:6)
- CI test matrix still includes 7.4 to 8.3: [ci.yml](/var/www/html/asana-client/.github/workflows/ci.yml:113)
- PHPUnit docblock metadata is still used in one test file (`@group integration`): [tests/Api/AttachmentApiServiceTest.php](/var/www/html/asana-client/tests/Api/AttachmentApiServiceTest.php:200)

## Version Choice and Its Impact

1. `PHPUnit 12.5.22` path:
- Requires PHP `>=8.3` for test execution.
- Smaller jump than 13, generally lower migration risk.

2. `PHPUnit 13.1.6` path:
- Requires PHP `>=8.4.1` for test execution.
- Latest major, but stricter runtime for test tooling.

In both paths, keeping package runtime compatibility at PHP `8.0+` is still possible because end users typically install with `--no-dev` and do not need PHPUnit.

## Concrete Change List

### A) Composer and dependency constraints

- [ ] Update runtime PHP constraint to drop 7.4:
  - [composer.json](/var/www/html/asana-client/composer.json:13)
  - Change from `^7.4 || ^8.0` to `^8.0`
- [ ] Update PHPUnit dev dependency:
  - [composer.json](/var/www/html/asana-client/composer.json:22)
  - Set to either `12.5.22` or `13.1.6` (or matching caret range if preferred).
- [ ] Regenerate lock file:
  - [composer.lock](/var/www/html/asana-client/composer.lock:1648)
  - [composer.lock](/var/www/html/asana-client/composer.lock:3240)
  - Ensure lock platform and resolved packages reflect the new constraints.

### B) PHPUnit test code and metadata compatibility

- [ ] Replace docblock group metadata with attributes (required for PHPUnit 12+):
  - [tests/Api/AttachmentApiServiceTest.php](/var/www/html/asana-client/tests/Api/AttachmentApiServiceTest.php:200)
  - [tests/Api/AttachmentApiServiceTest.php](/var/www/html/asana-client/tests/Api/AttachmentApiServiceTest.php:212)
  - Convert `@group integration` to `#[Group('integration')]` and add `use PHPUnit\Framework\Attributes\Group;`.

Notes:

- A repo scan found no usage of common removed PHPUnit APIs like `withConsecutive()`, `at()`, `assertRegExp()`, `assertArraySubset()`, `@expectedException`, etc.
- Most tests should be mechanically compatible once dependency and metadata/config updates are done.

### C) PHPUnit XML configuration migration

- [ ] Migrate PHPUnit config file to the target major format:
  - [phpunit.xml](/var/www/html/asana-client/phpunit.xml:1)
  - Run `./vendor/bin/phpunit --migrate-configuration`
- [ ] Verify that source coverage configuration is in the expected schema for the chosen version (modern config uses `<source>` definitions rather than legacy coverage include style).
- [ ] Keep integration exclusion behavior intact after metadata migration:
  - [phpunit.xml](/var/www/html/asana-client/phpunit.xml:9)

### D) PHPStan for runtime compatibility policy (PHP 8.0+)

- [ ] Update PHPStan target version:
  - [phpstan.neon.dist](/var/www/html/asana-client/phpstan.neon.dist:6)
  - Set `phpVersion: 80000` to enforce lowest-supported runtime compatibility (8.0 baseline), even if tests run on 8.3/8.4.
- [ ] Keep `phpstan/phpstan-phpunit` extension and verify it works with the selected PHPUnit major during CI.

### E) CI workflow changes

- [ ] Remove PHP 7.4 from the matrix:
  - [ci.yml](/var/www/html/asana-client/.github/workflows/ci.yml:113)
- [ ] Align test job PHP versions with PHPUnit requirement:
  - For PHPUnit 12.5.22: run PHPUnit on 8.3 (and optionally newer).
  - For PHPUnit 13.1.6: run PHPUnit on 8.4.1+.
- [ ] Keep runtime compatibility signal for PHP 8.0+ without PHPUnit on 8.0:
  - Add a dedicated compatibility job using PHP 8.0 with `composer install --no-dev`.
  - Run lightweight checks (autoload/smoke check) instead of PHPUnit in that job.
- [ ] Update coverage job version guards currently hardcoded to 8.3:
  - [ci.yml](/var/www/html/asana-client/.github/workflows/ci.yml:147)
  - [ci.yml](/var/www/html/asana-client/.github/workflows/ci.yml:157)
  - [ci.yml](/var/www/html/asana-client/.github/workflows/ci.yml:162)

### F) Documentation updates

- [ ] Update CI/version documentation:
  - [CONTRIBUTING.md](/var/www/html/asana-client/CONTRIBUTING.md:208)
  - Remove 7.4 references and document new test/runtime policy.
- [ ] Update README support statement (if/where PHP support is documented) to clearly separate:
  - runtime support (`PHP 8.0+`),
  - test-tooling runtime (depends on PHPUnit major).

## Suggested Execution Order

1. Decide target: `12.5.22` or `13.1.6`.
2. Update `composer.json` constraints and regenerate `composer.lock`.
3. Migrate `phpunit.xml`.
4. Replace `@group` metadata with `#[Group]`.
5. Update PHPStan `phpVersion` to `80000`.
6. Update CI matrix and add PHP 8.0 `--no-dev` compatibility job.
7. Update contributing/docs text.
8. Run full CI and fix any residual incompatibilities.

## Validation Checklist (Post-change)

- [ ] `composer validate --strict` passes.
- [ ] `composer analyse` passes with PHPStan targeting `80000`.
- [ ] PHPUnit passes on the selected high-PHP test runtime.
- [ ] CI includes a PHP 8.0 compatibility signal (non-test, `--no-dev`).
- [ ] No remaining 7.4 mentions in constraints/matrix/docs.

