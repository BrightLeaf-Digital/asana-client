<?php

namespace BrightleafDigital\Api;

use BrightleafDigital\Exceptions\ApiException;
use BrightleafDigital\Exceptions\RateLimitException;
use BrightleafDigital\Exceptions\ValidationException;
use BrightleafDigital\Http\HttpClientInterface;

/**
 * Service class for Goal Relationships-related API operations.
 *
 * @link https://developers.asana.com/reference/goal-relationships
 */
class GoalRelationshipsApiService extends BaseApiService
{
    /**
     * Get a goal relationship
     * GET /goal_relationships/{goal_relationship_gid}
     * Returns the complete goal relationship record for a single goal relationship.
     * API Documentation: https://developers.asana.com/reference/getgoalrelationship
     *
     * @param string $goalRelationshipGid Globally unique identifier for the goal relationship.
     * @param array $options Optional query parameters:
     * - opt_fields (string): Comma-separated list of fields to include.
     * - opt_pretty (bool): Pretty JSON.
     * @param int $responseType The type of response to return.
     *
     * @return array The response data.
     * @throws ApiException
     * @throws RateLimitException
     * @throws ValidationException If the goal relationship GID is empty or invalid.
     */
    public function getGoalRelationship(
        string $goalRelationshipGid,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($goalRelationshipGid, 'Goal Relationship GID');
        return $this->getResource('goal_relationships', $goalRelationshipGid, $options, $responseType);
    }

    /**
     * Update a goal relationship
     * PUT /goal_relationships/{goal_relationship_gid}
     * Updates fields on an existing goal relationship. Only provided fields are changed.
     * API Documentation: https://developers.asana.com/reference/updategoalrelationship
     *
     * @param string $goalRelationshipGid Globally unique identifier for the goal relationship.
     * @param float $contributionWeight The weight that the supporting resource's progress contributes to the
     * supported goal's progress. This can be 0, 1, or any value in between.
     * @param array $options Optional query parameters:
     * - opt_fields (string): Comma-separated list of fields to include.
     * - opt_pretty (bool): Pretty JSON.
     * @param int $responseType The type of response to return.
     *
     * @return array The response data.
     * @throws ApiException If the API request fails due to network issues, insufficient permissions, or rate limits.
     * @throws RateLimitException If the Asana API rate limit is exceeded.
     * @throws ValidationException If the goal relationship GID is empty or invalid, or contribution weight is invalid.
     */
    public function updateGoalRelationship(
        string $goalRelationshipGid,
        float $contributionWeight,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($goalRelationshipGid, 'Goal Relationship GID');
        $this->validateLimit($contributionWeight, 0, 1);
        $data = ['contribution_weight' => $contributionWeight];
        return $this->updateResource('goal_relationships', $goalRelationshipGid, $data, $options, $responseType);
    }

    /**
     * Get goal relationships
     * GET /goal_relationships
     * Returns compact goal relationship records filtered by supported goal.
     * API Documentation: https://developers.asana.com/reference/getgoalrelationships
     *
     * @param string $supportedGoalGid Globally unique identifier for the supported goal in the relationship.
     * @param array $options Optional query parameters:
     * - resource_subtype (string): Filter to relationships with a given subtype.
     * - limit (int): Results per page (1-100).
     * - offset (string): Offset token for pagination.
     * - opt_fields (string): Comma-separated list of fields to include.
     * - opt_pretty (bool): Pretty JSON.
     * @param int $responseType The type of response to return.
     *
     * @return array The response data.
     * @throws ApiException
     * @throws RateLimitException
     * @throws ValidationException If the supported goal GID is empty or invalid.
     */
    public function getGoalRelationships(
        string $supportedGoalGid,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($supportedGoalGid, 'Supported Goal GID');
        $options['supported_goal'] = $supportedGoalGid;
        return $this->getResources('goal_relationships', $options, $responseType);
    }

    /**
     * Add a supporting goal relationship
     * POST /goals/{goal_gid}/addSupportingRelationship
     * Creates a goal relationship by adding a supporting resource to a given goal.
     * API Documentation: https://developers.asana.com/reference/addsupportingrelationship
     *
     * @param string $goalGid Globally unique identifier for the goal.
     * @param array $data The supporting resource to add. Can include:
     * - supporting_resource (string, required): The gid of the supporting resource.
     * Must be the gid of a goal, project, task, or portfolio.
     * - contribution_weight (number): Defines how much the supporting goal’s progress contributes to the parent goal’s
     * overall progress.
     * - insert_before (string): An id of a subgoal of this parent goal. The new subgoal will be added before the one
     * specified here.
     * - insert_after (string): An id of a subgoal of this parent goal. The new subgoal will be added after the one
     * specified here.
     * @param array $options Optional query parameters:
     * - opt_fields (string): Comma-separated list of fields to include.
     * - opt_pretty (bool): Pretty JSON.
     * @param int $responseType The type of response to return.
     *
     * @return array The response data.
     * @throws ApiException
     * @throws RateLimitException
     * @throws ValidationException If the goal GID is empty or invalid.
     */
    public function addSupportingRelationship(
        string $goalGid,
        array $data,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($goalGid, 'Goal GID');
        return $this->createResource(
            "goals/$goalGid/addSupportingRelationship",
            $data,
            $options,
            $responseType
        );
    }

    /**
     * Remove a supporting goal relationship
     * POST /goals/{goal_gid}/removeSupportingRelationship
     * Removes a supporting goal relationship.
     * API Documentation: https://developers.asana.com/reference/removesupportingrelationship
     *
     * @param string $goalGid Globally unique identifier for the goal.
     * @param string $supportingResource The gid of the supporting resource to remove. Must be the gid of a goal,
     * project, task, or portfolio.
     * @param array $options Optional query parameters:
     * - opt_fields (string): Comma-separated list of fields to include.
     * - opt_pretty (bool): Pretty JSON.
     * @param int $responseType The type of response to return.
     *
     * @return array The response data.
     * @throws ApiException
     * @throws RateLimitException
     * @throws ValidationException If the goal GID or supporting resource GID is empty or invalid.
     */
    public function removeSupportingRelationship(
        string $goalGid,
        string $supportingResource,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($goalGid, 'Goal GID');
        $this->validateGid($supportingResource, 'Supporting Resource GID');
        $data = ['supporting_resource' => $supportingResource];
        // It seems counter intuitive to use createResource here, but we need a POST with $data. Don't ask me why
        // Asana didn't just use a DELETE or PUT or something like that.
        return $this->createResource(
            "goals/$goalGid/removeSupportingRelationship",
            $data,
            $options,
            $responseType
        );
    }
}
