<?php

namespace BrightleafDigital\Tests\Api;

use BrightleafDigital\Api\ProjectStatusesApiService;
use BrightleafDigital\Http\HttpClientInterface;
use BrightleafDigital\Exceptions\ValidationException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProjectStatusesApiServiceTest extends TestCase
{
    /** @var HttpClientInterface&MockObject */
    private $mockClient;

    /** @var ProjectStatusesApiService */
    private ProjectStatusesApiService $service;

    protected function setUp(): void
    {
        $this->mockClient = $this->createMock(HttpClientInterface::class);
        $this->service = new ProjectStatusesApiService($this->mockClient);
    }

    /**
     * Test getProjectStatus calls client with correct parameters.
     */
    public function testGetProjectStatus(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'project_statuses/12345', ['query' => []], HttpClientInterface::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getProjectStatus('12345');
    }

    /**
     * Test deleteProjectStatus calls client with correct parameters.
     */
    public function testDeleteProjectStatus(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('DELETE', 'project_statuses/12345', [], HttpClientInterface::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->deleteProjectStatus('12345');
    }

    /**
     * Test getProjectStatusesForProject calls client with correct parameters.
     */
    public function testGetProjectStatusesForProject(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'projects/67890/project_statuses', ['query' => []], HttpClientInterface::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getProjectStatusesForProject('67890');
    }

    /**
     * Test createProjectStatusForProject calls client with correct parameters.
     */
    public function testCreateProjectStatusForProject(): void
    {
        $data = ['color' => 'green', 'text' => 'On track'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'projects/67890/project_statuses',
                ['json' => ['data' => $data], 'query' => []],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->createProjectStatusForProject('67890', $data);
    }

    /**
     * Test createProjectStatusForProject throws validation exception if required fields are missing.
     */
    public function testCreateProjectStatusForProjectValidation(): void
    {
        $this->expectException(ValidationException::class);
        $this->service->createProjectStatusForProject('67890', ['text' => 'Missing color']);
    }

    /**
     * Test validation of GID.
     */
    public function testInvalidGidThrowsException(): void
    {
        $this->expectException(ValidationException::class);
        $this->service->getProjectStatus('');
    }
}
