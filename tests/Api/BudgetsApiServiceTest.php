<?php

namespace BrightleafDigital\Tests\Api;

use BrightleafDigital\Api\BudgetsApiService;
use BrightleafDigital\Http\HttpClientInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BudgetsApiServiceTest extends TestCase
{
    /** @var HttpClientInterface&MockObject */
    private $httpClient;
    private BudgetsApiService $service;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->service = new BudgetsApiService($this->httpClient);
    }

    public function testGetBudgets(): void
    {
        $parent = '12345';
        $options = [];
        $expectedResponse = ['data' => []];

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', 'budgets', ['query' => ['parent' => $parent]], HttpClientInterface::RESPONSE_DATA)
            ->willReturn($expectedResponse);

        $result = $this->service->getBudgets($parent, $options);
        $this->assertEquals($expectedResponse, $result);
    }

    public function testCreateBudget(): void
    {
        $data = ['parent' => '12345', 'budget_type' => 'effort'];
        $expectedResponse = ['data' => []];

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('POST', 'budgets', ['json' => ['data' => $data], 'query' => []], HttpClientInterface::RESPONSE_DATA)
            ->willReturn($expectedResponse);

        $result = $this->service->createBudget($data);
        $this->assertEquals($expectedResponse, $result);
    }

    public function testGetBudget(): void
    {
        $budgetGid = '12345';
        $expectedResponse = ['data' => []];

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', 'budgets/' . $budgetGid, ['query' => []], HttpClientInterface::RESPONSE_DATA)
            ->willReturn($expectedResponse);

        $result = $this->service->getBudget($budgetGid);
        $this->assertEquals($expectedResponse, $result);
    }

    public function testUpdateBudget(): void
    {
        $budgetGid = '12345';
        $data = ['total' => ['value' => 100]];
        $expectedResponse = ['data' => []];

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with(
                'PUT',
                'budgets/' . $budgetGid,
                ['json' => ['data' => $data], 'query' => []],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->updateBudget($budgetGid, $data);
        $this->assertEquals($expectedResponse, $result);
    }

    public function testDeleteBudget(): void
    {
        $budgetGid = '12345';
        $expectedResponse = ['data' => []];

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('DELETE', 'budgets/' . $budgetGid, [], HttpClientInterface::RESPONSE_DATA)
            ->willReturn($expectedResponse);

        $result = $this->service->deleteBudget($budgetGid);
        $this->assertEquals($expectedResponse, $result);
    }
}
