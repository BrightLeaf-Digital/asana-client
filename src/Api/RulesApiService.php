<?php

namespace BrightleafDigital\Api;

use BrightleafDigital\Exceptions\ApiException;
use BrightleafDigital\Exceptions\RateLimitException;
use BrightleafDigital\Exceptions\ValidationException;
use BrightleafDigital\Http\HttpClientInterface;

/**
 * Service for interacting with Asana Rules API endpoints.
 *
 * This class provides methods for triggering rules which use an "incoming web request" trigger.
 *
 * @link https://developers.asana.com/reference/triggerrule
 */
class RulesApiService extends BaseApiService
{
    /**
     * Trigger a rule
     * POST /rule_triggers/{rule_trigger_gid}/run
     * Trigger a rule which uses an "incoming web request" trigger.
     * API Documentation: https://developers.asana.com/reference/triggerrule
     *
     * @param string $ruleTriggerGid The ID of the incoming web request trigger.
     *                               Example: "12345"
     * @param array $data A dictionary of variables accessible from within the rule.
     *                    Example: ["variable_name" => "value"]
     * @param array $options Optional parameters to customize the request:
     * - opt_pretty (bool): Returns formatted JSON if true.
     *
     * @param int $responseType The type of response to return:
     * - HttpClientInterface::RESPONSE_FULL (1): Full response with status, headers, etc.
     * - HttpClientInterface::RESPONSE_NORMAL (2): Complete decoded JSON body
     * - HttpClientInterface::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type.
     * @throws ApiException
     * @throws RateLimitException
     * @throws ValidationException
     */
    public function triggerRule(
        string $ruleTriggerGid,
        array $data,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($ruleTriggerGid, 'Rule Trigger GID');

        return $this->createResource("rule_triggers/$ruleTriggerGid/run", $data, $options, $responseType);
    }
}
