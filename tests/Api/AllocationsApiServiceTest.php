<?php

namespace BrightleafDigital\Tests\Api;

use BrightleafDigital\Api\AllocationsApiService;
use BrightleafDigital\Http\HttpClientInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AllocationsApiServiceTest extends TestCase
{
    /** @var HttpClientInterface&MockObject */
    private $httpClient;

    /** @var AllocationsApiService  */
    private AllocationsApiService $service;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->service = new AllocationsApiService($this->httpClient);
    }

    public function testGetAllocations(): void
    {
        $options = ['parent' => '12345'];
        $expectedResponse = ['data' => []];

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', 'allocations', ['query' => $options], HttpClientInterface::RESPONSE_DATA)
            ->willReturn($expectedResponse);

        $result = $this->service->getAllocations($options);
        $this->assertEquals($expectedResponse, $result);
    }

    public function testCreateAllocation(): void
    {
        $data = ['parent' => '12345', 'assignee' => '67890'];
        $expectedResponse = ['data' => []];

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'allocations',
                ['json' => ['data' => $data], 'query' => []],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->createAllocation($data);
        $this->assertEquals($expectedResponse, $result);
    }

    public function testGetAllocation(): void
    {
        $allocationGid = '12345';
        $expectedResponse = ['data' => []];

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', 'allocations/' . $allocationGid, ['query' => []], HttpClientInterface::RESPONSE_DATA)
            ->willReturn($expectedResponse);

        $result = $this->service->getAllocation($allocationGid);
        $this->assertEquals($expectedResponse, $result);
    }

    public function testUpdateAllocation(): void
    {
        $allocationGid = '12345';
        $data = ['effort' => ['value' => 10]];
        $expectedResponse = ['data' => []];

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with(
                'PUT',
                'allocations/' . $allocationGid,
                ['json' => ['data' => $data], 'query' => []],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->updateAllocation($allocationGid, $data);
        $this->assertEquals($expectedResponse, $result);
    }

    public function testDeleteAllocation(): void
    {
        $allocationGid = '12345';
        $expectedResponse = ['data' => []];

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('DELETE', 'allocations/' . $allocationGid, [], HttpClientInterface::RESPONSE_DATA)
            ->willReturn($expectedResponse);

        $result = $this->service->deleteAllocation($allocationGid);
        $this->assertEquals($expectedResponse, $result);
    }
}
