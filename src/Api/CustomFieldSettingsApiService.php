<?php

namespace BrightleafDigital\Api;

use BrightleafDigital\Exceptions\ApiException;
use BrightleafDigital\Exceptions\RateLimitException;
use BrightleafDigital\Exceptions\ValidationException;
use BrightleafDigital\Http\HttpClientInterface;

/**
 * Service class for Custom Field Settings-related API operations.
 *
 * @link https://developers.asana.com/reference/custom-field-settings
 */
class CustomFieldSettingsApiService extends BaseApiService
{
    /**
     * Get a project's custom fields
     * GET /projects/{project_gid}/custom_field_settings
     * Returns a list of all of the custom fields settings on a project, in compact form.
     * API Documentation: https://developers.asana.com/reference/getcustomfieldsettingsforproject
     *
     * @param string $projectGid Globally unique identifier for the project.
     * @param array $options Optional parameters:
     * - limit (int): Results per page.
     * - offset (string): Offset token.
     * - opt_fields (string): Comma-separated list of fields.
     * - opt_pretty (bool): Pretty JSON.
     * @param int $responseType The type of response to return.
     *
     * @return array The response data.
     * @throws ApiException
     * @throws RateLimitException
     * @throws ValidationException If project GID is empty.
     */
    public function getCustomFieldSettingsForProject(
        string $projectGid,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($projectGid, 'Project GID');
        return $this->client->request(
            'GET',
            "projects/$projectGid/custom_field_settings",
            ['query' => $options],
            $responseType
        );
    }

    /**
     * Get a portfolio's custom fields
     * GET /portfolios/{portfolio_gid}/custom_field_settings
     * Returns a list of all of the custom fields settings on a portfolio, in compact form.
     * API Documentation: https://developers.asana.com/reference/getcustomfieldsettingsforportfolio
     *
     * @param string $portfolioGid Globally unique identifier for the portfolio.
     * @param array $options Optional parameters:
     * - limit (int): Results per page.
     * - offset (string): Offset token.
     * - opt_fields (string): Comma-separated list of fields.
     * - opt_pretty (bool): Pretty JSON.
     * @param int $responseType The type of response to return.
     *
     * @return array The response data.
     * @throws ApiException
     * @throws RateLimitException
     * @throws ValidationException If portfolio GID is empty.
     */
    public function getCustomFieldSettingsForPortfolio(
        string $portfolioGid,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($portfolioGid, 'Portfolio GID');
        return $this->client->request(
            'GET',
            "portfolios/$portfolioGid/custom_field_settings",
            ['query' => $options],
            $responseType
        );
    }

    /**
     * Get a team's custom fields
     * GET /teams/{team_gid}/custom_field_settings
     * Returns a list of all of the custom fields settings on a team, in compact form.
     * API Documentation: https://developers.asana.com/reference/getcustomfieldsettingsforteam
     *
     * @param string $teamGid Globally unique identifier for the team.
     * @param array $options Optional parameters:
     * - opt_fields (string): Comma-separated list of fields.
     * - opt_pretty (bool): Pretty JSON.
     * @param int $responseType The type of response to return.
     *
     * @return array The response data.
     * @throws ApiException
     * @throws RateLimitException
     * @throws ValidationException If team GID is empty.
     */
    public function getCustomFieldSettingsForTeam(
        string $teamGid,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($teamGid, 'Team GID');
        return $this->client->request(
            'GET',
            "teams/$teamGid/custom_field_settings",
            ['query' => $options],
            $responseType
        );
    }

    /**
     * Get a goal's custom fields
     * GET /goals/{goal_gid}/custom_field_settings
     * Returns a list of all of the custom fields settings on a goal, in compact form.
     * API Documentation: https://developers.asana.com/reference/getcustomfieldsettingsforgoal
     *
     * @param string $goalGid Globally unique identifier for the goal.
     * @param array $options Optional parameters:
     * - limit (int): Results per page.
     * - offset (string): Offset token.
     * - opt_fields (string): Comma-separated list of fields.
     * - opt_pretty (bool): Pretty JSON.
     * @param int $responseType The type of response to return.
     *
     * @return array The response data.
     * @throws ApiException
     * @throws RateLimitException
     * @throws ValidationException If goal GID is empty.
     */
    public function getCustomFieldSettingsForGoal(
        string $goalGid,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($goalGid, 'Goal GID');
        return $this->client->request(
            'GET',
            "goals/$goalGid/custom_field_settings",
            ['query' => $options],
            $responseType
        );
    }
}
