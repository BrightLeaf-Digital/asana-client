 # Testing Improvements
 
 [← Back to Roadmap](roadmap.md)

This document outlines testing enhancements needed for the Asana Client PHP library. Each item includes detailed explanations, code examples, and validation against API specifications.

<!-- links:items:start -->
 Links to items:
 - [1. Add integration tests](#1-add-integration-tests) (Roadmap Item 25)
<!-- links:items:end -->

## 1. Add integration tests

### Problem Statement
The library lacks integration tests that verify its behavior against the actual Asana API. This means that while individual components might work correctly in isolation, there's no guarantee they work correctly together or with the real API.

### Code Examples

#### Current Implementation:
```php
// Only unit tests exist, no integration tests
```

#### Expected Implementation:
```php
// In tests/Integration/TaskIntegrationTest.php
class TaskIntegrationTest extends TestCase
{
    private static $client;
    private static $workspace;
    private static $createdTasks = [];

    public static function setUpBeforeClass(): void
    {
        // Use a test PAT from environment variable
        $pat = getenv('ASANA_TEST_PAT');
        if (!$pat) {
            self::markTestSkipped('ASANA_TEST_PAT environment variable not set');
        }

        self::$client = AsanaClient::withPAT($pat);

        // Get a workspace to use for testing
        $workspaces = self::$client->workspaces()->getWorkspaces();
        if (empty($workspaces['data'])) {
            self::markTestSkipped('No workspaces available for testing');
        }

        self::$workspace = $workspaces['data'][0]['gid'];
    }

    public static function tearDownAfterClass(): void
    {
        // Clean up created tasks
        foreach (self::$createdTasks as $taskId) {
            try {
                self::$client->tasks()->deleteTask($taskId);
            } catch (Exception $e) {
                // Log but continue cleanup
                error_log("Failed to delete task {$taskId}: " . $e->getMessage());
            }
        }
    }

    public function testCreateAndGetTask()
    {
        $taskName = 'Integration Test Task ' . uniqid();
        $taskData = [
            'name' => $taskName,
            'workspace' => self::$workspace,
            'notes' => 'Created by integration test'
        ];

        // Create a task
        $createResponse = self::$client->tasks()->createTask($taskData);
        $this->assertArrayHasKey('data', $createResponse);
        $this->assertArrayHasKey('gid', $createResponse['data']);
        $this->assertEquals($taskName, $createResponse['data']['name']);

        $taskId = $createResponse['data']['gid'];
        self::$createdTasks[] = $taskId; // Store for cleanup

        // Get the task
        $getResponse = self::$client->tasks()->getTask($taskId);
        $this->assertArrayHasKey('data', $getResponse);
        $this->assertEquals($taskId, $getResponse['data']['gid']);
        $this->assertEquals($taskName, $getResponse['data']['name']);
    }

    public function testUpdateTask()
    {
        // Create a task for updating
        $taskName = 'Update Test Task ' . uniqid();
        $taskData = [
            'name' => $taskName,
            'workspace' => self::$workspace
        ];

        $createResponse = self::$client->tasks()->createTask($taskData);
        $taskId = $createResponse['data']['gid'];
        self::$createdTasks[] = $taskId; // Store for cleanup

        // Update the task
        $updatedName = 'Updated ' . $taskName;
        $updateResponse = self::$client->tasks()->updateTask($taskId, [
            'name' => $updatedName,
            'notes' => 'Updated by integration test'
        ]);

        $this->assertArrayHasKey('data', $updateResponse);
        $this->assertEquals($updatedName, $updateResponse['data']['name']);

        // Verify the update
        $getResponse = self::$client->tasks()->getTask($taskId);
        $this->assertEquals($updatedName, $getResponse['data']['name']);
    }

    // Additional integration tests...
}
```

### File References
- `tests/Integration/`: New directory for integration tests
- `tests/Integration/TaskIntegrationTest.php`: Integration tests for tasks
- `tests/Integration/ProjectIntegrationTest.php`: Integration tests for projects
- `phpunit.xml`: Configuration for running integration tests separately

### API Spec Validation
Integration tests directly validate compliance with the API specification by testing against the actual API. They verify that:
1. The client can successfully communicate with the API
2. Requests are formatted correctly according to the API specification
3. Responses are processed correctly
4. Error handling works as expected with real API errors

### Critical Evaluation
- **Actual Impact**: High - Without integration tests, there's no guarantee the library works correctly with the actual API
- **Priority Level**: Medium (Moved to Phase 5) - Should be addressed after core API coverage is complete.
- **Implementation Status**: Not implemented - No integration tests exist
- **Feasibility Note**: Requires assessment of Asana Sandbox account availability and their 1-year duration limitation.
- **Spec Compliance**: Not validated - Without integration tests, compliance with the API specification isn't verified
- **Difficulty/Complexity**: High - Requires setting up test environments, handling real API calls, managing test data cleanup, and dealing with external dependencies

### Recommended Action
Create a suite of integration tests that verify the library's behavior against the actual Asana API. Use a test account or sandbox environment to avoid affecting production data.
