<?php

namespace BrightleafDigital\Api;

use BrightleafDigital\Exceptions\ApiException;
use BrightleafDigital\Exceptions\RateLimitException;
use BrightleafDigital\Exceptions\ValidationException;
use BrightleafDigital\Http\HttpClientInterface;

/**
 * Service class for Custom Types-related API operations.
 *
 * @link https://developers.asana.com/reference/custom-types
 */
class CustomTypesApiService extends BaseApiService
{
    /**
     * Get all custom types associated with a project
     * GET /custom_types
     * Returns a list of custom types associated with an object. Currently, only projects are supported.
     * API Documentation: https://developers.asana.com/reference/getcustomtypes
     *
     * @param string $projectGid Globally unique identifier for the project to filter custom types by.
     * @param array $options Optional query parameters:
     * - limit (int): Results per page (1-100).
     * - offset (string): Offset token.
     * - opt_fields (string): Comma-separated list of fields to include.
     * - opt_pretty (bool): Pretty JSON.
     * @param int $responseType The type of response to return.
     *
     * @return array The response data.
     * @throws ApiException
     * @throws RateLimitException
     * @throws ValidationException If the project GID is empty or invalid.
     */
    public function getCustomTypes(
        string $projectGid,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($projectGid, 'Project GID');
        $options['project'] = $projectGid;
        return $this->getResources('custom_types', $options, $responseType);
    }

    /**
     * Get a custom type
     * GET /custom_types/{custom_type_gid}
     * Returns the complete custom type record for a single custom type.
     * API Documentation: https://developers.asana.com/reference/getcustomtype
     *
     * @param string $customTypeGid Globally unique identifier for the custom type.
     * @param array $options Optional query parameters:
     * - opt_fields (string): Comma-separated list of fields to include.
     * - opt_pretty (bool): Pretty JSON.
     * @param int $responseType The type of response to return.
     *
     * @return array The response data.
     * @throws ApiException
     * @throws RateLimitException
     * @throws ValidationException If the custom type GID is empty or invalid.
     */
    public function getCustomType(
        string $customTypeGid,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($customTypeGid, 'Custom Type GID');
        return $this->getResource('custom_types', $customTypeGid, $options, $responseType);
    }
}
