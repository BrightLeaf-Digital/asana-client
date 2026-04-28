<?php

namespace BrightleafDigital\Api;

use BrightleafDigital\Exceptions\ApiException;
use BrightleafDigital\Exceptions\RateLimitException;
use BrightleafDigital\Exceptions\ValidationException;
use BrightleafDigital\Http\HttpClientInterface;

class ProjectPortfolioSettingsApiService extends BaseApiService
{
    /**
     * Get a project portfolio setting
     * GET /project_portfolio_settings/{project_portfolio_setting_gid}
     * Returns the full record for a single project portfolio setting.
     * A project portfolio setting models the relationship between a portfolio and one of
     * its contained projects, including whether portfolio members automatically gain
     * access to the project.
     * Requires the `project_portfolio_settings:read` OAuth scope.
     * API Documentation: https://developers.asana.com/reference/getprojectportfoliosetting
     *
     * @param string $projectPortfolioSettingGid The unique global ID of the project portfolio setting.
     *                                            Example: "12345"
     * @param array $options Optional parameters to customize the request:
     * - opt_fields (string): Comma-separated fields to include in the response
     *   (e.g., "is_access_control_inherited,project,portfolio")
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
     * - Just the data object containing the setting details including:
     *   - gid: Unique identifier of the setting
     *   - resource_type: Always "project_portfolio_setting"
     *   - is_access_control_inherited: Boolean; when true, portfolio members automatically
     *     gain access to the project with their mapped role
     *   - project: Object with gid and name of the project
     *   - portfolio: Object with gid and name of the portfolio
     *
     * @throws ApiException
     * @throws RateLimitException
     * @throws ValidationException
     */
    public function getProjectPortfolioSetting(
        string $projectPortfolioSettingGid,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($projectPortfolioSettingGid, 'Project Portfolio Setting GID');

        return $this->getResource(
            'project_portfolio_settings',
            $projectPortfolioSettingGid,
            $options,
            $responseType
        );
    }

    /**
     * Update a project portfolio setting
     * PUT /project_portfolio_settings/{project_portfolio_setting_gid}
     * Updates the properties of a project portfolio setting. Only Project Admins can
     * modify the `is_access_control_inherited` field.
     * By default, inheritance is set to false for new connections.
     * Requires the `project_portfolio_settings:write` OAuth scope.
     * API Documentation: https://developers.asana.com/reference/updateprojectportfoliosetting
     *
     * @param string $projectPortfolioSettingGid The unique global ID of the project portfolio setting.
     *                                            Example: "12345"
     * @param array $data Properties to update. Can include:
     * - is_access_control_inherited (bool): When true, portfolio members automatically gain
     *   access to the project with roles mapped 1:1 from their portfolio role.
     *   Example: ["is_access_control_inherited" => true]
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
    public function updateProjectPortfolioSetting(
        string $projectPortfolioSettingGid,
        array $data,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($projectPortfolioSettingGid, 'Project Portfolio Setting GID');

        return $this->updateResource(
            'project_portfolio_settings',
            $projectPortfolioSettingGid,
            $data,
            $options,
            $responseType
        );
    }

    /**
     * Get project portfolio settings for a project
     * GET /projects/{project_gid}/project_portfolio_settings
     * Returns a list of project portfolio settings for all portfolios that contain the given project.
     * Requires the `project_portfolio_settings:read` OAuth scope.
     * API Documentation: https://developers.asana.com/reference/getprojectportfoliosettingsforproject
     *
     * @param string $projectGid The unique global ID of the project.
     *                           Example: "12345"
     * @param array $options Optional parameters to customize the request:
     *
     * Pagination parameters:
     * - limit (int): Maximum number of settings to return. Default is 20, max is 100
     * - offset (string): Offset token for pagination
     *
     * Display parameters:
     * - opt_fields (string): Comma-separated fields to include in the response
     *   (e.g., "is_access_control_inherited,project,portfolio")
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
    public function getProjectPortfolioSettingsForProject(
        string $projectGid,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($projectGid, 'Project GID');

        return $this->client->request(
            'GET',
            "projects/$projectGid/project_portfolio_settings",
            ['query' => $options],
            $responseType
        );
    }

    /**
     * Get project portfolio settings for a portfolio
     * GET /portfolios/{portfolio_gid}/project_portfolio_settings
     * Returns a list of project portfolio settings for all projects contained in the given portfolio.
     * Requires the `project_portfolio_settings:read` OAuth scope.
     * API Documentation: https://developers.asana.com/reference/getprojectportfoliosettingsforportfolio
     *
     * @param string $portfolioGid The unique global ID of the portfolio.
     *                             Example: "12345"
     * @param array $options Optional parameters to customize the request:
     *
     * Pagination parameters:
     * - limit (int): Maximum number of settings to return. Default is 20, max is 100
     * - offset (string): Offset token for pagination
     *
     * Display parameters:
     * - opt_fields (string): Comma-separated fields to include in the response
     *   (e.g., "is_access_control_inherited,project,portfolio")
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
    public function getProjectPortfolioSettingsForPortfolio(
        string $portfolioGid,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($portfolioGid, 'Portfolio GID');

        return $this->client->request(
            'GET',
            "portfolios/$portfolioGid/project_portfolio_settings",
            ['query' => $options],
            $responseType
        );
    }
}
