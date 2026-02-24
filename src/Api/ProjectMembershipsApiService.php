<?php

namespace BrightleafDigital\Api;

use BrightleafDigital\Exceptions\ApiException;
use BrightleafDigital\Exceptions\RateLimitException;
use BrightleafDigital\Exceptions\ValidationException;
use BrightleafDigital\Http\HttpClientInterface;

class ProjectMembershipsApiService extends BaseApiService
{
    /**
     *
     * Get a project membership
     * GET /project_memberships/{project_membership_gid}
     * Returns the complete project record for a single project membership.
     * API Documentation: https://developers.asana.com/reference/getprojectmembership
     *
     * @deprecated This endpoint is deprecated in favor of the {@see MembershipApiService::getMembership()} endpoint.
     * @param string $projectMembershipGid The unique global ID of the project membership to retrieve.
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
    public function getProjectMembership(
        string $projectMembershipGid,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($projectMembershipGid, 'Project Membership GID');

        return $this->getResource('project_memberships', $projectMembershipGid, $options, $responseType);
    }

    /**
     * Get memberships from a project
     * GET /projects/{project_gid}/project_memberships
     * Returns the compact project membership records for the project.
     * API Documentation: https://developers.asana.com/reference/getprojectmembershipsforproject
     *
     * @deprecated This endpoint is deprecated in favor of the {@see MembershipApiService::getMemberships()} endpoint.
     * @param string $projectGid The unique global ID of the project.
     * @param array $options Optional query parameters:
     * - user (string): Filter to memberships for a given user (e.g., "me")
     * - limit (int): Max number of results
     * - offset (string): Offset for pagination
     * - opt_fields (string): Comma-separated fields to include
     * - opt_pretty (bool): Returns formatted JSON if true
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
    public function getProjectMembershipsForProject(
        string $projectGid,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($projectGid, 'Project GID');

        $endpoint = "projects/{$projectGid}/project_memberships";
        return $this->client->request('GET', $endpoint, ['query' => $options], $responseType);
    }
}
