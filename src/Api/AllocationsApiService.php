<?php

namespace BrightleafDigital\Api;

use BrightleafDigital\Exceptions\ApiException;
use BrightleafDigital\Exceptions\RateLimitException;
use BrightleafDigital\Exceptions\ValidationException;
use BrightleafDigital\Http\HttpClientInterface;

/**
 * Service class for Allocations-related API operations.
 *
 * @link https://developers.asana.com/reference/allocations
 */
class AllocationsApiService extends BaseApiService
{
    /**
     * Get an allocation.
     * GET /allocations/{allocation_gid}
     * Returns the complete allocation record for a single allocation.
     * API Documentation: https://developers.asana.com/reference/getallocation
     *
     * @param string $allocationGid Globally unique identifier for the allocation.
     * @param array $options Query parameters for formatting results.
     * @param int $responseType The type of response to return.
     *
     * @return array The response data.
     * @throws ApiException If the API request fails due to network issues, insufficient permissions, or rate limits.
     * @throws RateLimitException If the Asana API rate limit is exceeded.
     * @throws ValidationException If the allocation GID is empty or invalid.
     */
    public function getAllocation(
        string $allocationGid,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($allocationGid, 'Allocation GID');
        return $this->getResource('allocations', $allocationGid, $options, $responseType);
    }

    /**
     * Update an allocation.
     * PUT /allocations/{allocation_gid}
     * Updates an existing allocation.
     * API Documentation: https://developers.asana.com/reference/updateallocation
     *
     * @param string $allocationGid Globally unique identifier for the allocation.
     * @param array $data The properties of the allocation to update. Can include:
     * - start_date (string): The localized day on which the allocation starts (YYYY-MM-DD).
     * - end_date (string): The localized day on which the allocation ends (YYYY-MM-DD).
     * @param array $options Query parameters for formatting results.
     * @param int $responseType The type of response to return.
     *
     * @return array The response data.
     * @throws ApiException If the API request fails due to network issues, insufficient permissions, or rate limits.
     * @throws RateLimitException If the Asana API rate limit is exceeded.
     * @throws ValidationException If the allocation GID is empty or invalid.
     */
    public function updateAllocation(
        string $allocationGid,
        array $data,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($allocationGid, 'Allocation GID');
        return $this->updateResource('allocations', $allocationGid, $data, $options, $responseType);
    }

    /**
     * Delete an allocation.
     * DELETE /allocations/{allocation_gid}
     * Deletes a specific, existing allocation.
     * API Documentation: https://developers.asana.com/reference/deleteallocation
     *
     * @param string $allocationGid Globally unique identifier for the allocation.
     * @param int $responseType The type of response to return.
     *
     * @return array The response data.
     * @throws ApiException If the API request fails due to network issues, insufficient permissions, or rate limits.
     * @throws RateLimitException If the Asana API rate limit is exceeded.
     * @throws ValidationException If the allocation GID is empty or invalid.
     */
    public function deleteAllocation(
        string $allocationGid,
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($allocationGid, 'Allocation GID');
        return $this->deleteResource('allocations', $allocationGid, $responseType);
    }

    /**
     * Get multiple allocations.
     * GET /allocations
     * Returns a list of allocations in a workspace.
     * API Documentation: https://developers.asana.com/reference/getallocations
     *
     * @param array $options Optional parameters to customize the request:
     * - parent (string): Globally unique identifier for the project to filter allocations by.
     * - assignee (string): Globally unique identifier for the user or placeholder the allocation is assigned to.
     * - workspace (string): Globally unique identifier for the workspace.
     * - limit (int): Results per page. The number of objects to return per page. The value must be between 1 and 100.
     * - offset (string): Offset token for pagination.
     * - opt_fields (string): A comma-separated list of fields to include in the response.
     * - opt_pretty (bool): Returns formatted JSON if true.
     * @param int $responseType The type of response to return.
     *
     * @return array The response data.
     * @throws ApiException If the API request fails due to network issues, insufficient permissions, or rate limits.
     * @throws RateLimitException If the Asana API rate limit is exceeded.
     */
    public function getAllocations(array $options = [], int $responseType = HttpClientInterface::RESPONSE_DATA): array
    {
        return $this->getResources('allocations', $options, $responseType);
    }

    /**
     * Create an allocation.
     * POST /allocations
     * Creates a new allocation.
     * API Documentation: https://developers.asana.com/reference/createallocation
     *
     * @param array $data The data for creating the allocation. Can include:
     * - assignee (string, required): Globally unique identifier for the user or placeholder assigned to the allocation.
     * - parent (string, required): Globally unique identifier for the project the allocation is on.
     * - start_date (string): The localized day on which the allocation starts (YYYY-MM-DD).
     * - end_date (string): The localized day on which the allocation ends (YYYY-MM-DD).
     * @param array $options Query parameters for formatting results.
     * @param int $responseType The type of response to return.
     *
     * @return array The response data.
     * @throws ApiException If the API request fails due to network issues, insufficient permissions, or rate limits.
     * @throws RateLimitException If the Asana API rate limit is exceeded.
     */
    public function createAllocation(
        array $data,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        return $this->createResource('allocations', $data, $options, $responseType);
    }
}
