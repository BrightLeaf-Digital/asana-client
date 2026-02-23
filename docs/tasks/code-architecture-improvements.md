 # Code Architecture Improvements
 
 [← Back to Roadmap](roadmap.md)

This document outlines architectural enhancements needed for the Asana Client PHP library. Each item includes detailed explanations, code examples, and validation against API specifications.

<!-- links:items:start -->
 Links to items:
 - [1. Implement interfaces for all major components](#1-implement-interfaces-for-all-major-components) (Roadmap Item 10)
 - [2. Separate configuration from implementation](#2-separate-configuration-from-implementation) (Roadmap Item 18)
 - [3. Implement proper service container/dependency injection](#3-implement-proper-service-containerdependency-injection) (Roadmap Item 11)
<!-- links:items:end -->

## ~~1. Implement interfaces for all major components~~

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

## ~~3. Implement proper service container/dependency injection~~
