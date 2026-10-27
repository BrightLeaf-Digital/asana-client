<?php

namespace BrightleafDigital\Api;

use BrightleafDigital\Exceptions\ApiException;
use BrightleafDigital\Exceptions\RateLimitException;
use BrightleafDigital\Exceptions\ValidationException;
use BrightleafDigital\Http\HttpClientInterface;

/**
 * Service class for Records-related API operations.
 *
 * A record is a row in a table. Each record belongs to exactly one table, and its values are
 * stored in the custom fields enabled on that table. Comments on records are managed through
 * {@see StoriesApiService::getStoriesForRecord()} and {@see StoriesApiService::createStoryForRecord()}.
 *
 * The API does not currently expose record permissions (privacy or record members).
 *
 * @link https://developers.asana.com/reference/records
 */
class RecordsApiService extends BaseApiService
{
    /**
     * Get records in a table
     * GET /tables/{table_gid}/records
     * Returns the compact records for all records in the given table.
     * Requires the `records:read` OAuth scope.
     * API Documentation: https://developers.asana.com/reference/getrecordsfortable
     *
     * @param string $tableGid The unique global ID of the table.
     *                         Example: "12345"
     * @param array $options Optional parameters to customize the request:
     *
     * Filtering parameters:
     * - modified_since (string): Only return records modified since this ISO 8601 date-time
     *   (e.g., "2026-10-01T00:00:00.000Z")
     * - sort_by (string): Sort order. Only "created_at" is supported; omit for default order
     *
     * Pagination parameters:
     * - limit (int): Results per page (1-100)
     * - offset (string): Offset token for pagination
     *
     * Display parameters:
     * - opt_fields (string): Comma-separated fields to include in the response
     *   (e.g., "name,custom_fields,created_at,modified_at")
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
     * - Just the data array containing the list of records with fields including:
     *   - gid: Unique identifier of the record
     *   - resource_type: Always "record"
     *   - name: Name of the record
     *
     * @throws ApiException
     * @throws RateLimitException
     * @throws ValidationException
     */
    public function getRecordsForTable(
        string $tableGid,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($tableGid, 'Table GID');

        return $this->getResources("tables/$tableGid/records", $options, $responseType);
    }

    /**
     * Create a record
     * POST /records
     * Creates a new record in a table. Returns the full record of the newly created record.
     * Requires the `records:write` OAuth scope.
     * API Documentation: https://developers.asana.com/reference/createrecord
     *
     * @param array $data Data for creating the record. Supported fields include:
     *                    Required:
     * - table (string): GID of the table to create the record in.
     *   Example: "12345"
     *                    Optional:
     * - name (string): Name of the record.
     *   Example: "Acme Corp"
     * - custom_fields (array): Map of custom field GID to value. The value is an enum option
     *   GID, string, number or object depending on the custom field type.
     *   Example: ["67890" => "Enterprise"]
     * - unique_fingerprint (string): Client-provided identifier used to deduplicate record
     *   creation within a table. A duplicate fingerprint fails with a 409 Conflict instead of
     *   creating a second record.
     *   Example: "sfdc-opportunity-0061"
     *   Example: ["table" => "12345", "name" => "Acme Corp", "custom_fields" => ["67890" => "Enterprise"]]
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
     * If $responseType is HttpClientInterface::RESPONSE_DATA (default):
     * - Just the data object containing the created record with fields including:
     *   - gid: Unique identifier of the record
     *   - resource_type: Always "record"
     *   - name: Name of the record
     *   - table: Object containing the table details
     *   - workspace: Object containing the workspace details
     *   - custom_fields: Array of custom field values on the record
     *   - created_at: Creation timestamp
     *   - modified_at: Last modification timestamp
     *   - created_by: Object containing the creator details
     *   - permalink_url: URL to the record in the Asana web app
     *   - comment_count: Number of comments on the record
     *   - is_in_archived_table: Whether the record's table is archived
     *
     * @throws ApiException
     * @throws RateLimitException
     * @throws ValidationException If the table field is missing
     */
    public function createRecord(
        array $data,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateRequiredFields($data, ['table'], 'record creation');

        return $this->createResource('records', $data, $options, $responseType);
    }

    /**
     * Get a record
     * GET /records/{record_gid}
     * Returns the complete record for a single record.
     * Requires the `records:read` OAuth scope.
     * API Documentation: https://developers.asana.com/reference/getrecord
     *
     * @param string $recordGid The unique global ID of the record.
     *                          Example: "12345"
     * @param array $options Optional parameters to customize the request:
     * - opt_fields (string): Comma-separated fields to include in the response
     *   (e.g., "name,table,custom_fields,permalink_url")
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
    public function getRecord(
        string $recordGid,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($recordGid, 'Record GID');

        return $this->getResource('records', $recordGid, $options, $responseType);
    }

    /**
     * Update a record
     * PUT /records/{record_gid}
     * Updates the properties of a record. Only the fields provided will be updated; any
     * unspecified fields will remain unchanged. Returns the complete updated record.
     * Requires the `records:write` OAuth scope.
     * API Documentation: https://developers.asana.com/reference/updaterecord
     *
     * @param string $recordGid The unique global ID of the record.
     *                          Example: "12345"
     * @param array $data Properties to update. Can include:
     * - name (string): New name of the record.
     * - custom_fields (array): Map of custom field GID to new value.
     *   Example: ["name" => "Acme Inc", "custom_fields" => ["67890" => "Mid-market"]]
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
    public function updateRecord(
        string $recordGid,
        array $data,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($recordGid, 'Record GID');

        return $this->updateResource('records', $recordGid, $data, $options, $responseType);
    }

    /**
     * Delete a record
     * DELETE /records/{record_gid}
     * Deletes a record. Returns an empty data record.
     * Requires the `records:delete` OAuth scope.
     * API Documentation: https://developers.asana.com/reference/deleterecord
     *
     * @param string $recordGid The unique global ID of the record.
     *                          Example: "12345"
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
    public function deleteRecord(
        string $recordGid,
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($recordGid, 'Record GID');

        return $this->deleteResource('records', $recordGid, $responseType);
    }

    /**
     * Search records in a workspace
     * GET /workspaces/{workspace_gid}/records/search
     * Mirrors the Asana web app's advanced search to find records across all of a workspace's
     * tables. Custom field filters use the same `custom_fields.{gid}.*` query parameters as task
     * search. Only available to premium Asana users; other users receive a 402 Payment Required.
     * Requires the `records:read` OAuth scope.
     * API Documentation: https://developers.asana.com/reference/searchrecordsforworkspace
     *
     * @param string $workspaceGid The unique global ID of the workspace or organization.
     *                             Example: "12345"
     * @param array $options Optional search parameters:
     * - text (string): Full-text search on the record name
     * - tables.any / tables.not / tables.all (string): Comma-separated table GIDs
     * - teams.any (string): Comma-separated team GIDs
     * - followers.any / followers.not (string): Comma-separated user identifiers ("me",
     *   an email, or a user GID)
     * - created_on, created_on.before, created_on.after (string): ISO 8601 dates
     * - created_at.before, created_at.after (string): ISO 8601 date-times
     * - custom_fields.{gid}.* : Custom field filters, as for task search
     * - sort_by (string): "created_at" or "modified_at" (default "modified_at")
     * - sort_ascending (bool): Default false
     * - limit (int): Results per page (1-100)
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
     * If $responseType is HttpClientInterface::RESPONSE_DATA (default):
     * - Just the data array containing the matching records with fields including:
     *   - gid: Unique identifier of the record
     *   - resource_type: Always "record"
     *   - name: Name of the record
     *
     * @throws ApiException
     * @throws RateLimitException
     * @throws ValidationException
     */
    public function searchRecords(
        string $workspaceGid,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($workspaceGid, 'Workspace GID');

        return $this->getResources("workspaces/$workspaceGid/records/search", $options, $responseType);
    }
}
