 # Code Architecture Improvements
 
 [← Back to Roadmap](roadmap.md)

This document outlines architectural enhancements needed for the Asana Client PHP library. Each item includes detailed explanations, code examples, and validation against API specifications.

<!-- links:items:start -->
 Links to items:
 - [2. Implement interfaces for all major components](#1-implement-interfaces-for-all-major-components) (Roadmap Item 10)
 - [3. Separate configuration from implementation](#2-separate-configuration-from-implementation) (Roadmap Item 18)
 - [4. Implement proper service container/dependency injection](#3-implement-proper-service-containerdependency-injection) (Roadmap Item 11)
<!-- links:items:end -->

## 1. Implement interfaces for all major components

### Problem Statement
The current codebase lacks interfaces for major components, making it difficult to implement alternative implementations or mock components for testing.

### Code Examples

#### Current Implementation:
```php
// Direct class usage without interfaces
class AsanaClient
{
    private $httpClient;
    private $oauthHandler;
    
    public function __construct($clientId, $clientSecret, $redirectUri)
    {
        $this->httpClient = new ApiClient();
        $this->oauthHandler = new OAuthHandler($clientId, $clientSecret, $redirectUri);
    }
    
    // Methods that directly use concrete implementations
}

// Usage in application code
$asanaClient = new AsanaClient($clientId, $clientSecret, $redirectUri);
```

#### Expected Implementation:
```php
// Define interfaces
interface HttpClientInterface
{
    public function request($method, $endpoint, $params = []);
}

interface AuthHandlerInterface
{
    public function getAuthorizationUrl($options = []);
    public function handleCallback($code, $state);
    public function refreshToken($refreshToken);
}

interface AsanaClientInterface
{
    public function tasks();
    public function projects();
    public function users();
    // Other resource methods
}

// Implement interfaces
class ApiClient implements HttpClientInterface
{
    public function request($method, $endpoint, $params = [])
    {
        // Implementation
    }
}

class OAuthHandler implements AuthHandlerInterface
{
    // Implementation of interface methods
}

class AsanaClient implements AsanaClientInterface
{
    private $httpClient;
    private $authHandler;
    
    public function __construct(HttpClientInterface $httpClient, AuthHandlerInterface $authHandler)
    {
        $this->httpClient = $httpClient;
        $this->authHandler = $authHandler;
    }
    
    // Implementation of interface methods
}

// Usage in application code with dependency injection
$httpClient = new ApiClient();
$authHandler = new OAuthHandler($clientId, $clientSecret, $redirectUri);
$asanaClient = new AsanaClient($httpClient, $authHandler);
```

### File References
- `src/Http/ApiClient.php`: HTTP client implementation
- `src/Auth/OAuthHandler.php`: OAuth authentication handler
- `src/AsanaClient.php`: Main client class

### API Spec Validation
This is a client-side architecture concern and doesn't directly relate to API specification compliance. However, a well-designed interface structure makes it easier to adapt to API changes or extensions.

### Critical Evaluation
- **Actual Impact**: Medium - Lack of interfaces makes the code less flexible and harder to test
- **Priority Level**: High - Should be addressed early to enable other improvements
- **Implementation Status**: Not implemented - Current code uses concrete classes without interfaces
- **Spec Compliance**: N/A - This is a client-side architecture concern
- **Difficulty/Complexity**: High - Requires designing comprehensive interfaces, refactoring existing classes to implement them, and updating dependency injection throughout the codebase

### Recommended Action
Define interfaces for all major components (HTTP client, authentication handlers, API services) and update implementations to use these interfaces. This will improve testability and flexibility.

## 2. Separate configuration from implementation

### Problem Statement
Configuration options are currently hardcoded or tightly coupled with implementation classes, making it difficult to customize behavior without modifying code.

### Code Examples

#### Current Implementation:
```php
// Configuration mixed with implementation
class AsanaClient
{
    private $baseUrl = 'https://app.asana.com/api/1.0';
    private $timeout = 30;
    
    public function __construct($clientId, $clientSecret, $redirectUri)
    {
        $this->httpClient = new ApiClient($this->baseUrl, $this->timeout);
        // Other initialization
    }
    
    // Methods with hardcoded configuration values
}
```

#### Expected Implementation:
```php
// Separate configuration class
class AsanaClientConfig
{
    private $baseUrl;
    private $timeout;
    private $retryAttempts;
    private $userAgent;
    
    public function __construct(array $options = [])
    {
        $this->baseUrl = $options['base_url'] ?? 'https://app.asana.com/api/1.0';
        $this->timeout = $options['timeout'] ?? 30;
        $this->retryAttempts = $options['retry_attempts'] ?? 3;
        $this->userAgent = $options['user_agent'] ?? 'AsanaClient PHP/' . PHP_VERSION;
    }
    
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }
    
    public function getTimeout()
    {
        return $this->timeout;
    }
    
    public function getRetryAttempts()
    {
        return $this->retryAttempts;
    }
    
    public function getUserAgent()
    {
        return $this->userAgent;
    }
}

// Implementation using configuration
class AsanaClient
{
    private $config;
    private $httpClient;
    
    public function __construct(AsanaClientConfig $config, HttpClientInterface $httpClient = null)
    {
        $this->config = $config;
        $this->httpClient = $httpClient ?? new ApiClient(
            $this->config->getBaseUrl(),
            $this->config->getTimeout(),
            $this->config->getUserAgent()
        );
    }
    
    // Methods using configuration from config object
}

// Usage
$config = new AsanaClientConfig([
    'timeout' => 60,
    'retry_attempts' => 5
]);
$asanaClient = new AsanaClient($config);
```

### File References
- `src/AsanaClient.php`: Main client class with configuration values
- `src/Http/ApiClient.php`: HTTP client with configuration values

### API Spec Validation
This is a client-side architecture concern and doesn't directly relate to API specification compliance. However, a flexible configuration system makes it easier to adapt to different API usage patterns.

### Critical Evaluation
- **Actual Impact**: Medium - Hardcoded configuration makes the library less flexible
- **Priority Level**: Medium - Should be addressed to improve customization options
- **Implementation Status**: Not implemented - Current code has configuration mixed with implementation
- **Spec Compliance**: N/A - This is a client-side architecture concern
- **Difficulty/Complexity**: Medium - Requires creating configuration classes and refactoring existing code to use configurable values, but follows established patterns

### Recommended Action
Create a dedicated configuration class that encapsulates all configurable options. Update all components to use this configuration class instead of hardcoded values.

## 3. Implement proper service container/dependency injection

### Problem Statement
The current code manually instantiates dependencies, making it difficult to replace components or inject mock objects for testing.

### Code Examples

#### Current Implementation:
```php
// Manual dependency instantiation
class AsanaClient
{
    private $taskService;
    private $projectService;
    
    public function __construct($clientId, $clientSecret, $redirectUri)
    {
        $httpClient = new ApiClient();
        $this->taskService = new TaskApiService($httpClient);
        $this->projectService = new ProjectApiService($httpClient);
        // Other services
    }
    
    public function tasks()
    {
        return $this->taskService;
    }
    
    public function projects()
    {
        return $this->projectService;
    }
}
```

#### Expected Implementation:
```php
// Using dependency injection
class AsanaClient
{
    private $services = [];
    private $httpClient;
    
    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }
    
    public function tasks()
    {
        return $this->getService('tasks', function() {
            return new TaskApiService($this->httpClient);
        });
    }
    
    public function projects()
    {
        return $this->getService('projects', function() {
            return new ProjectApiService($this->httpClient);
        });
    }
    
    private function getService($name, callable $factory)
    {
        if (!isset($this->services[$name])) {
            $this->services[$name] = $factory();
        }
        
        return $this->services[$name];
    }
    
    // Method to register custom service implementations
    public function registerService($name, $service)
    {
        $this->services[$name] = $service;
        return $this;
    }
}

// Usage with custom service
$httpClient = new ApiClient();
$asanaClient = new AsanaClient($httpClient);

// Replace a service with custom implementation
$customTaskService = new CustomTaskApiService($httpClient);
$asanaClient->registerService('tasks', $customTaskService);
```

### File References
- `src/AsanaClient.php`: Main client class that instantiates services

### API Spec Validation
This is a client-side architecture concern and doesn't directly relate to API specification compliance. However, a proper dependency injection system makes it easier to adapt to API changes or extensions.

### Critical Evaluation
- **Actual Impact**: Medium - Manual dependency instantiation makes the code less flexible and harder to test
- **Priority Level**: High - Should be addressed early to enable other improvements
- **Implementation Status**: Not implemented - Current code uses manual instantiation
- **Spec Compliance**: N/A - This is a client-side architecture concern
- **Difficulty/Complexity**: High - Requires implementing service container patterns, refactoring constructor dependencies throughout the codebase, and maintaining backward compatibility

### Recommended Action
Implement a simple service container or dependency injection pattern to manage service instances. Allow for service registration and replacement to improve testability and flexibility.
