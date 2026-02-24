<?php

namespace BrightleafDigital\Api;

use BrightleafDigital\Exceptions\ApiException;
use BrightleafDigital\Exceptions\RateLimitException;
use BrightleafDigital\Exceptions\ValidationException;
use BrightleafDigital\Http\HttpClientInterface;

class WorkspaceMembershipsApiService extends BaseApiService
{
    /**
     * Get a workspace membership
     * GET /workspace_memberships/{workspace_membership_gid}
     * Returns the complete workspace record for a single workspace membership.
     * API Documentation: https://developers.asana.com/reference/getworkspacemembership
     *
     * @param string $workspaceMembershipGid Globally unique identifier for the workspace membership.
     * @param array $options Optional query parameters:
     * - opt_fields (string): Comma-separated fields to include
     * - opt_pretty (bool): Returns formatted JSON if true
     *
     * @param int $responseType The type of response to return:
     * - HttpClientInterface::RESPONSE_FULL (1)
     * - HttpClientInterface::RESPONSE_NORMAL (2)
     * - HttpClientInterface::RESPONSE_DATA (3) default
     *
     * @return array
     * @throws ApiException
     * @throws RateLimitException
     * @throws ValidationException
     */
    public function getWorkspaceMembership(
        string $workspaceMembershipGid,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($workspaceMembershipGid, 'Workspace Membership GID');

        return $this->getResource('workspace_memberships', $workspaceMembershipGid, $options, $responseType);
    }

    /**
     * Get workspace memberships for a user
     * GET /users/{user_gid}/workspace_memberships
     * Returns the compact workspace membership records for the user.
     * API Documentation: https://developers.asana.com/reference/getworkspacemembershipsforuser
     *
     * @param string $userGid Globally unique identifier for the user.
     * @param array $options Optional query parameters:
     * - limit (int): Max number of results
     * - offset (string): Offset for pagination
     * - opt_fields (string): Comma-separated fields to include
     * - opt_pretty (bool): Returns formatted JSON if true
     *
     * @param int $responseType The type of response to return:
     * - HttpClientInterface::RESPONSE_FULL (1)
     * - HttpClientInterface::RESPONSE_NORMAL (2)
     * - HttpClientInterface::RESPONSE_DATA (3) default
     *
     * @return array
     * @throws ApiException
     * @throws RateLimitException
     * @throws ValidationException
     */
    public function getWorkspaceMembershipsForUser(
        string $userGid,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($userGid, 'User GID');

        $endpoint = "users/{$userGid}/workspace_memberships";
        return $this->client->request('GET', $endpoint, ['query' => $options], $responseType);
    }

    /**
     * Get the workspace memberships for a workspace
     * GET /workspaces/{workspace_gid}/workspace_memberships
     * Returns the compact workspace membership records for the workspace.
     * API Documentation: https://developers.asana.com/reference/getworkspacemembershipsforworkspace
     *
     * @param string $workspaceGid Globally unique identifier for the workspace or organization.
     * @param array $options Optional query parameters:
     * - user (string): A string identifying a user (e.g., "me", email, gid).
     * - limit (int): Max number of results
     * - offset (string): Offset for pagination
     * - opt_fields (string): Comma-separated fields to include
     * - opt_pretty (bool): Returns formatted JSON if true
     *
     * @param int $responseType The type of response to return:
     * - HttpClientInterface::RESPONSE_FULL (1)
     * - HttpClientInterface::RESPONSE_NORMAL (2)
     * - HttpClientInterface::RESPONSE_DATA (3) default
     *
     * @return array
     * @throws ApiException
     * @throws RateLimitException
     * @throws ValidationException
     */
    public function getWorkspaceMembershipsForWorkspace(
        string $workspaceGid,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($workspaceGid, 'Workspace GID');

        $endpoint = "workspaces/{$workspaceGid}/workspace_memberships";
        return $this->client->request('GET', $endpoint, ['query' => $options], $responseType);
    }
}
