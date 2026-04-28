<?php

namespace BrightleafDigital\Tests\Api;

use BrightleafDigital\Api\TimeTrackingCategoriesApiService;
use BrightleafDigital\Exceptions\ValidationException;
use BrightleafDigital\Http\HttpClientInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TimeTrackingCategoriesApiServiceTest extends TestCase
{
    private HttpClientInterface $mockClient;

    /** @var TimeTrackingCategoriesApiService */
    private TimeTrackingCategoriesApiService $service;

    /** @var (HttpClientInterface&MockObject)|null */
    private $mockClientMock = null;

    protected function setUp(): void
    {
        $this->mockClient = $this->createStub(HttpClientInterface::class);
        $this->mockClientMock = null;
        $this->service = new TimeTrackingCategoriesApiService($this->mockClient);
    }

    // ── getTimeTrackingCategories ─────────────────────────────────────────

    /**
     * Test getTimeTrackingCategories calls client with correct parameters.
     */
    public function testGetTimeTrackingCategories(): void
    {
        $expectedResponse = [
            ['gid' => '111', 'resource_type' => 'time_tracking_category', 'name' => 'Development'],
            ['gid' => '222', 'resource_type' => 'time_tracking_category', 'name' => 'Meetings'],
        ];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'time_tracking_categories',
                ['query' => ['workspace' => '12345']],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->getTimeTrackingCategories('12345');

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test getTimeTrackingCategories with additional options.
     */
    public function testGetTimeTrackingCategoriesWithOptions(): void
    {
        $options = ['opt_fields' => 'name,color,is_archived'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'time_tracking_categories',
                ['query' => ['opt_fields' => 'name,color,is_archived', 'workspace' => '12345']],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->getTimeTrackingCategories('12345', $options);
    }

    /**
     * Test getTimeTrackingCategories with custom response type.
     */
    public function testGetTimeTrackingCategoriesWithCustomResponseType(): void
    {
        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'time_tracking_categories',
                ['query' => ['workspace' => '12345']],
                HttpClientInterface::RESPONSE_FULL
            )
            ->willReturn([]);

        $this->service->getTimeTrackingCategories('12345', [], HttpClientInterface::RESPONSE_FULL);
    }

    /**
     * Test getTimeTrackingCategories throws exception for empty workspace GID.
     */
    public function testGetTimeTrackingCategoriesThrowsExceptionForEmptyWorkspaceGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Workspace GID must be a non-empty string.');

        $this->service->getTimeTrackingCategories('');
    }

    /**
     * Test getTimeTrackingCategories throws exception for non-numeric workspace GID.
     */
    public function testGetTimeTrackingCategoriesThrowsExceptionForNonNumericWorkspaceGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Workspace GID must be a numeric string.');

        $this->service->getTimeTrackingCategories('abc');
    }

    // ── getTimeTrackingCategory ───────────────────────────────────────────

    /**
     * Test getTimeTrackingCategory calls client with correct parameters.
     */
    public function testGetTimeTrackingCategory(): void
    {
        $expectedResponse = [
            'gid' => '12345',
            'resource_type' => 'time_tracking_category',
            'name' => 'Development',
            'color' => 'green',
            'is_archived' => false,
        ];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'time_tracking_categories/12345',
                ['query' => []],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->getTimeTrackingCategory('12345');

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test getTimeTrackingCategory with options.
     */
    public function testGetTimeTrackingCategoryWithOptions(): void
    {
        $options = ['opt_fields' => 'name,color,is_archived'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'time_tracking_categories/12345',
                ['query' => $options],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->getTimeTrackingCategory('12345', $options);
    }

    /**
     * Test getTimeTrackingCategory with custom response type.
     */
    public function testGetTimeTrackingCategoryWithCustomResponseType(): void
    {
        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'time_tracking_categories/12345',
                ['query' => []],
                HttpClientInterface::RESPONSE_FULL
            )
            ->willReturn([]);

        $this->service->getTimeTrackingCategory('12345', [], HttpClientInterface::RESPONSE_FULL);
    }

    /**
     * Test getTimeTrackingCategory throws exception for empty GID.
     */
    public function testGetTimeTrackingCategoryThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Time Tracking Category GID must be a non-empty string.');

        $this->service->getTimeTrackingCategory('');
    }

    /**
     * Test getTimeTrackingCategory throws exception for non-numeric GID.
     */
    public function testGetTimeTrackingCategoryThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Time Tracking Category GID must be a numeric string.');

        $this->service->getTimeTrackingCategory('abc');
    }

    // ── createTimeTrackingCategory ────────────────────────────────────────

    /**
     * Test createTimeTrackingCategory calls client with correct parameters.
     */
    public function testCreateTimeTrackingCategory(): void
    {
        $data = ['workspace' => '12345', 'name' => 'Development'];
        $expectedResponse = [
            'gid' => '99999',
            'resource_type' => 'time_tracking_category',
            'name' => 'Development',
        ];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'time_tracking_categories',
                ['json' => ['data' => $data], 'query' => []],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->createTimeTrackingCategory($data);

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test createTimeTrackingCategory with optional color field.
     */
    public function testCreateTimeTrackingCategoryWithColor(): void
    {
        $data = ['workspace' => '12345', 'name' => 'Development', 'color' => 'green'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'time_tracking_categories',
                ['json' => ['data' => $data], 'query' => []],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->createTimeTrackingCategory($data);
    }

    /**
     * Test createTimeTrackingCategory with options.
     */
    public function testCreateTimeTrackingCategoryWithOptions(): void
    {
        $data = ['workspace' => '12345', 'name' => 'Meetings'];
        $options = ['opt_fields' => 'name,color'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'time_tracking_categories',
                ['json' => ['data' => $data], 'query' => $options],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->createTimeTrackingCategory($data, $options);
    }

    /**
     * Test createTimeTrackingCategory with custom response type.
     */
    public function testCreateTimeTrackingCategoryWithCustomResponseType(): void
    {
        $data = ['workspace' => '12345', 'name' => 'Support'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'time_tracking_categories',
                ['json' => ['data' => $data], 'query' => []],
                HttpClientInterface::RESPONSE_FULL
            )
            ->willReturn([]);

        $this->service->createTimeTrackingCategory($data, [], HttpClientInterface::RESPONSE_FULL);
    }

    /**
     * Test createTimeTrackingCategory throws exception when workspace is missing.
     */
    public function testCreateTimeTrackingCategoryThrowsExceptionForMissingWorkspace(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(
            'Missing required field(s) for time tracking category creation: workspace'
        );

        $this->service->createTimeTrackingCategory(['name' => 'Development']);
    }

    /**
     * Test createTimeTrackingCategory throws exception when name is missing.
     */
    public function testCreateTimeTrackingCategoryThrowsExceptionForMissingName(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(
            'Missing required field(s) for time tracking category creation: name'
        );

        $this->service->createTimeTrackingCategory(['workspace' => '12345']);
    }

    /**
     * Test createTimeTrackingCategory throws exception when both fields are missing.
     */
    public function testCreateTimeTrackingCategoryThrowsExceptionForMissingBothFields(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(
            'Missing required field(s) for time tracking category creation: workspace, name'
        );

        $this->service->createTimeTrackingCategory([]);
    }

    // ── updateTimeTrackingCategory ────────────────────────────────────────

    /**
     * Test updateTimeTrackingCategory calls client with correct parameters.
     */
    public function testUpdateTimeTrackingCategory(): void
    {
        $data = ['name' => 'Backend Development'];
        $expectedResponse = ['gid' => '12345', 'name' => 'Backend Development'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'PUT',
                'time_tracking_categories/12345',
                ['json' => ['data' => $data], 'query' => []],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->updateTimeTrackingCategory('12345', $data);

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test updateTimeTrackingCategory with options.
     */
    public function testUpdateTimeTrackingCategoryWithOptions(): void
    {
        $data = ['color' => 'blue', 'is_archived' => false];
        $options = ['opt_fields' => 'name,color,is_archived'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'PUT',
                'time_tracking_categories/12345',
                ['json' => ['data' => $data], 'query' => $options],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->updateTimeTrackingCategory('12345', $data, $options);
    }

    /**
     * Test updateTimeTrackingCategory with custom response type.
     */
    public function testUpdateTimeTrackingCategoryWithCustomResponseType(): void
    {
        $data = ['is_archived' => true];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'PUT',
                'time_tracking_categories/12345',
                ['json' => ['data' => $data], 'query' => []],
                HttpClientInterface::RESPONSE_FULL
            )
            ->willReturn([]);

        $this->service->updateTimeTrackingCategory('12345', $data, [], HttpClientInterface::RESPONSE_FULL);
    }

    /**
     * Test updateTimeTrackingCategory throws exception for empty GID.
     */
    public function testUpdateTimeTrackingCategoryThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Time Tracking Category GID must be a non-empty string.');

        $this->service->updateTimeTrackingCategory('', ['name' => 'Updated']);
    }

    /**
     * Test updateTimeTrackingCategory throws exception for non-numeric GID.
     */
    public function testUpdateTimeTrackingCategoryThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Time Tracking Category GID must be a numeric string.');

        $this->service->updateTimeTrackingCategory('abc', ['name' => 'Updated']);
    }

    // ── deleteTimeTrackingCategory ────────────────────────────────────────

    /**
     * Test deleteTimeTrackingCategory calls client with correct parameters.
     */
    public function testDeleteTimeTrackingCategory(): void
    {
        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with('DELETE', 'time_tracking_categories/12345', [], HttpClientInterface::RESPONSE_DATA)
            ->willReturn([]);

        $result = $this->service->deleteTimeTrackingCategory('12345');

        $this->assertSame([], $result);
    }

    /**
     * Test deleteTimeTrackingCategory with custom response type.
     */
    public function testDeleteTimeTrackingCategoryWithCustomResponseType(): void
    {
        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with('DELETE', 'time_tracking_categories/12345', [], HttpClientInterface::RESPONSE_FULL)
            ->willReturn([]);

        $this->service->deleteTimeTrackingCategory('12345', HttpClientInterface::RESPONSE_FULL);
    }

    /**
     * Test deleteTimeTrackingCategory throws exception for empty GID.
     */
    public function testDeleteTimeTrackingCategoryThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Time Tracking Category GID must be a non-empty string.');

        $this->service->deleteTimeTrackingCategory('');
    }

    /**
     * Test deleteTimeTrackingCategory throws exception for non-numeric GID.
     */
    public function testDeleteTimeTrackingCategoryThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Time Tracking Category GID must be a numeric string.');

        $this->service->deleteTimeTrackingCategory('abc');
    }

    // ── getTimeTrackingEntriesForCategory ─────────────────────────────────

    /**
     * Test getTimeTrackingEntriesForCategory calls client with correct parameters.
     */
    public function testGetTimeTrackingEntriesForCategory(): void
    {
        $expectedResponse = [
            ['gid' => '111', 'duration_minutes' => 60, 'entered_on' => '2026-04-28'],
            ['gid' => '222', 'duration_minutes' => 30, 'entered_on' => '2026-04-27'],
        ];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'time_tracking_categories/12345/time_tracking_entries',
                ['query' => []],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->getTimeTrackingEntriesForCategory('12345');

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test getTimeTrackingEntriesForCategory with options.
     */
    public function testGetTimeTrackingEntriesForCategoryWithOptions(): void
    {
        $options = ['opt_fields' => 'created_by,duration_minutes,entered_on,task', 'limit' => 50];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'time_tracking_categories/12345/time_tracking_entries',
                ['query' => $options],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->getTimeTrackingEntriesForCategory('12345', $options);
    }

    /**
     * Test getTimeTrackingEntriesForCategory with custom response type.
     */
    public function testGetTimeTrackingEntriesForCategoryWithCustomResponseType(): void
    {
        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'time_tracking_categories/12345/time_tracking_entries',
                ['query' => []],
                HttpClientInterface::RESPONSE_FULL
            )
            ->willReturn([]);

        $this->service->getTimeTrackingEntriesForCategory('12345', [], HttpClientInterface::RESPONSE_FULL);
    }

    /**
     * Test getTimeTrackingEntriesForCategory throws exception for empty GID.
     */
    public function testGetTimeTrackingEntriesForCategoryThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Time Tracking Category GID must be a non-empty string.');

        $this->service->getTimeTrackingEntriesForCategory('');
    }

    /**
     * Test getTimeTrackingEntriesForCategory throws exception for non-numeric GID.
     */
    public function testGetTimeTrackingEntriesForCategoryThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Time Tracking Category GID must be a numeric string.');

        $this->service->getTimeTrackingEntriesForCategory('abc');
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
