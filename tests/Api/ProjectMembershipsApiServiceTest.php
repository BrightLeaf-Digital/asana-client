<?php

namespace BrightleafDigital\Tests\Api;

use BrightleafDigital\Api\ProjectMembershipsApiService;
use BrightleafDigital\Http\HttpClientInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProjectMembershipsApiServiceTest extends TestCase
{
    /** @var HttpClientInterface&MockObject */
    private $mockClient;

    private ProjectMembershipsApiService $service;

    protected function setUp(): void
    {
        $this->mockClient = $this->createMock(HttpClientInterface::class);
        $this->service = new ProjectMembershipsApiService($this->mockClient);
    }

    public function testGetProjectMembership(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'project_memberships/12345', ['query' => []], HttpClientInterface::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getProjectMembership('12345');
    }

    public function testGetProjectMembershipWithOptions(): void
    {
        $options = ['opt_fields' => 'access_level,member,project'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'project_memberships/12345', ['query' => $options], HttpClientInterface::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getProjectMembership('12345', $options);
    }

    public function testGetProjectMembershipsForProject(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'projects/67890/project_memberships', ['query' => []], HttpClientInterface::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getProjectMembershipsForProject('67890');
    }

    public function testGetProjectMembershipsForProjectWithOptions(): void
    {
        $options = ['user' => 'me', 'limit' => 50, 'offset' => 'abc'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'projects/67890/project_memberships',
                ['query' => $options],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->getProjectMembershipsForProject('67890', $options);
    }

    public function testGetProjectMembershipWithCustomResponseType(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'project_memberships/12345', ['query' => []], HttpClientInterface::RESPONSE_FULL)
            ->willReturn([]);

        $this->service->getProjectMembership('12345', [], HttpClientInterface::RESPONSE_FULL);
    }
}
