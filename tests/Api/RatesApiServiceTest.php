<?php

namespace BrightleafDigital\Tests\Api;

use BrightleafDigital\Api\RatesApiService;
use BrightleafDigital\Http\HttpClientInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RatesApiServiceTest extends TestCase
{
    /** @var HttpClientInterface&MockObject */
    private $httpClient;

    private RatesApiService $service;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->service = new RatesApiService($this->httpClient);
    }

    public function testGetRates(): void
    {
        $options = ['parent' => '12345'];
        $expected = ['data' => []];

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', 'rates', ['query' => $options], HttpClientInterface::RESPONSE_DATA)
            ->willReturn($expected);

        $actual = $this->service->getRates($options);
        $this->assertSame($expected, $actual);
    }

    public function testCreateRate(): void
    {
        $data = ['parent' => '12345', 'resource' => '67890', 'rate' => 100];
        $expected = ['data' => ['gid' => '54321']];

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('POST', 'rates', ['json' => ['data' => $data], 'query' => []], HttpClientInterface::RESPONSE_DATA)
            ->willReturn($expected);

        $actual = $this->service->createRate($data);
        $this->assertSame($expected, $actual);
    }

    public function testGetRate(): void
    {
        $gid = '12345';
        $expected = ['data' => ['gid' => $gid]];

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', 'rates/' . $gid, ['query' => []], HttpClientInterface::RESPONSE_DATA)
            ->willReturn($expected);

        $actual = $this->service->getRate($gid);
        $this->assertSame($expected, $actual);
    }

    public function testUpdateRate(): void
    {
        $gid = '12345';
        $data = ['rate' => 150];
        $expected = ['data' => ['gid' => $gid, 'rate' => 150]];

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with(
                'PUT',
                'rates/' . $gid,
                ['json' => ['data' => $data], 'query' => []],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn($expected);

        $actual = $this->service->updateRate($gid, $data);
        $this->assertSame($expected, $actual);
    }

    public function testDeleteRate(): void
    {
        $gid = '12345';
        $expected = ['data' => []];

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('DELETE', 'rates/' . $gid, [], HttpClientInterface::RESPONSE_DATA)
            ->willReturn($expected);

        $actual = $this->service->deleteRate($gid);
        $this->assertSame($expected, $actual);
    }
}
