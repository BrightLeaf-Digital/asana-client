<?php

namespace BrightleafDigital\Tests\Api;

use BrightleafDigital\Api\TaskTemplatesApiService;
use BrightleafDigital\Http\HttpClientInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TaskTemplatesApiServiceTest extends TestCase
{
    /** @var HttpClientInterface&MockObject */
    private $httpClient;

    private TaskTemplatesApiService $service;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->service = new TaskTemplatesApiService($this->httpClient);
    }

    public function testGetTaskTemplates(): void
    {
        $projectGid = '321654';
        $expectedResponse = ['data' => []];

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', 'task_templates', ['query' => ['project' => $projectGid]], HttpClientInterface::RESPONSE_DATA)
            ->willReturn($expectedResponse);

        $result = $this->service->getTaskTemplates($projectGid);
        $this->assertEquals($expectedResponse, $result);
    }

    public function testGetTaskTemplate(): void
    {
        $taskTemplateGid = '1331';
        $expectedResponse = ['data' => []];

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', 'task_templates/' . $taskTemplateGid, ['query' => []], HttpClientInterface::RESPONSE_DATA)
            ->willReturn($expectedResponse);

        $result = $this->service->getTaskTemplate($taskTemplateGid);
        $this->assertEquals($expectedResponse, $result);
    }

    public function testDeleteTaskTemplate(): void
    {
        $taskTemplateGid = '1331';
        $expectedResponse = ['data' => []];

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('DELETE', 'task_templates/' . $taskTemplateGid, [], HttpClientInterface::RESPONSE_DATA)
            ->willReturn($expectedResponse);

        $result = $this->service->deleteTaskTemplate($taskTemplateGid);
        $this->assertEquals($expectedResponse, $result);
    }

    public function testInstantiateTask(): void
    {
        $taskTemplateGid = '1331';
        $name = 'New Task';
        $options = ['opt_fields' => 'status'];
        $expectedResponse = ['data' => ['status' => 'queued']];

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'task_templates/' . $taskTemplateGid . '/instantiateTask',
                ['json' => ['data' => ['name' => $name]], 'query' => $options],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->instantiateTask($taskTemplateGid, $name, $options);
        $this->assertEquals($expectedResponse, $result);
    }
}
