<?php

namespace BrightleafDigital\Api;

use BrightleafDigital\Exceptions\ApiException;
use BrightleafDigital\Exceptions\RateLimitException;
use BrightleafDigital\Exceptions\ValidationException;
use BrightleafDigital\Http\HttpClientInterface;

class PortfolioMembershipsApiService extends BaseApiService
{
    /**
     * Get multiple portfolio memberships
     * GET /portfolio_memberships
     * Returns a list of portfolio memberships in compact representation. You must
     * specify `portfolio`, `portfolio` and `user`, or `workspace` and `user`.
     * API Documentation: https://developers.asana.com/reference/getportfoliomemberships
     *
     * @param array $options Optional query parameters:
     * - portfolio (string): Globally unique identifier for the portfolio.
     * - workspace (string): Globally unique identifier for the workspace.
     * - user (string): Globally unique identifier for the user.
     * - limit (int): Max number of results
     * - offset (string): Offset for pagination
     * - opt_fields (string): Comma-separated fields to include
     * - opt_pretty (bool): Returns formatted JSON if true
     *
     * @param int $responseType The type of response to return:
     * - HttpClientInterface::RESPONSE_FULL (1)
     * - HttpClientInterface::RESPONSE_NORMAL (2)
     * - HttpClientInterface::RESPONSE_DATA (3) default
     *
     * @return array
     * @throws ApiException
     * @throws RateLimitException
     */
    public function getPortfolioMemberships(
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        return $this->getResources('portfolio_memberships', $options, $responseType);
    }

    /**
     * Get a portfolio membership
     * GET /portfolio_memberships/{portfolio_membership_gid}
     * Returns the complete portfolio record for a single portfolio membership.
     * API Documentation: https://developers.asana.com/reference/getportfoliomembership
     *
     * @param string $portfolioMembershipGid Globally unique identifier for the portfolio membership.
     * @param array $options Optional query parameters:
     * - opt_fields (string): Comma-separated fields to include
     * - opt_pretty (bool): Returns formatted JSON if true
     *
     * @param int $responseType The type of response to return:
     * - HttpClientInterface::RESPONSE_FULL (1)
     * - HttpClientInterface::RESPONSE_NORMAL (2)
     * - HttpClientInterface::RESPONSE_DATA (3) default
     *
     * @return array
     * @throws ApiException
     * @throws RateLimitException
     * @throws ValidationException
     */
    public function getPortfolioMembership(
        string $portfolioMembershipGid,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($portfolioMembershipGid, 'Portfolio Membership GID');

        return $this->getResource('portfolio_memberships', $portfolioMembershipGid, $options, $responseType);
    }

    /**
     * Get memberships from a portfolio
     * GET /portfolios/{portfolio_gid}/portfolio_memberships
     * Returns the compact portfolio membership records for the portfolio.
     * API Documentation: https://developers.asana.com/reference/getportfoliomembershipsforportfolio
     *
     * @param string $portfolioGid Globally unique identifier for the portfolio.
     * @param array $options Optional query parameters:
     * - user (string): Globally unique identifier for the user.
     * - limit (int): Max number of results
     * - offset (string): Offset for pagination
     * - opt_fields (string): Comma-separated fields to include
     * - opt_pretty (bool): Returns formatted JSON if true
     *
     * @param int $responseType The type of response to return:
     * - HttpClientInterface::RESPONSE_FULL (1)
     * - HttpClientInterface::RESPONSE_NORMAL (2)
     * - HttpClientInterface::RESPONSE_DATA (3) default
     *
     * @return array
     * @throws ApiException
     * @throws RateLimitException
     * @throws ValidationException
     */
    public function getPortfolioMembershipsForPortfolio(
        string $portfolioGid,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($portfolioGid, 'Portfolio GID');

        $endpoint = "portfolios/$portfolioGid/portfolio_memberships";
        return $this->client->request('GET', $endpoint, ['query' => $options], $responseType);
    }
}
