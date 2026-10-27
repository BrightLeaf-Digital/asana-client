<?php

namespace BrightleafDigital\Tests\Api;

use BrightleafDigital\Api\TablesApiService;
use BrightleafDigital\Exceptions\ValidationException;
use BrightleafDigital\Http\HttpClientInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TablesApiServiceTest extends TestCase
{
    private HttpClientInterface $mockClient;

    /** @var TablesApiService */
    private TablesApiService $service;

    /** @var (HttpClientInterface&MockObject)|null */
    private $mockClientMock = null;

    protected function setUp(): void
    {
        $this->mockClient = $this->createStub(HttpClientInterface::class);
        $this->mockClientMock = null;
        $this->service = new TablesApiService($this->mockClient);
    }

    // ── getTablesForWorkspace ─────────────────────────────────────────────

    /**
     * Test getTablesForWorkspace calls client with correct parameters.
     */
    public function testGetTablesForWorkspace(): void
    {
        $expectedResponse = [
            ['gid' => '111', 'resource_type' => 'table', 'name' => 'Customers'],
        ];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'workspaces/12345/tables',
                ['query' => ['limit' => 50]],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->getTablesForWorkspace('12345', ['limit' => 50]);

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test getTablesForWorkspace throws exception for non-numeric GID.
     */
    public function testGetTablesForWorkspaceThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Workspace GID must be a numeric string.');

        $this->service->getTablesForWorkspace('abc');
    }

    // ── createTable ───────────────────────────────────────────────────────

    /**
     * Test createTable calls client with correct parameters.
     */
    public function testCreateTable(): void
    {
        $data = ['workspace' => '12345', 'name' => 'Customers'];
        $expectedResponse = ['gid' => '99999', 'resource_type' => 'table'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'tables',
                ['json' => ['data' => $data], 'query' => []],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->createTable($data);

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test createTable throws exception when workspace is missing.
     */
    public function testCreateTableThrowsExceptionForMissingWorkspace(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Missing required field(s) for table creation: workspace');

        $this->service->createTable(['name' => 'Customers']);
    }

    // ── getTable ──────────────────────────────────────────────────────────

    /**
     * Test getTable calls client with correct parameters.
     */
    public function testGetTable(): void
    {
        $options = ['opt_fields' => 'name,custom_field_settings'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with('GET', 'tables/12345', ['query' => $options], HttpClientInterface::RESPONSE_DATA)
            ->willReturn(['gid' => '12345']);

        $this->service->getTable('12345', $options);
    }

    /**
     * Test getTable throws exception for empty GID.
     */
    public function testGetTableThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Table GID must be a non-empty string.');

        $this->service->getTable('');
    }

    // ── updateTable ───────────────────────────────────────────────────────

    /**
     * Test updateTable calls client with correct parameters.
     */
    public function testUpdateTable(): void
    {
        $data = ['name' => 'Accounts'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'PUT',
                'tables/12345',
                ['json' => ['data' => $data], 'query' => []],
                HttpClientInterface::RESPONSE_FULL
            )
            ->willReturn([]);

        $this->service->updateTable('12345', $data, [], HttpClientInterface::RESPONSE_FULL);
    }

    /**
     * Test updateTable throws exception for non-numeric GID.
     */
    public function testUpdateTableThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Table GID must be a numeric string.');

        $this->service->updateTable('abc', ['name' => 'Accounts']);
    }

    // ── deleteTable ───────────────────────────────────────────────────────

    /**
     * Test deleteTable calls client with correct parameters.
     */
    public function testDeleteTable(): void
    {
        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with('DELETE', 'tables/12345', [], HttpClientInterface::RESPONSE_DATA)
            ->willReturn([]);

        $this->assertSame([], $this->service->deleteTable('12345'));
    }

    /**
     * Test deleteTable throws exception for non-numeric GID.
     */
    public function testDeleteTableThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Table GID must be a numeric string.');

        $this->service->deleteTable('abc');
    }

    // ── addCustomFieldSettingForTable ─────────────────────────────────────

    /**
     * Test addCustomFieldSettingForTable calls client with correct parameters.
     */
    public function testAddCustomFieldSettingForTable(): void
    {
        $data = ['custom_field' => '67890', 'is_important' => true];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'tables/12345/addCustomFieldSetting',
                ['json' => ['data' => $data], 'query' => []],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn(['gid' => '555', 'resource_type' => 'custom_field_setting']);

        $this->service->addCustomFieldSettingForTable('12345', $data);
    }

    /**
     * Test addCustomFieldSettingForTable throws exception when custom_field is missing.
     */
    public function testAddCustomFieldSettingForTableThrowsExceptionForMissingCustomField(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('custom_field');

        $this->service->addCustomFieldSettingForTable('12345', ['is_important' => true]);
    }

    // ── removeCustomFieldSettingForTable ──────────────────────────────────

    /**
     * Test removeCustomFieldSettingForTable calls client with correct parameters.
     */
    public function testRemoveCustomFieldSettingForTable(): void
    {
        $data = ['custom_field' => '67890'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'tables/12345/removeCustomFieldSetting',
                ['json' => ['data' => $data]],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->removeCustomFieldSettingForTable('12345', $data);
    }

    /**
     * Test removeCustomFieldSettingForTable throws exception for non-numeric GID.
     */
    public function testRemoveCustomFieldSettingForTableThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Table GID must be a numeric string.');

        $this->service->removeCustomFieldSettingForTable('abc', ['custom_field' => '67890']);
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
