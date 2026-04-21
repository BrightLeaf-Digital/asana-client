<?php

namespace BrightleafDigital\Tests\Api;

use BrightleafDigital\Api\RulesApiService;
use BrightleafDigital\Http\HttpClientInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RulesApiServiceTest extends TestCase
{
    private HttpClientInterface $mockClient;

    /** @var RulesApiService */
    private RulesApiService $service;

    /** @var (HttpClientInterface&MockObject)|null */
    private $mockClientMock = null;

    protected function setUp(): void
    {
        $this->mockClient = $this->createStub(HttpClientInterface::class);
        $this->mockClientMock = null;
        $this->service = new RulesApiService($this->mockClient);
    }

    /**
     * Test triggerRule calls client with correct parameters.
     */
    public function testTriggerRule(): void
    {
        $ruleTriggerGid = '12345';
        $data = ['variable_name' => 'value'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                "rule_triggers/$ruleTriggerGid/run",
                ['json' => ['data' => $data], 'query' => []],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->triggerRule($ruleTriggerGid, $data);
    }

    /**
     * Test triggerRule with options.
     */
    public function testTriggerRuleWithOptions(): void
    {
        $ruleTriggerGid = '12345';
        $data = ['variable_name' => 'value'];
        $options = ['opt_pretty' => 'true'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                "rule_triggers/$ruleTriggerGid/run",
                ['json' => ['data' => $data], 'query' => $options],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->triggerRule($ruleTriggerGid, $data, $options);
    }

    /**
     * Test triggerRule with custom response type.
     */
    public function testTriggerRuleWithCustomResponseType(): void
    {
        $ruleTriggerGid = '12345';
        $data = ['variable_name' => 'value'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                "rule_triggers/$ruleTriggerGid/run",
                ['json' => ['data' => $data], 'query' => []],
                HttpClientInterface::RESPONSE_FULL
            )
            ->willReturn([]);

        $this->service->triggerRule($ruleTriggerGid, $data, [], HttpClientInterface::RESPONSE_FULL);
    }

    /**
     * Test validation of Rule Trigger GID.
     */
    public function testTriggerRuleWithInvalidGid(): void
    {
        $this->expectException(\BrightleafDigital\Exceptions\ValidationException::class);
        $this->service->triggerRule('', []);
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
