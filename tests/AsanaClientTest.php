<?php

namespace BrightleafDigital\Tests;

use BrightleafDigital\AsanaClient;
use BrightleafDigital\Api\TaskApiService;
use BrightleafDigital\Auth\AuthHandlerInterface;
use BrightleafDigital\Auth\TokenManager;
use BrightleafDigital\Container\ServiceContainer;
use BrightleafDigital\Http\HttpClientInterface;
use BrightleafDigital\Storage\TokenStorageInterface;
use League\OAuth2\Client\Token\AccessToken;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class AsanaClientTest extends TestCase
{
    private ServiceContainer $container;
    private AsanaClient $client;
    private TokenStorageInterface $storage;
    private AuthHandlerInterface $authHandler;

    protected function tearDown(): void
    {
        // Clean up any accidental token files created during tests
        @unlink(getcwd() . '/token.json');
    }

    protected function setUp(): void
    {
        $this->container = new ServiceContainer();
        $this->container->set(LoggerInterface::class, new NullLogger());

        $this->storage = $this->createStub(TokenStorageInterface::class);
        $this->container->set(TokenStorageInterface::class, $this->storage);

        $this->authHandler = $this->createStub(AuthHandlerInterface::class);
        $this->container->set(AuthHandlerInterface::class, $this->authHandler);

        $tokenManager = new TokenManager($this->storage, $this->authHandler);
        $this->container->set(TokenManager::class, $tokenManager);

        $httpClient = $this->createStub(HttpClientInterface::class);
        $this->container->set(HttpClientInterface::class, $httpClient);

        // Register a few services for testing
        $this->container->set(TaskApiService::class, new TaskApiService($httpClient));

        $this->client = new AsanaClient($this->container);
    }

    public function testTasksReturnsService(): void
    {
        $this->assertInstanceOf(TaskApiService::class, $this->client->tasks());
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetAuthorizationUrlDelegatesToAuthHandler(): void
    {
        $this->mockAuthHandler()
            ->expects($this->once())
            ->method('getAuthorizationUrl')
            ->with(['scope' => 'default'])
            ->willReturn('https://example.com/auth');

        $result = $this->client->getAuthorizationUrl(['scope' => 'default']);
        $this->assertSame('https://example.com/auth', $result);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testHandleCallbackSetsToken(): void
    {
        $token = new AccessToken(['access_token' => 'foo', 'expires' => time() + 3600]);

        $this->mockAuthHandler()
            ->expects($this->once())
            ->method('handleCallback')
            ->with('code123', 'verifier123')
            ->willReturn($token);

        $this->client->handleCallback('code123', 'verifier123');

        $this->assertSame('foo', $this->client->getAccessToken()->getToken());
    }

    public function testSetAccessToken(): void
    {
        $token = new AccessToken(['access_token' => 'bar', 'expires' => time() + 3600]);
        $this->client->setAccessToken($token);

        $this->assertSame('bar', $this->client->getAccessToken()->getToken());
    }

    public function testWithPATStaticFactory(): void
    {
        $path = sys_get_temp_dir() . '/asana-client-test-token.json';
        @unlink($path);
        $client = AsanaClient::withPAT('my-pat', $path);
        $this->assertSame('my-pat', $client->getAccessToken()->getToken());
        @unlink($path);
    }

    public function testWithAccessTokenStaticFactory(): void
    {
        $path = sys_get_temp_dir() . '/asana-client-test-token.json';
        @unlink($path);
        $tokenData = ['access_token' => 'abc', 'expires' => time() + 3600];
        $client = AsanaClient::withAccessToken('id', 'secret', $tokenData, $path);
        $this->assertSame('abc', $client->getAccessToken()->getToken());
        @unlink($path);
    }

    public function testIsHandshake(): void
    {
        $this->assertTrue($this->client->isHandshake(['X-Hook-Secret' => '123']));
        $this->assertFalse($this->client->isHandshake([]));
    }

    public function testHandleHandshake(): void
    {
        $this->assertSame('123', $this->client->handleHandshake(['X-Hook-Secret' => '123']));
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testRefreshTokenDelegatesToTokenManager(): void
    {
        $oldToken = new AccessToken(['access_token' => 'old', 'refresh_token' => 'ref', 'expires' => time() - 10]);
        $newToken = new AccessToken(['access_token' => 'new', 'expires' => time() + 3600]);

        $this->mockAuthHandler()
            ->expects($this->once())
            ->method('refreshToken')
            ->willReturn($newToken);

        $this->client->setAccessToken($oldToken);

        $this->client->refreshToken();

        $this->assertSame('new', $this->client->getAccessToken()->getToken());
    }

    /**
     * @return AuthHandlerInterface&MockObject
     */
    private function mockAuthHandler(): AuthHandlerInterface
    {
        if ($this->authHandler instanceof MockObject) {
            return $this->authHandler;
        }

        $this->authHandler = $this->createMock(AuthHandlerInterface::class);
        $this->container->set(AuthHandlerInterface::class, $this->authHandler);
        $this->container->set(TokenManager::class, new TokenManager($this->storage, $this->authHandler));

        return $this->authHandler;
    }
}
