<?php

namespace BrightleafDigital\Api;

use BrightleafDigital\Exceptions\ApiException;
use BrightleafDigital\Exceptions\RateLimitException;
use BrightleafDigital\Exceptions\ValidationException;
use BrightleafDigital\Http\HttpClientInterface;

/**
 * Service class for Budgets-related API operations.
 *
 * @link https://developers.asana.com/reference/budgets
 */
class BudgetsApiService extends BaseApiService
{
    /**
     * Get multiple budgets.
     * GET /budgets
     * Returns a list of budgets in a project.
     * API Documentation: https://developers.asana.com/reference/getbudgets
     *
     * @param string $parent Globally unique identifier for the budget's parent object.
     *                       This currently can only be a `project`.
     * @param array $options Optional parameters to customize the request:
     * - opt_fields (string): A comma-separated list of fields to include in the response.
     * - opt_pretty (bool): Returns formatted JSON if true.
     * @param int $responseType The type of response to return.
     *
     * @return array The response data.
     * @throws ApiException If the API request fails due to network issues, insufficient permissions, or rate limits.
     * @throws RateLimitException If the Asana API rate limit is exceeded.
     * @throws ValidationException If the parent GID is empty or invalid.
     */
    public function getBudgets(
        string $parent,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($parent, 'Parent GID');
        $options['parent'] = $parent;
        return $this->getResources('budgets', $options, $responseType);
    }

    /**
     * Create a budget.
     * POST /budgets
     * Creates a new budget.
     * API Documentation: https://developers.asana.com/reference/createbudget
     *
     * @param array $data The data for creating the budget. Can include:
     * - parent (string, required): Globally unique identifier for the project the budget is on.
     * - resource_subtype (string): The subtype of this resource. Must be one of `actual_actual`,
     *      `actual_estimate`, or `estimate_estimate`.
     * @param array $options Query parameters for formatting results.
     * @param int $responseType The type of response to return.
     *
     * @return array The response data.
     * @throws ApiException If the API request fails due to network issues, insufficient permissions, or rate limits.
     * @throws RateLimitException If the Asana API rate limit is exceeded.
     */
    public function createBudget(
        array $data,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        return $this->createResource('budgets', $data, $options, $responseType);
    }

    /**
     * Get a budget.
     * GET /budgets/{budget_gid}
     * Returns the complete budget record for a single budget.
     * API Documentation: https://developers.asana.com/reference/getbudget
     *
     * @param string $budgetGid Globally unique identifier for the budget.
     * @param array $options Query parameters for formatting results.
     * @param int $responseType The type of response to return.
     *
     * @return array The response data.
     * @throws ApiException If the API request fails due to network issues, insufficient permissions, or rate limits.
     * @throws RateLimitException If the Asana API rate limit is exceeded.
     * @throws ValidationException If the budget GID is empty or invalid.
     */
    public function getBudget(
        string $budgetGid,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($budgetGid, 'Budget GID');
        return $this->getResource('budgets', $budgetGid, $options, $responseType);
    }

    /**
     * Update a budget.
     * PUT /budgets/{budget_gid}
     * Updates an existing budget.
     * API Documentation: https://developers.asana.com/reference/updatebudget
     *
     * @param string $budgetGid Globally unique identifier for the budget.
     * @param array $data The properties of the budget to update. Can include:
     * - estimate (array): Defines how the estimate portion of a budget is configured.
     * - actual (array): Defines how the actual portion of a budget is configured.
     * - total (array): Defines how the total portion of a budget is configured.
     * @param array $options Query parameters for formatting results.
     * @param int $responseType The type of response to return.
     *
     * @return array The response data.
     * @throws ApiException If the API request fails due to network issues, insufficient permissions, or rate limits.
     * @throws RateLimitException If the Asana API rate limit is exceeded.
     * @throws ValidationException If the budget GID is empty or invalid.
     */
    public function updateBudget(
        string $budgetGid,
        array $data,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($budgetGid, 'Budget GID');
        return $this->updateResource('budgets', $budgetGid, $data, $options, $responseType);
    }

    /**
     * Delete a budget.
     * DELETE /budgets/{budget_gid}
     * Deletes a specific, existing budget.
     * API Documentation: https://developers.asana.com/reference/deletebudget
     *
     * @param string $budgetGid Globally unique identifier for the budget.
     * @param int $responseType The type of response to return.
     *
     * @return array The response data.
     * @throws ApiException If the API request fails due to network issues, insufficient permissions, or rate limits.
     * @throws RateLimitException If the Asana API rate limit is exceeded.
     * @throws ValidationException If the budget GID is empty or invalid.
     */
    public function deleteBudget(string $budgetGid, int $responseType = HttpClientInterface::RESPONSE_DATA): array
    {
        $this->validateGid($budgetGid, 'Budget GID');
        return $this->deleteResource('budgets', $budgetGid, $responseType);
    }
}
