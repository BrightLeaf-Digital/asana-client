<?php

namespace BrightleafDigital\Tests\Api;

use BrightleafDigital\Api\StoriesApiService;
use BrightleafDigital\Http\HttpClientInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StoriesApiServiceTest extends TestCase
{
    /** @var HttpClientInterface&MockObject */
    private $mockClient;

    /** @var StoriesApiService */
    private StoriesApiService $service;

    protected function setUp(): void
    {
        $this->mockClient = $this->createMock(HttpClientInterface::class);
        $this->service = new StoriesApiService($this->mockClient);
    }

    /**
     * Test getStory calls client with correct parameters.
     */
    public function testGetStory(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'stories/12345', ['query' => []], HttpClientInterface::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getStory('12345');
    }

    /**
     * Test updateStory calls client with correct parameters.
     */
    public function testUpdateStory(): void
    {
        $data = ['text' => 'Updated comment'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'PUT',
                'stories/12345',
                ['json' => ['data' => $data], 'query' => []],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->updateStory('12345', $data);
    }

    /**
     * Test deleteStory calls client with correct parameters.
     */
    public function testDeleteStory(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('DELETE', 'stories/12345', [], HttpClientInterface::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->deleteStory('12345');
    }

    /**
     * Test getStoriesForTask calls client with correct parameters.
     */
    public function testGetStoriesForTask(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'tasks/67890/stories', ['query' => []], HttpClientInterface::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getStoriesForTask('67890');
    }

    /**
     * Test createStoryForTask calls client with correct parameters.
     */
    public function testCreateStoryForTask(): void
    {
        $data = ['text' => 'New comment'];

        $this->mockClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'tasks/67890/stories',
                ['json' => ['data' => $data], 'query' => []],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->createStoryForTask('67890', $data);
    }

    /**
     * Test validation fails for empty GID.
     */
    public function testGetStoryWithEmptyGid(): void
    {
        $this->expectException(\BrightleafDigital\Exceptions\ValidationException::class);
        $this->service->getStory('');
    }

    /**
     * Test createStoryForTask fails without text or html_text.
     */
    public function testCreateStoryWithoutContent(): void
    {
        $this->expectException(\BrightleafDigital\Exceptions\ValidationException::class);
        $this->service->createStoryForTask('67890', ['is_pinned' => true]);
    }
}
