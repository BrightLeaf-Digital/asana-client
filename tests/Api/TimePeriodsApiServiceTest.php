<?php

namespace BrightleafDigital\Tests\Api;

use BrightleafDigital\Api\TimePeriodsApiService;
use BrightleafDigital\Http\HttpClientInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TimePeriodsApiServiceTest extends TestCase
{
    /** @var HttpClientInterface&MockObject */
    private $httpClient;

    private TimePeriodsApiService $service;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->service = new TimePeriodsApiService($this->httpClient);
    }

    public function testGetTimePeriods(): void
    {
        $workspaceGid = '12345';
        $options = ['start_on' => '2024-01-01'];
        $expected = ['data' => []];

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'time_periods',
                ['query' => ['start_on' => '2024-01-01', 'workspace' => $workspaceGid]],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn($expected);

        $actual = $this->service->getTimePeriods($workspaceGid, $options);
        $this->assertSame($expected, $actual);
    }

    public function testGetTimePeriod(): void
    {
        $gid = '12345';
        $expected = ['data' => ['gid' => $gid]];

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', 'time_periods/' . $gid, ['query' => []], HttpClientInterface::RESPONSE_DATA)
            ->willReturn($expected);

        $actual = $this->service->getTimePeriod($gid);
        $this->assertSame($expected, $actual);
    }
}
