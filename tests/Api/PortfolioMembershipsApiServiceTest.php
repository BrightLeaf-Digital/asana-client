<?php

namespace BrightleafDigital\Tests\Api;

use BrightleafDigital\Api\PortfolioMembershipsApiService;
use BrightleafDigital\Http\HttpClientInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PortfolioMembershipsApiServiceTest extends TestCase
{
    /** @var HttpClientInterface&MockObject */
    private $mockClient;

    private PortfolioMembershipsApiService $service;

    protected function setUp(): void
    {
        $this->mockClient = $this->createMock(HttpClientInterface::class);
        $this->service = new PortfolioMembershipsApiService($this->mockClient);
    }

    public function testGetPortfolioMemberships(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'portfolio_memberships', ['query' => []], HttpClientInterface::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getPortfolioMemberships();
    }

    public function testGetPortfolioMembership(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'portfolio_memberships/12345', ['query' => []], HttpClientInterface::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getPortfolioMembership('12345');
    }

    public function testGetPortfolioMembershipsForPortfolio(): void
    {
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'portfolios/67890/portfolio_memberships', ['query' => []], HttpClientInterface::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getPortfolioMembershipsForPortfolio('67890');
    }

    public function testGetPortfolioMembershipsWithOptions(): void
    {
        $options = ['user' => 'me', 'limit' => 10];
        $this->mockClient->expects($this->once())
            ->method('request')
            ->with('GET', 'portfolio_memberships', ['query' => $options], HttpClientInterface::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getPortfolioMemberships($options);
    }
}
