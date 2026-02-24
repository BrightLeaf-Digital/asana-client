<?php

namespace BrightleafDigital\Tests\Api;

use BrightleafDigital\Api\GoalRelationshipsApiService;
use BrightleafDigital\Http\HttpClientInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GoalRelationshipsApiServiceTest extends TestCase
{
    /** @var HttpClientInterface&MockObject */
    private $httpClient;

    private GoalRelationshipsApiService $service;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->service = new GoalRelationshipsApiService($this->httpClient);
    }

    public function testGetGoalRelationship(): void
    {
        $gid = '12345';
        $expected = ['data' => ['gid' => $gid]];

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', 'goal_relationships/' . $gid, ['query' => []], HttpClientInterface::RESPONSE_DATA)
            ->willReturn($expected);

        $actual = $this->service->getGoalRelationship($gid);
        $this->assertSame($expected, $actual);
    }

    public function testUpdateGoalRelationship(): void
    {
        $gid = '12345';
        $contributionWeight = 0.5;
        $data = ['contribution_weight' => $contributionWeight];
        $expected = ['data' => ['gid' => $gid, 'contribution_weight' => 0.5]];

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with(
                'PUT',
                'goal_relationships/' . $gid,
                ['json' => ['data' => $data], 'query' => []],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn($expected);

        $actual = $this->service->updateGoalRelationship($gid, $contributionWeight);
        $this->assertSame($expected, $actual);
    }

    public function testGetGoalRelationships(): void
    {
        $supportedGoalGid = '67890';
        $options = ['resource_subtype' => 'subgoal'];
        $expected = ['data' => []];

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'goal_relationships',
                ['query' => ['resource_subtype' => 'subgoal', 'supported_goal' => $supportedGoalGid]],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn($expected);

        $actual = $this->service->getGoalRelationships($supportedGoalGid, $options);
        $this->assertSame($expected, $actual);
    }

    public function testAddSupportingRelationship(): void
    {
        $goalGid = '12345';
        $data = ['supporting_resource' => '67890'];
        $options = ['opt_fields' => 'contribution_weight'];
        $expected = ['data' => ['gid' => '54321']];

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                "goals/$goalGid/addSupportingRelationship",
                ['json' => ['data' => $data], 'query' => $options],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn($expected);

        $actual = $this->service->addSupportingRelationship($goalGid, $data, $options);
        $this->assertSame($expected, $actual);
    }

    public function testRemoveSupportingRelationship(): void
    {
        $goalGid = '12345';
        $supportingResource = '67890';
        $data = ['supporting_resource' => $supportingResource];
        $expected = ['data' => []];

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                "goals/$goalGid/removeSupportingRelationship",
                ['json' => ['data' => $data], 'query' => []],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn($expected);

        $actual = $this->service->removeSupportingRelationship($goalGid, $supportingResource);
        $this->assertSame($expected, $actual);
    }
}
