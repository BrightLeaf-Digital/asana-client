# Changelog

All notable changes to this project will be documented in this file.

The format is based on Keep a Changelog, and this project adheres to Semantic Versioning.

## v0.2.1 - 2026-04-28

### Added
- add new scopes for ooo entries, portfolios, and time tracking (ffa664e)
- add OooEntriesApiService and ProjectPortfolioSettingsApiService and RolesApiService and TimesheetApprovalStatusesApiService and TimeTrackingCategoriesApiService  with full CRUD operations and unit tests (1aa03de)

### Chore
- migrate to PHP 8.0+, upgrade PHPUnit to 12.5, update CI and tools config (1680368)
- remove old phpstan and rector configurations (c517cc6)
- update .gitignore to include CLAUDE.md (691cf58)
- add PHPCompatibility, refine scripts and workflows (6e00841)
- remove phpstan-baseline, update PHPStan config, and adjust exception constructors (28241af)
- update test suites with stub-based mock creation and method adjustments (831b9a9)

### Documentation
- outline PHPUnit upgrade and PHP 8.0+ compatibility steps (7722a4e)

### Fixed
- fixed phpstan warning with always true ternary (65e6f40)


## v0.2.0 - 2026-02-25

### Added
- add changelog generation workflow with git-cliff (4075f80)
- implement GitHub Actions CI pipeline with PHP matrix (79ce253)
- add Dependabot and CodeQL workflows (86b1125)
- enhance AsanaClient tests with temp storage and encryption (e42b525)
- replace CodeQL with Semgrep for PHP code scanning (5839a80)
- conditional Xdebug setup for PHP 8.3 coverage (a36e3a2)
- skip workflows for dependabot actor (cfab908)
- update dependencies and actions for workflows (67f300b)
- update php_codesniffer and phpunit to specific versions (716e7e6)
- downgrade league/oauth2-client and guzzlehttp/guzzle versions (5a0d115)
- enhance workflows and dependency updates (c0de0fc)
- update PHPUnit config and enhance test coverage setup (fd0e60c)
- expand PHPUnit version compatibility in composer.json to fix CI workflow matrix (8a50af7)
- enhance Semgrep workflow with Python setup and SARIF upload (1c9463c)
- enhance Semgrep workflow with scoped triggers and expanded rules (ef815bf)
- add `TEAM_MEMBERSHIPS_READ` scope for team memberships API access (dbcf661)
- add `TIME_TRACKING_READ` scope for time tracking API access (cfd943d)
- add custom field viewing functionality (8991bd1)
- add enhanced error handling and PHPStan integration (9c06734)
- add scopes for timesheet approval statuses API (b882645)
- integrate phpstan-phpunit extension (faa23b6)
- Step 2 - Code Review and Improvements (#13) (db8e0a4)
- add WebhooksApiService with full test coverage (fe53388)
- add EventsApiService with full test coverage (36927a1)
- add TeamsApiService with full test coverage (80a9841)
- add PortfoliosApiService with full test coverage (1a82825)
- add GoalsApiService with full test coverage (5ff565f)
- add TimeTrackingEntriesApiService with full test coverage (0ffbee2)
- add ProjectTemplatesApiService with full test coverage (56be323)
- add BatchApiService with full test coverage (caf28ee)
- add StatusUpdatesApiService with full test coverage (47fbdf5)
- add UserTaskListsApiService with full test coverage (51fceb2)
- add getWorkspaceEvents endpoint to EventsApiService (a5f44ee)
- add missing `JOBS_READ` scope constant (a3b900a)
- implement structured logging across all client layers (ecacf28)
- add complete webhook support with HMAC verification (9c0b26d)
- add `ROLES_*` scope constants for role management (4a05273)
- add StoriesApiService with full CRUD support and tests (2814008)
- add membership services for portfolio, project, team, and workspace (7e783e6)
- add ProjectStatusesApiService with comprehensive functionality and tests (6e61f74)
- add AuditLogApiService and RulesApiService with full functionality and tests (1376a93)
- add AllocationsApiService, BudgetsApiService, and ReactionsApiService with full functionality and tests (5017ec3)
- add AccessRequestsApiService, CustomTypesApiService, GoalRelationshipsApiService, OrganizationExportsApiService, and ProjectBriefsApiService with full functionality and tests (e195624)
- add CustomFieldSettingsApiService, ExportsApiService, JobsApiService, RatesApiService, TimePeriodsApiService, and TypeaheadApiService with full functionality and tests (6be0307)

### Changed
- fix coding standard violations (40d3246)
- improve constructor readability and add clarifying comment (d89f5b2)
- fix formatting of `AsanaOAuthHandler::__construct` (3441dd5)
- replace `AsanaApiException` with `ApiException` (1c88853)
- extract shared CRUD logic into BaseApiService (35c0fe8)
- remove redundant argument from handshake header validation (dc5dcf4)
- adopt DI container, interfaces, and token storage (cd2ff19)

### Chore
- update workflows to support PHP 8.5 (eeb1f98)
- ensure correct branch is used for changelog updates (3f25c9b)
- update changelog workflow for manual version inputs (9a86bba)
- update changelog for v0.2.0 (18a2554)
- improve changelog workflow with full fetch and unreleased option (57f9d66)
- remove `--unreleased` flag from changelog workflow (6eecbed)
- refactor changelog workflow to use environment variables for versioning (da5949e)
- update changelog for v0.2.0 (0fb91b8)
- improve changelog workflow with local tag creation and streamlined note insertion (787590c)
- enhance changelog workflow with validation and improved note insertion (3ee81d5)
- refine changelog workflow with enhanced validation and formatting (ad68ea6)

### Documentation
- add contributing guidelines (619f3fc)
- update roadmap and task details for completed docs (5ec7048)
- update CI badge links to correct repository location (7a7714b)
- simplify completed CI/CD task description (ddc6472)
- update URLs to use corrected repository slug (f35bc5e)
- fix PHPDoc comments based on review feedback (4555d21)
- fix PHPDoc formatting for better IDE preview across all services (1c2da57)
- improve README with new API examples and update roadmap (a48d9a4)
- update implementation statuses and priorities in roadmap (692c1ab)
- add link sections to task documents (a8c9957)
- add link sections to task documents (ec214ea)
- remove outdated security improvements document (858683d)
- remove outdated documentation improvements file (e494b84)
- remove build and deployment improvements document (6d24adb)
- update task numbering and remove completed items (ced4c31)
- add roadmap links to task documentation (932fd11)
- remove completed tasks and outdated sections (f5b17db)

### Fixed
- sanitize output to enhance security and prevent XSS (c52a614)
- remove unused pip cache setting in Semgrep workflow (0fd3af1)
- sanitize task name output to prevent XSS (68bcdc8)
- add Semgrep suppression comments to prevent false positives (c6f0c18)
- update Semgrep config and remove suppression comments (89af71d)
- update scopes comments for attachment, task, and webhook APIs (18f0668)
- standardize comment formatting for API scopes (7621db9)
- escape link URL in customFields.php (ec3af5a)
- improve assertion for logout with non-existing token file (5e462d1)
- fixed phpcs violations (f181263)
- adjust phpstan config includes section (6a5e2a6)
- handle multiline commit messages in changelogs (b00f9cc)
- update PHPUnit to 9.6.33+ to fix CVE-2026-24765 (ad44f85)
- Add some missing endpoints to constant comments in the `Scopes` class (b1a02cc)
- correct GoalsApiService to match official Asana API documentation (e0684de)
- correct WebhooksApiService method order to match official Asana API documentation (8c497a8)
- correct PHPDoc default label in EventsApiService getEvents return block (54f6cfe)
- correct TeamsApiService method order to match official Asana API documentation (e5d94a0)
- correct PortfoliosApiService to match official Asana API documentation (50ab93b)
- correct TimeTrackingEntriesApiService to match official Asana API documentation (1e290a1)
- correct ProjectTemplatesApiService to match official Asana API documentation (05eb674)
- correct BatchApiService to match official Asana API documentation (6006c3c)
- correct StatusUpdatesApiService to match official Asana API documentation (7c7dd5a)
- allow 'me' as user GID in getTeamsForUser and getUserTaskListForUser (11072cd)
- allow 'me' as owner GID in getPortfolios (1039f2d)
- correct API documentation links in StatusUpdatesApiService (27a5189)
- added missing argument in workflow (d7d2bd8)
- remove conflicting `OUTPUT` environment variable from release workflow (28036fc)


## v.0.1.2 - 2025-08-18

### Changed
- Re-ordered and expanded OAuth scopes for consistency and coverage (commits: 5847f9b, e890ebe, 42e26f8, 515548b).
- Expanded API method doc blocks with detailed response structures and additional examples (b66ba0f).
- Replaced `$fullResponse` boolean with a constant to allow returning only `data` when desired (aff5b32).

### Added
- Added docs directory with roadmap and updated README and .gitignore accordingly (684b76e).
- Added new folders scaffolding (59e28dd).
- Improved CryptoUtils utility class (2f056dc).
- Example clarifications in code comments (b8301f6).
- Added this changelog (20e2720).

### Documentation
- Misc documentation updates (1da2a38).

## v0.1.1 - 2025-06-24

### Added
- API endpoint and HTTP method information included in API method doc blocks (b18ceef).

## v0.1.0 - 2025-05-08

### Added
- New static constructor for OAuth flows (e1364a2).
- Crypto utilities class to encrypt and decrypt tokens (e5540fa).
- PHPDoc/docblocks for encryption methods (5aea165).

### Changed
- Updated manual refresh method to always refresh (3f7ce2a).
- Updated utility methods to use new encryption methods (01c216a).
- Normalize line endings to LF (c954878).

### Fixed
- Fixed method call and comment cleanups (03daeba, 08edd22).

### Documentation
- Updated README (ef74633).

## v0.0.4 - 2025-05-01

### Changed
- Refresh token method now includes the refresh token in the returned token payload (a609608).

## v0.0.3 - 2025-04-30

### Added
- Callback hooks to token refresh flow (f6a170f).
- Example: viewing project custom fields (4a7a7a5).
- Getter for retrieving the current access token (8138f81).

### Changed
- Ensure access token return is JSON-serializable; refactor return handling (6da36b8, 90c2ea1).

### Fixed
- File upload method docs and stream handling issues (1796469).
- Example fixes and minor adjustments (8e2e219, 6da83c6).

### Documentation
- Update README with pre-1.0 roadmap and minor text fixes (db77f40).

### Chore
- Ignore VSCode files (d79c6af).

## v0.0.2 - 2025-04-22

### Added
- Support for OAuth scopes (ee4f031).
- CustomFieldApiService and WorkspaceApiService (9f42e7a).
- Initial tests for AsanaClient (52a4760).

### Changed
- ApiClient returns the whole response body (not just `data`); examples updated (a3743e1, 24231f0).
- Composer constraints updated to avoid exact version pinning; PHP version requirements clarified (99736c1, eefb0a2, e380e2e).

### Documentation
- README usage examples and authentication details (304fac7).
- README updates (e23f711).

### Chore
- Added .aiignore to .gitignore (3a1aacc).

## v0.0.1 - 2025-04-03

Initial tagged release.

### Added
- Asana PHP client basic file structure and OAuth2 scaffolding.
- Multiple API service classes (Tasks, Projects, Workspaces, Users, Tags, etc.).
- Examples for common workflows.
- Error handling improvements and custom exceptions.
