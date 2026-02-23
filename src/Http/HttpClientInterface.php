<?php

namespace BrightleafDigital\Http;

use BrightleafDigital\Exceptions\ApiException;
use BrightleafDigital\Exceptions\RateLimitException;

/**
 * Interface for the Asana API HTTP client.
 */
interface HttpClientInterface
{
    /**
     * Response type constants
     */
    public const RESPONSE_FULL = 1;     // Return full response with status, headers, etc.
    public const RESPONSE_NORMAL = 2;   // Return the complete decoded JSON body
    public const RESPONSE_DATA = 3;     // Return only the data subset (default)

    /**
     * Sends an HTTP request with the specified method, URI, and options.
     *
     * @param string $method The HTTP method to use (e.g., 'GET', 'POST', etc.).
     * @param string $uri The URI to make the request to.
     * @param array $options Additional options for the request.
     * @param int $responseType The type of response to return.
     *
     * @return array The response data.
     * @throws ApiException If the request fails.
     * @throws RateLimitException If rate limit is exceeded and all retries are exhausted.
     */
    public function request(
        string $method,
        string $uri,
        array $options = [],
        int $responseType = self::RESPONSE_DATA
    ): array;
}
