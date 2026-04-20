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
    /** @var TokenStorageInterface&MockObject */
    private $mockStorage;

    /** @var AuthHandlerInterface&MockObject */
    private $mockAuthHandler;

    /** @var TokenManager */
    private $tokenManager;

    protected function setUp(): void
    {
        $this->mockStorage = $this->createMock(TokenStorageInterface::class);
        $this->mockAuthHandler = $this->createMock(AuthHandlerInterface::class);
        $this->tokenManager = new TokenManager(
            $this->mockStorage,
            $this->mockAuthHandler,
            new NullLogger()
        );
    }

    public function testGetAccessTokenStringLoadsFromStorage(): void
    {
        $tokenData = ['access_token' => 'test-token', 'expires' => time() + 3600];
        $this->mockStorage->expects($this->once())
            ->method('load')
            ->willReturn($tokenData);

        $result = $this->tokenManager->getAccessTokenString();

        $this->assertSame('test-token', $result);
    }

    public function testGetAccessTokenStringThrowsExceptionIfNoToken(): void
    {
        $this->mockStorage->method('load')->willReturn(null);

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

        $this->mockStorage->method('load')->willReturn($expiredToken->jsonSerialize());
        $this->mockAuthHandler->expects($this->once())
            ->method('refreshToken')
            ->willReturn($newToken);

        $this->mockStorage->expects($this->once())
            ->method('save')
            ->with($this->isArray());

        $this->tokenManager->ensureValidToken();

        $this->assertSame('new-token', $this->tokenManager->getAccessTokenString());
    }

    public function testSetAccessTokenSavesToStorage(): void
    {
        $token = new AccessToken(['access_token' => 'manual-token', 'expires' => time() + 3600]);
        $this->mockStorage->expects($this->once())
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
}
