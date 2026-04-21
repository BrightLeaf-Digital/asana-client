<?php

namespace BrightleafDigital\Tests\Api;

use BrightleafDigital\Api\TeamsApiService;
use BrightleafDigital\Exceptions\ValidationException;
use BrightleafDigital\Http\HttpClientInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TeamsApiServiceTest extends TestCase
{
    private HttpClientInterface $mockClient;

    /** @var TeamsApiService */
    private TeamsApiService $service;

    /** @var (HttpClientInterface&MockObject)|null */
    private $mockClientMock = null;

    protected function setUp(): void
    {
        $this->mockClient = $this->createStub(HttpClientInterface::class);
        $this->mockClientMock = null;
        $this->service = new TeamsApiService($this->mockClient);
    }

    // ── createTeam ──────────────────────────────────────────────────────

    /**
     * Test createTeam calls client with correct parameters.
     */
    public function testCreateTeam(): void
    {
        $data = ['name' => 'Engineering', 'organization' => '12345'];
        $expectedResponse = ['gid' => '99999', 'resource_type' => 'team', 'name' => 'Engineering'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'teams',
                ['json' => ['data' => $data], 'query' => []],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->createTeam($data);

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test createTeam with options.
     */
    public function testCreateTeamWithOptions(): void
    {
        $data = ['name' => 'Engineering', 'organization' => '12345'];
        $options = ['opt_fields' => 'name,description,organization'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'teams',
                ['json' => ['data' => $data], 'query' => $options],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->createTeam($data, $options);
    }

    /**
     * Test createTeam with optional description and visibility.
     */
    public function testCreateTeamWithOptionalFields(): void
    {
        $data = [
            'name' => 'Engineering',
            'organization' => '12345',
            'description' => 'The engineering team',
            'visibility' => 'secret',
        ];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'teams',
                ['json' => ['data' => $data], 'query' => []],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->createTeam($data);
    }

    /**
     * Test createTeam throws exception when name is missing.
     */
    public function testCreateTeamThrowsExceptionForMissingName(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Missing required field(s) for team creation: name');

        $this->service->createTeam(['organization' => '12345']);
    }

    /**
     * Test createTeam throws exception when organization is missing.
     */
    public function testCreateTeamThrowsExceptionForMissingOrganization(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Missing required field(s) for team creation: organization');

        $this->service->createTeam(['name' => 'Engineering']);
    }

    /**
     * Test createTeam throws exception when both name and organization are missing.
     */
    public function testCreateTeamThrowsExceptionForMissingBothFields(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Missing required field(s) for team creation: name, organization');

        $this->service->createTeam([]);
    }

    // ── getTeam ─────────────────────────────────────────────────────────

    /**
     * Test getTeam calls client with correct parameters.
     */
    public function testGetTeam(): void
    {
        $expectedResponse = ['gid' => '12345', 'resource_type' => 'team', 'name' => 'Engineering'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with('GET', 'teams/12345', ['query' => []], HttpClientInterface::RESPONSE_DATA)
            ->willReturn($expectedResponse);

        $result = $this->service->getTeam('12345');

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test getTeam with options.
     */
    public function testGetTeamWithOptions(): void
    {
        $options = ['opt_fields' => 'name,description,organization'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with('GET', 'teams/12345', ['query' => $options], HttpClientInterface::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getTeam('12345', $options);
    }

    /**
     * Test getTeam with custom response type.
     */
    public function testGetTeamWithCustomResponseType(): void
    {
        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with('GET', 'teams/12345', ['query' => []], HttpClientInterface::RESPONSE_FULL)
            ->willReturn([]);

        $this->service->getTeam('12345', [], HttpClientInterface::RESPONSE_FULL);
    }

    /**
     * Test getTeam throws exception for empty GID.
     */
    public function testGetTeamThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Team GID must be a non-empty string.');

        $this->service->getTeam('');
    }

    /**
     * Test getTeam throws exception for non-numeric GID.
     */
    public function testGetTeamThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Team GID must be a numeric string.');

        $this->service->getTeam('abc');
    }

    // ── updateTeam ──────────────────────────────────────────────────────

    /**
     * Test updateTeam calls client with correct parameters.
     */
    public function testUpdateTeam(): void
    {
        $data = ['name' => 'Updated Team', 'description' => 'New description'];
        $expectedResponse = ['gid' => '12345', 'resource_type' => 'team', 'name' => 'Updated Team'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'PUT',
                'teams/12345',
                ['json' => ['data' => $data], 'query' => []],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->updateTeam('12345', $data);

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test updateTeam with options.
     */
    public function testUpdateTeamWithOptions(): void
    {
        $data = ['name' => 'Updated Team'];
        $options = ['opt_fields' => 'name,description'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'PUT',
                'teams/12345',
                ['json' => ['data' => $data], 'query' => $options],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->updateTeam('12345', $data, $options);
    }

    /**
     * Test updateTeam with custom response type.
     */
    public function testUpdateTeamWithCustomResponseType(): void
    {
        $data = ['name' => 'Updated Team'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'PUT',
                'teams/12345',
                ['json' => ['data' => $data], 'query' => []],
                HttpClientInterface::RESPONSE_FULL
            )
            ->willReturn([]);

        $this->service->updateTeam('12345', $data, [], HttpClientInterface::RESPONSE_FULL);
    }

    /**
     * Test updateTeam throws exception for empty GID.
     */
    public function testUpdateTeamThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Team GID must be a non-empty string.');

        $this->service->updateTeam('', ['name' => 'Test']);
    }

    /**
     * Test updateTeam throws exception for non-numeric GID.
     */
    public function testUpdateTeamThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Team GID must be a numeric string.');

        $this->service->updateTeam('abc', ['name' => 'Test']);
    }

    // ── getTeamsForWorkspace ────────────────────────────────────────────

    /**
     * Test getTeamsForWorkspace calls client with correct parameters.
     */
    public function testGetTeamsForWorkspace(): void
    {
        $expectedResponse = [
            ['gid' => '111', 'name' => 'Engineering'],
        ];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with('GET', 'workspaces/12345/teams', ['query' => []], HttpClientInterface::RESPONSE_DATA)
            ->willReturn($expectedResponse);

        $result = $this->service->getTeamsForWorkspace('12345');

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test getTeamsForWorkspace with options.
     */
    public function testGetTeamsForWorkspaceWithOptions(): void
    {
        $options = ['opt_fields' => 'name,description', 'limit' => 50];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with('GET', 'workspaces/12345/teams', ['query' => $options], HttpClientInterface::RESPONSE_DATA)
            ->willReturn([]);

        $this->service->getTeamsForWorkspace('12345', $options);
    }

    /**
     * Test getTeamsForWorkspace throws exception for empty GID.
     */
    public function testGetTeamsForWorkspaceThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Workspace GID must be a non-empty string.');

        $this->service->getTeamsForWorkspace('');
    }

    /**
     * Test getTeamsForWorkspace throws exception for non-numeric GID.
     */
    public function testGetTeamsForWorkspaceThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Workspace GID must be a numeric string.');

        $this->service->getTeamsForWorkspace('abc');
    }

    // ── getTeamsForUser ─────────────────────────────────────────────────

    /**
     * Test getTeamsForUser calls client with correct parameters.
     */
    public function testGetTeamsForUser(): void
    {
        $expectedResponse = [
            ['gid' => '111', 'name' => 'Engineering'],
            ['gid' => '222', 'name' => 'Design'],
        ];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'users/12345/teams',
                ['query' => ['organization' => '67890']],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->getTeamsForUser('12345', '67890');

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test getTeamsForUser with options.
     */
    public function testGetTeamsForUserWithOptions(): void
    {
        $options = ['opt_fields' => 'name,description'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'users/12345/teams',
                ['query' => ['opt_fields' => 'name,description', 'organization' => '67890']],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->getTeamsForUser('12345', '67890', $options);
    }

    /**
     * Test getTeamsForUser with custom response type.
     */
    public function testGetTeamsForUserWithCustomResponseType(): void
    {
        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'users/12345/teams',
                ['query' => ['organization' => '67890']],
                HttpClientInterface::RESPONSE_FULL
            )
            ->willReturn([]);

        $this->service->getTeamsForUser('12345', '67890', [], HttpClientInterface::RESPONSE_FULL);
    }

    /**
     * Test getTeamsForUser throws exception for empty user GID.
     */
    public function testGetTeamsForUserThrowsExceptionForEmptyUserGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('User GID must be a non-empty string.');

        $this->service->getTeamsForUser('', '67890');
    }

    /**
     * Test getTeamsForUser throws exception for invalid user GID.
     */
    public function testGetTeamsForUserThrowsExceptionForInvalidUserGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('User GID must be a numeric string, "me", or a valid email address.');

        $this->service->getTeamsForUser('abc', '67890');
    }

    /**
     * Test getTeamsForUser accepts "me" as user GID.
     */
    public function testGetTeamsForUserAcceptsMeAsUserGid(): void
    {
        $expectedResponse = [
            ['gid' => '111', 'name' => 'Engineering'],
        ];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'users/me/teams',
                ['query' => ['organization' => '67890']],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->getTeamsForUser('me', '67890');

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test getTeamsForUser accepts an email address as user GID.
     */
    public function testGetTeamsForUserAcceptsEmailAsUserGid(): void
    {
        $expectedResponse = [
            ['gid' => '111', 'name' => 'Engineering'],
        ];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'users/user@example.com/teams',
                ['query' => ['organization' => '67890']],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn($expectedResponse);

        $result = $this->service->getTeamsForUser('user@example.com', '67890');

        $this->assertSame($expectedResponse, $result);
    }

    /**
     * Test getTeamsForUser throws exception for empty organization GID.
     */
    public function testGetTeamsForUserThrowsExceptionForEmptyOrgGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Organization GID must be a non-empty string.');

        $this->service->getTeamsForUser('12345', '');
    }

    /**
     * Test getTeamsForUser throws exception for non-numeric organization GID.
     */
    public function testGetTeamsForUserThrowsExceptionForNonNumericOrgGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Organization GID must be a numeric string.');

        $this->service->getTeamsForUser('12345', 'abc');
    }

    // ── addUserToTeam ───────────────────────────────────────────────────

    /**
     * Test addUserToTeam calls client with correct parameters.
     */
    public function testAddUserToTeam(): void
    {
        $data = ['user' => '67890'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'teams/12345/addUser',
                ['json' => ['data' => $data], 'query' => []],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->addUserToTeam('12345', $data);
    }

    /**
     * Test addUserToTeam with options.
     */
    public function testAddUserToTeamWithOptions(): void
    {
        $data = ['user' => '67890'];
        $options = ['opt_fields' => 'user,team'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'teams/12345/addUser',
                ['json' => ['data' => $data], 'query' => $options],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->addUserToTeam('12345', $data, $options);
    }

    /**
     * Test addUserToTeam throws exception for empty team GID.
     */
    public function testAddUserToTeamThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Team GID must be a non-empty string.');

        $this->service->addUserToTeam('', ['user' => '67890']);
    }

    /**
     * Test addUserToTeam throws exception for non-numeric team GID.
     */
    public function testAddUserToTeamThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Team GID must be a numeric string.');

        $this->service->addUserToTeam('abc', ['user' => '67890']);
    }

    /**
     * Test addUserToTeam throws exception when user field is missing.
     */
    public function testAddUserToTeamThrowsExceptionForMissingUser(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Missing required field(s) for adding user to team: user');

        $this->service->addUserToTeam('12345', []);
    }

    // ── removeUserFromTeam ──────────────────────────────────────────────

    /**
     * Test removeUserFromTeam calls client with correct parameters.
     */
    public function testRemoveUserFromTeam(): void
    {
        $data = ['user' => '67890'];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'teams/12345/removeUser',
                ['json' => ['data' => $data], 'query' => []],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn([]);

        $result = $this->service->removeUserFromTeam('12345', $data);

        $this->assertSame([], $result);
    }

    /**
     * Test removeUserFromTeam with options.
     */
    public function testRemoveUserFromTeamWithOptions(): void
    {
        $data = ['user' => '67890'];
        $options = ['opt_pretty' => true];

        $this->mockClient()->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'teams/12345/removeUser',
                ['json' => ['data' => $data], 'query' => $options],
                HttpClientInterface::RESPONSE_DATA
            )
            ->willReturn([]);

        $this->service->removeUserFromTeam('12345', $data, $options);
    }

    /**
     * Test removeUserFromTeam throws exception for empty team GID.
     */
    public function testRemoveUserFromTeamThrowsExceptionForEmptyGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Team GID must be a non-empty string.');

        $this->service->removeUserFromTeam('', ['user' => '67890']);
    }

    /**
     * Test removeUserFromTeam throws exception for non-numeric team GID.
     */
    public function testRemoveUserFromTeamThrowsExceptionForNonNumericGid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Team GID must be a numeric string.');

        $this->service->removeUserFromTeam('abc', ['user' => '67890']);
    }

    /**
     * Test removeUserFromTeam throws exception when user field is missing.
     */
    public function testRemoveUserFromTeamThrowsExceptionForMissingUser(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Missing required field(s) for removing user from team: user');

        $this->service->removeUserFromTeam('12345', []);
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
