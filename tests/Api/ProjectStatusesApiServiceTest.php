<?php

namespace BrightleafDigital\Tests\Api;

use BrightleafDigital\Api\ProjectStatusesApiService;
use BrightleafDigital\Http\HttpClientInterface;
use BrightleafDigital\Exceptions\ValidationException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProjectStatusesApiServiceTest extends TestCase
{
    private HttpClientInterface $mockClient;

    /** @var ProjectStatusesApiService */
    private ProjectStatusesApiService $service;

    /** @var (HttpClientInterface&MockObject)|null */
    private $mockClientMock = null;

    protected function setUp(): void
    {
        $this->mockClient = $this->createStub(HttpClientInterface::class);
        $this->mockClientMock = null;
        $this->service = new ProjectStatusesApiService($this->mockClient);
    }

    /**
     * Test getProjectStatus calls client with correct parameters.
     */
    public function testGetProjectStatus(): void
    {
        $this->mockClient()->expects($this->once())
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
        $this->mockClient()->expects($this->once())
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
        $this->mockClient()->expects($this->once())
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

        $this->mockClient()->expects($this->once())
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

    /**
     * @return HttpClientInterface&MockObject
     */
    private function mockClient(): HttpClientInterface
    {
        if ($this->mockClientMock === null) {
            $this->mockClientMock = $this->createMock(HttpClientInterface::class);
            $this->mockClient = $this->mockClientMock;
            $serviceClass = $this->service::class;
            $this->service = new $serviceClass($this->mockClient);
        }

        return $this->mockClientMock;
    }
}
