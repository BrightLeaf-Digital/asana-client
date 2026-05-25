# PHPUnit and PHP 8.1 Compatibility Maintenance Plan

## Current Status
- PHPUnit is pinned to the `12.5` line for development and test execution.
- PHP 7.4 and 8.0 runtime support dropped; minimum runtime is `PHP 8.1+`.
- CI verifies production dependency installation on PHP 8.1 and runs PHPUnit on PHP 8.3+.
- PHPCompatibility checks source compatibility against `8.1+`.
- PHPStan baseline removed as it is no longer required.

## Addressing PHPUnit Notices
- The project is now reporting 190 PHPUnit notices, primarily: "No expectations were configured for the mock object...".
- **How to fix:**
  1.  **Refactor to Stubs:** If a mock object is created but no expectations (`expects()`, `with()`, etc.) are set, refactor the test to use a simple stub instead of a mock.
  2.  **Opt-Out:** If the mock is intentional, add the `#[AllowMockObjectsWithoutExpectations]` attribute to the test method or class.
