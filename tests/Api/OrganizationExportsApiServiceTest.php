<?php

namespace BrightleafDigital\Tests\Api;

use BrightleafDigital\Api\OrganizationExportsApiService;
use BrightleafDigital\Http\HttpClientInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OrganizationExportsApiServiceTest extends TestCase
{
    /** @var HttpClientInterface&MockObject */
    private $httpClient;

    private OrganizationExportsApiService $service;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->service = new OrganizationExportsApiService($this->httpClient);
    }

    public function testCreateOrganizationExport(): void
    {
        $organizationGid = '999';
        $data = ['organization' => $organizationGid];
        $expectedResponse = ['data' => ['gid' => '1']];

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'organization_exports',
                ['json' => ['data' => $data], 'query' => []],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->createOrganizationExport($organizationGid);
        $this->assertEquals($expectedResponse, $result);
    }

    public function testGetOrganizationExport(): void
    {
        $exportGid = '12345';
        $expectedResponse = ['data' => ['gid' => $exportGid]];

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', 'organization_exports/' . $exportGid, ['query' => []], HttpClientInterface::RESPONSE_DATA)
            ->willReturn($expectedResponse);

        $result = $this->service->getOrganizationExport($exportGid);
        $this->assertEquals($expectedResponse, $result);
    }
}
