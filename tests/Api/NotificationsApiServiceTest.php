<?php

namespace BrightleafDigital\Tests\Api;

use BrightleafDigital\Api\NotificationsApiService;
use BrightleafDigital\Exceptions\ValidationException;
use BrightleafDigital\Http\HttpClientInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NotificationsApiServiceTest extends TestCase
{
    private HttpClientInterface $mockClient;

    /** @var NotificationsApiService */
    private NotificationsApiService $service;

    /** @var (HttpClientInterface&MockObject)|null */
    private $mockClientMock = null;

    protected function setUp(): void
    {
        $this->mockClient = $this->createStub(HttpClientInterface::class);
        $this->mockClientMock = null;
        $this->service = new NotificationsApiService($this->mockClient);
    }

    // ── getNotifications ──────────────────────────────────────────────────

    /**
     * Test getNotifications defaults to "me" and adds the workspace to the query.
     */
    public function testGetNotifications(): void
    {
        $expectedResponse = [
            [
                'gid' => '1234567890',
                'resource_type' => 'notification',
                'priority_classification' => 'unclassified',
                'is_read' => false,
            ],
        ];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'users/me/notifications',
                ['query' => ['limit' => 20, 'opt_fields' => 'story.text', 'workspace' => '1111111111']],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->getNotifications('1111111111', 'me', ['limit' => 20, 'opt_fields' => 'story.text']);

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test getNotifications accepts a numeric user GID and a custom response type.
     */
    public function testGetNotificationsWithUserGidAndResponseType(): void
    {
        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'users/2222222222/notifications',
                ['query' => ['workspace' => '1111111111']],
                HttpClientInterface::RESPONSE_FULL
            )
            ->willReturn([]);

        $this->service->getNotifications('1111111111', '2222222222', [], HttpClientInterface::RESPONSE_FULL);
    }

    /**
     * Test getNotifications throws exception for non-numeric workspace GID.
     */
    public function testGetNotificationsThrowsExceptionForInvalidWorkspaceGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Workspace GID must be a numeric string.');

        $this->service->getNotifications('abc');
    }

    /**
     * Test getNotifications throws exception for an invalid user GID.
     */
    public function testGetNotificationsThrowsExceptionForInvalidUserGid(): void
    {
        $this->expectException(ValidationException::class);

        $this->service->getNotifications('1111111111', 'not-a-user');
    }

    /**
     * Test getNotifications throws exception for a limit above 100.
     */
    public function testGetNotificationsThrowsExceptionForInvalidLimit(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Limit must be between 1 and 100.');

        $this->service->getNotifications('1111111111', 'me', ['limit' => 101]);
    }

    // ── markNotificationsRead ─────────────────────────────────────────────

    /**
     * Test markNotificationsRead posts deduplicated GIDs with the workspace.
     */
    public function testMarkNotificationsRead(): void
    {
        $expectedResponse = ['failed_gids' => []];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'users/me/notifications/markRead',
                [
                    'json' => ['data' => ['workspace' => '1111111111', 'notification_gids' => ['12', '13']]],
                    'query' => [],
                ],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->markNotificationsRead('1111111111', ['12', '13', '12']);

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test markNotificationsRead allows 50 unique GIDs after removing duplicates.
     */
    public function testMarkNotificationsReadAllowsFiftyAfterDeduplication(): void
    {
        $gids = array_map('strval', range(1, 50));

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->willReturn(['failed_gids' => []]);

        $this->service->markNotificationsRead('1111111111', array_merge($gids, ['1']));
    }

    /**
     * Test markNotificationsRead throws exception for more than 50 GIDs.
     */
    public function testMarkNotificationsReadThrowsExceptionForTooManyGids(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Notification GIDs must contain at most 50 items.');

        $this->service->markNotificationsRead('1111111111', array_map('strval', range(1, 51)));
    }

    /**
     * Test markNotificationsRead throws exception for an empty GID list.
     */
    public function testMarkNotificationsReadThrowsExceptionForEmptyGids(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Notification GIDs must be a non-empty array.');

        $this->service->markNotificationsRead('1111111111', []);
    }

    /**
     * Test markNotificationsRead throws exception for a non-numeric GID.
     */
    public function testMarkNotificationsReadThrowsExceptionForInvalidGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Notification GIDs[1] must be a numeric string.');

        $this->service->markNotificationsRead('1111111111', ['12', 'invalid_gid']);
    }

    // ── markNotificationsUnread ───────────────────────────────────────────

    /**
     * Test markNotificationsUnread posts to the markUnread endpoint.
     */
    public function testMarkNotificationsUnread(): void
    {
        $expectedResponse = ['failed_gids' => ['13']];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'users/2222222222/notifications/markUnread',
                [
                    'json' => ['data' => ['workspace' => '1111111111', 'notification_gids' => ['12', '13']]],
                    'query' => [],
                ],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->markNotificationsUnread('1111111111', ['12', '13'], '2222222222');

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test markNotificationsUnread throws exception for non-numeric workspace GID.
     */
    public function testMarkNotificationsUnreadThrowsExceptionForInvalidWorkspaceGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Workspace GID must be a numeric string.');

        $this->service->markNotificationsUnread('abc', ['12']);
    }

    /**
     * @return HttpClientInterface&MockObject
     */
    private function mockClient(): HttpClientInterface
    {
        if ($this->mockClientMock === null) {
            $this->mockClientMock = $this->createMock(HttpClientInterface::class);
            $this->mockClient = $this->mockClientMock;
            $serviceClass = $this->service::class;
            $this->service = new $serviceClass($this->mockClient);
        }

        return $this->mockClientMock;
    }
}
