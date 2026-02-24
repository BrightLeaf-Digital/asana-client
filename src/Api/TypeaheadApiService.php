<?php

namespace BrightleafDigital\Api;

use BrightleafDigital\Exceptions\ApiException;
use BrightleafDigital\Exceptions\RateLimitException;
use BrightleafDigital\Exceptions\ValidationException;
use BrightleafDigital\Http\HttpClientInterface;

/**
 * Service class for Typeahead-related API operations.
 *
 * @link https://developers.asana.com/reference/typeahead
 */
class TypeaheadApiService extends BaseApiService
{
    /**
     * Get objects via typeahead
     * GET /workspaces/{workspace_gid}/typeahead
     * Retrieves objects in the workspace based via an auto-completion/typeahead search algorithm.
     * API Documentation: https://developers.asana.com/reference/typeaheadforworkspace
     *
     * @param string $workspaceGid Globally unique identifier for the workspace.
     * @param array $options Optional parameters to customize the request:
     * - resource_type (string, required): The type of values the typeahead should return.
     *   (custom_field, goal, project, project_template, portfolio, tag, task, team, user)
     * - type (string): Deprecated, use resource_type instead.
     * - query (string): The string to search for.
     * - count (int): The number of results to return. The 1-100, default 20.
     * - opt_fields (string): Comma-separated list of fields.
     * - opt_pretty (bool): Pretty JSON.
     * @param int $responseType The type of response to return.
     *
     * @return array The response data.
     * @throws ApiException
     * @throws RateLimitException
     * @throws ValidationException If workspace GID is empty.
     */
    public function typeaheadForWorkspace(
        string $workspaceGid,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($workspaceGid, 'Workspace GID');
        $this->validateRequiredFields($options, ['resource_type'], 'Typeahead for workspace');
        return $this->client->request(
            'GET',
            "workspaces/$workspaceGid/typeahead",
            ['query' => $options],
            $responseType
        );
    }
}
