<?php

namespace BrightleafDigital\Api;

use BrightleafDigital\Exceptions\ApiException;
use BrightleafDigital\Exceptions\RateLimitException;
use BrightleafDigital\Exceptions\ValidationException;
use BrightleafDigital\Http\HttpClientInterface;

class TimesheetApprovalStatusesApiService extends BaseApiService
{
    /**
     * Get multiple timesheet approval statuses
     * GET /timesheet_approval_statuses
     * Returns a list of timesheet approval status records filtered by the given criteria.
     * Requires the Timesheets & Budgeting add-on and the `timesheet_approval_statuses:read` OAuth scope.
     * API Documentation: https://developers.asana.com/reference/gettimesheetapprovalstatuses
     *
     * @param array $options Optional parameters to customize the request:
     *
     * Filtering parameters:
     * - workspace (string): GID of the workspace to filter by (required)
     * - user (string): GID of the user to filter timesheets by
     * - start_date (string): Start of date range in YYYY-MM-DD format
     * - end_date (string): End of date range in YYYY-MM-DD format
     * - approval_statuses (string): Comma-separated list of statuses to filter by.
     *   Allowed values: "DRAFT", "SUBMITTED", "APPROVED", "REJECTED"
     *
     * Pagination parameters:
     * - limit (int): Maximum number of results to return. Default is 20, max is 100
     * - offset (string): Offset token for pagination
     *
     * Display parameters:
     * - opt_fields (string): Comma-separated fields to include in the response
     *   (e.g., "user,workspace,approval_status,start_on,end_on,created_at")
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
     * - Just the data array containing timesheet approval status records with fields:
     *   - gid: Unique identifier
     *   - resource_type: Always "timesheet_approval_status"
     *   - user: Object containing the user details
     *   - workspace: Object containing the workspace details
     *   - approval_status: One of "DRAFT", "SUBMITTED", "APPROVED", "REJECTED"
     *   - start_on: Start date of the week (Monday, YYYY-MM-DD)
     *   - end_on: End date of the week (Sunday, YYYY-MM-DD)
     *   - created_at: Creation timestamp
     *
     * @throws ApiException
     * @throws RateLimitException
     */
    public function getTimesheetApprovalStatuses(
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        return $this->getResources('timesheet_approval_statuses', $options, $responseType);
    }

    /**
     * Get a timesheet approval status
     * GET /timesheet_approval_statuses/{timesheet_approval_status_gid}
     * Returns the full record for a single timesheet approval status.
     * Requires the Timesheets & Budgeting add-on and the `timesheet_approval_statuses:read` OAuth scope.
     * API Documentation: https://developers.asana.com/reference/gettimesheetapprovalstatus
     *
     * @param string $timesheetApprovalStatusGid The unique global ID of the timesheet approval status.
     *                                            Example: "12345"
     * @param array $options Optional parameters to customize the request:
     * - opt_fields (string): Comma-separated fields to include in the response
     *   (e.g., "user,workspace,approval_status,start_on,end_on,created_at")
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
    public function getTimesheetApprovalStatus(
        string $timesheetApprovalStatusGid,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($timesheetApprovalStatusGid, 'Timesheet Approval Status GID');

        return $this->getResource('timesheet_approval_statuses', $timesheetApprovalStatusGid, $options, $responseType);
    }

    /**
     * Get or create a timesheet approval status
     * POST /timesheet_approval_statuses
     * Gets or creates the timesheet approval status for the specified user and week.
     * The week is identified by any date within it — the API always resolves to Monday–Sunday UTC.
     * New timesheets are created with an initial status of "DRAFT".
     * Requires the Timesheets & Budgeting add-on and the `timesheet_approval_statuses:write` OAuth scope.
     * API Documentation: https://developers.asana.com/reference/createtimesheetapprovalstatus
     *
     * @param array $data Data for the get-or-create operation. Supported fields include:
     *                    Required:
     * - workspace (string): GID of the workspace.
     *   Example: "12345"
     * - user (string): GID of the user (or "me" for the current user).
     *   Example: "67890"
     * - date (string): Any date within the target week in YYYY-MM-DD format.
     *   Example: "2026-04-28"
     *   Example: ["workspace" => "12345", "user" => "67890", "date" => "2026-04-28"]
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
    public function getOrCreateTimesheetApprovalStatus(
        array $data,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateRequiredFields($data, ['workspace', 'user', 'date'], 'timesheet approval status get-or-create');

        return $this->createResource('timesheet_approval_statuses', $data, $options, $responseType);
    }

    /**
     * Update a timesheet approval status
     * PUT /timesheet_approval_statuses/{timesheet_approval_status_gid}
     * Updates the approval status of a timesheet. Valid state transitions are:
     * DRAFT → SUBMITTED, SUBMITTED → APPROVED, SUBMITTED → REJECTED, REJECTED → DRAFT.
     * An optional message can be recorded with the transition.
     * Requires the Timesheets & Budgeting add-on and the `timesheet_approval_statuses:write` OAuth scope.
     * API Documentation: https://developers.asana.com/reference/updatetimesheetapprovalstatus
     *
     * @param string $timesheetApprovalStatusGid The unique global ID of the timesheet approval status.
     *                                            Example: "12345"
     * @param array $data Properties to update. Can include:
     * - approval_status (string): The new approval status.
     *   Allowed values: "DRAFT", "SUBMITTED", "APPROVED", "REJECTED"
     * - message (string): Optional message to record with the status change.
     *   Example: ["approval_status" => "SUBMITTED"]
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
    public function updateTimesheetApprovalStatus(
        string $timesheetApprovalStatusGid,
        array $data,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($timesheetApprovalStatusGid, 'Timesheet Approval Status GID');

        return $this->updateResource(
            'timesheet_approval_statuses',
            $timesheetApprovalStatusGid,
            $data,
            $options,
            $responseType
        );
    }
}
