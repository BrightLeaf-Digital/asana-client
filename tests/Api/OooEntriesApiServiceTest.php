<?php

namespace BrightleafDigital\Tests\Api;

use BrightleafDigital\Api\OooEntriesApiService;
use BrightleafDigital\Exceptions\ValidationException;
use BrightleafDigital\Http\HttpClientInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OooEntriesApiServiceTest extends TestCase
{
    private HttpClientInterface $mockClient;

    /** @var OooEntriesApiService */
    private OooEntriesApiService $service;

    /** @var (HttpClientInterface&MockObject)|null */
    private $mockClientMock = null;

    protected function setUp(): void
    {
        $this->mockClient = $this->createStub(HttpClientInterface::class);
        $this->mockClientMock = null;
        $this->service = new OooEntriesApiService($this->mockClient);
    }

    // ── getOooEntries ─────────────────────────────────────────────────────

    /**
     * Test getOooEntries calls client with correct parameters.
     */
    public function testGetOooEntries(): void
    {
        $expectedResponse = [
            ['gid' => '111', 'resource_type' => 'ooo_entry', 'start_date' => '2026-05-01'],
            ['gid' => '222', 'resource_type' => 'ooo_entry', 'start_date' => '2026-06-10'],
        ];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'ooo_entries',
                ['query' => []],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->getOooEntries();

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test getOooEntries with filtering options.
     */
    public function testGetOooEntriesWithOptions(): void
    {
        $options = [
            'user' => '67890',
            'workspace' => '12345',
            'start_date' => '2026-05-01',
            'end_date' => '2026-05-31',
        ];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'ooo_entries',
                ['query' => $options],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->getOooEntries($options);
    }

    /**
     * Test getOooEntries with custom response type.
     */
    public function testGetOooEntriesWithCustomResponseType(): void
    {
        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'ooo_entries',
                ['query' => []],
                HttpClientInterface::RESPONSE_FULL
            )
            ->willReturn([]);

        $this->service->getOooEntries([], HttpClientInterface::RESPONSE_FULL);
    }

    // ── createOooEntry ────────────────────────────────────────────────────

    /**
     * Test createOooEntry calls client with correct parameters.
     */
    public function testCreateOooEntry(): void
    {
        $data = [
            'user' => '67890',
            'workspace' => '12345',
            'start_date' => '2026-05-01',
            'end_date' => '2026-05-07',
        ];
        $expectedResponse = ['gid' => '99999', 'resource_type' => 'ooo_entry'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'ooo_entries',
                ['json' => ['data' => $data], 'query' => []],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->createOooEntry($data);

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test createOooEntry with options.
     */
    public function testCreateOooEntryWithOptions(): void
    {
        $data = [
            'user' => '67890',
            'workspace' => '12345',
            'start_date' => '2026-05-01',
            'end_date' => '2026-05-07',
        ];
        $options = ['opt_fields' => 'user,workspace,start_date,end_date'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'ooo_entries',
                ['json' => ['data' => $data], 'query' => $options],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->createOooEntry($data, $options);
    }

    /**
     * Test createOooEntry with custom response type.
     */
    public function testCreateOooEntryWithCustomResponseType(): void
    {
        $data = [
            'user' => '67890',
            'workspace' => '12345',
            'start_date' => '2026-05-01',
            'end_date' => '2026-05-07',
        ];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'ooo_entries',
                ['json' => ['data' => $data], 'query' => []],
                HttpClientInterface::RESPONSE_FULL
            )
            ->willReturn([]);

        $this->service->createOooEntry($data, [], HttpClientInterface::RESPONSE_FULL);
    }

    /**
     * Test createOooEntry throws exception when user is missing.
     */
    public function testCreateOooEntryThrowsExceptionForMissingUser(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Missing required field(s) for OOO entry creation: user');

        $this->service->createOooEntry([
            'workspace' => '12345',
            'start_date' => '2026-05-01',
            'end_date' => '2026-05-07',
        ]);
    }

    /**
     * Test createOooEntry throws exception when workspace is missing.
     */
    public function testCreateOooEntryThrowsExceptionForMissingWorkspace(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Missing required field(s) for OOO entry creation: workspace');

        $this->service->createOooEntry([
            'user' => '67890',
            'start_date' => '2026-05-01',
            'end_date' => '2026-05-07',
        ]);
    }

    /**
     * Test createOooEntry throws exception when start_date is missing.
     */
    public function testCreateOooEntryThrowsExceptionForMissingStartDate(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Missing required field(s) for OOO entry creation: start_date');

        $this->service->createOooEntry([
            'user' => '67890',
            'workspace' => '12345',
            'end_date' => '2026-05-07',
        ]);
    }

    /**
     * Test createOooEntry throws exception when end_date is missing.
     */
    public function testCreateOooEntryThrowsExceptionForMissingEndDate(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Missing required field(s) for OOO entry creation: end_date');

        $this->service->createOooEntry([
            'user' => '67890',
            'workspace' => '12345',
            'start_date' => '2026-05-01',
        ]);
    }

    /**
     * Test createOooEntry throws exception when all required fields are missing.
     */
    public function testCreateOooEntryThrowsExceptionForMissingAllFields(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(
            'Missing required field(s) for OOO entry creation: user, workspace, start_date, end_date'
        );

        $this->service->createOooEntry([]);
    }

    // ── getOooEntry ───────────────────────────────────────────────────────

    /**
     * Test getOooEntry calls client with correct parameters.
     */
    public function testGetOooEntry(): void
    {
        $expectedResponse = [
            'gid' => '12345',
            'resource_type' => 'ooo_entry',
            'start_date' => '2026-05-01',
            'end_date' => '2026-05-07',
        ];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with('GET', 'ooo_entries/12345', ['query' => []], HttpClientInterface::RESPONSE_DATA)
            ->willReturn($expectedResponse);

        $result = $this->service->getOooEntry('12345');

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test getOooEntry with options.
     */
    public function testGetOooEntryWithOptions(): void
    {
        $options = ['opt_fields' => 'user,workspace,start_date,end_date,created_by'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with('GET', 'ooo_entries/12345', ['query' => $options], HttpClientInterface::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getOooEntry('12345', $options);
    }

    /**
     * Test getOooEntry with custom response type.
     */
    public function testGetOooEntryWithCustomResponseType(): void
    {
        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with('GET', 'ooo_entries/12345', ['query' => []], HttpClientInterface::RESPONSE_FULL)
            ->willReturn([]);

        $this->service->getOooEntry('12345', [], HttpClientInterface::RESPONSE_FULL);
    }

    /**
     * Test getOooEntry throws exception for empty GID.
     */
    public function testGetOooEntryThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('OOO Entry GID must be a non-empty string.');

        $this->service->getOooEntry('');
    }

    /**
     * Test getOooEntry throws exception for non-numeric GID.
     */
    public function testGetOooEntryThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('OOO Entry GID must be a numeric string.');

        $this->service->getOooEntry('abc');
    }

    // ── updateOooEntry ────────────────────────────────────────────────────

    /**
     * Test updateOooEntry calls client with correct parameters.
     */
    public function testUpdateOooEntry(): void
    {
        $data = ['end_date' => '2026-05-14'];
        $expectedResponse = ['gid' => '12345', 'end_date' => '2026-05-14'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'PUT',
                'ooo_entries/12345',
                ['json' => ['data' => $data], 'query' => []],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->updateOooEntry('12345', $data);

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test updateOooEntry with options.
     */
    public function testUpdateOooEntryWithOptions(): void
    {
        $data = ['start_date' => '2026-05-02', 'end_date' => '2026-05-08'];
        $options = ['opt_fields' => 'start_date,end_date'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'PUT',
                'ooo_entries/12345',
                ['json' => ['data' => $data], 'query' => $options],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->updateOooEntry('12345', $data, $options);
    }

    /**
     * Test updateOooEntry with custom response type.
     */
    public function testUpdateOooEntryWithCustomResponseType(): void
    {
        $data = ['end_date' => '2026-05-21'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'PUT',
                'ooo_entries/12345',
                ['json' => ['data' => $data], 'query' => []],
                HttpClientInterface::RESPONSE_FULL
            )
            ->willReturn([]);

        $this->service->updateOooEntry('12345', $data, [], HttpClientInterface::RESPONSE_FULL);
    }

    /**
     * Test updateOooEntry throws exception for empty GID.
     */
    public function testUpdateOooEntryThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('OOO Entry GID must be a non-empty string.');

        $this->service->updateOooEntry('', ['end_date' => '2026-05-14']);
    }

    /**
     * Test updateOooEntry throws exception for non-numeric GID.
     */
    public function testUpdateOooEntryThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('OOO Entry GID must be a numeric string.');

        $this->service->updateOooEntry('abc', ['end_date' => '2026-05-14']);
    }

    // ── deleteOooEntry ────────────────────────────────────────────────────

    /**
     * Test deleteOooEntry calls client with correct parameters.
     */
    public function testDeleteOooEntry(): void
    {
        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with('DELETE', 'ooo_entries/12345', [], HttpClientInterface::RESPONSE_DATA)
            ->willReturn([]);

        $result = $this->service->deleteOooEntry('12345');

        $this->assertSame([], $result);
    }

    /**
     * Test deleteOooEntry with custom response type.
     */
    public function testDeleteOooEntryWithCustomResponseType(): void
    {
        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with('DELETE', 'ooo_entries/12345', [], HttpClientInterface::RESPONSE_FULL)
            ->willReturn([]);

        $this->service->deleteOooEntry('12345', HttpClientInterface::RESPONSE_FULL);
    }

    /**
     * Test deleteOooEntry throws exception for empty GID.
     */
    public function testDeleteOooEntryThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('OOO Entry GID must be a non-empty string.');

        $this->service->deleteOooEntry('');
    }

    /**
     * Test deleteOooEntry throws exception for non-numeric GID.
     */
    public function testDeleteOooEntryThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('OOO Entry GID must be a numeric string.');

        $this->service->deleteOooEntry('abc');
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
