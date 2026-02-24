<?php

namespace BrightleafDigital\Api;

use BrightleafDigital\Exceptions\ApiException;
use BrightleafDigital\Exceptions\RateLimitException;
use BrightleafDigital\Exceptions\ValidationException;
use BrightleafDigital\Http\HttpClientInterface;

/**
 * Service class for Project Briefs-related API operations.
 *
 * @link https://developers.asana.com/reference/project-briefs
 */
class ProjectBriefsApiService extends BaseApiService
{
    /**
     * Get a project brief
     * GET /project_briefs/{project_brief_gid}
     * Returns the complete project brief record.
     * API Documentation: https://developers.asana.com/reference/getprojectbrief
     *
     * @param string $projectBriefGid Globally unique identifier for the project brief.
     * @param array $options Optional query parameters:
     * - opt_fields (string): A comma-separated list of fields to include in the response.
     * - opt_pretty (bool): Returns formatted JSON if true.
     * @param int $responseType The type of response to return.
     *
     * @return array The response data.
     * @throws ApiException If the API request fails due to network issues, insufficient permissions, or rate limits.
     * @throws RateLimitException If the Asana API rate limit is exceeded.
     * @throws ValidationException If the project brief GID is empty or invalid.
     */
    public function getProjectBrief(
        string $projectBriefGid,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($projectBriefGid, 'Project Brief GID');
        return $this->getResource('project_briefs', $projectBriefGid, $options, $responseType);
    }

    /**
     * Update a project brief
     * PUT /project_briefs/{project_brief_gid}
     * Updates an existing project brief. Only supplied fields are updated.
     * API Documentation: https://developers.asana.com/reference/updateprojectbrief
     *
     * @param string $projectBriefGid Globally unique identifier for the project brief.
     * @param array $data The properties to update. Can include:
     * - title (string): The title of the brief.
     * - text (string): The plain text content of the brief.
     * - html_text (string): The HTML content of the brief.
     * - Use either text or html_text, not both. html_text is preferred.
     * @param array $options Optional query parameters:
     * - opt_fields (string): Comma-separated list of fields to include in response.
     * - opt_pretty (bool): Pretty JSON.
     * @param int $responseType The type of response to return.
     *
     * @return array The response data.
     * @throws ApiException If the API request fails due to network issues, insufficient permissions, or rate limits.
     * @throws RateLimitException If the Asana API rate limit is exceeded.
     * @throws ValidationException If the project brief GID is empty or invalid.
     */
    public function updateProjectBrief(
        string $projectBriefGid,
        array $data,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($projectBriefGid, 'Project Brief GID');
        return $this->updateResource('project_briefs', $projectBriefGid, $data, $options, $responseType);
    }

    /**
     * Delete a project brief
     * DELETE /project_briefs/{project_brief_gid}
     * Deletes a specific, existing project brief.
     * API Documentation: https://developers.asana.com/reference/deleteprojectbrief
     *
     * @param string $projectBriefGid Globally unique identifier for the project brief.
     * @param int $responseType The type of response to return.
     *
     * @return array The response data (empty object).
     * @throws ApiException
     * @throws RateLimitException
     * @throws ValidationException If the project brief GID is empty or invalid.
     */
    public function deleteProjectBrief(
        string $projectBriefGid,
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($projectBriefGid, 'Project Brief GID');
        return $this->deleteResource('project_briefs', $projectBriefGid, $responseType);
    }

    /**
     * Create a project brief
     * POST /projects/{project_gid}/project_briefs
     * Creates a new project brief in a project.
     * API Documentation: https://developers.asana.com/reference/createprojectbrief
     *
     * @param string $projectGid Globally unique identifier for the project.
     * @param array $data The properties for the new brief. Can include:
     * - title (string): The title of the brief.
     * - text (string): The plain text content of the brief.
     * - html_text (string): The HTML content of the brief.
     * - Use either text or html_text, not both. html_text is preferred.
 * @param array $options Optional query parameters:
     * - opt_fields (string): Comma-separated list of fields to include in response.
     * - opt_pretty (bool): Pretty JSON.
     * @param int $responseType The type of response to return.
     *
     * @return array The response data.
     * @throws ApiException If the API request fails due to network issues, insufficient permissions, or rate limits.
     * @throws RateLimitException If the Asana API rate limit is exceeded.
     * @throws ValidationException If the project GID is empty or invalid.
     */
    public function createProjectBrief(
        string $projectGid,
        array $data,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($projectGid, 'Project GID');
        return $this->createResource(
            "projects/$projectGid/project_briefs",
            $data,
            $options,
            $responseType
        );
    }
}
