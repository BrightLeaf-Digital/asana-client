<?php

namespace BrightleafDigital\Api;

use BrightleafDigital\Exceptions\ApiException;
use BrightleafDigital\Exceptions\RateLimitException;
use BrightleafDigital\Exceptions\ValidationException;
use BrightleafDigital\Http\HttpClientInterface;

/**
 * Service class for Reactions-related API operations.
 *
 * @link https://developers.asana.com/reference/reactions
 */
class ReactionsApiService extends BaseApiService
{
    /**
     * Get reactions with an emoji base on an object.
     * GET /reactions
     * Returns the reactions with a specified emoji base character on the object.
     * API Documentation: https://developers.asana.com/reference/getreactionsonobject
     *
     * @param string $target Globally unique identifier for object to fetch reactions from.
     *                      Must be a GID for a status update or story.
     * @param string $emojiBase Only return reactions with this emoji base character.
     * @param array $options Optional parameters to customize the request:
     * - opt_fields (string): A comma-separated list of fields to include in the response.
     * - opt_pretty (bool): Returns formatted JSON if true.
     * @param int $responseType The type of response to return.
     *
     * @return array The response data.
     * @throws ApiException If the API request fails due to network issues, insufficient permissions, or rate limits.
     * @throws RateLimitException If the Asana API rate limit is exceeded.
     * @throws ValidationException If the target GID is empty or invalid.
     */
    public function getReactionsOnObject(
        string $target,
        string $emojiBase,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($target, 'Target GID');
        $options['target'] = $target;
        $options['emoji_base'] = $emojiBase;
        return $this->getResources('reactions', $options, $responseType);
    }
}
