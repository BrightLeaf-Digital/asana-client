<?php

namespace BrightleafDigital\Api;

use BrightleafDigital\Exceptions\ApiException;
use BrightleafDigital\Exceptions\RateLimitException;
use BrightleafDigital\Exceptions\ValidationException;
use BrightleafDigital\Http\HttpClientInterface;

/**
 * Service class for Rates-related API operations.
 *
 * @link https://developers.asana.com/reference/rates
 */
class RatesApiService extends BaseApiService
{
    /**
     * Get multiple rates
     * GET /rates
     * Returns a list of rate records.
     * API Documentation: https://developers.asana.com/reference/getrates
     *
     * @param array $options Optional parameters to customize the request:
     * - parent (string, required): Globally unique identifier for the project to filter rates by.
     * - resource (string): Globally unique identifier for the user or placeholder to filter rates by.
     * - limit (int): Results per page.
     * - offset (string): Offset token.
     * - opt_fields (string): Comma-separated list of fields.
     * - opt_pretty (bool): Pretty JSON.
     * @param int $responseType The type of response to return.
     *
     * @return array The response data.
     * @throws ApiException
     * @throws RateLimitException
     */
    public function getRates(array $options = [], int $responseType = HttpClientInterface::RESPONSE_DATA): array
    {
        return $this->getResources('rates', $options, $responseType);
    }

    /**
     * Create a rate
     * POST /rates
     * Creates a new rate.
     * API Documentation: https://developers.asana.com/reference/createrate
     *
     * @param array $data The data for creating the rate. Can include:
     * - parent (string, required): Globally unique identifier for the project the rate is on.
     * - resource (string, required): Globally unique identifier for the user or placeholder the rate is for.
     * - rate (number, required): The rate value.
     * @param array $options Query parameters for formatting results.
     * @param int $responseType The type of response to return.
     *
     * @return array The response data.
     * @throws ApiException
     * @throws RateLimitException
     * @throws ValidationException
     */
    public function createRate(
        array $data,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateRequiredFields($data, ['parent', 'resource', 'rate'], 'Create rate');
        return $this->createResource('rates', $data, $options, $responseType);
    }

    /**
     * Get a rate
     * GET /rates/{rate_gid}
     * Returns the complete rate record for a single rate.
     * API Documentation: https://developers.asana.com/reference/getrate
     *
     * @param string $rateGid Globally unique identifier for the rate.
     * @param array $options Query parameters for formatting results.
     * @param int $responseType The type of response to return.
     *
     * @return array The response data.
     * @throws ApiException
     * @throws RateLimitException
     * @throws ValidationException If rate GID is empty.
     */
    public function getRate(
        string $rateGid,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($rateGid, 'Rate GID');
        return $this->getResource('rates', $rateGid, $options, $responseType);
    }

    /**
     * Update a rate
     * PUT /rates/{rate_gid}
     * Updates an existing rate.
     * API Documentation: https://developers.asana.com/reference/updaterate
     *
     * @param string $rateGid Globally unique identifier for the rate.
     * @param array $data The properties of the rate to update. Can include:
     * - rate (number): The rate value.
     * @param array $options Query parameters for formatting results.
     * @param int $responseType The type of response to return.
     *
     * @return array The response data.
     * @throws ApiException
     * @throws RateLimitException
     * @throws ValidationException If rate GID is empty.
     */
    public function updateRate(
        string $rateGid,
        array $data,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($rateGid, 'Rate GID');
        return $this->updateResource('rates', $rateGid, $data, $options, $responseType);
    }

    /**
     * Delete a rate
     * DELETE /rates/{rate_gid}
     * Deletes a specific, existing rate.
     * API Documentation: https://developers.asana.com/reference/deleterate
     *
     * @param string $rateGid Globally unique identifier for the rate.
     * @param int $responseType The type of response to return.
     *
     * @return array The response data (empty object).
     * @throws ApiException
     * @throws RateLimitException
     * @throws ValidationException If rate GID is empty.
     */
    public function deleteRate(string $rateGid, int $responseType = HttpClientInterface::RESPONSE_DATA): array
    {
        $this->validateGid($rateGid, 'Rate GID');
        return $this->deleteResource('rates', $rateGid, $responseType);
    }
}
