<?php

namespace BrightleafDigital\Api;

use BrightleafDigital\Exceptions\AsanaApiException;
use BrightleafDigital\Http\AsanaApiClient;
use BrightleafDigital\Utils\ValidationTrait;
use InvalidArgumentException;

class EventsApiService
{
    use ValidationTrait;

    /**
     * An HTTP client instance configured to interact with the Asana API.
     * This property stores an instance of AsanaApiClient which handles all HTTP communication
     * with the Asana API endpoints. It provides authenticated access to API resources and
     * manages request/response handling.
     */
    private AsanaApiClient $client;

    /**
     * Constructor for initializing the service with an Asana API client.
     * Sets up the service instance using the provided Asana API client.
     * @param AsanaApiClient $client The Asana API client instance used to interact with the Asana API.
     * @return void
     */
    public function __construct(AsanaApiClient $client)
    {
        $this->client = $client;
    }

    /**
     * Get events on a resource
     * GET /events
     * Returns the set of events that have occurred since the last sync token was generated.
     * The Events API uses a sync token-based mechanism to provide incremental updates.
     * On the first call, provide only the resource GID; the response will include a sync token
     * but no events. Subsequent requests should always provide the sync token from the
     * immediately preceding call to receive events that occurred since that token was generated.
     * A new sync token will always be included in every response. If the sync token is too old
     * (expired), the API will return an error (412 Precondition Failed) but will still include
     * a new sync token to use for future requests.
     * API Documentation: https://developers.asana.com/reference/getevents
     * @param string $resourceGid The unique global ID of the resource to get events for.
     *                            This can be a task, project, or other Asana resource.
     *                            Example: "12345"
     * @param string|null $syncToken A sync token received from a previous getEvents call.
     *                               Pass null on the first call to establish the initial
     *                               sync position. The API will return a new sync token
     *                               with each response.
     *                               Example: "de4774f6915eae04714ca93bb2f5ee81"
     * @param array $options Optional parameters to customize the request:
     * - opt_fields (string): A comma-separated list of fields to include in the response
     *   (e.g., "user,resource,type,action,parent,created_at")
     * - opt_pretty (bool): Returns formatted JSON if true
     *
     * @param int $responseType The type of response to return:
     *
     * - AsanaApiClient::RESPONSE_FULL (1): Full response with status, headers, etc.
     * - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body (default)
     * - AsanaApiClient::RESPONSE_DATA (3): Only the data subset
     *   Note: The default response type is RESPONSE_NORMAL (not RESPONSE_DATA) to ensure
     *                              the sync token is included in the response for subsequent requests.
     * @return array The response data based on the specified response type:
     *
     * If $responseType is AsanaApiClient::RESPONSE_FULL:
     * - status: HTTP status code
     * - reason: Response status message
     * - headers: Response headers
     * - body: Decoded response body containing event data
     * - raw_body: Raw response body
     * - request: Original request details
     *
     * If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     * - Complete decoded JSON response including:
     * - data: Array of event objects
     * - sync: The new sync token to use for the next request
     * - has_more: Whether there are more events to fetch
     *
     * If $responseType is AsanaApiClient::RESPONSE_DATA:
     * - Just the data array containing the list of events with fields including:
     * - user: The user who triggered the event
     * - resource: The resource that was affected
     * - type: The type of event (e.g., "task", "project", "story")
     * - action: The action that occurred (e.g., "changed", "added", "removed")
     * - parent: The parent resource if applicable
     * - created_at: Timestamp when the event occurred
     * - change: Object describing what changed (field, action, new_value, etc.)
     *                 Additional fields as specified in opt_fields
     *
     * @throws InvalidArgumentException If the resource GID is empty or not numeric
     * @throws AsanaApiException If the sync token is invalid/expired (412 Precondition Failed),
     *                          insufficient permissions, network issues, or rate limiting occurs
     */
    public function getEvents(
        string $resourceGid,
        ?string $syncToken = null,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_NORMAL
    ): array {
        $this->validateGid($resourceGid, 'Resource GID');

        $options['resource'] = $resourceGid;

        if ($syncToken !== null) {
            $options['sync'] = $syncToken;
        }

        return $this->client->request('GET', 'events', ['query' => $options], $responseType);
    }

    /**
     * Get workspace events
     * GET /workspaces/{workspace_gid}/events
     * Returns the full record for all events that have occurred since the sync token was created
     * for the given workspace. The response is a list of events and the schema of each event is the
     * same as described in the resource-level events endpoint.
     * This endpoint provides workspace-level event streams that are useful for data loss prevention
     * (DLP), e-discovery, and archiving purposes. Unlike resource-level events, workspace events
     * include events across all objects in the workspace, even when access control would typically
     * restrict them.
     * Note: This API is exclusively available through a service account in an Enterprise+ domain.
     * Asana limits a single sync token to 1000 events (compared to 100 for resource-level events).
     * If more than 1000 events exist for a given workspace, has_more: true will be returned in the
     * response, indicating that there are more events to pull.
     * API Documentation: https://developers.asana.com/reference/getworkspaceevents
     * @param string $workspaceGid The unique global ID of the workspace or organization.
     *                             This identifier can be found in the admin console or
     *                             returned from workspace-related API endpoints.
     *                             Example: "12345"
     * @param string|null $syncToken A sync token received from a previous getWorkspaceEvents call.
     *                               Pass null on the first call to establish the initial
     *                               sync position. The API will return a new sync token
     *                               with each response.
     *                               Note: On the first request, omit the sync token. The response
     *                               will be the same as for an expired sync token, and will include
     *                               a new valid sync token. If the sync token is too old, the API
     *                               will return a 412 Precondition Failed error with a fresh token.
     *                               Example: "de4774f6915eae04714ca93bb2f5ee81"
     * @param array $options Optional parameters to customize the request:
     * - opt_fields (string): A comma-separated list of fields to include in the response
     *   (e.g., "user,resource,type,action,parent,created_at")
     * - opt_pretty (bool): Returns formatted JSON if true
     *
     * @param int $responseType The type of response to return:
     *
     * - AsanaApiClient::RESPONSE_FULL (1): Full response with status, headers, etc.
     * - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body (default)
     * - AsanaApiClient::RESPONSE_DATA (3): Only the data subset
     *   Note: The default response type is RESPONSE_NORMAL (not RESPONSE_DATA) to ensure
     *                              the sync token is included in the response for subsequent requests.
     * @return array The response data based on the specified response type:
     *
     * If $responseType is AsanaApiClient::RESPONSE_FULL:
     * - status: HTTP status code
     * - reason: Response status message
     * - headers: Response headers
     * - body: Decoded response body containing event data
     * - raw_body: Raw response body
     * - request: Original request details
     *
     * If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     * - Complete decoded JSON response including:
     * - data: Array of event objects
     * - sync: The new sync token to use for the next request
     * - has_more: Whether there are more events to fetch
     *
     * If $responseType is AsanaApiClient::RESPONSE_DATA:
     * - Just the data array containing the list of events with fields including:
     * - user: The user who triggered the event
     * - resource: The resource that was affected
     * - type: The type of event (e.g., "task", "project", "story")
     * - action: The action that occurred (e.g., "changed", "added", "removed", "deleted", "undeleted")
     * - parent: The parent resource if applicable
     * - created_at: Timestamp when the event occurred
     * - change: Object describing what changed (field, action, new_value, added_value, removed_value)
     *                 Additional fields as specified in opt_fields
     *
     * @throws InvalidArgumentException If the workspace GID is empty or not numeric
     * @throws AsanaApiException If the sync token is invalid/expired (412 Precondition Failed),
     *                          insufficient permissions (requires Enterprise+ service account),
     *                          network issues, or rate limiting occurs
     */
    public function getWorkspaceEvents(
        string $workspaceGid,
        ?string $syncToken = null,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_NORMAL
    ): array {
        $this->validateGid($workspaceGid, 'Workspace GID');

        if ($syncToken !== null) {
            $options['sync'] = $syncToken;
        }

        return $this->client->request(
            'GET',
            "workspaces/$workspaceGid/events",
            ['query' => $options],
            $responseType
        );
    }
}
