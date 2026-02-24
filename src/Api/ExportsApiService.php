<?php

namespace BrightleafDigital\Api;

use BrightleafDigital\Exceptions\ApiException;
use BrightleafDigital\Exceptions\RateLimitException;
use BrightleafDigital\Exceptions\ValidationException;
use BrightleafDigital\Http\HttpClientInterface;

/**
 * Service class for Exports-related API operations.
 *
 * @link https://developers.asana.com/reference/exports
 */
class ExportsApiService extends BaseApiService
{
    /**
     * Initiate a graph export
     * POST /exports/graph
     * Initiates a graph export job for a given parent object (goal, team, portfolio, or project).
     * API Documentation: https://developers.asana.com/reference/creategraphexport
     *
     * @param string $parent Globally unique ID of the parent object: goal, project, portfolio, or team.
     * @param array $options Query parameters for formatting results.
     * @param int $responseType The type of response to return.
     *
     * @return array The response data (GraphExportResponse).
     * @throws ApiException
     * @throws RateLimitException
     * @throws ValidationException If parent GID is empty.
     */
    public function createGraphExport(
        string $parent,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($parent, 'Parent GID');
        $data = ['parent' => $parent];
        return $this->createResource('exports/graph', $data, $options, $responseType);
    }

    /**
     * Initiate a resource export
     * POST /exports/resource
     * Initiates a bulk export of resources for a workspace.
     * API Documentation: https://developers.asana.com/reference/createresourceexport
     *
     * @param string $workspace GID of a workspace.
     * @param array $exportRequestParameters An array containing the parameters for the export request.
     * @param array $options Query parameters for formatting results.
     * @param int $responseType The type of response to return.
     *
     * @return array The response data (ResourceExportResponse).
     * @throws ApiException
     * @throws RateLimitException
     * @throws ValidationException If workspace GID is empty.
     */
    public function createResourceExport(
        string $workspace,
        array $exportRequestParameters = [],
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($workspace, 'Workspace GID');
        $data = [
            'workspace' => $workspace,
            'export_request_parameters' => $exportRequestParameters,
        ];
        return $this->createResource('exports/resource', $data, $options, $responseType);
    }
}
