<?php

namespace BrightleafDigital\Tests\Api;

use BrightleafDigital\Api\StoriesApiService;
use BrightleafDigital\Exceptions\ValidationException;
use BrightleafDigital\Http\HttpClientInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StoriesApiServiceTest extends TestCase
{
    private HttpClientInterface $mockClient;

    /** @var StoriesApiService */
    private StoriesApiService $service;

    /** @var (HttpClientInterface&MockObject)|null */
    private $mockClientMock = null;

    protected function setUp(): void
    {
        $this->mockClient = $this->createStub(HttpClientInterface::class);
        $this->mockClientMock = null;
        $this->service = new StoriesApiService($this->mockClient);
    }

    /**
     * Test getStory calls client with correct parameters.
     */
    public function testGetStory(): void
    {
        $this->mockClient()->expects($this->once())
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

        $this->mockClient()->expects($this->once())
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
        $this->mockClient()->expects($this->once())
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
        $this->mockClient()->expects($this->once())
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

        $this->mockClient()->expects($this->once())
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
     * Test getStoriesForRecord calls client with correct parameters.
     */
    public function testGetStoriesForRecord(): void
    {
        $options = ['resource_subtype' => 'comment_added', 'sort_ascending' => false];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with('GET', 'records/67890/stories', ['query' => $options], HttpClientInterface::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getStoriesForRecord('67890', $options);
    }

    /**
     * Test createStoryForRecord calls client with correct parameters.
     */
    public function testCreateStoryForRecord(): void
    {
        $data = ['text' => 'New comment'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'records/67890/stories',
                ['json' => ['data' => $data], 'query' => []],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->createStoryForRecord('67890', $data);
    }

    /**
     * Test getStoriesForRecord rejects a non-numeric GID.
     */
    public function testGetStoriesForRecordThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Record GID must be a numeric string.');

        $this->service->getStoriesForRecord('abc');
    }

    /**
     * Test createStoryForRecord requires text or html_text.
     */
    public function testCreateStoryForRecordRequiresText(): void
    {
        $this->expectException(ValidationException::class);

        $this->service->createStoryForRecord('67890', ['is_pinned' => true]);
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
