<?php

namespace BrightleafDigital\Exceptions;

use Throwable;

/**
 * Exception thrown when the Asana API rate limit is exceeded.
 *
 * This exception provides additional context about rate limiting,
 * including the retry-after duration suggested by the API.
 */
class RateLimitException extends ApiException
{
    /**
     * The number of seconds to wait before retrying the request.
     */
    private int $retryAfter;

    /**
     * Constructor for RateLimitException.
     *
     * @param string $message The exception message.
     * @param int $retryAfter The number of seconds to wait before retrying.
     * @param array $responseData Optional decoded response body or error data.
     * @param Throwable|null $previous The previous throwable used for exception chaining.
     * @param array $requestData Optional request data.
     */
    public function __construct(
        string $message,
        int $retryAfter = 60,
        array $responseData = [],
        ?Throwable $previous = null,
        array $requestData = []
    ) {
        $this->retryAfter = $retryAfter;
        parent::__construct($message, 429, $requestData, $responseData, $previous);
    }

    /**
     * Get the number of seconds to wait before retrying.
     *
     * @return int The retry-after duration in seconds.
     */
    public function getRetryAfter(): int
    {
        return $this->retryAfter;
    }
}
