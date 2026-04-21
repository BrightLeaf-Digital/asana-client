# PHPUnit Migration: Post-Migration Maintenance Plan

## Current Status
- PHPUnit upgraded to `12.5.23`.
- PHP 7.4 runtime support dropped; minimum runtime is `PHP 8.0+`.
- CI infrastructure updated to test on PHP 8.3 and run PHPCompatibility checks against `8.0+`.
- Migration task fully completed.

## Remaining Actionable Items (Technical Debt)

- [ ] **Address PHPStan Baseline Errors:**
  - The project currently has 426 issues tracked in `phpstan-baseline.neon`, primarily related to PHPUnit `MockObject` type mismatches (`InvocationOrder` vs `InvokedCount`) introduced in the PHPUnit 12+ migration.
  - **Plan:** Periodically review the baseline and attempt to resolve these issues by refactoring test code or updating type annotations as `phpstan-phpunit` support matures.
  
- [ ] **Report False Positives:**
  - Verify if the `InvocationOrder` / `InvokedCount` errors persist with the latest version of `phpstan-phpunit` and, if they remain false positives, report them to the `phpstan-phpunit` GitHub repository.

- [ ] **Long-term Maintenance:**
  - Utilize `rector.php` for automated refactoring as new PHPUnit major versions are released.
  - Maintain `phpstan.test.neon` to ensure test-specific code is analyzed correctly.
