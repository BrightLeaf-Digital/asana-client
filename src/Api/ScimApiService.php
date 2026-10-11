<?php

namespace BrightleafDigital\Api;

use BrightleafDigital\Exceptions\ApiException;
use BrightleafDigital\Exceptions\RateLimitException;
use BrightleafDigital\Http\HttpClientInterface;

/**
 * SCIM directory listing for service accounts in Enterprise domains.
 * Authenticate with a service account token; ordinary PATs and OAuth tokens cannot access SCIM.
 *
 * Pagination is opt-in. With no paging parameters, Asana returns the full matching directory.
 * Paged responses contain Resources, totalResults, itemsPerPage and nextCursor.
 * startIndex is included for index requests and omitted for cursor requests.
 * Equality filters return matching resources without pagination.
 *
 * @link https://developers.asana.com/docs/scim
 * @link https://forum.asana.com/t/new-pagination-for-scim-users-and-groups-endpoints/1157938
 */
class ScimApiService extends BaseApiService
{
    /**
     * List users in the domain (excludes guests).
     * GET /scim/Users
     *
     * @param array $options Optional query parameters:
     * - filter (string): Equality filter, e.g. userName eq "user@example.com".
     * - startIndex (int): 1-based starting index; defaults to 1, values below 1 become 1.
     * - count (int): Page size; defaults to 100, values above 1000 are capped at 1000.
     * - cursor (string): Opaque nextCursor from the preceding response; overrides startIndex.
     * Including startIndex, count or cursor enables pagination. Options are forwarded unchanged.
     * @param int $responseType NORMAL (default) returns the complete SCIM envelope.
     * FULL adds HTTP status, headers and request metadata; DATA also returns the SCIM envelope.
     * @return array Complete SCIM list response, or the FULL response wrapper.
     * @throws ApiException Invalid pagination returns HTTP 400 with scimType invalidCursor
     * or invalidValue, available through getResponseData().
     * @throws RateLimitException
     */
    public function getUsers(
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_NORMAL
    ): array {
        return $this->getResources('scim/Users', $options, $responseType);
    }

    /**
     * List groups (Asana teams) in the domain.
     * GET /scim/Groups
     *
     * @param array $options Optional query parameters:
     * - filter (string): Equality filter, e.g. displayName eq "Engineering".
     * - startIndex (int): 1-based starting index; defaults to 1, values below 1 become 1.
     * - count (int): Page size; defaults to 100, values above 1000 are capped at 1000.
     * - cursor (string): Opaque nextCursor from the preceding response; overrides startIndex.
     * Including startIndex, count or cursor enables pagination. Options are forwarded unchanged.
     * @param int $responseType NORMAL (default) returns the complete SCIM envelope.
     * FULL adds HTTP status, headers and request metadata; DATA also returns the SCIM envelope.
     * @return array Complete SCIM list response, or the FULL response wrapper.
     * @throws ApiException Invalid pagination returns HTTP 400 with scimType invalidCursor
     * or invalidValue, available through getResponseData().
     * @throws RateLimitException
     */
    public function getGroups(
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_NORMAL
    ): array {
        return $this->getResources('scim/Groups', $options, $responseType);
    }
}
