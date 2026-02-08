<?php

namespace BrightleafDigital\Tests\Api;

use BrightleafDigital\Api\EventsApiService;
use BrightleafDigital\Http\AsanaApiClient;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\Exception as MockException;
use PHPUnit\Framework\TestCase;

class EventsApiServiceTest extends TestCase
{
    /** @var AsanaApiClient&\PHPUnit\Framework\MockObject\MockObject */
    private $mockClient;

    /** @var EventsApiService */
    private $service;

    /**
     * @throws MockException
     */
    protected function setUp(): void
    {
        $this->mockClient = $this->createMock(AsanaApiClient::class);
        $this->service = new EventsApiService($this->mockClient);
    }

    /**
     * Test getEvents calls client with correct parameters (first call, no sync token).
     */
    public function testGetEvents(): void
    {
        $resourceGid = '12345';
        $expectedResponse = [
            ['type' => 'task', 'action' => 'changed', 'resource' => ['gid' => '12345']],
        ];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'events', ['query' => ['resource' => '12345']], AsanaApiClient::RESPONSE_NORMAL)
            ->willReturn($expectedResponse);

        $result = $this->service->getEvents($resourceGid);

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test getEvents passes sync token when provided.
     */
    public function testGetEventsWithSyncToken(): void
    {
        $resourceGid = '12345';
        $syncToken = 'de4774f6915eae04714ca93bb2f5ee81';
        $expectedResponse = [
            ['type' => 'task', 'action' => 'added', 'resource' => ['gid' => '67890']],
        ];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'events',
                ['query' => ['resource' => '12345', 'sync' => $syncToken]],
                AsanaApiClient::RESPONSE_NORMAL
            )
            ->willReturn($expectedResponse);

        $result = $this->service->getEvents($resourceGid, $syncToken);

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test getEvents with null sync token does not include sync in query.
     */
    public function testGetEventsWithNullSyncToken(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'events', ['query' => ['resource' => '12345']], AsanaApiClient::RESPONSE_NORMAL)
            ->willReturn([]);

        $this->service->getEvents('12345', null);
    }

    /**
     * Test getEvents with additional options.
     */
    public function testGetEventsWithOptions(): void
    {
        $resourceGid = '12345';
        $options = ['opt_fields' => 'user,resource,type,action,created_at'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'events',
                ['query' => ['opt_fields' => 'user,resource,type,action,created_at', 'resource' => '12345']],
                AsanaApiClient::RESPONSE_NORMAL
            )
            ->willReturn([]);

        $this->service->getEvents($resourceGid, null, $options);
    }

    /**
     * Test getEvents with sync token and options combined.
     */
    public function testGetEventsWithSyncTokenAndOptions(): void
    {
        $resourceGid = '12345';
        $syncToken = 'abc123synctoken';
        $options = ['opt_fields' => 'user,resource,type'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'events',
                ['query' => ['opt_fields' => 'user,resource,type', 'resource' => '12345', 'sync' => $syncToken]],
                AsanaApiClient::RESPONSE_NORMAL
            )
            ->willReturn([]);

        $this->service->getEvents($resourceGid, $syncToken, $options);
    }

    /**
     * Test getEvents with custom response type.
     */
    public function testGetEventsWithCustomResponseType(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'events', ['query' => ['resource' => '12345']], AsanaApiClient::RESPONSE_FULL)
            ->willReturn([]);

        $this->service->getEvents('12345', null, [], AsanaApiClient::RESPONSE_FULL);
    }

    /**
     * Test getEvents with RESPONSE_NORMAL to get sync token from response body.
     */
    public function testGetEventsWithNormalResponseType(): void
    {
        $expectedResponse = [
            'data' => [['type' => 'task', 'action' => 'changed']],
            'sync' => 'newsynctoken123',
            'has_more' => false,
        ];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'events', ['query' => ['resource' => '12345']], AsanaApiClient::RESPONSE_NORMAL)
            ->willReturn($expectedResponse);

        $result = $this->service->getEvents('12345', null, [], AsanaApiClient::RESPONSE_NORMAL);

        $this->assertSame($expectedResponse, $result);
        $this->assertArrayHasKey('sync', $result);
        $this->assertArrayHasKey('has_more', $result);
    }

    /**
     * Test getEvents throws exception for empty resource GID.
     */
    public function testGetEventsThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Resource GID must be a non-empty string.');

        $this->service->getEvents('');
    }

    /**
     * Test getEvents throws exception for non-numeric resource GID.
     */
    public function testGetEventsThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Resource GID must be a numeric string.');

        $this->service->getEvents('abc');
    }

    /**
     * Test getEvents throws exception for whitespace-only resource GID.
     */
    public function testGetEventsThrowsExceptionForWhitespaceGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Resource GID must be a non-empty string.');

        $this->service->getEvents('   ');
    }

    /**
     * Test getWorkspaceEvents calls client with correct parameters (first call, no sync token).
     */
    public function testGetWorkspaceEvents(): void
    {
        $workspaceGid = '12345';
        $expectedResponse = [
            'data' => [
                ['type' => 'task', 'action' => 'changed', 'resource' => ['gid' => '67890']],
            ],
            'sync' => 'newsynctoken123',
            'has_more' => false,
        ];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'workspaces/12345/events', ['query' => []], AsanaApiClient::RESPONSE_NORMAL)
            ->willReturn($expectedResponse);

        $result = $this->service->getWorkspaceEvents($workspaceGid);

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test getWorkspaceEvents passes sync token when provided.
     */
    public function testGetWorkspaceEventsWithSyncToken(): void
    {
        $workspaceGid = '12345';
        $syncToken = 'de4774f6915eae04714ca93bb2f5ee81';
        $expectedResponse = [
            'data' => [
                ['type' => 'task', 'action' => 'added', 'resource' => ['gid' => '67890']],
            ],
            'sync' => 'updatedsynctoken456',
            'has_more' => false,
        ];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'workspaces/12345/events',
                ['query' => ['sync' => $syncToken]],
                AsanaApiClient::RESPONSE_NORMAL
            )
            ->willReturn($expectedResponse);

        $result = $this->service->getWorkspaceEvents($workspaceGid, $syncToken);

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test getWorkspaceEvents with null sync token does not include sync in query.
     */
    public function testGetWorkspaceEventsWithNullSyncToken(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'workspaces/12345/events', ['query' => []], AsanaApiClient::RESPONSE_NORMAL)
            ->willReturn([]);

        $this->service->getWorkspaceEvents('12345', null);
    }

    /**
     * Test getWorkspaceEvents with additional options.
     */
    public function testGetWorkspaceEventsWithOptions(): void
    {
        $workspaceGid = '12345';
        $options = ['opt_fields' => 'user,resource,type,action,created_at'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'workspaces/12345/events',
                ['query' => ['opt_fields' => 'user,resource,type,action,created_at']],
                AsanaApiClient::RESPONSE_NORMAL
            )
            ->willReturn([]);

        $this->service->getWorkspaceEvents($workspaceGid, null, $options);
    }

    /**
     * Test getWorkspaceEvents with sync token and options combined.
     */
    public function testGetWorkspaceEventsWithSyncTokenAndOptions(): void
    {
        $workspaceGid = '12345';
        $syncToken = 'abc123synctoken';
        $options = ['opt_fields' => 'user,resource,type'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'workspaces/12345/events',
                ['query' => ['opt_fields' => 'user,resource,type', 'sync' => $syncToken]],
                AsanaApiClient::RESPONSE_NORMAL
            )
            ->willReturn([]);

        $this->service->getWorkspaceEvents($workspaceGid, $syncToken, $options);
    }

    /**
     * Test getWorkspaceEvents with custom response type.
     */
    public function testGetWorkspaceEventsWithCustomResponseType(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'workspaces/12345/events', ['query' => []], AsanaApiClient::RESPONSE_FULL)
            ->willReturn([]);

        $this->service->getWorkspaceEvents('12345', null, [], AsanaApiClient::RESPONSE_FULL);
    }

    /**
     * Test getWorkspaceEvents with RESPONSE_NORMAL to get sync token from response body.
     */
    public function testGetWorkspaceEventsWithNormalResponseType(): void
    {
        $expectedResponse = [
            'data' => [['type' => 'task', 'action' => 'changed']],
            'sync' => 'newsynctoken123',
            'has_more' => true,
        ];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'workspaces/12345/events', ['query' => []], AsanaApiClient::RESPONSE_NORMAL)
            ->willReturn($expectedResponse);

        $result = $this->service->getWorkspaceEvents('12345', null, [], AsanaApiClient::RESPONSE_NORMAL);

        $this->assertSame($expectedResponse, $result);
        $this->assertArrayHasKey('sync', $result);
        $this->assertArrayHasKey('has_more', $result);
    }

    /**
     * Test getWorkspaceEvents throws exception for empty workspace GID.
     */
    public function testGetWorkspaceEventsThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Workspace GID must be a non-empty string.');

        $this->service->getWorkspaceEvents('');
    }

    /**
     * Test getWorkspaceEvents throws exception for non-numeric workspace GID.
     */
    public function testGetWorkspaceEventsThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Workspace GID must be a numeric string.');

        $this->service->getWorkspaceEvents('abc');
    }

    /**
     * Test getWorkspaceEvents throws exception for whitespace-only workspace GID.
     */
    public function testGetWorkspaceEventsThrowsExceptionForWhitespaceGid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Workspace GID must be a non-empty string.');

        $this->service->getWorkspaceEvents('   ');
    }
}
