<?php

namespace BrightleafDigital\Tests\Api;

use BrightleafDigital\Api\ReactionsApiService;
use BrightleafDigital\Http\HttpClientInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReactionsApiServiceTest extends TestCase
{
    /** @var HttpClientInterface&MockObject */
    private $httpClient;
    private ReactionsApiService $service;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->service = new ReactionsApiService($this->httpClient);
    }

    public function testGetReactionsOnObject(): void
    {
        $target = '159874';
        $emojiBase = '👍';
        $options = [];
        $expectedResponse = ['data' => []];

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'reactions',
                ['query' => ['target' => $target, 'emoji_base' => $emojiBase]],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->getReactionsOnObject($target, $emojiBase, $options);
        $this->assertEquals($expectedResponse, $result);
    }
}
