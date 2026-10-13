<?php

namespace BrightleafDigital\Tests\Api;

use ArrayObject;
use BrightleafDigital\AsanaClient;
use BrightleafDigital\Exceptions\ApiException;
use BrightleafDigital\Http\AsanaApiClient;
use BrightleafDigital\Http\HttpClientInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use ReflectionProperty;

/**
 * Exercise service calls through the real HTTP stack, replacing only the network handler.
 * These tests verify the client contract, not Asana's HTML validation or synchronization.
 *
 * @phpstan-type HistoryEntry array{
 *     request: RequestInterface, response: ResponseInterface|null, error: mixed, options: array<mixed>
 * }
 */
class RichTextCustomFieldsTest extends TestCase
{
    #[DataProvider('writeEndpoints')]
    public function testWritesPreserveRichTextAndPlainTextFields(
        string $service,
        string $operation,
        array $arguments,
        string $method,
        string $path
    ): void {
        $html = '<body><strong>Résumé &amp; release</strong> '
            . '<a href="https://example.com/?a=1&amp;b=2">Details</a></body>';
        $data = [
            'name' => 'Release',
            'custom_fields' => ['22222' => '<strong>Keep this as plain text</strong>', '33333' => 42],
            'html_custom_fields' => ['11111' => $html, '44444' => '<body></body>'],
        ];
        $options = ['opt_fields' => 'custom_fields.html_text_value,custom_fields.text_value'];
        $history = $this->newHistory();
        $client = $this->clientWithResponse(new Response(200, [], '{"data":{"gid":"99999"}}'), $history);

        $result = $client->$service()->$operation(...array_merge($arguments, [$data, $options]));

        $this->assertSame(['gid' => '99999'], $result);
        $this->assertCount(1, $history);
        $request = $history[0]['request'];
        $this->assertSame($method, $request->getMethod());
        $this->assertSame('/api/1.0/' . $path, $request->getUri()->getPath());
        $this->assertSame(['data' => $data], json_decode((string) $request->getBody(), true, 512, JSON_THROW_ON_ERROR));
        parse_str($request->getUri()->getQuery(), $query);
        $this->assertSame($options, $query);
    }

    public static function writeEndpoints(): array
    {
        return [
            'create task' => ['tasks', 'createTask', [], 'POST', 'tasks'],
            'update task' => ['tasks', 'updateTask', ['12345'], 'PUT', 'tasks/12345'],
            'create subtask' => ['tasks', 'createSubtaskForTask', ['12345'], 'POST', 'tasks/12345/subtasks'],
            'create project' => ['projects', 'createProject', [], 'POST', 'projects'],
            'update project' => ['projects', 'updateProject', ['12345'], 'PUT', 'projects/12345'],
            'create team project' => ['projects', 'createProjectInTeam', ['12345'], 'POST', 'teams/12345/projects'],
            'create workspace project' => [
                'projects', 'createProjectInWorkspace', ['12345'], 'POST', 'workspaces/12345/projects',
            ],
            'update portfolio' => ['portfolios', 'updatePortfolio', ['12345'], 'PUT', 'portfolios/12345'],
            'update goal' => ['goals', 'updateGoal', ['12345'], 'PUT', 'goals/12345'],
        ];
    }

    #[DataProvider('readEndpoints')]
    public function testReadsPreserveHtmlNullAndAbsentValues(
        string $service,
        string $operation,
        array $arguments,
        string $path,
        array $options,
        bool $collection,
        bool $settings,
        int $responseType
    ): void {
        $fields = [
            ['gid' => '11111', 'resource_subtype' => 'text', 'text_value' => 'Hello & goodbye',
                'html_text_value' => '<body><strong>Hello</strong> &amp; goodbye</body>',
                'display_value' => 'Hello & goodbye'],
            ['gid' => '22222', 'resource_subtype' => 'text', 'text_value' => null, 'html_text_value' => null],
            ['gid' => '33333', 'resource_subtype' => 'number', 'number_value' => 42],
        ];
        $resource = ['gid' => '12345'];
        if ($settings) {
            $resource['custom_field_settings'] = array_map(fn(array $field) => ['custom_field' => $field], $fields);
        } else {
            $resource['custom_fields'] = $fields;
        }
        $body = ['data' => $collection ? [$resource] : $resource];
        $encoded = json_encode($body, JSON_THROW_ON_ERROR);
        $history = $this->newHistory();
        $client = $this->clientWithResponse(new Response(200, [], $encoded), $history);

        $result = $client->$service()->$operation(...array_merge($arguments, [$responseType]));

        if ($responseType === HttpClientInterface::RESPONSE_FULL) {
            $this->assertSame($body, $result['body']);
            $this->assertSame($encoded, $result['raw_body']);
        } else {
            $this->assertSame($responseType === HttpClientInterface::RESPONSE_DATA ? $body['data'] : $body, $result);
        }
        $this->assertCount(1, $history);
        $request = $history[0]['request'];
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('/api/1.0/' . $path, $request->getUri()->getPath());
        parse_str($request->getUri()->getQuery(), $query);
        $this->assertSame($options, $query);
    }

    public static function readEndpoints(): array
    {
        $html = ['opt_fields' => 'custom_fields.html_text_value,custom_fields.text_value'];
        $endpoints = [
            'task default' => ['tasks', 'getTask', ['12345', []], 'tasks/12345', [], false, false],
            'task custom ID default' => [
                'tasks', 'getTaskByCustomId', ['12345', 'REL-1'],
                'workspaces/12345/tasks/custom_id/REL-1', [], false, false,
            ],
            'task list' => ['tasks', 'getTasks', [$html + ['project' => '12345']], 'tasks',
                $html + ['project' => '12345'], true, false],
            'project tasks' => ['tasks', 'getTasksByProject', ['12345', $html], 'projects/12345/tasks',
                $html, true, false],
            'section tasks' => ['tasks', 'getTasksBySection', ['12345', $html], 'sections/12345/tasks',
                $html, true, false],
            'tag tasks' => ['tasks', 'getTasksByTag', ['12345', $html], 'tags/12345/tasks',
                $html, true, false],
        ];
        // Preserve both documented paths without claiming that the server accepts either one.
        $resourceReads = ['projects' => 'getProject', 'portfolios' => 'getPortfolio', 'goals' => 'getGoal'];
        foreach ($resourceReads as $service => $op) {
            foreach ([false, true] as $settings) {
                $options = $settings
                    ? ['opt_fields' => 'custom_field_settings.custom_field.html_text_value']
                    : $html;
                $name = $service . ($settings ? ' spec path' : ' announcement path');
                $endpoints[$name] = [$service, $op, ['12345', $options], $service . '/12345',
                    $options, false, $settings];
            }
        }
        $cases = [];
        foreach ($endpoints as $name => $endpoint) {
            foreach (
                [HttpClientInterface::RESPONSE_DATA, HttpClientInterface::RESPONSE_NORMAL,
                    HttpClientInterface::RESPONSE_FULL] as $type
            ) {
                $cases[$name . ' response ' . $type] = array_merge($endpoint, [$type]);
            }
        }
        return $cases;
    }

    #[DataProvider('richTextErrors')]
    public function testRichTextErrorsPreserveServerDetails(string $html, string $message): void
    {
        $body = ['errors' => [['message' => $message]]];
        $history = $this->newHistory();
        $client = $this->clientWithResponse(new Response(400, [], json_encode($body, JSON_THROW_ON_ERROR)), $history);
        $data = ['html_custom_fields' => ['11111' => $html]];

        try {
            $client->tasks()->updateTask('12345', $data);
            $this->fail('Expected a rich text API error');
        } catch (ApiException $e) {
            $this->assertSame(400, $e->getCode());
            $this->assertSame($body, $e->getResponseData());
            $this->assertStringContainsString($message, $e->getMessage());
            $this->assertSame($data, $e->getRequestData()['options']['json']['data']);
            $this->assertCount(1, $history);
        }
    }

    public static function richTextErrors(): array
    {
        return [
            'missing body' => ['<strong>Hello</strong>',
                'Rich text custom field value must be wrapped in <body> tags.'],
            'invalid HTML' => ['<body><unsupported>Hello</unsupported></body>',
                'Unable to parse rich text HTML. The value contains invalid or unsupported HTML elements.'],
            'anchor attribute' => ['<body><a href="https://example.com" unsupported="true">Hello</a></body>',
                'Rich text HTML contains an unsupported attribute on an anchor tag.'],
            'non-text field' => ['<body>Hello</body>', 'Custom field 11111 does not support rich text content.'],
        ];
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
    private function clientWithResponse(Response $response, ArrayObject $history): AsanaClient
    {
        $client = AsanaClient::withPAT('test-token', false);
        $http = $client->getContainer()->get(HttpClientInterface::class);
        $property = new ReflectionProperty(AsanaApiClient::class, 'httpClient');
        /** @var Client $guzzle */
        $guzzle = $property->getValue($http);
        $stack = $guzzle->getConfig('handler');
        $this->assertInstanceOf(HandlerStack::class, $stack);
        $stack->setHandler(new MockHandler([$response]));
        $stack->push(Middleware::history($history));
        return $client;
    }
}
