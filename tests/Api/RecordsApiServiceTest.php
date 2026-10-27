<?php

namespace BrightleafDigital\Tests\Api;

use BrightleafDigital\Api\RecordsApiService;
use BrightleafDigital\Exceptions\ValidationException;
use BrightleafDigital\Http\HttpClientInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RecordsApiServiceTest extends TestCase
{
    private HttpClientInterface $mockClient;

    /** @var RecordsApiService */
    private RecordsApiService $service;

    /** @var (HttpClientInterface&MockObject)|null */
    private $mockClientMock = null;

    protected function setUp(): void
    {
        $this->mockClient = $this->createStub(HttpClientInterface::class);
        $this->mockClientMock = null;
        $this->service = new RecordsApiService($this->mockClient);
    }

    // ── getRecordsForTable ────────────────────────────────────────────────

    /**
     * Test getRecordsForTable calls client with correct parameters.
     */
    public function testGetRecordsForTable(): void
    {
        $options = ['limit' => 50, 'sort_by' => 'created_at', 'modified_since' => '2026-10-01T00:00:00.000Z'];
        $expectedResponse = [
            ['gid' => '111', 'resource_type' => 'record', 'name' => 'Acme Corp'],
        ];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with('GET', 'tables/12345/records', ['query' => $options], HttpClientInterface::RESPONSE_DATA)
            ->willReturn($expectedResponse);

        $result = $this->service->getRecordsForTable('12345', $options);

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test getRecordsForTable throws exception for non-numeric GID.
     */
    public function testGetRecordsForTableThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Table GID must be a numeric string.');

        $this->service->getRecordsForTable('abc');
    }

    // ── createRecord ──────────────────────────────────────────────────────

    /**
     * Test createRecord calls client with correct parameters.
     */
    public function testCreateRecord(): void
    {
        $data = [
            'table' => '12345',
            'name' => 'Acme Corp',
            'custom_fields' => ['67890' => 'Enterprise'],
            'unique_fingerprint' => 'sfdc-opportunity-0061',
        ];
        $expectedResponse = ['gid' => '99999', 'resource_type' => 'record'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'records',
                ['json' => ['data' => $data], 'query' => []],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->createRecord($data);

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test createRecord throws exception when table is missing.
     */
    public function testCreateRecordThrowsExceptionForMissingTable(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Missing required field(s) for record creation: table');

        $this->service->createRecord(['name' => 'Acme Corp']);
    }

    // ── getRecord ─────────────────────────────────────────────────────────

    /**
     * Test getRecord calls client with correct parameters.
     */
    public function testGetRecord(): void
    {
        $options = ['opt_fields' => 'name,table,custom_fields'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with('GET', 'records/12345', ['query' => $options], HttpClientInterface::RESPONSE_DATA)
            ->willReturn(['gid' => '12345']);

        $this->service->getRecord('12345', $options);
    }

    /**
     * Test getRecord throws exception for empty GID.
     */
    public function testGetRecordThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Record GID must be a non-empty string.');

        $this->service->getRecord('');
    }

    // ── updateRecord ──────────────────────────────────────────────────────

    /**
     * Test updateRecord calls client with correct parameters.
     */
    public function testUpdateRecord(): void
    {
        $data = ['name' => 'Acme Inc', 'custom_fields' => ['67890' => 'Mid-market']];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'PUT',
                'records/12345',
                ['json' => ['data' => $data], 'query' => []],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->updateRecord('12345', $data);
    }

    /**
     * Test updateRecord throws exception for non-numeric GID.
     */
    public function testUpdateRecordThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Record GID must be a numeric string.');

        $this->service->updateRecord('abc', ['name' => 'Acme Inc']);
    }

    // ── deleteRecord ──────────────────────────────────────────────────────

    /**
     * Test deleteRecord calls client with correct parameters.
     */
    public function testDeleteRecord(): void
    {
        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with('DELETE', 'records/12345', [], HttpClientInterface::RESPONSE_FULL)
            ->willReturn([]);

        $this->service->deleteRecord('12345', HttpClientInterface::RESPONSE_FULL);
    }

    /**
     * Test deleteRecord throws exception for non-numeric GID.
     */
    public function testDeleteRecordThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Record GID must be a numeric string.');

        $this->service->deleteRecord('abc');
    }

    // ── searchRecords ─────────────────────────────────────────────────────

    /**
     * Test searchRecords calls client with correct parameters.
     */
    public function testSearchRecords(): void
    {
        $options = ['text' => 'Acme', 'tables.any' => '111,222', 'sort_by' => 'created_at'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'workspaces/12345/records/search',
                ['query' => $options],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->searchRecords('12345', $options);
    }

    /**
     * Test searchRecords throws exception for non-numeric workspace GID.
     */
    public function testSearchRecordsThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Workspace GID must be a numeric string.');

        $this->service->searchRecords('abc');
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
