<?php

namespace BrightleafDigital\Tests\Api;

use BrightleafDigital\Api\GoalsApiService;
use BrightleafDigital\Exceptions\ValidationException;
use BrightleafDigital\Http\HttpClientInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GoalsApiServiceTest extends TestCase
{
    private HttpClientInterface $mockClient;

    /** @var GoalsApiService */
    private GoalsApiService $service;

    /** @var (HttpClientInterface&MockObject)|null */
    private $mockClientMock = null;

    protected function setUp(): void
    {
        $this->mockClient = $this->createStub(HttpClientInterface::class);
        $this->mockClientMock = null;
        $this->service = new GoalsApiService($this->mockClient);
    }

    // ── getGoal ─────────────────────────────────────────────────────────

    /**
     * Test getGoal calls client with correct parameters.
     */
    public function testGetGoal(): void
    {
        $expectedResponse = ['gid' => '12345', 'resource_type' => 'goal', 'name' => 'My Goal'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with('GET', 'goals/12345', ['query' => []], HttpClientInterface::RESPONSE_DATA)
            ->willReturn($expectedResponse);

        $result = $this->service->getGoal('12345');

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test getGoal with options.
     */
    public function testGetGoalWithOptions(): void
    {
        $options = ['opt_fields' => 'name,owner,workspace'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with('GET', 'goals/12345', ['query' => $options], HttpClientInterface::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getGoal('12345', $options);
    }

    /**
     * Test getGoal with custom response type.
     */
    public function testGetGoalWithCustomResponseType(): void
    {
        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with('GET', 'goals/12345', ['query' => []], HttpClientInterface::RESPONSE_FULL)
            ->willReturn([]);

        $this->service->getGoal('12345', [], HttpClientInterface::RESPONSE_FULL);
    }

    /**
     * Test getGoal throws exception for empty GID.
     */
    public function testGetGoalThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Goal GID must be a non-empty string.');

        $this->service->getGoal('');
    }

    /**
     * Test getGoal throws exception for non-numeric GID.
     */
    public function testGetGoalThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Goal GID must be a numeric string.');

        $this->service->getGoal('abc');
    }

    // ── updateGoal ──────────────────────────────────────────────────────

    /**
     * Test updateGoal calls client with correct parameters.
     */
    public function testUpdateGoal(): void
    {
        $data = ['name' => 'Updated Goal'];
        $expectedResponse = ['gid' => '12345', 'name' => 'Updated Goal'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'PUT',
                'goals/12345',
                ['json' => ['data' => $data], 'query' => []],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->updateGoal('12345', $data);

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test updateGoal with options.
     */
    public function testUpdateGoalWithOptions(): void
    {
        $data = ['name' => 'Updated'];
        $options = ['opt_fields' => 'name,owner'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'PUT',
                'goals/12345',
                ['json' => ['data' => $data], 'query' => $options],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->updateGoal('12345', $data, $options);
    }

    /**
     * Test updateGoal with custom response type.
     */
    public function testUpdateGoalWithCustomResponseType(): void
    {
        $data = ['name' => 'Updated'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'PUT',
                'goals/12345',
                ['json' => ['data' => $data], 'query' => []],
                HttpClientInterface::RESPONSE_FULL
            )
            ->willReturn([]);

        $this->service->updateGoal('12345', $data, [], HttpClientInterface::RESPONSE_FULL);
    }

    /**
     * Test updateGoal throws exception for empty GID.
     */
    public function testUpdateGoalThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Goal GID must be a non-empty string.');

        $this->service->updateGoal('', ['name' => 'Test']);
    }

    /**
     * Test updateGoal throws exception for non-numeric GID.
     */
    public function testUpdateGoalThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Goal GID must be a numeric string.');

        $this->service->updateGoal('abc', ['name' => 'Test']);
    }

    // ── deleteGoal ──────────────────────────────────────────────────────

    /**
     * Test deleteGoal calls client with correct parameters.
     */
    public function testDeleteGoal(): void
    {
        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with('DELETE', 'goals/12345', [], HttpClientInterface::RESPONSE_DATA)
            ->willReturn([]);

        $result = $this->service->deleteGoal('12345');

        $this->assertSame([], $result);
    }

    /**
     * Test deleteGoal with custom response type.
     */
    public function testDeleteGoalWithCustomResponseType(): void
    {
        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with('DELETE', 'goals/12345', [], HttpClientInterface::RESPONSE_FULL)
            ->willReturn([]);

        $this->service->deleteGoal('12345', HttpClientInterface::RESPONSE_FULL);
    }

    /**
     * Test deleteGoal throws exception for empty GID.
     */
    public function testDeleteGoalThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Goal GID must be a non-empty string.');

        $this->service->deleteGoal('');
    }

    /**
     * Test deleteGoal throws exception for non-numeric GID.
     */
    public function testDeleteGoalThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Goal GID must be a numeric string.');

        $this->service->deleteGoal('abc');
    }

    // ── getGoals ────────────────────────────────────────────────────────

    /**
     * Test getGoals calls client with correct parameters.
     */
    public function testGetGoals(): void
    {
        $expectedResponse = [
            ['gid' => '111', 'name' => 'Goal A'],
            ['gid' => '222', 'name' => 'Goal B'],
        ];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'goals',
                ['query' => []],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->getGoals();

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test getGoals with filtering options.
     */
    public function testGetGoalsWithOptions(): void
    {
        $options = ['workspace' => '12345', 'opt_fields' => 'name,owner'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'goals',
                ['query' => $options],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->getGoals($options);
    }

    /**
     * Test getGoals with custom response type.
     */
    public function testGetGoalsWithCustomResponseType(): void
    {
        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'goals',
                ['query' => []],
                HttpClientInterface::RESPONSE_FULL
            )
            ->willReturn([]);

        $this->service->getGoals([], HttpClientInterface::RESPONSE_FULL);
    }

    /**
     * Test getGoals with workspace and team filters.
     */
    public function testGetGoalsWithMultipleFilters(): void
    {
        $options = [
            'workspace' => '12345',
            'team' => '67890',
            'is_workspace_level' => true,
        ];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'goals',
                ['query' => $options],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->getGoals($options);
    }

    // ── createGoal ──────────────────────────────────────────────────────

    /**
     * Test createGoal calls client with correct parameters.
     */
    public function testCreateGoal(): void
    {
        $data = ['name' => 'Increase revenue by 20%', 'workspace' => '12345'];
        $expectedResponse = ['gid' => '99999', 'resource_type' => 'goal', 'name' => 'Increase revenue by 20%'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'goals',
                ['json' => ['data' => $data], 'query' => []],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->createGoal($data);

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test createGoal with options.
     */
    public function testCreateGoalWithOptions(): void
    {
        $data = ['name' => 'Increase revenue by 20%', 'workspace' => '12345'];
        $options = ['opt_fields' => 'name,owner,workspace'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'goals',
                ['json' => ['data' => $data], 'query' => $options],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->createGoal($data, $options);
    }

    /**
     * Test createGoal with optional fields.
     */
    public function testCreateGoalWithOptionalFields(): void
    {
        $data = [
            'name' => 'Increase revenue by 20%',
            'workspace' => '12345',
            'due_on' => '2026-12-31',
            'owner' => '67890',
            'notes' => 'Important business goal',
        ];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'goals',
                ['json' => ['data' => $data], 'query' => []],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->createGoal($data);
    }

    // ── createGoalMetric ────────────────────────────────────────────────

    /**
     * Test createGoalMetric calls client with correct parameters.
     */
    public function testCreateGoalMetric(): void
    {
        $data = [
            'metric_type' => 'number',
            'initial_number_value' => 0,
            'target_number_value' => 100,
        ];
        $expectedResponse = ['gid' => '12345', 'name' => 'My Goal'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'goals/12345/setMetric',
                ['json' => ['data' => $data], 'query' => []],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->createGoalMetric('12345', $data);

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test createGoalMetric with options.
     */
    public function testCreateGoalMetricWithOptions(): void
    {
        $data = ['metric_type' => 'percentage', 'target_number_value' => 100];
        $options = ['opt_fields' => 'name,metric'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'goals/12345/setMetric',
                ['json' => ['data' => $data], 'query' => $options],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->createGoalMetric('12345', $data, $options);
    }

    /**
     * Test createGoalMetric with custom response type.
     */
    public function testCreateGoalMetricWithCustomResponseType(): void
    {
        $data = ['metric_type' => 'number', 'target_number_value' => 50];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'goals/12345/setMetric',
                ['json' => ['data' => $data], 'query' => []],
                HttpClientInterface::RESPONSE_FULL
            )
            ->willReturn([]);

        $this->service->createGoalMetric('12345', $data, [], HttpClientInterface::RESPONSE_FULL);
    }

    /**
     * Test createGoalMetric throws exception for empty GID.
     */
    public function testCreateGoalMetricThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Goal GID must be a non-empty string.');

        $this->service->createGoalMetric('', ['metric_type' => 'number']);
    }

    /**
     * Test createGoalMetric throws exception for non-numeric GID.
     */
    public function testCreateGoalMetricThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Goal GID must be a numeric string.');

        $this->service->createGoalMetric('abc', ['metric_type' => 'number']);
    }

    // ── updateGoalMetric ────────────────────────────────────────────────

    /**
     * Test updateGoalMetric calls client with correct parameters.
     */
    public function testUpdateGoalMetric(): void
    {
        $data = ['current_number_value' => 50];
        $expectedResponse = ['gid' => '12345', 'name' => 'My Goal'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'goals/12345/setMetricCurrentValue',
                ['json' => ['data' => $data], 'query' => []],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->updateGoalMetric('12345', $data);

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test updateGoalMetric with options.
     */
    public function testUpdateGoalMetricWithOptions(): void
    {
        $data = ['current_number_value' => 75];
        $options = ['opt_fields' => 'name,metric'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'goals/12345/setMetricCurrentValue',
                ['json' => ['data' => $data], 'query' => $options],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->updateGoalMetric('12345', $data, $options);
    }

    /**
     * Test updateGoalMetric with custom response type.
     */
    public function testUpdateGoalMetricWithCustomResponseType(): void
    {
        $data = ['current_number_value' => 25];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'goals/12345/setMetricCurrentValue',
                ['json' => ['data' => $data], 'query' => []],
                HttpClientInterface::RESPONSE_FULL
            )
            ->willReturn([]);

        $this->service->updateGoalMetric('12345', $data, [], HttpClientInterface::RESPONSE_FULL);
    }

    /**
     * Test updateGoalMetric throws exception for empty GID.
     */
    public function testUpdateGoalMetricThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Goal GID must be a non-empty string.');

        $this->service->updateGoalMetric('', ['current_number_value' => 50]);
    }

    /**
     * Test updateGoalMetric throws exception for non-numeric GID.
     */
    public function testUpdateGoalMetricThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Goal GID must be a numeric string.');

        $this->service->updateGoalMetric('abc', ['current_number_value' => 50]);
    }

    // ── addFollowers ────────────────────────────────────────────────────

    /**
     * Test addFollowers calls client with correct parameters.
     */
    public function testAddFollowers(): void
    {
        $followers = ['67890', '11111'];
        $expectedResponse = ['gid' => '12345', 'name' => 'My Goal'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'goals/12345/addFollowers',
                ['json' => ['data' => ['followers' => $followers]], 'query' => []],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->addFollowers('12345', $followers);

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test addFollowers with options.
     */
    public function testAddFollowersWithOptions(): void
    {
        $followers = ['67890'];
        $options = ['opt_fields' => 'name,followers'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'goals/12345/addFollowers',
                ['json' => ['data' => ['followers' => $followers]], 'query' => $options],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->addFollowers('12345', $followers, $options);
    }

    /**
     * Test addFollowers with custom response type.
     */
    public function testAddFollowersWithCustomResponseType(): void
    {
        $followers = ['67890'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'goals/12345/addFollowers',
                ['json' => ['data' => ['followers' => $followers]], 'query' => []],
                HttpClientInterface::RESPONSE_FULL
            )
            ->willReturn([]);

        $this->service->addFollowers('12345', $followers, [], HttpClientInterface::RESPONSE_FULL);
    }

    /**
     * Test addFollowers throws exception for empty GID.
     */
    public function testAddFollowersThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Goal GID must be a non-empty string.');

        $this->service->addFollowers('', ['67890']);
    }

    /**
     * Test addFollowers throws exception for non-numeric GID.
     */
    public function testAddFollowersThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Goal GID must be a numeric string.');

        $this->service->addFollowers('abc', ['67890']);
    }

    // ── removeFollowers ─────────────────────────────────────────────────

    /**
     * Test removeFollowers calls client with correct parameters.
     */
    public function testRemoveFollowers(): void
    {
        $followers = ['67890', '11111'];
        $expectedResponse = ['gid' => '12345', 'name' => 'My Goal'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'goals/12345/removeFollowers',
                ['json' => ['data' => ['followers' => $followers]], 'query' => []],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->removeFollowers('12345', $followers);

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test removeFollowers with options.
     */
    public function testRemoveFollowersWithOptions(): void
    {
        $followers = ['67890'];
        $options = ['opt_fields' => 'name,followers'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'goals/12345/removeFollowers',
                ['json' => ['data' => ['followers' => $followers]], 'query' => $options],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->removeFollowers('12345', $followers, $options);
    }

    /**
     * Test removeFollowers with custom response type.
     */
    public function testRemoveFollowersWithCustomResponseType(): void
    {
        $followers = ['67890'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'goals/12345/removeFollowers',
                ['json' => ['data' => ['followers' => $followers]], 'query' => []],
                HttpClientInterface::RESPONSE_FULL
            )
            ->willReturn([]);

        $this->service->removeFollowers('12345', $followers, [], HttpClientInterface::RESPONSE_FULL);
    }

    /**
     * Test removeFollowers throws exception for empty GID.
     */
    public function testRemoveFollowersThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Goal GID must be a non-empty string.');

        $this->service->removeFollowers('', ['67890']);
    }

    /**
     * Test removeFollowers throws exception for non-numeric GID.
     */
    public function testRemoveFollowersThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Goal GID must be a numeric string.');

        $this->service->removeFollowers('abc', ['67890']);
    }

    // ── getParentGoalsForGoal ───────────────────────────────────────────

    /**
     * Test getParentGoalsForGoal calls client with correct parameters.
     */
    public function testGetParentGoalsForGoal(): void
    {
        $expectedResponse = [['gid' => '111', 'name' => 'Parent Goal']];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with('GET', 'goals/12345/parentGoals', ['query' => []], HttpClientInterface::RESPONSE_DATA)
            ->willReturn($expectedResponse);

        $result = $this->service->getParentGoalsForGoal('12345');

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test getParentGoalsForGoal with options.
     */
    public function testGetParentGoalsForGoalWithOptions(): void
    {
        $options = ['opt_fields' => 'name,owner,workspace'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with('GET', 'goals/12345/parentGoals', ['query' => $options], HttpClientInterface::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getParentGoalsForGoal('12345', $options);
    }

    /**
     * Test getParentGoalsForGoal with custom response type.
     */
    public function testGetParentGoalsForGoalWithCustomResponseType(): void
    {
        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with('GET', 'goals/12345/parentGoals', ['query' => []], HttpClientInterface::RESPONSE_FULL)
            ->willReturn([]);

        $this->service->getParentGoalsForGoal('12345', [], HttpClientInterface::RESPONSE_FULL);
    }

    /**
     * Test getParentGoalsForGoal throws exception for empty GID.
     */
    public function testGetParentGoalsForGoalThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Goal GID must be a non-empty string.');

        $this->service->getParentGoalsForGoal('');
    }

    /**
     * Test getParentGoalsForGoal throws exception for non-numeric GID.
     */
    public function testGetParentGoalsForGoalThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Goal GID must be a numeric string.');

        $this->service->getParentGoalsForGoal('abc');
    }

    // ── addCustomFieldSettingForGoal ────────────────────────────────────

    /**
     * Test addCustomFieldSettingForGoal calls client with correct parameters.
     */
    public function testAddCustomFieldSettingForGoal(): void
    {
        $data = ['custom_field' => '67890', 'is_important' => true];
        $expectedResponse = [
            'gid' => '99999',
            'resource_type' => 'custom_field_setting',
            'custom_field' => ['gid' => '67890'],
        ];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'goals/12345/addCustomFieldSetting',
                ['json' => ['data' => $data]],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->addCustomFieldSettingForGoal('12345', $data);

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test addCustomFieldSettingForGoal with custom response type.
     */
    public function testAddCustomFieldSettingForGoalWithCustomResponseType(): void
    {
        $data = ['custom_field' => '67890'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'goals/12345/addCustomFieldSetting',
                ['json' => ['data' => $data]],
                HttpClientInterface::RESPONSE_FULL
            )
            ->willReturn([]);

        $this->service->addCustomFieldSettingForGoal('12345', $data, HttpClientInterface::RESPONSE_FULL);
    }

    /**
     * Test addCustomFieldSettingForGoal throws exception for empty GID.
     */
    public function testAddCustomFieldSettingForGoalThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Goal GID must be a non-empty string.');

        $this->service->addCustomFieldSettingForGoal('', ['custom_field' => '67890']);
    }

    /**
     * Test addCustomFieldSettingForGoal throws exception for non-numeric GID.
     */
    public function testAddCustomFieldSettingForGoalThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Goal GID must be a numeric string.');

        $this->service->addCustomFieldSettingForGoal('abc', ['custom_field' => '67890']);
    }

    // ── removeCustomFieldSettingForGoal ─────────────────────────────────

    /**
     * Test removeCustomFieldSettingForGoal calls client with correct parameters.
     */
    public function testRemoveCustomFieldSettingForGoal(): void
    {
        $data = ['custom_field' => '67890'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'goals/12345/removeCustomFieldSetting',
                ['json' => ['data' => $data]],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn([]);

        $result = $this->service->removeCustomFieldSettingForGoal('12345', $data);

        $this->assertSame([], $result);
    }

    /**
     * Test removeCustomFieldSettingForGoal with custom response type.
     */
    public function testRemoveCustomFieldSettingForGoalWithCustomResponseType(): void
    {
        $data = ['custom_field' => '67890'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'goals/12345/removeCustomFieldSetting',
                ['json' => ['data' => $data]],
                HttpClientInterface::RESPONSE_FULL
            )
            ->willReturn([]);

        $this->service->removeCustomFieldSettingForGoal('12345', $data, HttpClientInterface::RESPONSE_FULL);
    }

    /**
     * Test removeCustomFieldSettingForGoal throws exception for empty GID.
     */
    public function testRemoveCustomFieldSettingForGoalThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Goal GID must be a non-empty string.');

        $this->service->removeCustomFieldSettingForGoal('', ['custom_field' => '67890']);
    }

    /**
     * Test removeCustomFieldSettingForGoal throws exception for non-numeric GID.
     */
    public function testRemoveCustomFieldSettingForGoalThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Goal GID must be a numeric string.');

        $this->service->removeCustomFieldSettingForGoal('abc', ['custom_field' => '67890']);
    }

    // ── getStoriesForGoal ────────────────────────────────────────────────

    /**
     * Test getStoriesForGoal calls client with correct parameters.
     */
    public function testGetStoriesForGoal(): void
    {
        $expectedResponse = [
            ['gid' => '111', 'resource_type' => 'story', 'text' => 'Great progress!'],
            ['gid' => '222', 'resource_type' => 'story', 'text' => 'On track!'],
        ];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with('GET', 'goals/12345/stories', ['query' => []], HttpClientInterface::RESPONSE_DATA)
            ->willReturn($expectedResponse);

        $result = $this->service->getStoriesForGoal('12345');

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test getStoriesForGoal with options.
     */
    public function testGetStoriesForGoalWithOptions(): void
    {
        $options = ['opt_fields' => 'text,created_at,created_by', 'limit' => 50];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with('GET', 'goals/12345/stories', ['query' => $options], HttpClientInterface::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getStoriesForGoal('12345', $options);
    }

    /**
     * Test getStoriesForGoal with custom response type.
     */
    public function testGetStoriesForGoalWithCustomResponseType(): void
    {
        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with('GET', 'goals/12345/stories', ['query' => []], HttpClientInterface::RESPONSE_FULL)
            ->willReturn([]);

        $this->service->getStoriesForGoal('12345', [], HttpClientInterface::RESPONSE_FULL);
    }

    /**
     * Test getStoriesForGoal throws exception for empty GID.
     */
    public function testGetStoriesForGoalThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Goal GID must be a non-empty string.');

        $this->service->getStoriesForGoal('');
    }

    /**
     * Test getStoriesForGoal throws exception for non-numeric GID.
     */
    public function testGetStoriesForGoalThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Goal GID must be a numeric string.');

        $this->service->getStoriesForGoal('abc');
    }

    // ── createStoryForGoal ───────────────────────────────────────────────

    /**
     * Test createStoryForGoal calls client with correct parameters.
     */
    public function testCreateStoryForGoal(): void
    {
        $data = ['text' => 'Great progress this week!'];
        $expectedResponse = [
            'gid' => '99999',
            'resource_type' => 'story',
            'text' => 'Great progress this week!',
        ];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'goals/12345/stories',
                ['json' => ['data' => $data], 'query' => []],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->createStoryForGoal('12345', $data);

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test createStoryForGoal with html_text.
     */
    public function testCreateStoryForGoalWithHtmlText(): void
    {
        $data = ['html_text' => '<body>Great <em>progress</em>!</body>'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'goals/12345/stories',
                ['json' => ['data' => $data], 'query' => []],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->createStoryForGoal('12345', $data);
    }

    /**
     * Test createStoryForGoal with options.
     */
    public function testCreateStoryForGoalWithOptions(): void
    {
        $data = ['text' => 'Update'];
        $options = ['opt_fields' => 'text,created_at,created_by'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'goals/12345/stories',
                ['json' => ['data' => $data], 'query' => $options],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->createStoryForGoal('12345', $data, $options);
    }

    /**
     * Test createStoryForGoal with custom response type.
     */
    public function testCreateStoryForGoalWithCustomResponseType(): void
    {
        $data = ['text' => 'Update'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'goals/12345/stories',
                ['json' => ['data' => $data], 'query' => []],
                HttpClientInterface::RESPONSE_FULL
            )
            ->willReturn([]);

        $this->service->createStoryForGoal('12345', $data, [], HttpClientInterface::RESPONSE_FULL);
    }

    /**
     * Test createStoryForGoal throws exception for empty GID.
     */
    public function testCreateStoryForGoalThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Goal GID must be a non-empty string.');

        $this->service->createStoryForGoal('', ['text' => 'Update']);
    }

    /**
     * Test createStoryForGoal throws exception for non-numeric GID.
     */
    public function testCreateStoryForGoalThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Goal GID must be a numeric string.');

        $this->service->createStoryForGoal('abc', ['text' => 'Update']);
    }

    /**
     * @return HttpClientInterface&MockObject
     */
    private function mockClient(): HttpClientInterface
    {
        if ($this->mockClientMock === null) {
            $this->mockClientMock = $this->createMock(HttpClientInterface::class);
            $this->mockClient = $this->mockClientMock;
            $serviceClass = $this->service::class;
            $this->service = new $serviceClass($this->mockClient);
        }

        return $this->mockClientMock;
    }
}
