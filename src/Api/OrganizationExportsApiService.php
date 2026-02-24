<?php

namespace BrightleafDigital\Api;

use BrightleafDigital\Exceptions\ApiException;
use BrightleafDigital\Exceptions\RateLimitException;
use BrightleafDigital\Exceptions\ValidationException;
use BrightleafDigital\Http\HttpClientInterface;

/**
 * Service class for Organization Exports-related API operations.
 *
 * @link https://developers.asana.com/reference/organization-exports
 */
class OrganizationExportsApiService extends BaseApiService
{
    /**
     * Create an organization export request
     * POST /organization_exports
     * This method creates a request to export an Organization. Asana will complete the export asynchronously.
     * API Documentation: https://developers.asana.com/reference/createorganizationexport
     *
     * @param string $organization Globally unique identifier for the workspace or organization.
     * @param array $options Optional query parameters:
     * - opt_fields (string): A comma-separated list of fields to include in the response.
     * - opt_pretty (bool): Returns formatted JSON if true.
     * @param int $responseType The type of response to return.
     *
     * @return array The response data (OrganizationExportResponse).
     * @throws ApiException If the API request fails due to network issues, insufficient permissions, or rate limits.
     * @throws RateLimitException If the Asana API rate limit is exceeded.
     * @throws ValidationException If organization is empty or invalid.
     */
    public function createOrganizationExport(
        string $organization,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($organization, 'Organization GID');
        $data = ['organization' => $organization];
        return $this->createResource('organization_exports', $data, $options, $responseType);
    }

    /**
     * Get details on an org export request
     * GET /organization_exports/{organization_export_gid}
     * Returns details of a previously-requested Organization export.
     * API Documentation: https://developers.asana.com/reference/getorganizationexport
     *
     * @param string $organizationExportGid Globally unique identifier for the organization export.
     * @param array $options Optional query parameters:
     * - opt_fields (string): A comma-separated list of fields to include in the response.
     * - opt_pretty (bool): Returns formatted JSON if true.
     * @param int $responseType The type of response to return.
     *
     * @return array The response data (OrganizationExportResponse).
     * @throws ApiException If the API request fails due to network issues, insufficient permissions, or rate limits.
     * @throws RateLimitException If the Asana API rate limit is exceeded.
     * @throws ValidationException If the organization export GID is empty or invalid.
     */
    public function getOrganizationExport(
        string $organizationExportGid,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($organizationExportGid, 'Organization Export GID');
        return $this->getResource('organization_exports', $organizationExportGid, $options, $responseType);
    }
}
