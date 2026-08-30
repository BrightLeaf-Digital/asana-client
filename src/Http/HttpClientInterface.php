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
     * Asana feature flag values for use with enableFeatureFlag()/disableFeatureFlag().
     */

    /** Early access to the AI Teammates (agent actor) API surfaces. */
    public const FLAG_AI_TEAMMATE_ACTORS = 'ai_teammate_actors';

    /**
     * Include objects carrying Asana-created custom types in bulk collection responses.
     *
     * Asana rolled enhanced custom type support out on 2026-08-18 with this flag off, so
     * `GET /tasks`, `GET /projects` and `GET /portfolios` currently hide objects whose custom
     * type was created by an Asana product. On 2027-01-13 the default flips and those objects
     * are returned unless the flag is explicitly disabled. Explicit `custom_type` filters are
     * unaffected by the flag either way.
     *
     * @see https://forum.asana.com/t/upcoming-enhanced-custom-type-support-for-projects-and-portfolios-plus-new-custom-type-filtering/1153530
     */
    public const FLAG_INCLUDE_ASANA_CREATED_CUSTOM_TYPES = 'include_asana_created_custom_types';

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

    /**
     * Enable an Asana feature flag sent via the Asana-Enable request header.
     * Required for early-access features such as AI Teammates (ai_teammate_actors).
     *
     * Calling this for a flag that is currently disabled moves it to the enabled list.
     *
     * @param string $flag The feature flag value (e.g., 'ai_teammate_actors').
     * @return static
     */
    public function enableFeatureFlag(string $flag): static;

    /**
     * Disable an Asana feature flag sent via the Asana-Disable request header.
     *
     * Used to opt out of a deprecation whose default has already flipped on, e.g. sending
     * `Asana-Disable: include_asana_created_custom_types` after 2027-01-13 to keep bulk
     * collections free of objects with Asana-created custom types.
     *
     * Calling this for a flag that is currently enabled moves it to the disabled list.
     *
     * @param string $flag The feature flag value (e.g., 'include_asana_created_custom_types').
     * @return static
     */
    public function disableFeatureFlag(string $flag): static;
}
