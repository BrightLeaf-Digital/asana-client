<?php

namespace BrightleafDigital\Tests\Api;

use BrightleafDigital\Api\AuditLogApiService;
use BrightleafDigital\Http\HttpClientInterface;
use PHPUnit\Framework\TestCase;

class AuditLogApiServiceTest extends TestCase
{
    /** @var AuditLogApiService */
    private AuditLogApiService $service;

    protected function setUp(): void
    {
        $client = $this->createStub(HttpClientInterface::class);
        $this->service = new AuditLogApiService($client);
    }

    /**
     * Test getAuditLogEvents calls client with correct parameters.
     */
    public function testGetAuditLogEvents(): void
    {
        $workspaceGid = '12345';
        $mockClient = $this->createMock(HttpClientInterface::class);
        $this->service = new AuditLogApiService($mockClient);

        $mockClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                "workspaces/$workspaceGid/audit_log_events",
                ['query' => []],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->getAuditLogEvents($workspaceGid);
    }

    /**
     * Test getAuditLogEvents with options.
     */
    public function testGetAuditLogEventsWithOptions(): void
    {
        $workspaceGid = '12345';
        $options = [
            'limit' => 50,
            'event_type' => 'login',
            'actor_type' => 'user'
        ];

        $mockClient = $this->createMock(HttpClientInterface::class);
        $this->service = new AuditLogApiService($mockClient);

        $mockClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                "workspaces/$workspaceGid/audit_log_events",
                ['query' => $options],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->getAuditLogEvents($workspaceGid, $options);
    }

    /**
     * Test getAuditLogEvents with custom response type.
     */
    public function testGetAuditLogEventsWithCustomResponseType(): void
    {
        $workspaceGid = '12345';
        $mockClient = $this->createMock(HttpClientInterface::class);
        $this->service = new AuditLogApiService($mockClient);

        $mockClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                "workspaces/$workspaceGid/audit_log_events",
                ['query' => []],
                HttpClientInterface::RESPONSE_FULL
            )
            ->willReturn([]);

        $this->service->getAuditLogEvents($workspaceGid, [], HttpClientInterface::RESPONSE_FULL);
    }

    /**
     * Test validation of Workspace GID.
     */
    public function testGetAuditLogEventsWithInvalidGid(): void
    {
        $this->expectException(\BrightleafDigital\Exceptions\ValidationException::class);
        $this->service->getAuditLogEvents('');
    }
}
