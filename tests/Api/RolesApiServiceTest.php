<?php

namespace BrightleafDigital\Tests\Api;

use BrightleafDigital\Api\RolesApiService;
use BrightleafDigital\Exceptions\ValidationException;
use BrightleafDigital\Http\HttpClientInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RolesApiServiceTest extends TestCase
{
    private HttpClientInterface $mockClient;

    /** @var RolesApiService */
    private RolesApiService $service;

    /** @var (HttpClientInterface&MockObject)|null */
    private $mockClientMock = null;

    protected function setUp(): void
    {
        $this->mockClient = $this->createStub(HttpClientInterface::class);
        $this->mockClientMock = null;
        $this->service = new RolesApiService($this->mockClient);
    }

    // ── getRoles ─────────────────────────────────────────────────────────

    /**
     * Test getRoles calls client with correct parameters.
     */
    public function testGetRoles(): void
    {
        $expectedResponse = [
            ['gid' => '111', 'resource_type' => 'role', 'name' => 'Member'],
            ['gid' => '222', 'resource_type' => 'role', 'name' => 'Guest'],
        ];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'roles',
                ['query' => ['workspace' => '12345']],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->getRoles('12345');

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test getRoles with additional options.
     */
    public function testGetRolesWithOptions(): void
    {
        $options = ['opt_fields' => 'name,description,is_standard_role'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'roles',
                ['query' => ['opt_fields' => 'name,description,is_standard_role', 'workspace' => '12345']],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->getRoles('12345', $options);
    }

    /**
     * Test getRoles with custom response type.
     */
    public function testGetRolesWithCustomResponseType(): void
    {
        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'roles',
                ['query' => ['workspace' => '12345']],
                HttpClientInterface::RESPONSE_FULL
            )
            ->willReturn([]);

        $this->service->getRoles('12345', [], HttpClientInterface::RESPONSE_FULL);
    }

    /**
     * Test getRoles throws exception for empty workspace GID.
     */
    public function testGetRolesThrowsExceptionForEmptyWorkspaceGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Workspace GID must be a non-empty string.');

        $this->service->getRoles('');
    }

    /**
     * Test getRoles throws exception for non-numeric workspace GID.
     */
    public function testGetRolesThrowsExceptionForNonNumericWorkspaceGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Workspace GID must be a numeric string.');

        $this->service->getRoles('abc');
    }

    // ── getRole ──────────────────────────────────────────────────────────

    /**
     * Test getRole calls client with correct parameters.
     */
    public function testGetRole(): void
    {
        $expectedResponse = [
            'gid' => '12345',
            'resource_type' => 'role',
            'name' => 'Member',
            'is_standard_role' => true,
        ];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with('GET', 'roles/12345', ['query' => []], HttpClientInterface::RESPONSE_DATA)
            ->willReturn($expectedResponse);

        $result = $this->service->getRole('12345');

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test getRole with options.
     */
    public function testGetRoleWithOptions(): void
    {
        $options = ['opt_fields' => 'name,description,is_standard_role'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with('GET', 'roles/12345', ['query' => $options], HttpClientInterface::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getRole('12345', $options);
    }

    /**
     * Test getRole with custom response type.
     */
    public function testGetRoleWithCustomResponseType(): void
    {
        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with('GET', 'roles/12345', ['query' => []], HttpClientInterface::RESPONSE_FULL)
            ->willReturn([]);

        $this->service->getRole('12345', [], HttpClientInterface::RESPONSE_FULL);
    }

    /**
     * Test getRole throws exception for empty GID.
     */
    public function testGetRoleThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Role GID must be a non-empty string.');

        $this->service->getRole('');
    }

    /**
     * Test getRole throws exception for non-numeric GID.
     */
    public function testGetRoleThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Role GID must be a numeric string.');

        $this->service->getRole('abc');
    }

    // ── createRole ───────────────────────────────────────────────────────

    /**
     * Test createRole calls client with correct parameters.
     */
    public function testCreateRole(): void
    {
        $data = [
            'workspace' => '12345',
            'name' => 'Project Reviewer',
            'description' => 'Can review and comment on projects',
            'base_role_type' => 'member',
        ];
        $expectedResponse = ['gid' => '99999', 'resource_type' => 'role', 'name' => 'Project Reviewer'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'roles',
                ['json' => ['data' => $data], 'query' => []],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->createRole($data);

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test createRole with options.
     */
    public function testCreateRoleWithOptions(): void
    {
        $data = [
            'workspace' => '12345',
            'name' => 'Project Reviewer',
            'description' => 'Can review and comment on projects',
            'base_role_type' => 'member',
        ];
        $options = ['opt_fields' => 'name,description'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'roles',
                ['json' => ['data' => $data], 'query' => $options],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->createRole($data, $options);
    }

    /**
     * Test createRole with custom response type.
     */
    public function testCreateRoleWithCustomResponseType(): void
    {
        $data = [
            'workspace' => '12345',
            'name' => 'Project Reviewer',
            'description' => 'Can review',
            'base_role_type' => 'member',
        ];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'roles',
                ['json' => ['data' => $data], 'query' => []],
                HttpClientInterface::RESPONSE_FULL
            )
            ->willReturn([]);

        $this->service->createRole($data, [], HttpClientInterface::RESPONSE_FULL);
    }

    /**
     * Test createRole throws exception when workspace is missing.
     */
    public function testCreateRoleThrowsExceptionForMissingWorkspace(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Missing required field(s) for role creation: workspace');

        $this->service->createRole([
            'name' => 'Project Reviewer',
            'description' => 'Can review',
            'base_role_type' => 'member',
        ]);
    }

    /**
     * Test createRole throws exception when name is missing.
     */
    public function testCreateRoleThrowsExceptionForMissingName(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Missing required field(s) for role creation: name');

        $this->service->createRole([
            'workspace' => '12345',
            'description' => 'Can review',
            'base_role_type' => 'member',
        ]);
    }

    /**
     * Test createRole throws exception when description is missing.
     */
    public function testCreateRoleThrowsExceptionForMissingDescription(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Missing required field(s) for role creation: description');

        $this->service->createRole([
            'workspace' => '12345',
            'name' => 'Project Reviewer',
            'base_role_type' => 'member',
        ]);
    }

    /**
     * Test createRole throws exception when base_role_type is missing.
     */
    public function testCreateRoleThrowsExceptionForMissingBaseRoleType(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Missing required field(s) for role creation: base_role_type');

        $this->service->createRole([
            'workspace' => '12345',
            'name' => 'Project Reviewer',
            'description' => 'Can review',
        ]);
    }

    /**
     * Test createRole throws exception when all required fields are missing.
     */
    public function testCreateRoleThrowsExceptionForMissingAllFields(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(
            'Missing required field(s) for role creation: workspace, name, description, base_role_type'
        );

        $this->service->createRole([]);
    }

    // ── updateRole ───────────────────────────────────────────────────────

    /**
     * Test updateRole calls client with correct parameters.
     */
    public function testUpdateRole(): void
    {
        $data = ['name' => 'Senior Project Reviewer'];
        $expectedResponse = ['gid' => '12345', 'name' => 'Senior Project Reviewer'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'PUT',
                'roles/12345',
                ['json' => ['data' => $data], 'query' => []],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->updateRole('12345', $data);

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test updateRole with options.
     */
    public function testUpdateRoleWithOptions(): void
    {
        $data = ['description' => 'Updated description'];
        $options = ['opt_fields' => 'name,description'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'PUT',
                'roles/12345',
                ['json' => ['data' => $data], 'query' => $options],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->updateRole('12345', $data, $options);
    }

    /**
     * Test updateRole with custom response type.
     */
    public function testUpdateRoleWithCustomResponseType(): void
    {
        $data = ['name' => 'Updated'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'PUT',
                'roles/12345',
                ['json' => ['data' => $data], 'query' => []],
                HttpClientInterface::RESPONSE_FULL
            )
            ->willReturn([]);

        $this->service->updateRole('12345', $data, [], HttpClientInterface::RESPONSE_FULL);
    }

    /**
     * Test updateRole throws exception for empty GID.
     */
    public function testUpdateRoleThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Role GID must be a non-empty string.');

        $this->service->updateRole('', ['name' => 'Updated']);
    }

    /**
     * Test updateRole throws exception for non-numeric GID.
     */
    public function testUpdateRoleThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Role GID must be a numeric string.');

        $this->service->updateRole('abc', ['name' => 'Updated']);
    }

    // ── deleteRole ───────────────────────────────────────────────────────

    /**
     * Test deleteRole calls client with correct parameters.
     */
    public function testDeleteRole(): void
    {
        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with('DELETE', 'roles/12345', [], HttpClientInterface::RESPONSE_DATA)
            ->willReturn([]);

        $result = $this->service->deleteRole('12345');

        $this->assertSame([], $result);
    }

    /**
     * Test deleteRole with custom response type.
     */
    public function testDeleteRoleWithCustomResponseType(): void
    {
        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with('DELETE', 'roles/12345', [], HttpClientInterface::RESPONSE_FULL)
            ->willReturn([]);

        $this->service->deleteRole('12345', HttpClientInterface::RESPONSE_FULL);
    }

    /**
     * Test deleteRole throws exception for empty GID.
     */
    public function testDeleteRoleThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Role GID must be a non-empty string.');

        $this->service->deleteRole('');
    }

    /**
     * Test deleteRole throws exception for non-numeric GID.
     */
    public function testDeleteRoleThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Role GID must be a numeric string.');

        $this->service->deleteRole('abc');
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
