<?php

namespace BrightleafDigital\Tests\Api;

use BrightleafDigital\Api\AgentApiService;
use BrightleafDigital\Exceptions\ValidationException;
use BrightleafDigital\Http\HttpClientInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AgentApiServiceTest extends TestCase
{
    /** @var HttpClientInterface&MockObject */
    private $httpClient;

    private AgentApiService $service;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->service = new AgentApiService($this->httpClient);
    }

    public function testGetAgentsInWorkspace(): void
    {
        $workspaceGid = '12345';
        $options = ['limit' => 20];
        $expected = [['gid' => '111', 'resource_type' => 'user', 'resource_subtype' => 'agent']];

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                "workspaces/$workspaceGid/agents",
                ['query' => $options],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn($expected);

        $result = $this->service->getAgentsInWorkspace($workspaceGid, $options);
        $this->assertSame($expected, $result);
    }

    public function testGetAgentsInWorkspaceWithNoOptions(): void
    {
        $workspaceGid = '12345';
        $expected = [];

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', "workspaces/$workspaceGid/agents", ['query' => []], HttpClientInterface::RESPONSE_DATA)
            ->willReturn($expected);

        $result = $this->service->getAgentsInWorkspace($workspaceGid);
        $this->assertSame($expected, $result);
    }

    public function testGetAgentsInWorkspaceThrowsOnEmptyWorkspaceGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->service->getAgentsInWorkspace('');
    }

    public function testGetAgentsInWorkspaceThrowsOnNonNumericWorkspaceGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->service->getAgentsInWorkspace('not-a-gid');
    }

    public function testGetAgent(): void
    {
        $agentGid = '99999';
        $options = ['opt_fields' => 'gid,name,description'];
        $expected = ['gid' => $agentGid, 'name' => 'My Agent', 'resource_subtype' => 'agent'];

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                "agents/$agentGid",
                ['query' => $options],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn($expected);

        $result = $this->service->getAgent($agentGid, $options);
        $this->assertSame($expected, $result);
    }

    public function testGetAgentWithResponseType(): void
    {
        $agentGid = '99999';
        $expected = ['data' => ['gid' => $agentGid]];

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', "agents/$agentGid", ['query' => []], HttpClientInterface::RESPONSE_NORMAL)
            ->willReturn($expected);

        $result = $this->service->getAgent($agentGid, [], HttpClientInterface::RESPONSE_NORMAL);
        $this->assertSame($expected, $result);
    }

    public function testGetAgentThrowsOnEmptyAgentGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->service->getAgent('');
    }

    public function testGetAgentThrowsOnNonNumericAgentGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->service->getAgent('not-a-gid');
    }
}
