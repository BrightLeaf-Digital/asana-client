<?php

namespace BrightleafDigital\Tests\Api;

use ArrayObject;
use BrightleafDigital\AsanaClient;
use BrightleafDigital\Exceptions\ApiException;
use BrightleafDigital\Http\AsanaApiClient;
use BrightleafDigital\Http\HttpClientInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use ReflectionProperty;

/**
 * @phpstan-type HistoryEntry array{
 *     request: RequestInterface, response: ResponseInterface|null, error: mixed, options: array<mixed>
 * }
 */
class ScimApiServiceTest extends TestCase
{
    #[DataProvider('listRequests')]
    public function testListsPreserveScimEnvelopeAndQuery(
        string $method,
        string $endpoint,
        array $options,
        array $metadata
    ): void {
        $body = [
            'schemas' => ['urn:ietf:params:scim:api:messages:2.0:ListResponse'],
            'Resources' => [['id' => '12345']],
            'totalResults' => 5400,
        ] + $metadata;
        $history = $this->newHistory();
        $client = $this->clientWithResponses([new Response(200, [], json_encode($body))], $history);

        $result = $client->scim()->$method($options);

        $this->assertSame($body, $result);
        $this->assertCount(1, $history);
        $request = $history[0]['request'];
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('/api/1.0/scim/' . $endpoint, $request->getUri()->getPath());
        $this->assertSame('Bearer service-account-test-token', $request->getHeaderLine('Authorization'));
        parse_str($request->getUri()->getQuery(), $query);
        $this->assertSame(array_map('strval', $options), $query);
        $this->assertSame($client->scim(), $client->scim());
    }

    public static function listRequests(): array
    {
        $cases = [];
        foreach (['Users' => 'getUsers', 'Groups' => 'getGroups'] as $endpoint => $method) {
            $cases[$endpoint . ' unpaged'] = [$method, $endpoint, [], []];
            $cases[$endpoint . ' index'] = [
                $method, $endpoint, ['startIndex' => 1, 'count' => 2],
                ['startIndex' => 1, 'itemsPerPage' => 2, 'nextCursor' => 'opaque+/='],
            ];
            $cases[$endpoint . ' count only'] = [
                $method, $endpoint, ['count' => 2],
                ['startIndex' => 1, 'itemsPerPage' => 2, 'nextCursor' => 'opaque+/='],
            ];
            $cases[$endpoint . ' cursor'] = [
                $method, $endpoint, ['cursor' => 'opaque+/=', 'startIndex' => 999, 'count' => 2],
                ['itemsPerPage' => 1],
            ];
            $cases[$endpoint . ' server clamps'] = [
                $method, $endpoint, ['startIndex' => 0, 'count' => 1001],
                ['startIndex' => 1, 'itemsPerPage' => 1000],
            ];
            $filter = $endpoint === 'Users' ? 'userName eq "user@example.com"' : 'displayName eq "Engineering"';
            $cases[$endpoint . ' equality filter'] = [$method, $endpoint, ['filter' => $filter, 'count' => 2], []];
        }
        return $cases;
    }

    public function testCursorCanBeUsedToReadNextPage(): void
    {
        $first = ['Resources' => [['id' => '1']], 'totalResults' => 2,
            'startIndex' => 1, 'itemsPerPage' => 1, 'nextCursor' => 'opaque+/='];
        $last = ['Resources' => [['id' => '2']], 'totalResults' => 2, 'itemsPerPage' => 1];
        $history = $this->newHistory();
        $client = $this->clientWithResponses([
            new Response(200, [], json_encode($first)),
            new Response(200, [], json_encode($last)),
        ], $history);

        $page = $client->scim()->getUsers(['count' => 1]);
        $next = $client->scim()->getUsers(['cursor' => $page['nextCursor'], 'count' => 1]);

        $this->assertSame($last, $next);
        parse_str($history[1]['request']->getUri()->getQuery(), $query);
        $this->assertSame(['cursor' => 'opaque+/=', 'count' => '1'], $query);
    }

    #[DataProvider('responseTypes')]
    public function testExplicitResponseTypes(string $method, int $responseType): void
    {
        $body = ['Resources' => [], 'totalResults' => 0];
        $history = $this->newHistory();
        $client = $this->clientWithResponses([new Response(200, [], json_encode($body))], $history);

        $result = $client->scim()->$method([], $responseType);

        if ($responseType === HttpClientInterface::RESPONSE_FULL) {
            $this->assertSame(200, $result['status']);
            $this->assertSame($body, $result['body']);
        } else {
            $this->assertSame($body, $result);
        }
    }

    public static function responseTypes(): array
    {
        return [
            ['getUsers', HttpClientInterface::RESPONSE_FULL],
            ['getGroups', HttpClientInterface::RESPONSE_FULL],
            ['getUsers', HttpClientInterface::RESPONSE_DATA],
            ['getGroups', HttpClientInterface::RESPONSE_DATA],
        ];
    }

    #[DataProvider('invalidPagination')]
    public function testPaginationErrorsPreserveScimDetails(string $method, array $options, string $scimType): void
    {
        $body = [
            'schemas' => ['urn:ietf:params:scim:api:messages:2.0:Error'],
            'status' => '400',
            'scimType' => $scimType,
            'detail' => 'Invalid pagination parameter',
        ];
        $history = $this->newHistory();
        $client = $this->clientWithResponses([new Response(400, [], json_encode($body))], $history);

        try {
            $client->scim()->$method($options);
            $this->fail('Expected a pagination error');
        } catch (ApiException $e) {
            $this->assertSame(400, $e->getCode());
            $this->assertSame($body, $e->getResponseData());
            $this->assertSame($body['detail'], $e->getMessage());
            $this->assertSame($options, $e->getRequestData()['options']['query']);
            $this->assertCount(1, $history);
        }
    }

    public static function invalidPagination(): array
    {
        $cases = [];
        foreach (['getUsers', 'getGroups'] as $method) {
            $cases[] = [$method, ['cursor' => 'expired'], 'invalidCursor'];
            $cases[] = [$method, ['startIndex' => 'not-an-integer'], 'invalidValue'];
            $cases[] = [$method, ['count' => 'not-an-integer'], 'invalidValue'];
        }
        return $cases;
    }

    /**
     * @return ArrayObject<int, HistoryEntry>
     */
    private function newHistory(): ArrayObject
    {
        return new ArrayObject();
    }

    /**
     * @param ArrayObject<int, HistoryEntry> $history
     */
    private function clientWithResponses(array $responses, ArrayObject $history): AsanaClient
    {
        $client = AsanaClient::withPAT('service-account-test-token', false);
        $http = $client->getContainer()->get(HttpClientInterface::class);
        $property = new ReflectionProperty(AsanaApiClient::class, 'httpClient');
        /** @var Client $guzzle */
        $guzzle = $property->getValue($http);
        $stack = $guzzle->getConfig('handler');
        $stack->setHandler(new MockHandler($responses));
        $stack->push(Middleware::history($history));
        return $client;
    }
}
