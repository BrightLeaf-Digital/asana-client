<?php

namespace BrightleafDigital\Api;

use BrightleafDigital\Exceptions\ApiException;
use BrightleafDigital\Exceptions\RateLimitException;
use BrightleafDigital\Exceptions\ValidationException;
use BrightleafDigital\Http\HttpClientInterface;

/**
 * Service class for Notifications-related API operations.
 *
 * A notification is a recipient-specific record of activity that Asana surfaced in a user's
 * Inbox. Several users can have notifications pointing at the same story, but each notification
 * carries its own read state. Read state is shared with the Asana Inbox, so marking a
 * notification read or unread here changes what the user sees in Asana.
 *
 * These endpoints only work for the authenticated user's own Inbox: the user GID must be "me"
 * or the authenticated user's GID, otherwise Asana returns 403 Forbidden. This applies to
 * service accounts as well.
 *
 * Known limitations: notifications are polling only (no webhooks or Events API), are returned
 * individually rather than grouped into threads, and only read state can be changed (no
 * archiving, muting or bookmarking).
 *
 * @link https://developers.asana.com/reference/notifications
 */
class NotificationsApiService extends BaseApiService
{
    /**
     * Maximum number of notification GIDs accepted by markRead / markUnread.
     */
    public const MAX_NOTIFICATIONS_PER_UPDATE = 50;

    /**
     * Get a user's notifications
     * GET /users/{user_gid}/notifications
     * Returns the authenticated user's Inbox notifications in a workspace, newest first.
     * Requires the `notifications:read` OAuth scope. Reading some fields (e.g. story text or
     * task projects) may require additional scopes such as `stories:read` or `tasks:read`.
     * API Documentation: https://developers.asana.com/reference/getnotifications
     *
     * @param string $workspaceGid The GID of the workspace to return notifications from.
     *                             Example: "12345"
     * @param string $userGid The authenticated user's GID, or "me" (default).
     * @param array $options Optional parameters to customize the request:
     *
     * Pagination parameters:
     * - limit (int): Results per page (1-100, defaults to 50)
     * - offset (string): Offset token returned in next_page
     *
     * Display parameters:
     * - opt_fields (string): Comma-separated fields to include in the response. Fields on
     *   work objects and the story can be requested as well
     *   (e.g., "work_objects.projects.name,story.text")
     * - opt_pretty (bool): Returns formatted JSON if true
     *
     * @param int $responseType The type of response to return:
     * - HttpClientInterface::RESPONSE_FULL (1): Full response with status, headers, etc.
     * - HttpClientInterface::RESPONSE_NORMAL (2): Complete decoded JSON body
     * - HttpClientInterface::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type.
     *
     * If $responseType is HttpClientInterface::RESPONSE_DATA (default):
     * - Just the data array containing the list of notifications with fields including:
     *   - gid: Unique identifier of the notification
     *   - resource_type: Always "notification"
     *   - priority_classification: The notification's classification. Always "unclassified"
     *     for now; Asana will add more values, so do not treat this as a closed set
     *   - story_resource_subtype: Subtype of the story that produced the notification
     *     (e.g., "comment_added", "overdue_tasks_reminder")
     *   - work_objects: Array of compact resources the notification refers to (tasks,
     *     projects, ...). Check each entry's resource_type before reading type-specific fields
     *   - story: Compact story, or null when the activity is not exposed through the
     *     Stories API (e.g., system-generated reminders)
     *   - created_by: The user, AI teammate, app or automation behind the activity, or null
     *   - created_at: Creation timestamp
     *   - is_read: Whether the notification is read in the user's Inbox
     *
     * @throws ApiException
     * @throws RateLimitException
     * @throws ValidationException
     */
    public function getNotifications(
        string $workspaceGid,
        string $userGid = 'me',
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($workspaceGid, 'Workspace GID');
        $this->validateUserGid($userGid);

        if (isset($options['limit'])) {
            $this->validateLimit($options['limit']);
        }

        $options['workspace'] = $workspaceGid;

        return $this->getResources("users/$userGid/notifications", $options, $responseType);
    }

    /**
     * Mark notifications as read
     * POST /users/{user_gid}/notifications/markRead
     * Marks 1 to 50 notifications as read. Read state is shared with the Asana Inbox, so only
     * call this when it reflects the user's intent.
     * Requires the `notifications:write` OAuth scope.
     * API Documentation: https://developers.asana.com/reference/marknotificationsread
     *
     * @param string $workspaceGid The GID of the workspace containing the notifications.
     *                             Example: "12345"
     * @param array $notificationGids 1 to 50 notification GIDs to update. Duplicates are removed
     *                                before sending.
     *                                Example: ["111", "222"]
     * @param string $userGid The authenticated user's GID, or "me" (default).
     * @param array $options Optional parameters to customize the request:
     * - opt_pretty (bool): Returns formatted JSON if true
     *
     * @param int $responseType The type of response to return:
     * - HttpClientInterface::RESPONSE_FULL (1): Full response with status, headers, etc.
     * - HttpClientInterface::RESPONSE_NORMAL (2): Complete decoded JSON body
     * - HttpClientInterface::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type.
     *
     * If $responseType is HttpClientInterface::RESPONSE_DATA (default):
     * - failed_gids: Array of notification GIDs that could not be updated
     *
     * @throws ApiException
     * @throws RateLimitException
     * @throws ValidationException If the GIDs are invalid or there are more than 50 of them
     */
    public function markNotificationsRead(
        string $workspaceGid,
        array $notificationGids,
        string $userGid = 'me',
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        return $this->updateReadState(
            'markRead',
            $workspaceGid,
            $notificationGids,
            $userGid,
            $options,
            $responseType
        );
    }

    /**
     * Mark notifications as unread
     * POST /users/{user_gid}/notifications/markUnread
     * Marks 1 to 50 notifications as unread. Read state is shared with the Asana Inbox, so only
     * call this when it reflects the user's intent.
     * Requires the `notifications:write` OAuth scope.
     * API Documentation: https://developers.asana.com/reference/marknotificationsunread
     *
     * @param string $workspaceGid The GID of the workspace containing the notifications.
     *                             Example: "12345"
     * @param array $notificationGids 1 to 50 notification GIDs to update. Duplicates are removed
     *                                before sending.
     *                                Example: ["111", "222"]
     * @param string $userGid The authenticated user's GID, or "me" (default).
     * @param array $options Optional parameters to customize the request:
     * - opt_pretty (bool): Returns formatted JSON if true
     *
     * @param int $responseType The type of response to return:
     * - HttpClientInterface::RESPONSE_FULL (1): Full response with status, headers, etc.
     * - HttpClientInterface::RESPONSE_NORMAL (2): Complete decoded JSON body
     * - HttpClientInterface::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type.
     *
     * If $responseType is HttpClientInterface::RESPONSE_DATA (default):
     * - failed_gids: Array of notification GIDs that could not be updated
     *
     * @throws ApiException
     * @throws RateLimitException
     * @throws ValidationException If the GIDs are invalid or there are more than 50 of them
     */
    public function markNotificationsUnread(
        string $workspaceGid,
        array $notificationGids,
        string $userGid = 'me',
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        return $this->updateReadState(
            'markUnread',
            $workspaceGid,
            $notificationGids,
            $userGid,
            $options,
            $responseType
        );
    }

    /**
     * Shared implementation for markRead / markUnread.
     *
     * @throws ApiException
     * @throws RateLimitException
     * @throws ValidationException
     */
    private function updateReadState(
        string $action,
        string $workspaceGid,
        array $notificationGids,
        string $userGid,
        array $options,
        int $responseType
    ): array {
        $this->validateGid($workspaceGid, 'Workspace GID');
        $this->validateUserGid($userGid);
        $this->validateGidArray($notificationGids, 'Notification GIDs');

        $notificationGids = array_values(array_unique(array_map('trim', $notificationGids)));

        if (count($notificationGids) > self::MAX_NOTIFICATIONS_PER_UPDATE) {
            throw new ValidationException(
                sprintf('Notification GIDs must contain at most %d items.', self::MAX_NOTIFICATIONS_PER_UPDATE)
            );
        }

        return $this->client->request(
            'POST',
            "users/$userGid/notifications/$action",
            [
                'json' => ['data' => ['workspace' => $workspaceGid, 'notification_gids' => $notificationGids]],
                'query' => $options,
            ],
            $responseType
        );
    }
}
