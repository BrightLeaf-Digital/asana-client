# Brightleaf Digital Asana API Client for PHP

[![CI](https://github.com/Eitan-brightleaf/asana-client/actions/workflows/ci.yml/badge.svg)](https://github.com/Eitan-brightleaf/asana-client/actions/workflows/ci.yml)
[![semgrep (php)](https://github.com/Eitan-brightleaf/asana-client/actions/workflows/semgrep.yml/badge.svg)](https://github.com/Eitan-brightleaf/asana-client/actions/workflows/semgrep.yml)

A modern, maintained PHP client library for the Asana API.

## Common development commands

- Lint: `composer lint`
- Auto-fix: `composer lint:fix`
- Test: `composer test`
- Coverage (outputs to build/logs): `composer test:coverage`

## Motivation

This library was created because the official Asana PHP library is no longer maintained, is outdated, and uses a library with a known security vulnerability. After searching for alternatives, I couldn't find any third-party libraries that appeared to be actively maintained.

## Status

This library is currently pre‑v1. Breaking changes may occur between releases while we finalize the public API and internal architecture.

### Breaking Changes (v0.x → v0.y)

As we approach v1.0, several foundational changes have been made:

- **Constructor Changes**: `AsanaClient` now requires a `Psr\Container\ContainerInterface`. Use the static factories (`withPAT`, `OAuth`, `withAccessToken`) for the simplest setup.
- **Dependency Injection**: The library now uses a service container. You can replace core components (HTTP client, Auth handler, Token storage) by providing your own container.
- **Token Management**: Direct methods like `saveToken`, `loadToken`, and `onTokenRefresh` have been removed. Use `TokenManager` (available via the container) or `subscribeToTokenRefresh()` on the client.
- **Response Constants**: `AsanaApiClient::RESPONSE_*` constants have moved to `HttpClientInterface::RESPONSE_*`.
- **Exceptions**: `AsanaApiException` has been renamed to `ApiException`.
- **Static Factories**: `withPersonalAccessToken` has been renamed to `withPAT`.
- **Webhook Handshake**: `WebhooksApiService::handleHandshake` (and `AsanaClient::handleHandshake`) signature changed to remove redundant arguments.

This is my first library of this kind, and I am still developing my skills as a junior developer. Any reviews, comments, contributions, or suggestions are highly welcome - especially since my only peer review so far has been from AI. I would particularly appreciate help with:

- Writing tests
- Reviewing documentation
- Identifying improvements

## OAuth Scopes

This library now supports Asana's new OAuth permission scopes. These scopes provide more granular control over what 
actions an app can perform following the principle of least privilege and should enhance user trust and so increase app 
adoption. 

### Important Notes:

- **Incomplete Rollout**: Asana has not yet introduced scopes for all API endpoints. More scopes will be added in the future and incorporated into this library.
- **Backward Compatibility**: Existing apps can continue to use the `default` scope (full access) for now, and new apps can still toggle this option on in the app settings.
- **Getting Full Access With This Library**:
  - When creating your app in Asana, request full access permissions
  - When generating an authorization URL, pass an empty array for scopes: `$authUrl = $asanaClient->getAuthorizationUrl([]);`
- **Helper `Scopes` Class**: To simplify things for developers using this library, I've created a helper `Scopes` class containing
constants with available scopes. As noted below the library may not cover all API endpoints so some scopes in helper class
may correspond to an endpoint without support. Contributions and help expanding the library to have full API coverage is
welcome!

For more information about the new OAuth scopes and implementation details, refer to [Asana's announcement](https://forum.asana.com/t/new-oauth-permission-scopes/1048556/1) and its linked documentation.

## Features

- Modern PHP implementation
- Supports both OAuth 2.0 and Personal Access Tokens (PATs)
- Easy-to-use API that follows consistent patterns
- Fixes common pain points from the official Asana library
- Actively maintained
- Includes built-in token encryption utilities (`CryptoUtils::encrypt` and `CryptoUtils::decrypt`) to help secure 
sensitive token fields during storage. **Meant to be used only in local dev environments. Please use production grade security libraries in production.**
- Includes built-in support for automatic token refreshes, with customizable `onTokenRefresh` events to keep persisted 
storage up to date.



## API Coverage

This library provides comprehensive coverage of the Asana API, including:

### Core Resources
- **Tasks** - Create, read, update, delete tasks and manage task relationships
- **Projects** - Manage projects, sections, and project memberships
- **Users** - Get user information and manage user settings
- **Workspaces** - Access workspace information and settings
- **Tags** - Create and manage tags for organizing tasks
- **Attachments** - Upload and manage file attachments
- **Custom Fields** - Work with custom fields and their settings
- **Sections** - Organize tasks within projects using sections
- **Memberships** - Manage project and workspace memberships

### Advanced Features
- **Webhooks** - Set up real-time notifications for resource changes
- **Events** - Poll for events and track changes to resources
- **Teams** - Manage teams and team memberships
- **Portfolios** - Create and manage project portfolios
- **Goals** - Track organizational goals and objectives
- **Time Tracking** - Record and manage time entries on tasks
- **Project Templates** - Create projects from templates
- **Batch API** - Execute multiple API requests in a single call
- **Status Updates** - Post and retrieve project status updates
- **User Task Lists** - Access "My Tasks" and personal task lists

### Quick Examples

```php
// Webhooks - Real-time notifications
$webhook = $client->webhooks()->createWebhook([
    'resource' => $projectGid,
    'target' => 'https://example.com/webhook'
]);

// Events - Poll for changes
$events = $client->events()->getEvents($resourceGid);

// Teams - Manage teams
$teams = $client->teams()->getTeams($workspaceGid);
$team = $client->teams()->createTeam(['name' => 'Engineering', 'workspace' => $workspaceGid]);

// Portfolios - Organize projects
$portfolios = $client->portfolios()->getPortfolios($workspaceGid);
$portfolio = $client->portfolios()->createPortfolio(['name' => 'Q1 Projects', 'workspace' => $workspaceGid]);

// Goals - Track objectives
$goals = $client->goals()->getGoals(['workspace' => $workspaceGid]);
$goal = $client->goals()->createGoal(['name' => 'Increase Revenue', 'workspace' => $workspaceGid]);

// Time Tracking - Log time entries
$entry = $client->timeTrackingEntries()->createTimeTrackingEntry([
    'task' => $taskGid,
    'duration_minutes' => 120
]);

// Project Templates - Standardize workflows
$templates = $client->projectTemplates()->getProjectTemplates($workspaceGid);
$project = $client->projectTemplates()->instantiateProject($templateGid, ['name' => 'New Project']);

// Batch API - Optimize multiple requests
$responses = $client->batch()->submitBatch([
    ['method' => 'GET', 'relative_path' => '/tasks/12345'],
    ['method' => 'GET', 'relative_path' => '/projects/67890']
]);

// Status Updates - Project communication
$statusUpdate = $client->statusUpdates()->createStatusUpdate([
    'parent' => $projectGid,
    'text' => 'Project is on track',
    'status_type' => 'on_track'
]);

// User Task Lists - Personal task management
$myTasks = $client->userTaskLists()->getUserTaskList($userTaskListGid);
```

## Design Decisions

- When a field is required by an Asana API endpoint (such as a workspace GID), it's typically required as a method argument
- Some exceptions exist where it made more sense to let users include required fields in the data array (for example, in `createTask()` where users need to provide several fields anyway, and might use a workspace GID or project GID)
- Consistent return patterns to make working with responses predictable
- Focus on developer experience and ease of use

## Installation

```bash
composer require brightleafdigital/asana-client
```
then use Composer's autoload:
```php
require __DIR__.'/vendor/autoload.php';
```

## Basic Usage

To get started you need an Asana app configured with a proper redirect URL. You get the client ID and secret from the app. Remember to store them securely!
Please read the [official documentation](https://developers.asana.com/docs/overview#authentication-basics) if you aren't sure how to set up an app.

### Using Personal Access Token (PAT)

```php
use BrightleafDigital\AsanaClient;

$personalAccessToken = 'your-personal-access-token';
$client = AsanaClient::withPAT($personalAccessToken);

// Get user information
$me = $client->users()->getCurrentUser();

// Create a task
$taskData = [
    'name' => 'My new task',
    'notes' => 'Task description',
    'projects' => ['12345678901234'] // Project GID
];
$task = $client->tasks()->createTask($taskData);
```

### Using OAuth 2.0

```php
use BrightleafDigital\AsanaClient;
use BrightleafDigital\Auth\Scopes;

$clientId = 'your-client-id';
$clientSecret = 'your-client-secret';
$redirectUri = 'https://your-app.com/callback';

// Bootstrap with defaults (uses an internal container and file token storage)
$client = AsanaClient::OAuth($clientId, $clientSecret, $redirectUri, __DIR__ . '/token.json');

// Request specific scopes and get URL + state + PKCE verifier
$auth = $client->getSecureAuthorizationUrl([
    Scopes::TASKS_READ,
    Scopes::PROJECTS_READ,
    Scopes::USERS_READ
]);

// Store $auth['state'] and $auth['codeVerifier'] in the session, then redirect to $auth['url']

// In your callback handler:
$code = $_GET['code'];
$pkceVerifier = $_SESSION['oauth2_pkce_verifier'] ?? null;
$client->handleCallback($code, $pkceVerifier); // Token is saved automatically via TokenManager

// Then use the client
$me = $client->users()->getCurrentUser();
```

### Token Management and Storage Options

The `handleCallback()` method returns an `AccessToken` object, which contains the token itself, its expiry, and a refresh token.

This library provides flexibility in how you manage and store tokens. By default, tokens are automatically persisted to a `token.json` file in your current working directory.

#### Built-In Token Storage

The library uses a `TokenManager` to handle the token lifecycle. When you initialize the client using a static factory method, you can specify how tokens should be stored.

1.  **Default File Storage**: If no storage is specified, it defaults to `./token.json`.
2.  **Custom File Path**: Pass a string path to use `FileTokenStorage` at that location.
3.  **In-Memory Storage**: Pass `false` to use `MemoryTokenStorage` (no disk writes).
4.  **Custom Implementation**: Pass a `TokenStorageInterface` instance.

##### **Automatic Token Refresh Support**

The library **automatically handles token refreshes**. When a token expires, the client will:
1.  Request a new token from Asana.
2.  **Automatically save** it to your configured storage (e.g., update the `token.json` file).
3.  Notify any subscribers.

```php
use BrightleafDigital\AsanaClient;

// Initialize the client (automatically loads/saves to token.json)
$asanaClient = AsanaClient::OAuth($clientId, $clientSecret, $redirectUri, __DIR__ . '/token.json', null, $salt);

// Optional: Subscribe to refresh events for additional logging or custom logic
$asanaClient->subscribeToTokenRefresh(function ($newToken) {
    // Note: You DON'T need to call save() here; the library already did it!
    echo "Token refreshed successfully!";
});

// Example API call that triggers a token refresh if the token is expired
$userInfo = $asanaClient->users()->getCurrentUser();
```

#### **Flexible Token Handling for Advanced Users**

Advanced users can bypass the built-in storage by implementing `TokenStorageInterface` or by using memory storage (`false`) combined with `subscribeToTokenRefresh()` to handle their own persistence (e.g., to a database).

```php
// Use memory storage (false) so no files are written
$client = AsanaClient::withAccessToken($clientId, $clientSecret, $tokenData, false);

// Subscribe to refresh events to keep your DB in sync
$client->subscribeToTokenRefresh(function($newToken) use ($db) {
    $db->update('users')->set(['token' => json_encode($newToken)])->execute();
});
```

### Examples
More examples are available in the `examples` folder, including:
- OAuth flow setup with PKCE and state validation
- OAuth flow without additional security measures
- Using Personal Access Tokens
- Basic API usage examples
- All examples can be run directly in a browser

## Documentation Gaps

If you find something that isn't clear from either this library's documentation or the official Asana API documentation, the Asana developer forum is a valuable resource. There are often details or workarounds discussed there that aren't covered in the official documentation.

For example, creating a task in a specific section isn't documented in the API reference but can be found in forum discussions. If you discover such gaps:

1. Check the [Asana Developer Forum](https://forum.asana.com/c/developers/13)
2. Open an issue in this repository
3. Feel free to link to relevant forum or Stack Overflow posts

## 📘 Project Planning and Improvements

This library is actively developed with long-term maintainability in mind.  
For design decisions, planned features, and deferred items, see the following documentation:

- [Code Architecture Improvements](./docs/tasks/code-architecture-improvements.md)
- [Feature Additions](./docs/tasks/feature-additions.md)
- [Performance Improvements](./docs/tasks/performance-improvements.md)
- [Testing Improvements](./docs/tasks/testing-improvements.md)
- See [Task Summary & Prioritization](docs/tasks/roadmap.md) for a categorized and ranked view of all planned improvements.

Have an idea or want to help implement one of these? Open a [GitHub issue](https://github.com/Eitan-brightleaf/asana-client/issues/new) or submit a pull request.


## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.
