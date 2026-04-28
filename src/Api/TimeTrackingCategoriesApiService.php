<?php

namespace BrightleafDigital\Api;

use BrightleafDigital\Exceptions\ApiException;
use BrightleafDigital\Exceptions\RateLimitException;
use BrightleafDigital\Exceptions\ValidationException;
use BrightleafDigital\Http\HttpClientInterface;

class TimeTrackingCategoriesApiService extends BaseApiService
{
    /**
     * Get multiple time tracking categories
     * GET /time_tracking_categories
     * Returns a list of time tracking categories for the given workspace.
     * Requires the Timesheets & Budgets Add-on (TBAO) and the `time_tracking_categories:read` OAuth scope.
     * API Documentation: https://developers.asana.com/reference/gettimetrackingcategories
     *
     * @param string $workspaceGid The unique global ID of the workspace.
     *                             Example: "12345"
     * @param array $options Optional parameters to customize the request:
     *
     * Pagination parameters:
     * - limit (int): Maximum number of categories to return. Default is 20, max is 100
     * - offset (string): Offset token for pagination
     *
     * Display parameters:
     * - opt_fields (string): Comma-separated fields to include in the response
     *   (e.g., "name,color,is_archived")
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
     * - Just the data array containing the list of categories with fields including:
     *   - gid: Unique identifier of the category
     *   - resource_type: Always "time_tracking_category"
     *   - name: Name of the category (e.g., "Development", "Meetings")
     *   - color: Display color for the category
     *   - is_archived: Boolean; archived categories cannot be assigned to new entries
     *
     * @throws ApiException
     * @throws RateLimitException
     * @throws ValidationException
     */
    public function getTimeTrackingCategories(
        string $workspaceGid,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($workspaceGid, 'Workspace GID');

        $options['workspace'] = $workspaceGid;

        return $this->getResources('time_tracking_categories', $options, $responseType);
    }

    /**
     * Get a time tracking category
     * GET /time_tracking_categories/{time_tracking_category_gid}
     * Returns the full record for a single time tracking category.
     * Requires the Timesheets & Budgets Add-on (TBAO) and the `time_tracking_categories:read` OAuth scope.
     * API Documentation: https://developers.asana.com/reference/gettimetrackingcategory
     *
     * @param string $timeTrackingCategoryGid The unique global ID of the time tracking category.
     *                                         Example: "12345"
     * @param array $options Optional parameters to customize the request:
     * - opt_fields (string): Comma-separated fields to include in the response
     *   (e.g., "name,color,is_archived")
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
    public function getTimeTrackingCategory(
        string $timeTrackingCategoryGid,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($timeTrackingCategoryGid, 'Time Tracking Category GID');

        return $this->getResource('time_tracking_categories', $timeTrackingCategoryGid, $options, $responseType);
    }

    /**
     * Create a time tracking category
     * POST /time_tracking_categories
     * Creates a new time tracking category in the specified workspace. Admin permissions required.
     * Maximum of 500 active categories per workspace. Category names must be unique within a workspace.
     * Requires the Timesheets & Budgets Add-on (TBAO) and the `time_tracking_categories:write` OAuth scope.
     * API Documentation: https://developers.asana.com/reference/createtimetrackingcategory
     *
     * @param array $data Data for creating the category. Supported fields include:
     *                    Required:
     * - workspace (string): GID of the workspace to create the category in.
     *   Example: "12345"
     * - name (string): Name of the category. Must be unique within the workspace.
     *   Example: "Development"
     *                    Optional:
     * - color (string): Display color for the category.
     *   Example: ["workspace" => "12345", "name" => "Development", "color" => "green"]
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
    public function createTimeTrackingCategory(
        array $data,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateRequiredFields($data, ['workspace', 'name'], 'time tracking category creation');

        return $this->createResource('time_tracking_categories', $data, $options, $responseType);
    }

    /**
     * Update a time tracking category
     * PUT /time_tracking_categories/{time_tracking_category_gid}
     * Updates the properties of a time tracking category. Admin permissions required.
     * Requires the Timesheets & Budgets Add-on (TBAO) and the `time_tracking_categories:write` OAuth scope.
     * API Documentation: https://developers.asana.com/reference/updatetimetrackingcategory
     *
     * @param string $timeTrackingCategoryGid The unique global ID of the time tracking category.
     *                                         Example: "12345"
     * @param array $data Properties to update. Can include:
     * - name (string): New name for the category. Must be unique within the workspace.
     * - color (string): New display color for the category.
     * - is_archived (bool): Set to true to archive the category. Archived categories
     *   cannot be assigned to new time entries.
     *   Example: ["name" => "Backend Development", "color" => "blue"]
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
    public function updateTimeTrackingCategory(
        string $timeTrackingCategoryGid,
        array $data,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($timeTrackingCategoryGid, 'Time Tracking Category GID');

        return $this->updateResource(
            'time_tracking_categories',
            $timeTrackingCategoryGid,
            $data,
            $options,
            $responseType
        );
    }

    /**
     * Delete a time tracking category
     * DELETE /time_tracking_categories/{time_tracking_category_gid}
     * Deletes a time tracking category. Admin permissions required.
     * Requires the Timesheets & Budgets Add-on (TBAO) and the `time_tracking_categories:delete` OAuth scope.
     * API Documentation: https://developers.asana.com/reference/deletetimetrackingcategory
     *
     * @param string $timeTrackingCategoryGid The unique global ID of the time tracking category.
     *                                         Example: "12345"
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
    public function deleteTimeTrackingCategory(
        string $timeTrackingCategoryGid,
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($timeTrackingCategoryGid, 'Time Tracking Category GID');

        return $this->deleteResource('time_tracking_categories', $timeTrackingCategoryGid, $responseType);
    }

    /**
     * Get time tracking entries for a category
     * GET /time_tracking_categories/{time_tracking_category_gid}/time_tracking_entries
     * Returns a list of time tracking entries that have this category assigned.
     * Requires the Timesheets & Budgets Add-on (TBAO) and the `time_tracking_categories:read` OAuth scope.
     * API Documentation: https://developers.asana.com/reference/gettimetrackingentriesfortimetrackingcategory
     *
     * @param string $timeTrackingCategoryGid The unique global ID of the time tracking category.
     *                                         Example: "12345"
     * @param array $options Optional parameters to customize the request:
     *
     * Pagination parameters:
     * - limit (int): Maximum number of entries to return. Default is 20, max is 100
     * - offset (string): Offset token for pagination
     *
     * Display parameters:
     * - opt_fields (string): Comma-separated fields to include in the response
     *   (e.g., "created_by,duration_minutes,entered_on,task")
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
    public function getTimeTrackingEntriesForCategory(
        string $timeTrackingCategoryGid,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($timeTrackingCategoryGid, 'Time Tracking Category GID');

        return $this->client->request(
            'GET',
            "time_tracking_categories/$timeTrackingCategoryGid/time_tracking_entries",
            ['query' => $options],
            $responseType
        );
    }
}
