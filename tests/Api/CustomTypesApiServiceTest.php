<?php

namespace BrightleafDigital\Tests\Api;

use BrightleafDigital\Api\CustomTypesApiService;
use BrightleafDigital\Http\HttpClientInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CustomTypesApiServiceTest extends TestCase
{
    /** @var HttpClientInterface&MockObject */
    private $httpClient;

    private CustomTypesApiService $service;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->service = new CustomTypesApiService($this->httpClient);
    }

    public function testGetCustomTypes(): void
    {
        $projectGid = '1331';
        $options = ['limit' => 50];
        $expected = ['data' => []];

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'custom_types',
                ['query' => ['limit' => 50, 'project' => $projectGid]],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn($expected);

        $actual = $this->service->getCustomTypes($projectGid, $options);
        $this->assertSame($expected, $actual);
    }

    public function testGetCustomType(): void
    {
        $customTypeGid = '12345';
        $expected = ['data' => ['gid' => $customTypeGid]];

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', 'custom_types/' . $customTypeGid, ['query' => []], HttpClientInterface::RESPONSE_DATA)
            ->willReturn($expected);

        $actual = $this->service->getCustomType($customTypeGid);
        $this->assertSame($expected, $actual);
    }
}
