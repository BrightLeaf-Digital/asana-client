<?php

namespace BrightleafDigital\Tests\Api;

use BrightleafDigital\Api\TeamMembershipsApiService;
use BrightleafDigital\Http\HttpClientInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TeamMembershipsApiServiceTest extends TestCase
{
    /** @var HttpClientInterface&MockObject */
    private $mockClient;

    private TeamMembershipsApiService $service;

    protected function setUp(): void
    {
        $this->mockClient = $this->createMock(HttpClientInterface::class);
        $this->service = new TeamMembershipsApiService($this->mockClient);
    }

    public function testGetTeamMembership(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'team_memberships/12345', ['query' => []], HttpClientInterface::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getTeamMembership('12345');
    }

    public function testGetTeamMemberships(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'team_memberships', ['query' => []], HttpClientInterface::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getTeamMemberships();
    }

    public function testGetTeamMembershipsForTeam(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'teams/67890/team_memberships', ['query' => []], HttpClientInterface::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getTeamMembershipsForTeam('67890');
    }

    public function testGetTeamMembershipsForUser(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'users/11111/team_memberships',
                ['query' => ['workspace' => '22222']],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->getTeamMembershipsForUser('11111', '22222');
    }

    public function testGetTeamMembershipWithOptions(): void
    {
        $options = ['opt_fields' => 'team,user'];
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'team_memberships/12345', ['query' => $options], HttpClientInterface::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getTeamMembership('12345', $options);
    }
}
