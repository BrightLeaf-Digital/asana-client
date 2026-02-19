<?php

namespace BrightleafDigital\Exceptions;

use Throwable;

/**
 * Exception representing errors that occur during API interactions.
 */
class ApiException extends AsanaException
{
    /** @var array */
    protected array $requestData;

    /** @var array */
    protected array $responseData;

    /**
     * @param string $message
     * @param int $code
     * @param array $requestData Context of the request (method, endpoint, params)
     * @param array $responseData Parsed response payload if available
     * @param Throwable|null $previous
     */
    public function __construct(
        string $message,
        int $code = 0,
        array $requestData = [],
        array $responseData = [],
        ?Throwable $previous = null
    ) {
        $this->requestData = $requestData;
        $this->responseData = $responseData;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return array
     */
    public function getRequestData(): array
    {
        return $this->requestData;
    }

    /**
     * @return array
     */
    public function getResponseData(): array
    {
        return $this->responseData;
    }
}
