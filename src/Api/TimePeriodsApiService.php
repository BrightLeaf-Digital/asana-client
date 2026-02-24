<?php

namespace BrightleafDigital\Api;

use BrightleafDigital\Exceptions\ApiException;
use BrightleafDigital\Exceptions\RateLimitException;
use BrightleafDigital\Exceptions\ValidationException;
use BrightleafDigital\Http\HttpClientInterface;

/**
 * Service class for Time Periods-related API operations.
 *
 * @link https://developers.asana.com/reference/time-periods
 */
class TimePeriodsApiService extends BaseApiService
{
    /**
     * Get a time period
     * GET /time_periods/{time_period_gid}
     * Returns the full record for a single time period.
     * API Documentation: https://developers.asana.com/reference/gettimeperiod
     *
     * @param string $timePeriodGid Globally unique identifier for the time period.
     * @param array $options Query parameters for formatting results.
     * @param int $responseType The type of response to return.
     *
     * @return array The response data.
     * @throws ApiException
     * @throws RateLimitException
     * @throws ValidationException If time period GID is empty.
     */
    public function getTimePeriod(
        string $timePeriodGid,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($timePeriodGid, 'Time Period GID');
        return $this->getResource('time_periods', $timePeriodGid, $options, $responseType);
    }

    /**
     * Get time periods
     * GET /time_periods
     * Returns compact time period records.
     * API Documentation: https://developers.asana.com/reference/gettimeperiods
     *
     * @param string $workspace Globally unique identifier for the workspace.
     * @param array $options Optional parameters to customize the request:
     * - start_on (string): ISO 8601 date string.
     * - end_on (string): ISO 8601 date string.
     * - limit (int): Results per page.
     * - offset (string): Offset token.
     * - opt_fields (string): Comma-separated list of fields.
     * - opt_pretty (bool): Pretty JSON.
     * @param int $responseType The type of response to return.
     *
     * @return array The response data.
     * @throws ApiException
     * @throws RateLimitException
     * @throws ValidationException If workspace GID is empty.
     */
    public function getTimePeriods(
        string $workspace,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($workspace, 'Workspace GID');
        $options['workspace'] = $workspace;
        return $this->getResources('time_periods', $options, $responseType);
    }
}
