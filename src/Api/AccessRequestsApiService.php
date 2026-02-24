<?php

namespace BrightleafDigital\Api;

use BrightleafDigital\Exceptions\ApiException;
use BrightleafDigital\Exceptions\RateLimitException;
use BrightleafDigital\Exceptions\ValidationException;
use BrightleafDigital\Http\HttpClientInterface;

/**
 * Service class for Access Requests-related API operations.
 *
 * @link https://developers.asana.com/reference/access-requests
 */
class AccessRequestsApiService extends BaseApiService
{
    /**
     * Get access requests
     * GET /access_requests
     * Returns the pending access requests for a target object or a target object filtered by user.
     * API Documentation: https://developers.asana.com/reference/getaccessrequests
     *
     * @param string $targetGid Globally unique identifier for the target object (project or portfolio).
     * @param array $options Optional parameters to customize the request:
     * - user (string): A string identifying a user. Can be "me", an email, or the gid of a user.
     * - opt_fields (string): A comma-separated list of fields to include in the response.
     * - opt_pretty (bool): Returns formatted JSON if true.
     * @param int $responseType The type of response to return.
     *
     * @return array The response data.
     * @throws ApiException If the API request fails due to network issues, insufficient permissions, or rate limits.
     * @throws RateLimitException If the Asana API rate limit is exceeded.
     * @throws ValidationException If the target GID is empty or invalid.
     */
    public function getAccessRequests(
        string $targetGid,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($targetGid, 'Target GID');
        $options['target'] = $targetGid;
        return $this->getResources('access_requests', $options, $responseType);
    }

    /**
     * Create an access request
     * POST /access_requests
     * Submits a new access request for a private object. Currently supports projects and portfolios.
     * API Documentation: https://developers.asana.com/reference/createaccessrequest
     *
     * @param array $data The data for creating the access request. Can include:
     * - target (string, required): GID of the target object (project or portfolio).
     * - message (string): Optional message to include with the request.
     * @param array $options Query parameters for formatting results.
     * @param int $responseType The type of response to return.
     *
     * @return array The response data.
     * @throws ApiException If the API request fails due to network issues, insufficient permissions, or rate limits.
     * @throws RateLimitException If the Asana API rate limit is exceeded.
     * @throws ValidationException If data array is missing required fields.
     */
    public function createAccessRequest(
        array $data,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateRequiredFields($data, ['target'], 'Creating an Access Request');
        return $this->createResource('access_requests', $data, $options, $responseType);
    }

    /**
     * Approve an access request
     * POST /access_requests/{access_request_gid}/approve
     * Approves an access request for a target object.
     * API Documentation: https://developers.asana.com/reference/approveaccessrequest
     *
     * @param string $accessRequestGid Globally unique identifier for the access request.
     * @param int $responseType The type of response to return.
     *
     * @return array The response data (EmptyResponse).
     * @throws ApiException If the API request fails due to network issues, insufficient permissions, or rate limits.
     * @throws RateLimitException If the Asana API rate limit is exceeded.
     * @throws ValidationException If the access request GID is empty or invalid.
     */
    public function approveAccessRequest(
        string $accessRequestGid,
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($accessRequestGid, 'Access Request GID');
        return $this->client->request('POST', "access_requests/$accessRequestGid/approve", [], $responseType);
    }

    /**
     * Reject an access request
     * POST /access_requests/{access_request_gid}/reject
     * Rejects an access request for a target object.
     * API Documentation: https://developers.asana.com/reference/rejectaccessrequest
     *
     * @param string $accessRequestGid Globally unique identifier for the access request.
     * @param int $responseType The type of response to return.
     *
     * @return array The response data (EmptyResponse).
     * @throws ApiException If the API request fails due to network issues, insufficient permissions, or rate limits.
     * @throws RateLimitException If the Asana API rate limit is exceeded.
     * @throws ValidationException If the access request GID is empty or invalid.
     */
    public function rejectAccessRequest(
        string $accessRequestGid,
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($accessRequestGid, 'Access Request GID');
        return $this->client->request('POST', "access_requests/$accessRequestGid/reject", [], $responseType);
    }
}
