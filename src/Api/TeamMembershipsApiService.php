<?php

namespace BrightleafDigital\Api;

use BrightleafDigital\Exceptions\ApiException;
use BrightleafDigital\Exceptions\RateLimitException;
use BrightleafDigital\Exceptions\ValidationException;
use BrightleafDigital\Http\HttpClientInterface;

class TeamMembershipsApiService extends BaseApiService
{
    /**
     * Get a team membership
     * GET /team_memberships/{team_membership_gid}
     * Returns the complete team membership record for a single team membership.
     * API Documentation: https://developers.asana.com/reference/getteammembership
     *
     * @param string $teamMembershipGid Globally unique identifier for the team membership.
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
    public function getTeamMembership(
        string $teamMembershipGid,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($teamMembershipGid, 'Team Membership GID');

        return $this->getResource('team_memberships', $teamMembershipGid, $options, $responseType);
    }

    /**
     * Get team memberships
     * GET /team_memberships
     * Returns compact team membership records.
     * API Documentation: https://developers.asana.com/reference/getteammemberships
     *
     * @param array $options Optional query parameters:
     * - team (string): Globally unique identifier for the team.
     * - user (string): A string identifying a user (e.g., "me", email, gid).
     *   Must be used with the workspace parameter.
     * - workspace (string): Globally unique identifier for the workspace.
     *   Must be used with the user parameter.
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
     */
    public function getTeamMemberships(
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        return $this->getResources('team_memberships', $options, $responseType);
    }

    /**
     * Get memberships from a team
     * GET /teams/{team_gid}/team_memberships
     * Returns the compact team memberships for the team.
     * API Documentation: https://developers.asana.com/reference/getteammembershipsforteam
     *
     * @param string $teamGid Globally unique identifier for the team.
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
    public function getTeamMembershipsForTeam(
        string $teamGid,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($teamGid, 'Team GID');

        $endpoint = "teams/$teamGid/team_memberships";
        return $this->client->request('GET', $endpoint, ['query' => $options], $responseType);
    }

    /**
     * Get memberships from a user
     * GET /users/{user_gid}/team_memberships
     * Returns the compact team membership records for the user.
     * API Documentation: https://developers.asana.com/reference/getteammembershipsforuser
     *
     * @param string $userGid Globally unique identifier for the user.
     * @param string $workspace Globally unique identifier for the workspace.
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
    public function getTeamMembershipsForUser(
        string $userGid,
        string $workspace,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($userGid, 'User GID');
        $this->validateGid($workspace, 'Workspace GID');

        $options['workspace'] = $workspace;
        $endpoint = "users/$userGid/team_memberships";
        return $this->client->request('GET', $endpoint, ['query' => $options], $responseType);
    }
}
