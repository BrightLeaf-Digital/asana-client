<?php

namespace BrightleafDigital\Tests\Api;

use BrightleafDigital\Api\ProjectPortfolioSettingsApiService;
use BrightleafDigital\Exceptions\ValidationException;
use BrightleafDigital\Http\HttpClientInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProjectPortfolioSettingsApiServiceTest extends TestCase
{
    private HttpClientInterface $mockClient;

    /** @var ProjectPortfolioSettingsApiService */
    private ProjectPortfolioSettingsApiService $service;

    /** @var (HttpClientInterface&MockObject)|null */
    private $mockClientMock = null;

    protected function setUp(): void
    {
        $this->mockClient = $this->createStub(HttpClientInterface::class);
        $this->mockClientMock = null;
        $this->service = new ProjectPortfolioSettingsApiService($this->mockClient);
    }

    // ── getProjectPortfolioSetting ────────────────────────────────────────

    /**
     * Test getProjectPortfolioSetting calls client with correct parameters.
     */
    public function testGetProjectPortfolioSetting(): void
    {
        $expectedResponse = [
            'gid' => '12345',
            'resource_type' => 'project_portfolio_setting',
            'is_access_control_inherited' => false,
        ];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'project_portfolio_settings/12345',
                ['query' => []],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->getProjectPortfolioSetting('12345');

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test getProjectPortfolioSetting with options.
     */
    public function testGetProjectPortfolioSettingWithOptions(): void
    {
        $options = ['opt_fields' => 'is_access_control_inherited,project,portfolio'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'project_portfolio_settings/12345',
                ['query' => $options],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->getProjectPortfolioSetting('12345', $options);
    }

    /**
     * Test getProjectPortfolioSetting with custom response type.
     */
    public function testGetProjectPortfolioSettingWithCustomResponseType(): void
    {
        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'project_portfolio_settings/12345',
                ['query' => []],
                HttpClientInterface::RESPONSE_FULL
            )
            ->willReturn([]);

        $this->service->getProjectPortfolioSetting('12345', [], HttpClientInterface::RESPONSE_FULL);
    }

    /**
     * Test getProjectPortfolioSetting throws exception for empty GID.
     */
    public function testGetProjectPortfolioSettingThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Project Portfolio Setting GID must be a non-empty string.');

        $this->service->getProjectPortfolioSetting('');
    }

    /**
     * Test getProjectPortfolioSetting throws exception for non-numeric GID.
     */
    public function testGetProjectPortfolioSettingThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Project Portfolio Setting GID must be a numeric string.');

        $this->service->getProjectPortfolioSetting('abc');
    }

    // ── updateProjectPortfolioSetting ─────────────────────────────────────

    /**
     * Test updateProjectPortfolioSetting calls client with correct parameters.
     */
    public function testUpdateProjectPortfolioSetting(): void
    {
        $data = ['is_access_control_inherited' => true];
        $expectedResponse = [
            'gid' => '12345',
            'resource_type' => 'project_portfolio_setting',
            'is_access_control_inherited' => true,
        ];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'PUT',
                'project_portfolio_settings/12345',
                ['json' => ['data' => $data], 'query' => []],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->updateProjectPortfolioSetting('12345', $data);

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test updateProjectPortfolioSetting with options.
     */
    public function testUpdateProjectPortfolioSettingWithOptions(): void
    {
        $data = ['is_access_control_inherited' => false];
        $options = ['opt_fields' => 'is_access_control_inherited,project,portfolio'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'PUT',
                'project_portfolio_settings/12345',
                ['json' => ['data' => $data], 'query' => $options],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->updateProjectPortfolioSetting('12345', $data, $options);
    }

    /**
     * Test updateProjectPortfolioSetting with custom response type.
     */
    public function testUpdateProjectPortfolioSettingWithCustomResponseType(): void
    {
        $data = ['is_access_control_inherited' => true];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'PUT',
                'project_portfolio_settings/12345',
                ['json' => ['data' => $data], 'query' => []],
                HttpClientInterface::RESPONSE_FULL
            )
            ->willReturn([]);

        $this->service->updateProjectPortfolioSetting('12345', $data, [], HttpClientInterface::RESPONSE_FULL);
    }

    /**
     * Test updateProjectPortfolioSetting throws exception for empty GID.
     */
    public function testUpdateProjectPortfolioSettingThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Project Portfolio Setting GID must be a non-empty string.');

        $this->service->updateProjectPortfolioSetting('', ['is_access_control_inherited' => true]);
    }

    /**
     * Test updateProjectPortfolioSetting throws exception for non-numeric GID.
     */
    public function testUpdateProjectPortfolioSettingThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Project Portfolio Setting GID must be a numeric string.');

        $this->service->updateProjectPortfolioSetting('abc', ['is_access_control_inherited' => true]);
    }

    // ── getProjectPortfolioSettingsForProject ─────────────────────────────

    /**
     * Test getProjectPortfolioSettingsForProject calls client with correct parameters.
     */
    public function testGetProjectPortfolioSettingsForProject(): void
    {
        $expectedResponse = [
            ['gid' => '111', 'resource_type' => 'project_portfolio_setting'],
            ['gid' => '222', 'resource_type' => 'project_portfolio_setting'],
        ];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'projects/12345/project_portfolio_settings',
                ['query' => []],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->getProjectPortfolioSettingsForProject('12345');

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test getProjectPortfolioSettingsForProject with options.
     */
    public function testGetProjectPortfolioSettingsForProjectWithOptions(): void
    {
        $options = ['opt_fields' => 'is_access_control_inherited,portfolio', 'limit' => 50];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'projects/12345/project_portfolio_settings',
                ['query' => $options],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->getProjectPortfolioSettingsForProject('12345', $options);
    }

    /**
     * Test getProjectPortfolioSettingsForProject with custom response type.
     */
    public function testGetProjectPortfolioSettingsForProjectWithCustomResponseType(): void
    {
        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'projects/12345/project_portfolio_settings',
                ['query' => []],
                HttpClientInterface::RESPONSE_FULL
            )
            ->willReturn([]);

        $this->service->getProjectPortfolioSettingsForProject('12345', [], HttpClientInterface::RESPONSE_FULL);
    }

    /**
     * Test getProjectPortfolioSettingsForProject throws exception for empty GID.
     */
    public function testGetProjectPortfolioSettingsForProjectThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Project GID must be a non-empty string.');

        $this->service->getProjectPortfolioSettingsForProject('');
    }

    /**
     * Test getProjectPortfolioSettingsForProject throws exception for non-numeric GID.
     */
    public function testGetProjectPortfolioSettingsForProjectThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Project GID must be a numeric string.');

        $this->service->getProjectPortfolioSettingsForProject('abc');
    }

    // ── getProjectPortfolioSettingsForPortfolio ───────────────────────────

    /**
     * Test getProjectPortfolioSettingsForPortfolio calls client with correct parameters.
     */
    public function testGetProjectPortfolioSettingsForPortfolio(): void
    {
        $expectedResponse = [
            ['gid' => '333', 'resource_type' => 'project_portfolio_setting'],
        ];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'portfolios/67890/project_portfolio_settings',
                ['query' => []],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->getProjectPortfolioSettingsForPortfolio('67890');

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test getProjectPortfolioSettingsForPortfolio with options.
     */
    public function testGetProjectPortfolioSettingsForPortfolioWithOptions(): void
    {
        $options = ['opt_fields' => 'is_access_control_inherited,project', 'limit' => 100];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'portfolios/67890/project_portfolio_settings',
                ['query' => $options],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->getProjectPortfolioSettingsForPortfolio('67890', $options);
    }

    /**
     * Test getProjectPortfolioSettingsForPortfolio with custom response type.
     */
    public function testGetProjectPortfolioSettingsForPortfolioWithCustomResponseType(): void
    {
        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'portfolios/67890/project_portfolio_settings',
                ['query' => []],
                HttpClientInterface::RESPONSE_FULL
            )
            ->willReturn([]);

        $this->service->getProjectPortfolioSettingsForPortfolio('67890', [], HttpClientInterface::RESPONSE_FULL);
    }

    /**
     * Test getProjectPortfolioSettingsForPortfolio throws exception for empty GID.
     */
    public function testGetProjectPortfolioSettingsForPortfolioThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Portfolio GID must be a non-empty string.');

        $this->service->getProjectPortfolioSettingsForPortfolio('');
    }

    /**
     * Test getProjectPortfolioSettingsForPortfolio throws exception for non-numeric GID.
     */
    public function testGetProjectPortfolioSettingsForPortfolioThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Portfolio GID must be a numeric string.');

        $this->service->getProjectPortfolioSettingsForPortfolio('abc');
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
