<?php

namespace BrightleafDigital\Api;

use BrightleafDigital\Exceptions\AsanaApiException;
use BrightleafDigital\Http\AsanaApiClient;
use BrightleafDigital\Utils\ValidationTrait;
use InvalidArgumentException;

class GoalsApiService
{
    use ValidationTrait;

    /**
     * An HTTP client instance configured to interact with the Asana API.
     * This property stores an instance of AsanaApiClient which handles all HTTP communication
     * with the Asana API endpoints. It provides authenticated access to API resources and
     * manages request/response handling.
     */
    private AsanaApiClient $client;

    /**
     * Constructor for initializing the service with an Asana API client.
     * Sets up the service instance using the provided Asana API client.
     * @param AsanaApiClient $client The Asana API client instance used to interact with the Asana API.
     * @return void
     */
    public function __construct(AsanaApiClient $client)
    {
        $this->client = $client;
    }

    /**
     * Get a goal
     * GET /goals/{goal_gid}
     * Returns the full record for a single goal.
     * API Documentation: https://developers.asana.com/reference/getgoal
     * @param string $goalGid The unique global ID of the goal to retrieve.
     *                        Example: "12345"
     * @param array $options Optional parameters to customize the request:
     * - opt_fields (string): A comma-separated list of fields to include in the response
     *   (e.g., "name,owner,workspace,due_on,start_on,status,liked,num_likes")
     * - opt_pretty (bool): Returns formatted JSON if true
     *
     * @param int $responseType The type of response to return:
     *
     * - AsanaApiClient::RESPONSE_FULL (1): Full response with status, headers, etc.
     * - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
     * - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type:
     *
     * If $responseType is AsanaApiClient::RESPONSE_FULL:
     * - status: HTTP status code
     * - reason: Response status message
     * - headers: Response headers
     * - body: Decoded response body containing goal data
     * - raw_body: Raw response body
     * - request: Original request details
     *
     * If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     * - Complete decoded JSON response including data object and other metadata
     *
     * If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     * - Just the data object containing the goal details including:
     *   - gid: Unique identifier of the goal
     * - resource_type: Always "goal"
     * - name: Name of the goal
     * - owner: Object containing the owner details
     * - workspace: Object containing the workspace details
     * - due_on: Due date
     * - start_on: Start date
     * - status: Status of the goal
     *                 Additional fields as specified in opt_fields
     *
     * @throws AsanaApiException If invalid goal GID provided, insufficient permissions,
     *                          network issues, or rate limiting occurs
     */
    public function getGoal(
        string $goalGid,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateGid($goalGid, 'Goal GID');

        return $this->client->request('GET', "goals/$goalGid", ['query' => $options], $responseType);
    }

    /**
     * Update a goal
     * PUT /goals/{goal_gid}
     * Updates the properties of a goal. Only the fields provided in the data block will be updated;
     * any unspecified fields will remain unchanged.
     * API Documentation: https://developers.asana.com/reference/updategoal
     * @param string $goalGid The unique global ID of the goal to update.
     *                        Example: "12345"
     * @param array $data The properties of the goal to update. Can include:
     * - name (string): Name of the goal
     * - due_on (string): Due date in YYYY-MM-DD format
     * - start_on (string): Start date in YYYY-MM-DD format
     * - owner (string): GID of the user who owns the goal
     * - status (string): Status of the goal
     * - liked (bool): Whether the goal is liked
     * - notes (string): Free-form textual notes about the goal
     *   Example: ["name" => "Updated Goal Name", "status" => "on_track"]
     * @param array $options Optional parameters to customize the request:
     * - opt_fields (string): A comma-separated list of fields to include in the response
     * - opt_pretty (bool): Returns formatted JSON if true
     *
     * @param int $responseType The type of response to return:
     *
     * - AsanaApiClient::RESPONSE_FULL (1): Full response with status, headers, etc.
     * - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
     * - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type:
     *
     * If $responseType is AsanaApiClient::RESPONSE_FULL:
     * - status: HTTP status code
     * - reason: Response status message
     * - headers: Response headers
     * - body: Decoded response body containing updated goal data
     * - raw_body: Raw response body
     * - request: Original request details
     *
     * If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     * - Complete decoded JSON response including data object and other metadata
     *
     * If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     * - Just the data object containing the updated goal details
     * @throws AsanaApiException If invalid goal GID provided, malformed data,
     *                          insufficient permissions, or network issues occur
     */
    public function updateGoal(
        string $goalGid,
        array $data,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateGid($goalGid, 'Goal GID');

        return $this->client->request(
            'PUT',
            "goals/$goalGid",
            ['json' => ['data' => $data], 'query' => $options],
            $responseType
        );
    }

    /**
     * Delete a goal
     * DELETE /goals/{goal_gid}
     * Deletes the specified goal. This action is permanent and cannot be undone.
     * API Documentation: https://developers.asana.com/reference/deletegoal
     * @param string $goalGid The unique global ID of the goal to delete.
     *                        Example: "12345"
     * @param int $responseType The type of response to return:
     *
     * - AsanaApiClient::RESPONSE_FULL (1): Full response with status, headers, etc.
     * - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
     * - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type:
     *
     * If $responseType is AsanaApiClient::RESPONSE_FULL:
     * - status: HTTP status code
     * - reason: Response status message
     * - headers: Response headers
     * - body: Decoded response body (empty data object)
     * - raw_body: Raw response body
     * - request: Original request details
     *
     * If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     * - Complete decoded JSON response including empty data object
     *
     * If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     * - Just the data object (empty JSON object {}) indicating successful deletion
     * @throws AsanaApiException If the API request fails due to:
     *
     * - Invalid goal GID
     * - Goal not found
     * - Insufficient permissions to delete the goal
     * - Network connectivity issues
     * - Rate limiting
     */
    public function deleteGoal(string $goalGid, int $responseType = AsanaApiClient::RESPONSE_DATA): array
    {
        $this->validateGid($goalGid, 'Goal GID');

        return $this->client->request('DELETE', "goals/$goalGid", [], $responseType);
    }

    /**
     * Get multiple goals
     * GET /goals
     * Returns compact goal records filtered by the given criteria. You must specify at least
     * one of portfolio, project, task, team, or workspace to filter goals.
     * API Documentation: https://developers.asana.com/reference/getgoals
     * @param array $options Optional parameters to customize the request:
     *
     * Filtering parameters:
     * - portfolio (string): GID of a portfolio to filter goals from
     * - project (string): GID of a project to filter goals from
     * - task (string): GID of a task to filter goals from
     * - team (string): GID of a team to filter goals from
     * - workspace (string): GID of a workspace to filter goals from
     * - time_periods (array): Array of time period GIDs to filter by
     * - is_workspace_level (bool): Filter to workspace-level goals
     * Pagination parameters:
     * - limit (int): Maximum number of goals to return. Default is 20, max is 100
     * - offset (string): Offset token for pagination
     *
     * Display parameters:
     * - opt_fields (string): A comma-separated list of fields to include in the response
     * - opt_pretty (bool): Returns formatted JSON if true
     *
     * @param int $responseType The type of response to return:
     *
     * - AsanaApiClient::RESPONSE_FULL (1): Full response with status, headers, etc.
     * - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
     * - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type:
     *
     * If $responseType is AsanaApiClient::RESPONSE_FULL:
     * - status: HTTP status code
     * - reason: Response status message
     * - headers: Response headers
     * - body: Decoded response body containing goal data
     * - raw_body: Raw response body
     * - request: Original request details
     *
     * If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     * - Complete decoded JSON response including data array and pagination info
     *
     * If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     * - Just the data array containing the list of goals with fields including:
     *   - gid: Unique identifier of the goal
     * - resource_type: Always "goal"
     * - name: Name of the goal
     *                 Additional fields as specified in opt_fields
     *
     * @throws AsanaApiException If insufficient permissions, network issues, or rate limiting occurs
     */
    public function getGoals(
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        return $this->client->request('GET', 'goals', ['query' => $options], $responseType);
    }

    /**
     * Create a goal
     * POST /goals
     * Creates a new goal in the specified workspace. Returns the full record of the
     * newly created goal.
     * API Documentation: https://developers.asana.com/reference/creategoal
     * @param array $data Data for creating the goal. Supported fields include:
     * - name (string): Name of the goal.
     *   Example: "Increase revenue by 20%"
     * - workspace (string): GID of the workspace to create the goal in.
     *   Example: "12345"
     * - due_on (string): Due date in YYYY-MM-DD format
     * - start_on (string): Start date in YYYY-MM-DD format
     * - owner (string): GID of the user who owns the goal
     * - team (string): GID of the team the goal belongs to
     * - time_period (string): GID of the time period for the goal
     * - liked (bool): Whether the goal is liked by the current user
     * - is_workspace_level (bool): Whether this is a workspace-level goal
     * - notes (string): Free-form textual notes about the goal
     *   Example: ["name" => "Increase revenue by 20%", "workspace" => "12345"]
     * @param array $options Optional parameters to customize the request:
     * - opt_fields (string): A comma-separated list of fields to include in the response
     * - opt_pretty (bool): Returns formatted JSON if true
     *
     * @param int $responseType The type of response to return:
     *
     * - AsanaApiClient::RESPONSE_FULL (1): Full response with status, headers, etc.
     * - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
     * - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type:
     *
     * If $responseType is AsanaApiClient::RESPONSE_FULL:
     * - status: HTTP status code
     * - reason: Response status message
     * - headers: Response headers
     * - body: Decoded response body containing created goal data
     * - raw_body: Raw response body
     * - request: Original request details
     *
     * If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     * - Complete decoded JSON response including data object and other metadata
     *
     * If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     * - Just the data object containing the created goal details
     * @throws AsanaApiException If the API request fails due to:
     *
     * - Missing required fields
     * - Invalid field values
     * - Insufficient permissions
     * - Network connectivity issues
     * - Rate limiting
     */
    public function createGoal(
        array $data,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        return $this->client->request(
            'POST',
            'goals',
            ['json' => ['data' => $data], 'query' => $options],
            $responseType
        );
    }

    /**
     * Create a goal metric
     * POST /goals/{goal_gid}/setMetric
     * Creates and sets a goal metric for a specified goal. This defines the metric
     * used to track progress on the goal, such as a numeric value or percentage.
     * API Documentation: https://developers.asana.com/reference/creategoalmetric
     * @param string $goalGid The unique global ID of the goal to set the metric on.
     *                        Example: "12345"
     * @param array $data Data for creating the goal metric. Supported fields include:
     * - metric_type (string): The type of metric. Options: "number", "percentage", "currency"
     * - initial_number_value (number): The starting value of the metric
     * - target_number_value (number): The target value of the metric
     * - currency_code (string): ISO 4217 currency code (required if metric_type is "currency")
     * - unit (string): A human-readable label for the metric unit
     * - precision (int): The number of decimal places to display
     *   Example: ["metric_type" => "number", "initial_number_value" => 0,
     *                              "target_number_value" => 100]
     * @param array $options Optional parameters to customize the request:
     * - opt_fields (string): A comma-separated list of fields to include in the response
     * - opt_pretty (bool): Returns formatted JSON if true
     *
     * @param int $responseType The type of response to return:
     *
     * - AsanaApiClient::RESPONSE_FULL (1): Full response with status, headers, etc.
     * - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
     * - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type:
     *
     * If $responseType is AsanaApiClient::RESPONSE_FULL:
     * - status: HTTP status code
     * - reason: Response status message
     * - headers: Response headers
     * - body: Decoded response body containing goal data with metric
     * - raw_body: Raw response body
     * - request: Original request details
     *
     * If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     * - Complete decoded JSON response including data object and other metadata
     *
     * If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     * - Just the data object containing the goal details with the created metric
     * @throws AsanaApiException If invalid goal GID provided, insufficient permissions,
     *                          or network issues occur
     */
    public function createGoalMetric(
        string $goalGid,
        array $data,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateGid($goalGid, 'Goal GID');

        return $this->client->request(
            'POST',
            "goals/$goalGid/setMetric",
            ['json' => ['data' => $data], 'query' => $options],
            $responseType
        );
    }

    /**
     * Update a goal metric
     * POST /goals/{goal_gid}/setMetricCurrentValue
     * Updates the current value of a goal metric. This is used to track progress
     * toward the target value of the metric.
     * API Documentation: https://developers.asana.com/reference/updategoalmetric
     * @param string $goalGid The unique global ID of the goal to update the metric for.
     *                        Example: "12345"
     * @param array $data Data for updating the goal metric. Supported fields include:
     * - current_number_value (number): The current value of the metric
     *   Example: 50
     *                    Example: ["current_number_value" => 50]
     * @param array $options Optional parameters to customize the request:
     * - opt_fields (string): A comma-separated list of fields to include in the response
     * - opt_pretty (bool): Returns formatted JSON if true
     *
     * @param int $responseType The type of response to return:
     *
     * - AsanaApiClient::RESPONSE_FULL (1): Full response with status, headers, etc.
     * - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
     * - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type:
     *
     * If $responseType is AsanaApiClient::RESPONSE_FULL:
     * - status: HTTP status code
     * - reason: Response status message
     * - headers: Response headers
     * - body: Decoded response body containing goal data with updated metric
     * - raw_body: Raw response body
     * - request: Original request details
     *
     * If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     * - Complete decoded JSON response including data object and other metadata
     *
     * If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     * - Just the data object containing the goal details with the updated metric
     * @throws AsanaApiException If invalid goal GID provided, insufficient permissions,
     *                          or network issues occur
     */
    public function updateGoalMetric(
        string $goalGid,
        array $data,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateGid($goalGid, 'Goal GID');

        return $this->client->request(
            'POST',
            "goals/$goalGid/setMetricCurrentValue",
            ['json' => ['data' => $data], 'query' => $options],
            $responseType
        );
    }

    /**
     * Add followers to a goal
     * POST /goals/{goal_gid}/addFollowers
     * Adds the specified list of users as followers of the goal. Followers of a goal
     * receive notifications about updates to the goal.
     * API Documentation: https://developers.asana.com/reference/addfollowersforgoal
     * @param string $goalGid The unique global ID of the goal to add followers to.
     *                        Example: "12345"
     * @param array $followers An array of user GIDs representing the followers to add to the goal.
     *                         Each element should be a string containing a user GID.
     *                         Example: ["67890", "11111", "22222"]
     * @param array $options Optional parameters to customize the request:
     * - opt_fields (string): A comma-separated list of fields to include in the response
     * - opt_pretty (bool): Returns formatted JSON if true
     *
     * @param int $responseType The type of response to return:
     *
     * - AsanaApiClient::RESPONSE_FULL (1): Full response with status, headers, etc.
     * - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
     * - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type:
     *
     * If $responseType is AsanaApiClient::RESPONSE_FULL:
     * - status: HTTP status code
     * - reason: Response status message
     * - headers: Response headers
     * - body: Decoded response body containing goal data
     * - raw_body: Raw response body
     * - request: Original request details
     *
     * If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     * - Complete decoded JSON response including data object and other metadata
     *
     * If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     * - Just the data object containing the updated goal details including
     *                 the newly added followers
     * @throws AsanaApiException If invalid goal GID provided, invalid user GIDs,
     *                          insufficient permissions, or network issues occur
     */
    public function addFollowers(
        string $goalGid,
        array $followers,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateGid($goalGid, 'Goal GID');

        return $this->client->request(
            'POST',
            "goals/$goalGid/addFollowers",
            ['json' => ['data' => ['followers' => $followers]], 'query' => $options],
            $responseType
        );
    }

    /**
     * Remove followers from a goal
     * POST /goals/{goal_gid}/removeFollowers
     * Removes the specified list of users from the followers of the goal.
     * API Documentation: https://developers.asana.com/reference/removefollowersforgoal
     * @param string $goalGid The unique global ID of the goal to remove followers from.
     *                        Example: "12345"
     * @param array $followers An array of user GIDs representing the followers to remove from the goal.
     *                         Each element should be a string containing a user GID.
     *                         Example: ["67890", "11111", "22222"]
     * @param array $options Optional parameters to customize the request:
     * - opt_fields (string): A comma-separated list of fields to include in the response
     * - opt_pretty (bool): Returns formatted JSON if true
     *
     * @param int $responseType The type of response to return:
     *
     * - AsanaApiClient::RESPONSE_FULL (1): Full response with status, headers, etc.
     * - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
     * - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type:
     *
     * If $responseType is AsanaApiClient::RESPONSE_FULL:
     * - status: HTTP status code
     * - reason: Response status message
     * - headers: Response headers
     * - body: Decoded response body containing goal data
     * - raw_body: Raw response body
     * - request: Original request details
     *
     * If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     * - Complete decoded JSON response including data object and other metadata
     *
     * If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     * - Just the data object containing the updated goal details with
     *                 the specified followers removed
     * @throws AsanaApiException If invalid goal GID provided, invalid user GIDs,
     *                          insufficient permissions, or network issues occur
     */
    public function removeFollowers(
        string $goalGid,
        array $followers,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateGid($goalGid, 'Goal GID');

        return $this->client->request(
            'POST',
            "goals/$goalGid/removeFollowers",
            ['json' => ['data' => ['followers' => $followers]], 'query' => $options],
            $responseType
        );
    }

    /**
     * Get parent goals for a goal
     * GET /goals/{goal_gid}/parentGoals
     * Returns the compact records for all parent goals of the given goal.
     * API Documentation: https://developers.asana.com/reference/getparentgoalsforgoal
     * @param string $goalGid The unique global ID of the goal.
     *                        Example: "12345"
     * @param array $options Optional parameters to customize the request:
     * - opt_fields (string): A comma-separated list of fields to include in the response
     *   (e.g., "name,owner,workspace")
     * - opt_pretty (bool): Returns formatted JSON if true
     *
     * @param int $responseType The type of response to return:
     *
     * - AsanaApiClient::RESPONSE_FULL (1): Full response with status, headers, etc.
     * - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
     * - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type:
     *
     * If $responseType is AsanaApiClient::RESPONSE_FULL:
     * - status: HTTP status code
     * - reason: Response status message
     * - headers: Response headers
     * - body: Decoded response body containing parent goals data
     * - raw_body: Raw response body
     * - request: Original request details
     *
     * If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     * - Complete decoded JSON response including data array and pagination info
     *
     * If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     * - Just the data array containing the list of parent goals
     * @throws AsanaApiException If invalid goal GID provided, insufficient permissions,
     *                          network issues, or rate limiting occurs
     */
    public function getParentGoalsForGoal(
        string $goalGid,
        array $options = [],
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateGid($goalGid, 'Goal GID');

        return $this->client->request(
            'GET',
            "goals/$goalGid/parentGoals",
            ['query' => $options],
            $responseType
        );
    }

    /**
     * Add a custom field setting to a goal
     * POST /goals/{goal_gid}/addCustomFieldSetting
     * Adds a custom field to the specified goal. Custom fields are defined per-workspace
     * and must exist before they can be added to a goal.
     * API Documentation: https://developers.asana.com/reference/addcustomfieldsettingforgoal
     * @param string $goalGid The unique global ID of the goal to add the custom field to.
     *                        Example: "12345"
     * @param array $data Data for adding the custom field setting. Supported fields include:
     * - custom_field (string): The GID of the custom field to add to the goal.
     *   Example: "67890"
     * - is_important (boolean): Whether this custom field is considered important for the goal.
     *   Important custom fields are displayed prominently.
     * - insert_before (string): GID of the custom field setting to insert this new setting before.
     * - insert_after (string): GID of the custom field setting to insert this new setting after.
     *   Example: ["custom_field" => "67890", "is_important" => true]
     * @param int $responseType The type of response to return:
     *
     * - AsanaApiClient::RESPONSE_FULL (1): Full response with status, headers, etc.
     * - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
     * - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type:
     *
     * If $responseType is AsanaApiClient::RESPONSE_FULL:
     * - status: HTTP status code
     * - reason: Response status message
     * - headers: Response headers
     * - body: Decoded response body containing custom field setting data
     * - raw_body: Raw response body
     * - request: Original request details
     *
     * If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     * - Complete decoded JSON response including data object and other metadata
     *
     * If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     * - Just the data object containing the custom field setting details including:
     *   - gid: Unique identifier of the custom field setting
     * - resource_type: Always "custom_field_setting"
     * - custom_field: Object containing custom field details
     * - is_important: Boolean indicating if the custom field is important
     * @throws AsanaApiException If invalid goal GID provided, invalid custom field GID,
     *                          insufficient permissions, or network issues occur
     */
    public function addCustomFieldSettingForGoal(
        string $goalGid,
        array $data,
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateGid($goalGid, 'Goal GID');

        return $this->client->request(
            'POST',
            "goals/$goalGid/addCustomFieldSetting",
            ['json' => ['data' => $data]],
            $responseType
        );
    }

    /**
     * Remove a custom field setting from a goal
     * POST /goals/{goal_gid}/removeCustomFieldSetting
     * Removes a custom field from the specified goal.
     * API Documentation: https://developers.asana.com/reference/removecustomfieldsettingforgoal
     * @param string $goalGid The unique global ID of the goal to remove the custom field from.
     *                        Example: "12345"
     * @param array $data Data for removing the custom field setting. Supported fields include:
     * - custom_field (string): The GID of the custom field to remove from the goal.
     *   Example: "67890"
     *                    Example: ["custom_field" => "67890"]
     * @param int $responseType The type of response to return:
     *
     * - AsanaApiClient::RESPONSE_FULL (1): Full response with status, headers, etc.
     * - AsanaApiClient::RESPONSE_NORMAL (2): Complete decoded JSON body
     * - AsanaApiClient::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type:
     *
     * If $responseType is AsanaApiClient::RESPONSE_FULL:
     * - status: HTTP status code
     * - reason: Response status message
     * - headers: Response headers
     * - body: Decoded response body (empty data object)
     * - raw_body: Raw response body
     * - request: Original request details
     *
     * If $responseType is AsanaApiClient::RESPONSE_NORMAL:
     * - Complete decoded JSON response including empty data object
     *
     * If $responseType is AsanaApiClient::RESPONSE_DATA (default):
     * - Just the data object (empty JSON object {}) indicating successful removal
     * @throws AsanaApiException If invalid goal GID provided, invalid custom field GID,
     *                          insufficient permissions, or network issues occur
     */
    public function removeCustomFieldSettingForGoal(
        string $goalGid,
        array $data,
        int $responseType = AsanaApiClient::RESPONSE_DATA
    ): array {
        $this->validateGid($goalGid, 'Goal GID');

        return $this->client->request(
            'POST',
            "goals/$goalGid/removeCustomFieldSetting",
            ['json' => ['data' => $data]],
            $responseType
        );
    }
}
