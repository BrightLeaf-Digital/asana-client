<?php

namespace BrightleafDigital\Api;

use BrightleafDigital\Exceptions\ApiException;
use BrightleafDigital\Exceptions\RateLimitException;
use BrightleafDigital\Exceptions\ValidationException;
use BrightleafDigital\Http\HttpClientInterface;

/**
 * Service class for Tables-related API operations.
 *
 * A table is a container for structured records (rows), with columns defined by custom field
 * settings. Unlike projects and portfolios, tables only accept custom fields that are global
 * (published) to the workspace, and a table can hold at most 150 custom fields.
 *
 * Records inside a table are managed through {@see RecordsApiService}.
 *
 * @link https://developers.asana.com/reference/tables
 */
class TablesApiService extends BaseApiService
{
    /**
     * Get tables in a workspace
     * GET /workspaces/{workspace_gid}/tables
     * Returns the compact table records for all tables in the given workspace.
     * Requires the `tables:read` OAuth scope.
     * API Documentation: https://developers.asana.com/reference/gettablesforworkspace
     *
     * @param string $workspaceGid The unique global ID of the workspace or organization.
     *                             Example: "12345"
     * @param array $options Optional parameters to customize the request:
     * - limit (int): Results per page (1-100).
     * - offset (string): Offset token for pagination.
     * - opt_fields (string): Comma-separated fields to include in the response
     *   (e.g., "name,created_at,modified_at,workspace,custom_field_settings")
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
     * - Just the data array containing the list of tables with fields including:
     *   - gid: Unique identifier of the table
     *   - resource_type: Always "table"
     *   - name: Name of the table
     *
     * @throws ApiException
     * @throws RateLimitException
     * @throws ValidationException
     */
    public function getTablesForWorkspace(
        string $workspaceGid,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($workspaceGid, 'Workspace GID');

        return $this->getResources("workspaces/$workspaceGid/tables", $options, $responseType);
    }

    /**
     * Create a table
     * POST /tables
     * Creates a new table in a workspace. Returns the full record of the newly created table.
     * Requires the `tables:write` OAuth scope.
     * API Documentation: https://developers.asana.com/reference/createtable
     *
     * @param array $data Data for creating the table. Supported fields include:
     *                    Required:
     * - name (string): Name of the table.
     *   Example: "Customers"
     * - workspace (string): GID of the workspace or organization to create the table in.
     *   Tables cannot be moved to a different workspace once created.
     *   Example: "12345"
     *   Example: ["name" => "Customers", "workspace" => "12345"]
     *
     * Add columns after creation with addCustomFieldSettingForTable().
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
     * - Just the data object containing the created table with fields including:
     *   - gid: Unique identifier of the table
     *   - resource_type: Always "table"
     *   - name: Name of the table
     *   - created_at: Creation timestamp
     *   - modified_at: Last modification timestamp
     *   - workspace: Object containing the workspace details
     *   - custom_field_settings: Custom field settings enabled on the table
     *
     * @throws ApiException
     * @throws RateLimitException
     * @throws ValidationException If name or workspace is missing
     */
    public function createTable(
        array $data,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateRequiredFields($data, ['name', 'workspace'], 'table creation');

        return $this->createResource('tables', $data, $options, $responseType);
    }

    /**
     * Get a table
     * GET /tables/{table_gid}
     * Returns the complete table record for a single table.
     * Requires the `tables:read` OAuth scope.
     * API Documentation: https://developers.asana.com/reference/gettable
     *
     * @param string $tableGid The unique global ID of the table.
     *                         Example: "12345"
     * @param array $options Optional parameters to customize the request:
     * - opt_fields (string): Comma-separated fields to include in the response
     *   (e.g., "name,workspace,custom_field_settings.custom_field.name")
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
    public function getTable(
        string $tableGid,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($tableGid, 'Table GID');

        return $this->getResource('tables', $tableGid, $options, $responseType);
    }

    /**
     * Update a table
     * PUT /tables/{table_gid}
     * Updates the properties of a table. Only the fields provided will be updated; any
     * unspecified fields will remain unchanged. Returns the complete updated table record.
     * Requires the `tables:write` OAuth scope.
     * API Documentation: https://developers.asana.com/reference/updatetable
     *
     * @param string $tableGid The unique global ID of the table.
     *                         Example: "12345"
     * @param array $data Properties to update. Can include:
     * - name (string): New name of the table.
     *   Example: ["name" => "Accounts"]
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
    public function updateTable(
        string $tableGid,
        array $data,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($tableGid, 'Table GID');

        return $this->updateResource('tables', $tableGid, $data, $options, $responseType);
    }

    /**
     * Delete a table
     * DELETE /tables/{table_gid}
     * Deletes a table. Returns an empty data record.
     * Requires the `tables:delete` OAuth scope.
     * API Documentation: https://developers.asana.com/reference/deletetable
     *
     * @param string $tableGid The unique global ID of the table.
     *                         Example: "12345"
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
    public function deleteTable(
        string $tableGid,
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($tableGid, 'Table GID');

        return $this->deleteResource('tables', $tableGid, $responseType);
    }

    /**
     * Add a custom field to a table
     * POST /tables/{table_gid}/addCustomFieldSetting
     * Creates a custom field setting for the table. Tables only support custom fields that are
     * global (published) to the workspace; adding a non-global field fails with a 403. A table
     * can hold at most 150 custom fields.
     * Requires the `tables:write` OAuth scope.
     * API Documentation: https://developers.asana.com/reference/addcustomfieldsettingfortable
     *
     * @param string $tableGid The unique global ID of the table.
     *                         Example: "12345"
     * @param array $data Data for adding the custom field setting. Supported fields include:
     * - custom_field (string|array): GID of a global custom field, or an inline custom field
     *   definition. Required.
     *   Example: "67890"
     * - is_important (bool): Whether the field should be displayed prominently on the table.
     * - insert_before (string): GID of a custom field setting to insert the new setting before.
     * - insert_after (string): GID of a custom field setting to insert the new setting after.
     *   Example: ["custom_field" => "67890", "is_important" => true]
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
     * - Just the data object containing the custom field setting details including:
     *   - gid: Unique identifier of the custom field setting
     *   - resource_type: Always "custom_field_setting"
     *   - custom_field: Object containing custom field details
     *   - is_important: Boolean indicating if the custom field is important
     *
     * @throws ApiException
     * @throws RateLimitException
     * @throws ValidationException If the table GID is invalid or custom_field is missing
     */
    public function addCustomFieldSettingForTable(
        string $tableGid,
        array $data,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($tableGid, 'Table GID');
        $this->validateRequiredFields($data, ['custom_field'], 'adding a custom field to a table');

        return $this->createResource("tables/$tableGid/addCustomFieldSetting", $data, $options, $responseType);
    }

    /**
     * Remove a custom field from a table
     * POST /tables/{table_gid}/removeCustomFieldSetting
     * Removes a custom field setting from a table.
     * Requires the `tables:write` OAuth scope.
     * API Documentation: https://developers.asana.com/reference/removecustomfieldsettingfortable
     *
     * @param string $tableGid The unique global ID of the table.
     *                         Example: "12345"
     * @param array $data Data for removing the custom field setting. Supported fields include:
     * - custom_field (string): GID of the custom field to remove from the table. Required.
     *   Example: ["custom_field" => "67890"]
     * @param int $responseType The type of response to return:
     * - HttpClientInterface::RESPONSE_FULL (1): Full response with status, headers, etc.
     * - HttpClientInterface::RESPONSE_NORMAL (2): Complete decoded JSON body
     * - HttpClientInterface::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type.
     *
     * If $responseType is HttpClientInterface::RESPONSE_DATA (default):
     * - Just the data object (empty JSON object {}) indicating successful removal
     *
     * @throws ApiException
     * @throws RateLimitException
     * @throws ValidationException If the table GID is invalid or custom_field is missing
     */
    public function removeCustomFieldSettingForTable(
        string $tableGid,
        array $data,
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($tableGid, 'Table GID');
        $this->validateRequiredFields($data, ['custom_field'], 'removing a custom field from a table');

        return $this->client->request(
            'POST',
            "tables/$tableGid/removeCustomFieldSetting",
            ['json' => ['data' => $data]],
            $responseType
        );
    }
}
