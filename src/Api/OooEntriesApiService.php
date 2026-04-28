<?php

namespace BrightleafDigital\Api;

use BrightleafDigital\Exceptions\ApiException;
use BrightleafDigital\Exceptions\RateLimitException;
use BrightleafDigital\Exceptions\ValidationException;
use BrightleafDigital\Http\HttpClientInterface;

/**
 * Service for managing Out of Office (OOO) entries via the Asana API.
 *
 * This replaces the deprecated `vacation_dates` field on Workspace Memberships.
 * The `vacation_dates` field will be removed approximately October 2026.
 */
class OooEntriesApiService extends BaseApiService
{
    /**
     * Get multiple OOO entries
     * GET /ooo_entries
     * Returns a list of out-of-office entries filtered by the given criteria.
     * Requires the `ooo_entries:read` and `users:read` OAuth scopes.
     * API Documentation: https://developers.asana.com/reference/getoooentries
     *
     * @param array $options Optional parameters to customize the request:
     *
     * Filtering parameters:
     * - user (string): GID of the user to filter OOO entries for (required)
     * - workspace (string): GID of the workspace to filter by
     * - start_date (string): Return entries that overlap with or start on/after this date (YYYY-MM-DD)
     * - end_date (string): Return entries that overlap with or end on/before this date (YYYY-MM-DD)
     *
     * Pagination parameters:
     * - limit (int): Maximum number of entries to return. Default is 20, max is 100
     * - offset (string): Offset token for pagination
     *
     * Display parameters:
     * - opt_fields (string): Comma-separated fields to include in the response
     *   (e.g., "user,workspace,start_date,end_date,created_by")
     * - opt_pretty (bool): Returns formatted JSON if true
     *
     * @param int $responseType The type of response to return:
     * - HttpClientInterface::RESPONSE_FULL (1): Full response with status, headers, etc.
     * - HttpClientInterface::RESPONSE_NORMAL (2): Complete decoded JSON body
     * - HttpClientInterface::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type.
     *
     * If $responseType is HttpClientInterface::RESPONSE_DATA (default):
     * - Just the data array containing the list of OOO entries with fields including:
     *   - gid: Unique identifier of the OOO entry
     *   - resource_type: Always "ooo_entry"
     *   - start_date: Start date of the OOO period (YYYY-MM-DD)
     *   - end_date: End date of the OOO period (YYYY-MM-DD); must be on or after start_date
     *   - user: Object containing the user details
     *   - created_by: Object containing the creator details
     *   - workspace: Object containing the workspace details
     *
     * @throws ApiException
     * @throws RateLimitException
     */
    public function getOooEntries(
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        return $this->getResources('ooo_entries', $options, $responseType);
    }

    /**
     * Create an OOO entry
     * POST /ooo_entries
     * Creates a new out-of-office entry for a user. Multiple entries per user are supported,
     * and entries can overlap.
     * Requires the `ooo_entries:write` and `users:read` OAuth scopes.
     * API Documentation: https://developers.asana.com/reference/createoooentry
     *
     * @param array $data Data for creating the OOO entry. Supported fields include:
     *                    Required:
     * - user (string): GID of the user this OOO entry is for (or "me" for the current user).
     *   Example: "67890"
     * - workspace (string): GID of the workspace.
     *   Example: "12345"
     * - start_date (string): Start date of the OOO period in YYYY-MM-DD format.
     *   Example: "2026-05-01"
     * - end_date (string): End date of the OOO period in YYYY-MM-DD format.
     *   Must be on or after start_date.
     *   Example: "2026-05-07"
     *   Example: ["user" => "67890", "workspace" => "12345",
     *             "start_date" => "2026-05-01", "end_date" => "2026-05-07"]
     * @param array $options Optional parameters to customize the request:
     * - opt_fields (string): Comma-separated fields to include in the response
     * - opt_pretty (bool): Returns formatted JSON if true
     *
     * @param int $responseType The type of response to return:
     * - HttpClientInterface::RESPONSE_FULL (1): Full response with status, headers, etc.
     * - HttpClientInterface::RESPONSE_NORMAL (2): Complete decoded JSON body
     * - HttpClientInterface::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type.
     *
     * @throws ApiException
     * @throws RateLimitException
     * @throws ValidationException If required fields are missing
     */
    public function createOooEntry(
        array $data,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateRequiredFields(
            $data,
            ['user', 'workspace', 'start_date', 'end_date'],
            'OOO entry creation'
        );

        return $this->createResource('ooo_entries', $data, $options, $responseType);
    }

    /**
     * Get an OOO entry
     * GET /ooo_entries/{ooo_entry_gid}
     * Returns the full record for a single out-of-office entry.
     * Requires the `ooo_entries:read` and `users:read` OAuth scopes.
     * API Documentation: https://developers.asana.com/reference/getoooentry
     *
     * @param string $oooEntryGid The unique global ID of the OOO entry.
     *                            Example: "12345"
     * @param array $options Optional parameters to customize the request:
     * - opt_fields (string): Comma-separated fields to include in the response
     *   (e.g., "user,workspace,start_date,end_date,created_by")
     * - opt_pretty (bool): Returns formatted JSON if true
     *
     * @param int $responseType The type of response to return:
     * - HttpClientInterface::RESPONSE_FULL (1): Full response with status, headers, etc.
     * - HttpClientInterface::RESPONSE_NORMAL (2): Complete decoded JSON body
     * - HttpClientInterface::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type.
     *
     * @throws ApiException
     * @throws RateLimitException
     * @throws ValidationException
     */
    public function getOooEntry(
        string $oooEntryGid,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($oooEntryGid, 'OOO Entry GID');

        return $this->getResource('ooo_entries', $oooEntryGid, $options, $responseType);
    }

    /**
     * Update an OOO entry
     * PUT /ooo_entries/{ooo_entry_gid}
     * Updates the properties of an out-of-office entry. Only the fields provided
     * will be updated; any unspecified fields will remain unchanged.
     * Requires the `ooo_entries:write` and `users:read` OAuth scopes.
     * API Documentation: https://developers.asana.com/reference/updateoooentry
     *
     * @param string $oooEntryGid The unique global ID of the OOO entry.
     *                            Example: "12345"
     * @param array $data Properties to update. Can include:
     * - start_date (string): New start date in YYYY-MM-DD format.
     *   Example: "2026-05-02"
     * - end_date (string): New end date in YYYY-MM-DD format.
     *   Must be on or after start_date.
     *   Example: "2026-05-08"
     *   Example: ["start_date" => "2026-05-02", "end_date" => "2026-05-08"]
     * @param array $options Optional parameters to customize the request:
     * - opt_fields (string): Comma-separated fields to include in the response
     * - opt_pretty (bool): Returns formatted JSON if true
     *
     * @param int $responseType The type of response to return:
     * - HttpClientInterface::RESPONSE_FULL (1): Full response with status, headers, etc.
     * - HttpClientInterface::RESPONSE_NORMAL (2): Complete decoded JSON body
     * - HttpClientInterface::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type.
     *
     * @throws ApiException
     * @throws RateLimitException
     * @throws ValidationException
     */
    public function updateOooEntry(
        string $oooEntryGid,
        array $data,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($oooEntryGid, 'OOO Entry GID');

        return $this->updateResource('ooo_entries', $oooEntryGid, $data, $options, $responseType);
    }

    /**
     * Delete an OOO entry
     * DELETE /ooo_entries/{ooo_entry_gid}
     * Deletes an out-of-office entry. This action is permanent and cannot be undone.
     * Requires the `ooo_entries:delete` and `users:read` OAuth scopes.
     * API Documentation: https://developers.asana.com/reference/deleteoooentry
     *
     * @param string $oooEntryGid The unique global ID of the OOO entry.
     *                            Example: "12345"
     * @param int $responseType The type of response to return:
     * - HttpClientInterface::RESPONSE_FULL (1): Full response with status, headers, etc.
     * - HttpClientInterface::RESPONSE_NORMAL (2): Complete decoded JSON body
     * - HttpClientInterface::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type.
     *
     * If $responseType is HttpClientInterface::RESPONSE_DATA (default):
     * - Just the data object (empty JSON object {}) indicating successful deletion
     *
     * @throws ApiException
     * @throws RateLimitException
     * @throws ValidationException
     */
    public function deleteOooEntry(
        string $oooEntryGid,
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($oooEntryGid, 'OOO Entry GID');

        return $this->deleteResource('ooo_entries', $oooEntryGid, $responseType);
    }
}
