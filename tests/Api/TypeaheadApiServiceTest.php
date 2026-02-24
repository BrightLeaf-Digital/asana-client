<?php

namespace BrightleafDigital\Tests\Api;

use BrightleafDigital\Api\TypeaheadApiService;
use BrightleafDigital\Http\HttpClientInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TypeaheadApiServiceTest extends TestCase
{
    /** @var HttpClientInterface&MockObject */
    private $httpClient;

    private TypeaheadApiService $service;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->service = new TypeaheadApiService($this->httpClient);
    }

    public function testTypeaheadForWorkspace(): void
    {
        $workspaceGid = '12345';
        $options = ['resource_type' => 'task', 'query' => 'test'];
        $expected = ['data' => []];

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                "workspaces/$workspaceGid/typeahead",
                ['query' => $options],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn($expected);

        $actual = $this->service->typeaheadForWorkspace($workspaceGid, $options);
        $this->assertSame($expected, $actual);
    }
}
