<?php

namespace BrightleafDigital\Tests\Api;

use BrightleafDigital\Api\WorkspaceMembershipsApiService;
use BrightleafDigital\Http\HttpClientInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class WorkspaceMembershipsApiServiceTest extends TestCase
{
    /** @var HttpClientInterface&MockObject */
    private $mockClient;

    private WorkspaceMembershipsApiService $service;

    protected function setUp(): void
    {
        $this->mockClient = $this->createMock(HttpClientInterface::class);
        $this->service = new WorkspaceMembershipsApiService($this->mockClient);
    }

    public function testGetWorkspaceMembership(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'workspace_memberships/12345', ['query' => []], HttpClientInterface::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getWorkspaceMembership('12345');
    }

    public function testGetWorkspaceMembershipsForUser(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'users/67890/workspace_memberships', ['query' => []], HttpClientInterface::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getWorkspaceMembershipsForUser('67890');
    }

    public function testGetWorkspaceMembershipsForWorkspace(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'workspaces/11111/workspace_memberships', ['query' => []], HttpClientInterface::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getWorkspaceMembershipsForWorkspace('11111');
    }

    public function testGetWorkspaceMembershipsForWorkspaceWithOptions(): void
    {
        $options = ['user' => 'me', 'limit' => 20];
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'workspaces/11111/workspace_memberships',
                ['query' => $options],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->getWorkspaceMembershipsForWorkspace('11111', $options);
    }
}
