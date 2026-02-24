<?php

namespace BrightleafDigital\Api;

use BrightleafDigital\Exceptions\ApiException;
use BrightleafDigital\Exceptions\RateLimitException;
use BrightleafDigital\Exceptions\ValidationException;
use BrightleafDigital\Http\HttpClientInterface;

/**
 * Service class for Jobs-related API operations.
 *
 * @link https://developers.asana.com/reference/jobs
 */
class JobsApiService extends BaseApiService
{
    /**
     * Get a job by id
     * GET /jobs/{job_gid}
     * Returns the full record for a single job.
     * API Documentation: https://developers.asana.com/reference/getjob
     *
     * @param string $jobGid Globally unique identifier for the job.
     * @param array $options Query parameters for formatting results.
     * @param int $responseType The type of response to return.
     *
     * @return array The response data (JobResponse).
     * @throws ApiException
     * @throws RateLimitException
     * @throws ValidationException If job GID is empty.
     */
    public function getJob(
        string $jobGid,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($jobGid, 'Job GID');
        return $this->getResource('jobs', $jobGid, $options, $responseType);
    }
}
