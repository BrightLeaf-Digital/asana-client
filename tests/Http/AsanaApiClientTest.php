<?php

namespace BrightleafDigital\Tests\Http;

use BrightleafDigital\Exceptions\ApiException;
use BrightleafDigital\Exceptions\RateLimitException;
use BrightleafDigital\Http\AsanaApiClient;
use BrightleafDigital\Http\HttpClientInterface;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Uri;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use ReflectionClass;

class AsanaApiClientTest extends TestCase
{
    /** @var AsanaApiClient */
    private AsanaApiClient $apiClient;

    /**
     */
    protected function setUp(): void
    {
        $this->apiClient = new AsanaApiClient(fn() => 'test-access-token');
    }

    /**
     * @return GuzzleClient&MockObject
     */
    private function injectMockHttpClient(?AsanaApiClient $apiClient = null): GuzzleClient
    {
        $mockHttpClient = $this->createMock(GuzzleClient::class);
        $targetClient = $apiClient ?? $this->apiClient;

        $reflection = new ReflectionClass(AsanaApiClient::class);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($targetClient, $mockHttpClient);

        return $mockHttpClient;
    }

    /**
     * Test response type constants are defined correctly.
     */
    public function testResponseTypeConstants(): void
    {
        $this->assertSame(1, HttpClientInterface::RESPONSE_FULL);
        $this->assertSame(2, HttpClientInterface::RESPONSE_NORMAL);
        $this->assertSame(3, HttpClientInterface::RESPONSE_DATA);
    }

    /**
     * Test successful GET request returns data only (default response type).
     */
    public function testRequestReturnsDataByDefault(): void
    {
        $responseBody = ['data' => ['gid' => '12345', 'name' => 'Test Task']];
        $mockHttpClient = $this->injectMockHttpClient();

        $mockStream = $this->createStub(StreamInterface::class);
        $mockStream->method('__toString')->willReturn(json_encode($responseBody));

        $mockResponse = $this->createStub(Response::class);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $mockResponse->method('getStatusCode')->willReturn(200);

        $mockHttpClient->expects($this->once())
            ->method('request')
            ->with('GET', 'tasks/12345', [])
            ->willReturn($mockResponse);

        $result = $this->apiClient->request('GET', 'tasks/12345');

        $this->assertSame(['gid' => '12345', 'name' => 'Test Task'], $result);
    }

    /**
     * Test request with RESPONSE_NORMAL returns complete JSON body.
     */
    public function testRequestReturnsNormalResponse(): void
    {
        $responseBody = [
            'data' => ['gid' => '12345'],
            'next_page' => ['offset' => 'abc123']
        ];
        $mockHttpClient = $this->injectMockHttpClient();

        $mockStream = $this->createStub(StreamInterface::class);
        $mockStream->method('__toString')->willReturn(json_encode($responseBody));

        $mockResponse = $this->createStub(Response::class);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $mockResponse->method('getStatusCode')->willReturn(200);

        $mockHttpClient->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse);

        $result = $this->apiClient->request('GET', 'tasks', [], HttpClientInterface::RESPONSE_NORMAL);

        $this->assertSame($responseBody, $result);
    }

    /**
     * Test request with RESPONSE_FULL returns complete response details.
     */
    public function testRequestReturnsFullResponse(): void
    {
        $responseBody = ['data' => ['gid' => '12345']];
        $encodedBody = json_encode($responseBody);
        $mockHttpClient = $this->injectMockHttpClient();

        $mockStream = $this->createStub(StreamInterface::class);
        $mockStream->method('__toString')->willReturn($encodedBody);

        $mockResponse = $this->createStub(Response::class);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $mockResponse->method('getStatusCode')->willReturn(200);
        $mockResponse->method('getReasonPhrase')->willReturn('OK');
        $mockResponse->method('getHeaders')->willReturn(['Content-Type' => ['application/json']]);

        $mockHttpClient->expects($this->once())
            ->method('request')
            ->with('POST', 'tasks', ['json' => ['data' => ['name' => 'New Task']]])
            ->willReturn($mockResponse);

        $result = $this->apiClient->request(
            'POST',
            'tasks',
            ['json' => ['data' => ['name' => 'New Task']]],
            HttpClientInterface::RESPONSE_FULL
        );

        $this->assertArrayHasKey('status', $result);
        $this->assertArrayHasKey('reason', $result);
        $this->assertArrayHasKey('headers', $result);
        $this->assertArrayHasKey('body', $result);
        $this->assertArrayHasKey('raw_body', $result);
        $this->assertArrayHasKey('request', $result);

        $this->assertSame(200, $result['status']);
        $this->assertSame('OK', $result['reason']);
        $this->assertSame(['Content-Type' => ['application/json']], $result['headers']);
        $this->assertSame($responseBody, $result['body']);
    }

    /**
     * Test that response without 'data' key returns full body in RESPONSE_DATA mode.
     */
    public function testRequestReturnsFullBodyWhenNoDataKey(): void
    {
        $responseBody = ['gid' => '12345', 'name' => 'Test'];
        $mockHttpClient = $this->injectMockHttpClient();

        $mockStream = $this->createStub(StreamInterface::class);
        $mockStream->method('__toString')->willReturn(json_encode($responseBody));

        $mockResponse = $this->createStub(Response::class);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $mockResponse->method('getStatusCode')->willReturn(200);

        $mockHttpClient->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse);

        $result = $this->apiClient->request('GET', 'some/endpoint');

        $this->assertSame($responseBody, $result);
    }

    /**
     * Test that invalid JSON response throws ApiException.
     */
    public function testRequestThrowsExceptionOnInvalidJson(): void
    {
        $mockHttpClient = $this->injectMockHttpClient();
        $mockStream = $this->createStub(StreamInterface::class);
        $mockStream->method('__toString')->willReturn('not valid json');

        $mockResponse = $this->createStub(Response::class);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $mockResponse->method('getStatusCode')->willReturn(200);

        $mockHttpClient->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Invalid JSON response from Asana API.');

        $this->apiClient->request('GET', 'tasks');
    }

    /**
     * Test that GuzzleException with response is handled correctly.
     */
    public function testRequestHandlesGuzzleExceptionWithResponse(): void
    {
        $errorBody = [
            'errors' => [
                ['message' => 'task: Not a valid gid', 'help' => 'Check the ID']
            ]
        ];
        $mockHttpClient = $this->injectMockHttpClient();

        $mockStream = $this->createStub(StreamInterface::class);
        $mockStream->method('__toString')->willReturn(json_encode($errorBody));

        $mockResponse = $this->createStub(Response::class);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $mockResponse->method('getStatusCode')->willReturn(400);
        $mockResponse->method('getReasonPhrase')->willReturn('Bad Request');

        $mockRequest = $this->createStub(Request::class);
        $mockRequest->method('getMethod')->willReturn('GET');
        $mockRequest->method('getUri')->willReturn(
            new Uri('https://app.asana.com/api/1.0/tasks/invalid')
        );

        $exception = new RequestException(
            'Client error',
            $mockRequest,
            $mockResponse
        );

        $mockHttpClient->expects($this->once())
            ->method('request')
            ->willThrowException($exception);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('task: Not a valid gid');

        $this->apiClient->request('GET', 'tasks/invalid');
    }

    /**
     * Test that GuzzleException without response is handled correctly.
     */
    public function testRequestHandlesGuzzleExceptionWithoutResponse(): void
    {
        $mockHttpClient = $this->injectMockHttpClient();
        $mockRequest = $this->createStub(Request::class);

        $exception = new RequestException(
            'Network error',
            $mockRequest,
            null
        );

        $mockHttpClient->expects($this->once())
            ->method('request')
            ->willThrowException($exception);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Network error');

        $this->apiClient->request('GET', 'tasks');
    }

    /**
     * Test that GuzzleException with non-JSON response body is handled.
     */
    public function testRequestHandlesGuzzleExceptionWithPlainTextBody(): void
    {
        $mockHttpClient = $this->injectMockHttpClient();
        $mockStream = $this->createStub(StreamInterface::class);
        $mockStream->method('__toString')->willReturn('Service Unavailable');

        $mockResponse = $this->createStub(Response::class);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $mockResponse->method('getStatusCode')->willReturn(503);
        $mockResponse->method('getReasonPhrase')->willReturn('Service Unavailable');

        $mockRequest = $this->createStub(Request::class);

        $exception = new RequestException(
            'Server error',
            $mockRequest,
            $mockResponse
        );

        $mockHttpClient->expects($this->once())
            ->method('request')
            ->willThrowException($exception);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Service Unavailable');

        $this->apiClient->request('GET', 'tasks');
    }

    /**
     * Test request with query parameters.
     */
    public function testRequestWithQueryParameters(): void
    {
        $responseBody = ['data' => []];
        $mockHttpClient = $this->injectMockHttpClient();

        $mockStream = $this->createStub(StreamInterface::class);
        $mockStream->method('__toString')->willReturn(json_encode($responseBody));

        $mockResponse = $this->createStub(Response::class);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $mockResponse->method('getStatusCode')->willReturn(200);

        $expectedOptions = [
            'query' => [
                'workspace' => '12345',
                'opt_fields' => 'name,assignee',
                'limit' => 50
            ]
        ];

        $mockHttpClient->expects($this->once())
            ->method('request')
            ->with('GET', 'tasks', $expectedOptions)
            ->willReturn($mockResponse);

        $this->apiClient->request('GET', 'tasks', $expectedOptions);
    }

    /**
     * Test request with JSON body for POST.
     */
    public function testRequestWithJsonBody(): void
    {
        $responseBody = ['data' => ['gid' => '12345', 'name' => 'New Task']];
        $mockHttpClient = $this->injectMockHttpClient();

        $mockStream = $this->createStub(StreamInterface::class);
        $mockStream->method('__toString')->willReturn(json_encode($responseBody));

        $mockResponse = $this->createStub(Response::class);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $mockResponse->method('getStatusCode')->willReturn(201);

        $taskData = [
            'json' => [
                'data' => [
                    'name' => 'New Task',
                    'workspace' => '12345'
                ]
            ]
        ];

        $mockHttpClient->expects($this->once())
            ->method('request')
            ->with('POST', 'tasks', $taskData)
            ->willReturn($mockResponse);

        $result = $this->apiClient->request('POST', 'tasks', $taskData);

        $this->assertSame('12345', $result['gid']);
        $this->assertSame('New Task', $result['name']);
    }

    /**
     * Test PUT request for updating resources.
     */
    public function testPutRequest(): void
    {
        $responseBody = ['data' => ['gid' => '12345', 'name' => 'Updated Task']];
        $mockHttpClient = $this->injectMockHttpClient();

        $mockStream = $this->createStub(StreamInterface::class);
        $mockStream->method('__toString')->willReturn(json_encode($responseBody));

        $mockResponse = $this->createStub(Response::class);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $mockResponse->method('getStatusCode')->willReturn(200);

        $mockHttpClient->expects($this->once())
            ->method('request')
            ->with('PUT', 'tasks/12345', $this->anything())
            ->willReturn($mockResponse);

        $result = $this->apiClient->request('PUT', 'tasks/12345', [
            'json' => ['data' => ['name' => 'Updated Task']]
        ]);

        $this->assertSame('Updated Task', $result['name']);
    }

    /**
     * Test DELETE request.
     */
    public function testDeleteRequest(): void
    {
        $responseBody = ['data' => []];
        $mockHttpClient = $this->injectMockHttpClient();

        $mockStream = $this->createStub(StreamInterface::class);
        $mockStream->method('__toString')->willReturn(json_encode($responseBody));

        $mockResponse = $this->createStub(Response::class);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $mockResponse->method('getStatusCode')->willReturn(200);

        $mockHttpClient->expects($this->once())
            ->method('request')
            ->with('DELETE', 'tasks/12345', [])
            ->willReturn($mockResponse);

        $result = $this->apiClient->request('DELETE', 'tasks/12345');

        $this->assertSame([], $result);
    }

    /**
     * Test ApiException contains response data.
     */
    public function testApiExceptionContainsResponseData(): void
    {
        $errorBody = [
            'errors' => [
                ['message' => 'Invalid request', 'help' => 'See docs']
            ]
        ];
        $mockHttpClient = $this->injectMockHttpClient();

        $mockStream = $this->createStub(StreamInterface::class);
        $mockStream->method('__toString')->willReturn(json_encode($errorBody));

        $mockResponse = $this->createStub(Response::class);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $mockResponse->method('getStatusCode')->willReturn(400);
        $mockResponse->method('getReasonPhrase')->willReturn('Bad Request');

        $mockRequest = $this->createStub(Request::class);
        $mockRequest->method('getMethod')->willReturn('GET');
        $mockRequest->method('getUri')->willReturn(
            new Uri('https://app.asana.com/api/1.0/tasks')
        );

        $exception = new RequestException(
            'Client error',
            $mockRequest,
            $mockResponse
        );

        $mockHttpClient->expects($this->once())
            ->method('request')
            ->willThrowException($exception);

        try {
            $this->apiClient->request('GET', 'tasks');
            $this->fail('Expected ApiException was not thrown');
        } catch (ApiException $e) {
            $this->assertSame(400, $e->getCode());
            $this->assertIsArray($e->getResponseData());
            $this->assertArrayHasKey('errors', $e->getResponseData());
        }
    }

    /**
     * Test constructor sets up authorization header correctly.
     */
    public function testConstructorSetsAuthorizationHeader(): void
    {
        $token = 'my-test-token-12345';
        $client = new AsanaApiClient(fn() => $token);

        $reflection = new ReflectionClass(AsanaApiClient::class);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClient = $httpClientProperty->getValue($client);

        // Verify the client was created (we can't easily inspect Guzzle config)
        $this->assertInstanceOf(GuzzleClient::class, $httpClient);
    }

    /**
     * Test default retry constants are defined correctly.
     */
    public function testRetryConstants(): void
    {
        $this->assertSame(3, AsanaApiClient::DEFAULT_MAX_RETRIES);
        $this->assertSame(1, AsanaApiClient::DEFAULT_INITIAL_BACKOFF);
    }

    /**
     * Test constructor with custom logger.
     */
    public function testConstructorWithCustomLogger(): void
    {
        $mockLogger = $this->createStub(LoggerInterface::class);
        $client = new AsanaApiClient(fn() => 'test-token', $mockLogger);

        $this->assertSame($mockLogger, $client->getLogger());
    }

    /**
     * Test constructor uses NullLogger by default.
     */
    public function testConstructorUsesNullLoggerByDefault(): void
    {
        $client = new AsanaApiClient(fn() => 'test-token');

        $this->assertInstanceOf(NullLogger::class, $client->getLogger());
    }

    /**
     * Test constructor with custom retry settings.
     */
    public function testConstructorWithCustomRetrySettings(): void
    {
        $client = new AsanaApiClient(fn() => 'test-token', null, 5, 2);

        $reflection = new ReflectionClass(AsanaApiClient::class);

        $maxRetriesProperty = $reflection->getProperty('maxRetries');
        $maxRetriesProperty->setAccessible(true);
        $this->assertSame(5, $maxRetriesProperty->getValue($client));

        $initialBackoffProperty = $reflection->getProperty('initialBackoff');
        $initialBackoffProperty->setAccessible(true);
        $this->assertSame(2, $initialBackoffProperty->getValue($client));
    }

    /**
     * Test setLogger changes the logger.
     */
    public function testSetLogger(): void
    {
        $client = new AsanaApiClient(fn() => 'test-token');
        $mockLogger = $this->createStub(LoggerInterface::class);

        $result = $client->setLogger($mockLogger);

        $this->assertSame($client, $result); // Fluent interface
        $this->assertSame($mockLogger, $client->getLogger());
    }

    /**
     * Test rate limit exception is thrown after max retries.
     */
    public function testRateLimitExceptionAfterMaxRetries(): void
    {
        // Create client with 0 retries to immediately throw exception
        $apiClient = new AsanaApiClient(fn() => 'test-token', null, 0);

        $mockHttpClient = $this->injectMockHttpClient($apiClient);

        // Create a rate limit response
        $mockStream = $this->createStub(StreamInterface::class);
        $mockStream->method('__toString')->willReturn(json_encode([
            'errors' => [['message' => 'Rate limit exceeded']]
        ]));

        $mockResponse = $this->createStub(Response::class);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $mockResponse->method('getStatusCode')->willReturn(429);
        $mockResponse->method('getReasonPhrase')->willReturn('Too Many Requests');
        $mockResponse->method('hasHeader')->willReturn(true);
        $mockResponse->method('getHeaderLine')->willReturn('30');

        $mockRequest = $this->createStub(Request::class);

        $exception = new RequestException(
            'Rate limit exceeded',
            $mockRequest,
            $mockResponse
        );

        $mockHttpClient->expects($this->once())
            ->method('request')
            ->willThrowException($exception);

        $this->expectException(RateLimitException::class);
        $this->expectExceptionMessage('Rate limit exceeded. Please retry after');

        $apiClient->request('GET', 'tasks');
    }

    /**
     * Test that rate limit exception contains retry after value.
     */
    public function testRateLimitExceptionContainsRetryAfter(): void
    {
        // Create client with 0 retries to immediately throw exception
        $apiClient = new AsanaApiClient(fn() => 'test-token', null, 0);

        $mockHttpClient = $this->injectMockHttpClient($apiClient);

        // Create a rate limit response
        $mockStream = $this->createStub(StreamInterface::class);
        $mockStream->method('__toString')->willReturn('{}');

        $mockResponse = $this->createStub(Response::class);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $mockResponse->method('getStatusCode')->willReturn(429);
        $mockResponse->method('getReasonPhrase')->willReturn('Too Many Requests');
        $mockResponse->method('hasHeader')->willReturn(true);
        $mockResponse->method('getHeaderLine')->willReturn('45');

        $mockRequest = $this->createStub(Request::class);

        $exception = new RequestException(
            'Rate limit exceeded',
            $mockRequest,
            $mockResponse
        );

        $mockHttpClient->expects($this->once())
            ->method('request')
            ->willThrowException($exception);

        try {
            $apiClient->request('GET', 'tasks');
            $this->fail('Expected RateLimitException was not thrown');
        } catch (RateLimitException $e) {
            $this->assertSame(45, $e->getRetryAfter());
            $this->assertSame(429, $e->getCode());
        }
    }

    /**
     * Test logger receives debug calls during successful request.
     */
    public function testLoggerReceivesDebugCalls(): void
    {
        $mockLogger = $this->createMock(LoggerInterface::class);
        $mockLogger->expects($this->exactly(2))
            ->method('debug');

        $apiClient = new AsanaApiClient(fn() => 'test-token', $mockLogger);

        $mockHttpClient = $this->injectMockHttpClient($apiClient);

        $responseBody = ['data' => ['gid' => '12345']];

        $mockStream = $this->createStub(StreamInterface::class);
        $mockStream->method('__toString')->willReturn(json_encode($responseBody));

        $mockResponse = $this->createStub(Response::class);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $mockResponse->method('getStatusCode')->willReturn(200);

        $mockHttpClient->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse);

        $apiClient->request('GET', 'tasks/12345');
    }

    /**
     * Test logger receives error call on API failure.
     */
    public function testLoggerReceivesErrorOnFailure(): void
    {
        $mockLogger = $this->createMock(LoggerInterface::class);
        $mockLogger->expects($this->once())
            ->method('debug');
        $mockLogger->expects($this->once())
            ->method('error');

        $apiClient = new AsanaApiClient(fn() => 'test-token', $mockLogger);

        $mockHttpClient = $this->injectMockHttpClient($apiClient);

        $mockStream = $this->createStub(StreamInterface::class);
        $mockStream->method('__toString')->willReturn('Server Error');

        $mockResponse = $this->createStub(Response::class);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $mockResponse->method('getStatusCode')->willReturn(500);
        $mockResponse->method('getReasonPhrase')->willReturn('Internal Server Error');

        $mockRequest = $this->createStub(Request::class);

        $exception = new RequestException(
            'Server error',
            $mockRequest,
            $mockResponse
        );

        $mockHttpClient->expects($this->once())
            ->method('request')
            ->willThrowException($exception);

        $this->expectException(ApiException::class);

        $apiClient->request('GET', 'tasks');
    }

    /**
     * Test RESPONSE_FULL sanitizes options in output.
     */
    public function testResponseFullSanitizesOptions(): void
    {
        $responseBody = ['data' => ['gid' => '12345']];
        $mockHttpClient = $this->injectMockHttpClient();

        $mockStream = $this->createStub(StreamInterface::class);
        $mockStream->method('__toString')->willReturn(json_encode($responseBody));

        $mockResponse = $this->createStub(Response::class);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $mockResponse->method('getStatusCode')->willReturn(200);
        $mockResponse->method('getReasonPhrase')->willReturn('OK');
        $mockResponse->method('getHeaders')->willReturn([]);

        $mockHttpClient->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse);

        $optionsWithAuth = [
            'headers' => ['Authorization' => 'Bearer secret-token'],
            'query' => ['limit' => 10]
        ];

        $result = $this->apiClient->request(
            'GET',
            'tasks',
            $optionsWithAuth,
            HttpClientInterface::RESPONSE_FULL
        );

        // The options in the response should have Authorization redacted
        $this->assertSame('[REDACTED]', $result['request']['options']['headers']['Authorization']);
        $this->assertSame(['limit' => 10], $result['request']['options']['query']);
    }

    /**
     * Send a request through the real handler stack and return the outbound request.
     *
     * Replaces only the terminal handler, so the client's own middleware (auth and feature
     * flag headers) still runs.
     */
    private function captureOutboundRequest(AsanaApiClient $client): RequestInterface
    {
        $reflection = new ReflectionClass(AsanaApiClient::class);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        /** @var GuzzleClient $guzzle */
        $guzzle = $httpClientProperty->getValue($client);

        /** @var HandlerStack $stack */
        $stack = $guzzle->getConfig('handler');
        $stack->setHandler(new MockHandler([new Response(200, [], json_encode(['data' => []]))]));

        $captured = null;
        $stack->push(function (callable $handler) use (&$captured) {
            return function (RequestInterface $request, array $options) use ($handler, &$captured) {
                $captured = $request;

                return $handler($request, $options);
            };
        }, 'capture');

        $client->request('GET', 'tasks');

        $this->assertInstanceOf(RequestInterface::class, $captured);

        return $captured;
    }

    /**
     * Test an empty string query value is sent as a valueless parameter rather than dropped.
     *
     * Asana's `custom_type` filter uses `custom_type=` to mean "objects with no custom type".
     */
    public function testEmptyStringQueryValueIsPreserved(): void
    {
        $client = new AsanaApiClient(fn() => 'test-token');

        $reflection = new ReflectionClass(AsanaApiClient::class);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        /** @var GuzzleClient $guzzle */
        $guzzle = $httpClientProperty->getValue($client);

        /** @var HandlerStack $stack */
        $stack = $guzzle->getConfig('handler');
        $stack->setHandler(new MockHandler([new Response(200, [], json_encode(['data' => []]))]));

        $captured = null;
        $stack->push(function (callable $handler) use (&$captured) {
            return function (RequestInterface $request, array $options) use ($handler, &$captured) {
                $captured = $request;

                return $handler($request, $options);
            };
        }, 'capture');

        $client->request('GET', 'projects', ['query' => ['workspace' => '12345', 'custom_type' => '']]);

        $this->assertInstanceOf(RequestInterface::class, $captured);
        $this->assertSame('workspace=12345&custom_type=', $captured->getUri()->getQuery());
    }

    /**
     * Test feature flag constants are defined correctly.
     */
    public function testFeatureFlagConstants(): void
    {
        $this->assertSame('ai_teammate_actors', HttpClientInterface::FLAG_AI_TEAMMATE_ACTORS);
        $this->assertSame(
            'include_asana_created_custom_types',
            HttpClientInterface::FLAG_INCLUDE_ASANA_CREATED_CUSTOM_TYPES
        );
    }

    /**
     * Test no feature flag headers are sent when no flags are configured.
     */
    public function testNoFeatureFlagHeadersByDefault(): void
    {
        $request = $this->captureOutboundRequest(new AsanaApiClient(fn() => 'test-token'));

        $this->assertFalse($request->hasHeader('Asana-Enable'));
        $this->assertFalse($request->hasHeader('Asana-Disable'));
    }

    /**
     * Test enabled flags are sent as a comma-separated Asana-Enable header, without duplicates.
     */
    public function testEnableFeatureFlagSendsAsanaEnableHeader(): void
    {
        $client = new AsanaApiClient(fn() => 'test-token');
        $client->enableFeatureFlag('ai_teammate_actors')
            ->enableFeatureFlag(HttpClientInterface::FLAG_INCLUDE_ASANA_CREATED_CUSTOM_TYPES)
            ->enableFeatureFlag('ai_teammate_actors');

        $request = $this->captureOutboundRequest($client);

        $this->assertSame(
            'ai_teammate_actors,include_asana_created_custom_types',
            $request->getHeaderLine('Asana-Enable')
        );
        $this->assertFalse($request->hasHeader('Asana-Disable'));
    }

    /**
     * Test disabled flags are sent as a comma-separated Asana-Disable header, without duplicates.
     */
    public function testDisableFeatureFlagSendsAsanaDisableHeader(): void
    {
        $client = new AsanaApiClient(fn() => 'test-token');
        $client->disableFeatureFlag(HttpClientInterface::FLAG_INCLUDE_ASANA_CREATED_CUSTOM_TYPES)
            ->disableFeatureFlag(HttpClientInterface::FLAG_INCLUDE_ASANA_CREATED_CUSTOM_TYPES);

        $request = $this->captureOutboundRequest($client);

        $this->assertSame(
            'include_asana_created_custom_types',
            $request->getHeaderLine('Asana-Disable')
        );
        $this->assertFalse($request->hasHeader('Asana-Enable'));
    }

    /**
     * Test disabling a flag that is currently enabled moves it to the Asana-Disable header.
     */
    public function testDisableFeatureFlagOverridesPreviouslyEnabledFlag(): void
    {
        $client = new AsanaApiClient(fn() => 'test-token');
        $client->enableFeatureFlag(HttpClientInterface::FLAG_INCLUDE_ASANA_CREATED_CUSTOM_TYPES)
            ->enableFeatureFlag('ai_teammate_actors')
            ->disableFeatureFlag(HttpClientInterface::FLAG_INCLUDE_ASANA_CREATED_CUSTOM_TYPES);

        $request = $this->captureOutboundRequest($client);

        $this->assertSame('ai_teammate_actors', $request->getHeaderLine('Asana-Enable'));
        $this->assertSame(
            'include_asana_created_custom_types',
            $request->getHeaderLine('Asana-Disable')
        );
    }

    /**
     * Test enabling a flag that is currently disabled moves it back to the Asana-Enable header.
     */
    public function testEnableFeatureFlagOverridesPreviouslyDisabledFlag(): void
    {
        $client = new AsanaApiClient(fn() => 'test-token');
        $client->disableFeatureFlag(HttpClientInterface::FLAG_INCLUDE_ASANA_CREATED_CUSTOM_TYPES)
            ->enableFeatureFlag(HttpClientInterface::FLAG_INCLUDE_ASANA_CREATED_CUSTOM_TYPES);

        $request = $this->captureOutboundRequest($client);

        $this->assertSame(
            'include_asana_created_custom_types',
            $request->getHeaderLine('Asana-Enable')
        );
        $this->assertFalse($request->hasHeader('Asana-Disable'));
    }
}
