<?php

namespace BrightleafDigital\Tests\Auth;

use BrightleafDigital\Auth\AuthHandlerInterface;
use BrightleafDigital\Auth\TokenManager;
use BrightleafDigital\Exceptions\TokenInvalidException;
use BrightleafDigital\Storage\TokenStorageInterface;
use League\OAuth2\Client\Token\AccessToken;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class TokenManagerTest extends TestCase
{
    private TokenStorageInterface $mockStorage;

    private AuthHandlerInterface $mockAuthHandler;
    /** @var (TokenStorageInterface&MockObject)|null */
    private $mockStorageMock = null;
    /** @var (AuthHandlerInterface&MockObject)|null */
    private $mockAuthHandlerMock = null;

    /** @var TokenManager */
    private TokenManager $tokenManager;

    protected function setUp(): void
    {
        $this->mockStorage = $this->createStub(TokenStorageInterface::class);
        $this->mockAuthHandler = $this->createStub(AuthHandlerInterface::class);
        $this->mockStorageMock = null;
        $this->mockAuthHandlerMock = null;
        $this->tokenManager = new TokenManager(
            $this->mockStorage,
            $this->mockAuthHandler,
            new NullLogger()
        );
    }

    public function testGetAccessTokenStringLoadsFromStorage(): void
    {
        $tokenData = ['access_token' => 'test-token', 'expires' => time() + 3600];
        $this->mockStorage()->expects($this->once())
            ->method('load')
            ->willReturn($tokenData);

        $result = $this->tokenManager->getAccessTokenString();

        $this->assertSame('test-token', $result);
    }

    public function testGetAccessTokenStringThrowsExceptionIfNoToken(): void
    {
        $stubStorage = $this->createStub(TokenStorageInterface::class);
        $stubStorage->method('load')->willReturn(null);
        $this->tokenManager = new TokenManager($stubStorage, $this->mockAuthHandler, new NullLogger());

        $this->expectException(TokenInvalidException::class);
        $this->tokenManager->getAccessTokenString();
    }

    public function testEnsureValidTokenRefreshesIfExpired(): void
    {
        $expiredToken = new AccessToken([
            'access_token' => 'old-token',
            'refresh_token' => 'refresh-token',
            'expires' => time() - 3600
        ]);
        $newToken = new AccessToken([
            'access_token' => 'new-token',
            'refresh_token' => 'refresh-token',
            'expires' => time() + 3600
        ]);

        $storageMock = $this->mockStorage();
        $storageMock->expects($this->once())
            ->method('load')
            ->willReturn($expiredToken->jsonSerialize());

        $this->mockAuthHandler()->expects($this->once())
            ->method('refreshToken')
            ->willReturn($newToken);

        $storageMock->expects($this->once())
            ->method('save')
            ->with($this->isArray());

        $this->tokenManager->ensureValidToken();

        $this->assertSame('new-token', $this->tokenManager->getAccessTokenString());
    }

    public function testSetAccessTokenSavesToStorage(): void
    {
        $token = new AccessToken(['access_token' => 'manual-token', 'expires' => time() + 3600]);
        $this->mockStorage()->expects($this->once())
            ->method('save')
            ->with($token->jsonSerialize());

        $this->tokenManager->setAccessToken($token);

        $this->assertSame('manual-token', $this->tokenManager->getAccessTokenString());
    }

    public function testRefreshSubscribersAreNotified(): void
    {
        $token = new AccessToken(['access_token' => 'refreshed-token', 'expires' => time() + 3600]);
        $called = false;
        $this->tokenManager->subscribeToRefresh(function ($refreshedToken) use (&$called, $token) {
            $called = true;
            $this->assertSame($token->getToken(), $refreshedToken->getToken());
        });

        $this->tokenManager->setAccessToken($token);
        $this->assertTrue($called);
    }

    /**
     * @return TokenStorageInterface&MockObject
     */
    private function mockStorage(): TokenStorageInterface
    {
        if ($this->mockStorageMock === null) {
            $this->mockStorageMock = $this->createMock(TokenStorageInterface::class);
            $this->mockStorage = $this->mockStorageMock;
            $this->tokenManager = new TokenManager($this->mockStorage, $this->mockAuthHandler, new NullLogger());
        }

        return $this->mockStorageMock;
    }

    /**
     * @return AuthHandlerInterface&MockObject
     */
    private function mockAuthHandler(): AuthHandlerInterface
    {
        if ($this->mockAuthHandlerMock === null) {
            $this->mockAuthHandlerMock = $this->createMock(AuthHandlerInterface::class);
            $this->mockAuthHandler = $this->mockAuthHandlerMock;
            $this->tokenManager = new TokenManager($this->mockStorage, $this->mockAuthHandler, new NullLogger());
        }

        return $this->mockAuthHandlerMock;
    }
}
