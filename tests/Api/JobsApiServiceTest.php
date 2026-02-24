<?php

namespace BrightleafDigital\Tests\Api;

use BrightleafDigital\Api\JobsApiService;
use BrightleafDigital\Http\HttpClientInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class JobsApiServiceTest extends TestCase
{
    /** @var HttpClientInterface&MockObject */
    private $httpClient;

    private JobsApiService $service;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->service = new JobsApiService($this->httpClient);
    }

    public function testGetJob(): void
    {
        $gid = '12345';
        $expected = ['data' => ['gid' => $gid, 'status' => 'succeeded']];

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', 'jobs/' . $gid, ['query' => []], HttpClientInterface::RESPONSE_DATA)
            ->willReturn($expected);

        $actual = $this->service->getJob($gid);
        $this->assertSame($expected, $actual);
    }
}
