<?php

namespace BrightleafDigital\Tests\Api;

use BrightleafDigital\Api\AccessRequestsApiService;
use BrightleafDigital\Http\HttpClientInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AccessRequestsApiServiceTest extends TestCase
{
    /** @var HttpClientInterface&MockObject */
    private $httpClient;

    private AccessRequestsApiService $service;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->service = new AccessRequestsApiService($this->httpClient);
    }

    public function testGetAccessRequests(): void
    {
        $targetGid = '1331';
        $options = ['user' => 'me'];
        $expectedResponse = ['data' => []];

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'access_requests',
                ['query' => ['user' => 'me', 'target' => $targetGid]],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->getAccessRequests($targetGid, $options);
        $this->assertEquals($expectedResponse, $result);
    }

    public function testCreateAccessRequest(): void
    {
        $data = ['target' => '1331', 'message' => 'please'];
        $expectedResponse = ['data' => ['gid' => '123']];

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'access_requests',
                ['json' => ['data' => $data], 'query' => []],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->createAccessRequest($data);
        $this->assertEquals($expectedResponse, $result);
    }

    public function testApproveAccessRequest(): void
    {
        $accessRequestGid = '555';
        $expectedResponse = ['data' => []];

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('POST', 'access_requests/' . $accessRequestGid . '/approve', [], HttpClientInterface::RESPONSE_DATA)
            ->willReturn($expectedResponse);

        $result = $this->service->approveAccessRequest($accessRequestGid);
        $this->assertEquals($expectedResponse, $result);
    }

    public function testRejectAccessRequest(): void
    {
        $accessRequestGid = '555';
        $expectedResponse = ['data' => []];

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('POST', 'access_requests/' . $accessRequestGid . '/reject', [], HttpClientInterface::RESPONSE_DATA)
            ->willReturn($expectedResponse);

        $result = $this->service->rejectAccessRequest($accessRequestGid);
        $this->assertEquals($expectedResponse, $result);
    }
}
