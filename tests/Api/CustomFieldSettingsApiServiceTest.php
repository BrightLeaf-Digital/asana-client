<?php

namespace BrightleafDigital\Tests\Api;

use BrightleafDigital\Api\CustomFieldSettingsApiService;
use BrightleafDigital\Http\HttpClientInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CustomFieldSettingsApiServiceTest extends TestCase
{
    /** @var HttpClientInterface&MockObject */
    private $httpClient;

    private CustomFieldSettingsApiService $service;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->service = new CustomFieldSettingsApiService($this->httpClient);
    }

    public function testGetCustomFieldSettingsForProject(): void
    {
        $gid = '12345';
        $expected = ['data' => []];

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', "projects/$gid/custom_field_settings", ['query' => []], HttpClientInterface::RESPONSE_DATA)
            ->willReturn($expected);

        $actual = $this->service->getCustomFieldSettingsForProject($gid);
        $this->assertSame($expected, $actual);
    }

    public function testGetCustomFieldSettingsForPortfolio(): void
    {
        $gid = '12345';
        $expected = ['data' => []];

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', "portfolios/$gid/custom_field_settings", ['query' => []], HttpClientInterface::RESPONSE_DATA)
            ->willReturn($expected);

        $actual = $this->service->getCustomFieldSettingsForPortfolio($gid);
        $this->assertSame($expected, $actual);
    }

    public function testGetCustomFieldSettingsForGoal(): void
    {
        $gid = '12345';
        $expected = ['data' => []];

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', "goals/$gid/custom_field_settings", ['query' => []], HttpClientInterface::RESPONSE_DATA)
            ->willReturn($expected);

        $actual = $this->service->getCustomFieldSettingsForGoal($gid);
        $this->assertSame($expected, $actual);
    }

    public function testGetCustomFieldSettingsForTeam(): void
    {
        $gid = '12345';
        $expected = ['data' => []];

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', "teams/$gid/custom_field_settings", ['query' => []], HttpClientInterface::RESPONSE_DATA)
            ->willReturn($expected);

        $actual = $this->service->getCustomFieldSettingsForTeam($gid);
        $this->assertSame($expected, $actual);
    }
}
