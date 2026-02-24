<?php

namespace BrightleafDigital\Api;

use BrightleafDigital\Exceptions\ValidationException;
use BrightleafDigital\Http\HttpClientInterface;
use BrightleafDigital\Exceptions\ApiException;
use BrightleafDigital\Exceptions\RateLimitException;

/**
 * Service for interacting with Asana Project Statuses API endpoints.
 *
 * *Deprecated: new integrations should prefer the Status Updates API.*
 *
 * @link https://developers.asana.com/reference/project-statuses
 */
class ProjectStatusesApiService extends BaseApiService
{
    /**
     * Get a project status
     * GET /project_statuses/{project_status_gid}
     * Returns the complete record for a single status update.
     * API Documentation: https://developers.asana.com/reference/getprojectstatus
     *
     * @param string $projectStatusGid The unique global ID of the project status update.
     * @param array $options Optional parameters to customize the request.
     * @param int $responseType The type of response to return.
     * @return array
     *
     * @deprecated Use {@see StatusUpdatesApiService::getStatusUpdate()} instead.
     * @throws ApiException
     * @throws RateLimitException
     * @throws ValidationException
     */
    public function getProjectStatus(
        string $projectStatusGid,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($projectStatusGid, 'Project Status GID');

        return $this->getResource('project_statuses', $projectStatusGid, $options, $responseType);
    }

    /**
     * Delete a project status
     * DELETE /project_statuses/{project_status_gid}
     * Deletes a specific, existing project status update.
     * API Documentation: https://developers.asana.com/reference/deleteprojectstatus
     *
     * @param string $projectStatusGid The unique global ID of the project status update.
     * @param int $responseType The type of response to return.
     * @return array
     *
     * @deprecated Use {@see StatusUpdatesApiService::deleteStatusUpdate()} instead.
     * @throws ApiException
     * @throws RateLimitException
     * @throws ValidationException
     */
    public function deleteProjectStatus(
        string $projectStatusGid,
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($projectStatusGid, 'Project Status GID');

        return $this->deleteResource('project_statuses', $projectStatusGid, $responseType);
    }

    /**
     * Get statuses from a project
     * GET /projects/{project_gid}/project_statuses
     * Returns the compact project status update records for all updates on the project.
     * API Documentation: https://developers.asana.com/reference/getprojectstatusesforproject
     *
     * @param string $projectGid Globally unique identifier for the project.
     * @param array $options Optional parameters to customize the request.
     * @param int $responseType The type of response to return.
     * @return array
     *
     * @deprecated Use {@see StatusUpdatesApiService::getStatusUpdatesForObject()} instead.
     * @throws ApiException
     * @throws RateLimitException
     * @throws ValidationException
     */
    public function getProjectStatusesForProject(
        string $projectGid,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($projectGid, 'Project GID');

        return $this->getResources("projects/{$projectGid}/project_statuses", $options, $responseType);
    }

    /**
     * Create a project status
     * POST /projects/{project_gid}/project_statuses
     * Creates a new status update on the project.
     * API Documentation: https://developers.asana.com/reference/createprojectstatusforproject
     *
     * @param string $projectGid Globally unique identifier for the project.
     * @param array $data Data for creating the project status update.
     * @param array $options Optional parameters to customize the request.
     * @param int $responseType The type of response to return.
     * @return array
     *
     * @deprecated Use {@see StatusUpdatesApiService::createStatusUpdate()} instead.
     * @throws ApiException
     * @throws RateLimitException
     * @throws ValidationException
     */
    public function createProjectStatusForProject(
        string $projectGid,
        array $data,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($projectGid, 'Project GID');
        $this->validateRequiredFields($data, ['color', 'text'], 'project status creation');

        return $this->createResource("projects/{$projectGid}/project_statuses", $data, $options, $responseType);
    }
}
