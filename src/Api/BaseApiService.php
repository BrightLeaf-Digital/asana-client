<?php

namespace BrightleafDigital\Api;

use BrightleafDigital\Exceptions\ApiException;
use BrightleafDigital\Exceptions\RateLimitException;
use BrightleafDigital\Http\AsanaApiClient;
use BrightleafDigital\Utils\ValidationTrait;

/**
 * Base class for all Asana API service classes.
 *
 * This abstract class provides common functionality for making requests to the Asana API,
 * including standardized methods for CRUD operations and access to the API client.
 */
abstract class BaseApiService
{
    use ValidationTrait;

    /**
     * The Asana API client instance.
     *
     * Handles HTTP requests to the Asana API endpoints with proper authentication
     * and request formatting.
     *
     * @var AsanaApiClient
     */
    protected AsanaApiClient $client;

    /**
     * BaseApiService constructor.
     *
     * @param AsanaApiClient $client An instance of the AsanaApiClient responsible for handling API requests.
     */
    public function __construct(AsanaApiClient $client)
    {
        $this->client = $client;
    }

    /**
     * Standard GET request for a collection of resources.
     *
     * @param string $endpoint The API endpoint (e.g., 'tasks', 'projects').
     * @param array $options Query parameters for filtering, pagination, and formatting.
     * @param int $responseType The type of response to return (FULL, NORMAL, or DATA).
     *
     * @return array The response data.
     * @throws ApiException
     * @throws RateLimitException
     */
    protected function getResources(
        string $endpoint,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        return $this->client->request('GET', $endpoint, ['query' => $options], $responseType);
    }

    /**
     * Standard GET request for a single resource by its GID.
     *
     * @param string $endpoint The API endpoint (e.g., 'tasks', 'projects').
     * @param string $resourceGid The unique global ID of the resource.
     * @param array $options Query parameters for formatting results.
     * @param int $responseType The type of response to return (FULL, NORMAL, or DATA).
     *
     * @return array The response data.
     * @throws ApiException
     * @throws RateLimitException
     */
    protected function getResource(
        string $endpoint,
        string $resourceGid,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        return $this->client->request('GET', "$endpoint/$resourceGid", ['query' => $options], $responseType);
    }

    /**
     * Standard POST request to create a new resource.
     *
     * @param string $endpoint The API endpoint.
     * @param array $data The data for creating the resource.
     * @param array $options Query parameters for formatting results.
     * @param int $responseType The type of response to return.
     *
     * @return array The response data.
     * @throws ApiException
     * @throws RateLimitException
     */
    protected function createResource(
        string $endpoint,
        array $data,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        return $this->client->request(
            'POST',
            $endpoint,
            ['json' => ['data' => $data], 'query' => $options],
            $responseType
        );
    }

    /**
     * Standard PUT request to update an existing resource.
     *
     * @param string $endpoint The API endpoint.
     * @param string $resourceGid The unique global ID of the resource to update.
     * @param array $data The properties of the resource to update.
     * @param array $options Query parameters for formatting results.
     * @param int $responseType The type of response to return.
     *
     * @return array The response data.
     * @throws ApiException
     * @throws RateLimitException
     */
    protected function updateResource(
        string $endpoint,
        string $resourceGid,
        array $data,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        return $this->client->request(
            'PUT',
            "$endpoint/$resourceGid",
            ['json' => ['data' => $data], 'query' => $options],
            $responseType
        );
    }

    /**
     * Standard DELETE request for a resource.
     *
     * @param string $endpoint The API endpoint.
     * @param string $resourceGid The unique global ID of the resource to delete.
     * @param int $responseType The type of response to return.
     *
     * @return array The response data.
     * @throws ApiException
     * @throws RateLimitException
     */
    protected function deleteResource(
        string $endpoint,
        string $resourceGid,
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        return $this->client->request('DELETE', "$endpoint/$resourceGid", [], $responseType);
    }
}
