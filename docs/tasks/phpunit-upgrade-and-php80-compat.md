# PHPUnit Migration: Post-Migration Maintenance Plan

## Current Status
- PHPUnit upgraded to `12.5.23`.
- PHP 7.4 runtime support dropped; minimum runtime is `PHP 8.0+`.
- CI infrastructure updated to test on PHP 8.3 and run PHPCompatibility checks against `8.0+`.
- Migration task fully completed.
- PHPStan baseline removed as it is no longer required.

## Addressing PHPUnit Notices
- The project is now reporting 190 PHPUnit notices, primarily: "No expectations were configured for the mock object...".
- **How to fix:**
  1.  **Refactor to Stubs:** If a mock object is created but no expectations (`expects()`, `with()`, etc.) are set, refactor the test to use a simple stub instead of a mock.
  2.  **Opt-Out:** If the mock is intentional, add the `#[AllowMockObjectsWithoutExpectations]` attribute to the test method or class.
