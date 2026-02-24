<?php

namespace BrightleafDigital\Tests\Api;

use BrightleafDigital\Api\ProjectBriefsApiService;
use BrightleafDigital\Http\HttpClientInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProjectBriefsApiServiceTest extends TestCase
{
    /** @var HttpClientInterface&MockObject */
    private $httpClient;

    private ProjectBriefsApiService $service;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->service = new ProjectBriefsApiService($this->httpClient);
    }

    public function testGetProjectBrief(): void
    {
        $gid = '12345';
        $expected = ['data' => ['gid' => $gid]];

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', 'project_briefs/' . $gid, ['query' => []], HttpClientInterface::RESPONSE_DATA)
            ->willReturn($expected);

        $actual = $this->service->getProjectBrief($gid);
        $this->assertSame($expected, $actual);
    }

    public function testUpdateProjectBrief(): void
    {
        $gid = '12345';
        $data = ['text' => 'Updated'];
        $expected = ['data' => ['gid' => $gid, 'text' => 'Updated']];

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with(
                'PUT',
                'project_briefs/' . $gid,
                ['json' => ['data' => $data], 'query' => []],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn($expected);

        $actual = $this->service->updateProjectBrief($gid, $data);
        $this->assertSame($expected, $actual);
    }

    public function testDeleteProjectBrief(): void
    {
        $gid = '12345';
        $expected = ['data' => []];

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('DELETE', 'project_briefs/' . $gid, [], HttpClientInterface::RESPONSE_DATA)
            ->willReturn($expected);

        $actual = $this->service->deleteProjectBrief($gid);
        $this->assertSame($expected, $actual);
    }

    public function testCreateProjectBrief(): void
    {
        $projectGid = '999';
        $data = ['text' => 'Hello'];
        $options = ['opt_fields' => 'text'];
        $expected = ['data' => ['gid' => 'pb_123']];

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'projects/' . $projectGid . '/project_briefs',
                ['json' => ['data' => $data], 'query' => $options],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn($expected);

        $actual = $this->service->createProjectBrief($projectGid, $data, $options);
        $this->assertSame($expected, $actual);
    }
}
