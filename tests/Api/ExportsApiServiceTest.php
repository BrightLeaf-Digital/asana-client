<?php

namespace BrightleafDigital\Tests\Api;

use BrightleafDigital\Api\ExportsApiService;
use BrightleafDigital\Http\HttpClientInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ExportsApiServiceTest extends TestCase
{
    /** @var HttpClientInterface&MockObject */
    private $httpClient;

    private ExportsApiService $service;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->service = new ExportsApiService($this->httpClient);
    }

    public function testCreateGraphExport(): void
    {
        $parentGid = '12345';
        $expected = ['data' => ['gid' => '67890']];

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'exports/graph',
                ['json' => ['data' => ['parent' => $parentGid]], 'query' => []],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn($expected);

        $actual = $this->service->createGraphExport($parentGid);
        $this->assertSame($expected, $actual);
    }

    public function testCreateResourceExport(): void
    {
        $workspaceGid = '12345';
        $params = [['resource' => '67890']];
        $expected = ['data' => ['gid' => '54321']];

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'exports/resource',
                ['json' => ['data' => [
                    'workspace' => $workspaceGid,
                    'export_request_parameters' => $params
                ]], 'query' => []],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn($expected);

        $actual = $this->service->createResourceExport($workspaceGid, $params);
        $this->assertSame($expected, $actual);
    }
}
