<?php

namespace BrightleafDigital\Tests\Api;

use BrightleafDigital\Api\TimesheetApprovalStatusesApiService;
use BrightleafDigital\Exceptions\ValidationException;
use BrightleafDigital\Http\HttpClientInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TimesheetApprovalStatusesApiServiceTest extends TestCase
{
    private HttpClientInterface $mockClient;

    /** @var TimesheetApprovalStatusesApiService */
    private TimesheetApprovalStatusesApiService $service;

    /** @var (HttpClientInterface&MockObject)|null */
    private $mockClientMock = null;

    protected function setUp(): void
    {
        $this->mockClient = $this->createStub(HttpClientInterface::class);
        $this->mockClientMock = null;
        $this->service = new TimesheetApprovalStatusesApiService($this->mockClient);
    }

    // ── getTimesheetApprovalStatuses ──────────────────────────────────────

    /**
     * Test getTimesheetApprovalStatuses calls client with correct parameters.
     */
    public function testGetTimesheetApprovalStatuses(): void
    {
        $expectedResponse = [
            ['gid' => '111', 'resource_type' => 'timesheet_approval_status', 'approval_status' => 'DRAFT'],
            ['gid' => '222', 'resource_type' => 'timesheet_approval_status', 'approval_status' => 'SUBMITTED'],
        ];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'timesheet_approval_statuses',
                ['query' => []],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->getTimesheetApprovalStatuses();

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test getTimesheetApprovalStatuses with filtering options.
     */
    public function testGetTimesheetApprovalStatusesWithOptions(): void
    {
        $options = [
            'workspace' => '12345',
            'user' => '67890',
            'start_date' => '2026-04-01',
            'end_date' => '2026-04-30',
        ];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'timesheet_approval_statuses',
                ['query' => $options],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->getTimesheetApprovalStatuses($options);
    }

    /**
     * Test getTimesheetApprovalStatuses with custom response type.
     */
    public function testGetTimesheetApprovalStatusesWithCustomResponseType(): void
    {
        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'timesheet_approval_statuses',
                ['query' => []],
                HttpClientInterface::RESPONSE_FULL
            )
            ->willReturn([]);

        $this->service->getTimesheetApprovalStatuses([], HttpClientInterface::RESPONSE_FULL);
    }

    // ── getTimesheetApprovalStatus ────────────────────────────────────────

    /**
     * Test getTimesheetApprovalStatus calls client with correct parameters.
     */
    public function testGetTimesheetApprovalStatus(): void
    {
        $expectedResponse = [
            'gid' => '12345',
            'resource_type' => 'timesheet_approval_status',
            'approval_status' => 'DRAFT',
            'start_on' => '2026-04-21',
            'end_on' => '2026-04-27',
        ];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'timesheet_approval_statuses/12345',
                ['query' => []],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->getTimesheetApprovalStatus('12345');

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test getTimesheetApprovalStatus with options.
     */
    public function testGetTimesheetApprovalStatusWithOptions(): void
    {
        $options = ['opt_fields' => 'user,workspace,approval_status,start_on,end_on'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'timesheet_approval_statuses/12345',
                ['query' => $options],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->getTimesheetApprovalStatus('12345', $options);
    }

    /**
     * Test getTimesheetApprovalStatus with custom response type.
     */
    public function testGetTimesheetApprovalStatusWithCustomResponseType(): void
    {
        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'timesheet_approval_statuses/12345',
                ['query' => []],
                HttpClientInterface::RESPONSE_FULL
            )
            ->willReturn([]);

        $this->service->getTimesheetApprovalStatus('12345', [], HttpClientInterface::RESPONSE_FULL);
    }

    /**
     * Test getTimesheetApprovalStatus throws exception for empty GID.
     */
    public function testGetTimesheetApprovalStatusThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Timesheet Approval Status GID must be a non-empty string.');

        $this->service->getTimesheetApprovalStatus('');
    }

    /**
     * Test getTimesheetApprovalStatus throws exception for non-numeric GID.
     */
    public function testGetTimesheetApprovalStatusThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Timesheet Approval Status GID must be a numeric string.');

        $this->service->getTimesheetApprovalStatus('abc');
    }

    // ── getOrCreateTimesheetApprovalStatus ────────────────────────────────

    /**
     * Test getOrCreateTimesheetApprovalStatus calls client with correct parameters.
     */
    public function testGetOrCreateTimesheetApprovalStatus(): void
    {
        $data = [
            'workspace' => '12345',
            'user' => '67890',
            'date' => '2026-04-28',
        ];
        $expectedResponse = [
            'gid' => '99999',
            'resource_type' => 'timesheet_approval_status',
            'approval_status' => 'DRAFT',
        ];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'timesheet_approval_statuses',
                ['json' => ['data' => $data], 'query' => []],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->getOrCreateTimesheetApprovalStatus($data);

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test getOrCreateTimesheetApprovalStatus with options.
     */
    public function testGetOrCreateTimesheetApprovalStatusWithOptions(): void
    {
        $data = ['workspace' => '12345', 'user' => '67890', 'date' => '2026-04-28'];
        $options = ['opt_fields' => 'user,workspace,approval_status'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'timesheet_approval_statuses',
                ['json' => ['data' => $data], 'query' => $options],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->getOrCreateTimesheetApprovalStatus($data, $options);
    }

    /**
     * Test getOrCreateTimesheetApprovalStatus with custom response type.
     */
    public function testGetOrCreateTimesheetApprovalStatusWithCustomResponseType(): void
    {
        $data = ['workspace' => '12345', 'user' => '67890', 'date' => '2026-04-28'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'timesheet_approval_statuses',
                ['json' => ['data' => $data], 'query' => []],
                HttpClientInterface::RESPONSE_FULL
            )
            ->willReturn([]);

        $this->service->getOrCreateTimesheetApprovalStatus($data, [], HttpClientInterface::RESPONSE_FULL);
    }

    /**
     * Test getOrCreateTimesheetApprovalStatus throws exception when workspace is missing.
     */
    public function testGetOrCreateThrowsExceptionForMissingWorkspace(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(
            'Missing required field(s) for timesheet approval status get-or-create: workspace'
        );

        $this->service->getOrCreateTimesheetApprovalStatus(['user' => '67890', 'date' => '2026-04-28']);
    }

    /**
     * Test getOrCreateTimesheetApprovalStatus throws exception when user is missing.
     */
    public function testGetOrCreateThrowsExceptionForMissingUser(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(
            'Missing required field(s) for timesheet approval status get-or-create: user'
        );

        $this->service->getOrCreateTimesheetApprovalStatus(['workspace' => '12345', 'date' => '2026-04-28']);
    }

    /**
     * Test getOrCreateTimesheetApprovalStatus throws exception when date is missing.
     */
    public function testGetOrCreateThrowsExceptionForMissingDate(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(
            'Missing required field(s) for timesheet approval status get-or-create: date'
        );

        $this->service->getOrCreateTimesheetApprovalStatus(['workspace' => '12345', 'user' => '67890']);
    }

    /**
     * Test getOrCreateTimesheetApprovalStatus throws exception when all fields are missing.
     */
    public function testGetOrCreateThrowsExceptionForMissingAllFields(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(
            'Missing required field(s) for timesheet approval status get-or-create: workspace, user, date'
        );

        $this->service->getOrCreateTimesheetApprovalStatus([]);
    }

    // ── updateTimesheetApprovalStatus ─────────────────────────────────────

    /**
     * Test updateTimesheetApprovalStatus calls client with correct parameters.
     */
    public function testUpdateTimesheetApprovalStatus(): void
    {
        $data = ['approval_status' => 'SUBMITTED'];
        $expectedResponse = [
            'gid' => '12345',
            'resource_type' => 'timesheet_approval_status',
            'approval_status' => 'SUBMITTED',
        ];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'PUT',
                'timesheet_approval_statuses/12345',
                ['json' => ['data' => $data], 'query' => []],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->updateTimesheetApprovalStatus('12345', $data);

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test updateTimesheetApprovalStatus with options.
     */
    public function testUpdateTimesheetApprovalStatusWithOptions(): void
    {
        $data = ['approval_status' => 'APPROVED', 'message' => 'Looks good!'];
        $options = ['opt_fields' => 'approval_status,user'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'PUT',
                'timesheet_approval_statuses/12345',
                ['json' => ['data' => $data], 'query' => $options],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->updateTimesheetApprovalStatus('12345', $data, $options);
    }

    /**
     * Test updateTimesheetApprovalStatus with custom response type.
     */
    public function testUpdateTimesheetApprovalStatusWithCustomResponseType(): void
    {
        $data = ['approval_status' => 'REJECTED'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'PUT',
                'timesheet_approval_statuses/12345',
                ['json' => ['data' => $data], 'query' => []],
                HttpClientInterface::RESPONSE_FULL
            )
            ->willReturn([]);

        $this->service->updateTimesheetApprovalStatus('12345', $data, [], HttpClientInterface::RESPONSE_FULL);
    }

    /**
     * Test updateTimesheetApprovalStatus throws exception for empty GID.
     */
    public function testUpdateTimesheetApprovalStatusThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Timesheet Approval Status GID must be a non-empty string.');

        $this->service->updateTimesheetApprovalStatus('', ['approval_status' => 'SUBMITTED']);
    }

    /**
     * Test updateTimesheetApprovalStatus throws exception for non-numeric GID.
     */
    public function testUpdateTimesheetApprovalStatusThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Timesheet Approval Status GID must be a numeric string.');

        $this->service->updateTimesheetApprovalStatus('abc', ['approval_status' => 'SUBMITTED']);
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
