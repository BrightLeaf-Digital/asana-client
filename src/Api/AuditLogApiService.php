<?php

namespace BrightleafDigital\Api;

use BrightleafDigital\Exceptions\ApiException;
use BrightleafDigital\Exceptions\RateLimitException;
use BrightleafDigital\Exceptions\ValidationException;
use BrightleafDigital\Http\HttpClientInterface;

/**
 * Service for interacting with Asana Audit Log API endpoints.
 *
 * This class provides methods for retrieving audit log events that have been captured in your domain.
 * This endpoint is only available to Enterprise-tier users.
 *
 * @link https://developers.asana.com/reference/getauditlogevents
 */
class AuditLogApiService extends BaseApiService
{
    /**
     * Get audit log events
     * GET /workspaces/{workspace_gid}/audit_log_events
     * Retrieve the audit log events that have been captured in your domain.
     * API Documentation: https://developers.asana.com/reference/getauditlogevents
     *
     * @param string $workspaceGid Globally unique identifier for the workspace or organization.
     *                             Example: "12345"
     * @param array $options Optional parameters to filter and format results:
     * - start_at (string): Only return events created at or after this time (ISO 8601 string).
     * - end_at (string): Only return events created at or before this time (ISO 8601 string).
     * - event_type (string): The type of the event (e.g., "login", "task_created").
     * - actor_type (string): The type of the actor (e.g., "user", "service_account").
     * - actor_gid (string): The gid of the actor.
     * - resource_gid (string): The gid of the resource.
     * - limit (int): Maximum number of items to return. Default is 1000.
     * - offset (string): Offset token for pagination.
     * - opt_pretty (bool): Returns formatted JSON if true.
     *
     * @param int $responseType The type of response to return:
     * - HttpClientInterface::RESPONSE_FULL (1): Full response with status, headers, etc.
     * - HttpClientInterface::RESPONSE_NORMAL (2): Complete decoded JSON body
     * - HttpClientInterface::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type.
     * @throws ApiException
     * @throws RateLimitException
     * @throws ValidationException
     */
    public function getAuditLogEvents(
        string $workspaceGid,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($workspaceGid, 'Workspace GID');

        return $this->getResources("workspaces/$workspaceGid/audit_log_events", $options, $responseType);
    }
}
