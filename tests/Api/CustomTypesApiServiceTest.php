<?php

namespace BrightleafDigital\Tests\Api;

use BrightleafDigital\Api\CustomTypesApiService;
use BrightleafDigital\Exceptions\ValidationException;
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

    public function testGetCustomTypesWithWorkspaceOption(): void
    {
        $expected = ['data' => []];

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'custom_types',
                ['query' => ['workspace' => '12345', 'limit' => 50]],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn($expected);

        $actual = $this->service->getCustomTypes(null, ['workspace' => '12345', 'limit' => 50]);
        $this->assertSame($expected, $actual);
    }

    public function testGetCustomTypesRejectsBothProjectAndWorkspace(): void
    {
        $this->httpClient->expects($this->never())->method('request');

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Provide exactly one of a project GID or a "workspace" option, not both.');

        $this->service->getCustomTypes('1331', ['workspace' => '12345']);
    }

    public function testGetCustomTypesRequiresProjectOrWorkspace(): void
    {
        $this->httpClient->expects($this->never())->method('request');

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('You must provide either a project GID or a "workspace" option.');

        $this->service->getCustomTypes();
    }

    public function testGetCustomTypesValidatesWorkspaceGid(): void
    {
        $this->httpClient->expects($this->never())->method('request');

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Workspace GID must be a numeric string.');

        $this->service->getCustomTypes(null, ['workspace' => 'not-a-gid']);
    }

    public function testGetCustomTypesForWorkspace(): void
    {
        $expected = ['data' => []];

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'custom_types',
                ['query' => ['limit' => 50, 'workspace' => '12345']],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn($expected);

        $actual = $this->service->getCustomTypesForWorkspace('12345', ['limit' => 50]);
        $this->assertSame($expected, $actual);
    }

    public function testGetCustomTypesForWorkspaceDropsProjectOption(): void
    {
        $expected = ['data' => []];

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'custom_types',
                ['query' => ['workspace' => '12345']],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn($expected);

        $actual = $this->service->getCustomTypesForWorkspace('12345', ['project' => '1331']);
        $this->assertSame($expected, $actual);
    }

    public function testGetCustomTypesForWorkspaceValidatesGid(): void
    {
        $this->httpClient->expects($this->never())->method('request');

        $this->expectException(ValidationException::class);

        $this->service->getCustomTypesForWorkspace('');
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
