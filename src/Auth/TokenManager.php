<?php

namespace BrightleafDigital\Auth;

use BrightleafDigital\Exceptions\AuthException;
use BrightleafDigital\Exceptions\TokenInvalidException;
use BrightleafDigital\Storage\TokenStorageInterface;
use GuzzleHttp\Exception\GuzzleException;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Manages the lifecycle of the Asana access token.
 */
class TokenManager
{
    private ?AccessToken $accessToken = null;
    private TokenStorageInterface $storage;
    private ?AuthHandlerInterface $authHandler;
    private LoggerInterface $logger;

    /**
     * @var array<string|int, callable>
     */
    private array $refreshSubscribers = [];

    public function __construct(
        TokenStorageInterface $storage,
        ?AuthHandlerInterface $authHandler = null,
        ?LoggerInterface $logger = null
    ) {
        $this->storage = $storage;
        $this->authHandler = $authHandler;
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * Returns the current access token string, refreshing if necessary.
     *
     * @return string
     * @throws TokenInvalidException
     */
    public function getAccessTokenString(): string
    {
        $this->ensureValidToken();
        return $this->accessToken->getToken();
    }

    /**
     * Returns the full AccessToken object.
     *
     * @return AccessToken|null
     */
    public function getAccessToken(): ?AccessToken
    {
        if ($this->accessToken === null) {
            $this->loadToken();
        }
        return $this->accessToken;
    }

    /**
     * Sets the current access token and saves it to storage.
     *
     * @param AccessToken $token
     * @return void
     */
    public function setAccessToken(AccessToken $token): void
    {
        $this->accessToken = $token;
        $this->storage->save($token->jsonSerialize());
        $this->notifySubscribers($token);
    }

    /**
     * Ensures the token is loaded and valid.
     *
     * @throws TokenInvalidException
     */
    public function ensureValidToken(): void
    {
        if ($this->accessToken === null) {
            $this->loadToken();
        }

        if ($this->accessToken === null) {
            throw new TokenInvalidException('No access token available. Please authenticate.');
        }

        if ($this->accessToken->hasExpired()) {
            $this->refreshToken();
        }
    }

    /**
     * Refreshes the token using the refresh token.
     *
     * @throws TokenInvalidException
     */
    public function refreshToken(): void
    {
        if ($this->authHandler === null) {
            throw new TokenInvalidException('OAuth credentials not provided. Cannot refresh token.');
        }

        $token = $this->getAccessToken();
        if ($token === null || $token->getRefreshToken() === null) {
            throw new TokenInvalidException('No refresh token available. Cannot refresh token.');
        }

        try {
            $newToken = $this->authHandler->refreshToken($token);
            $this->setAccessToken($newToken);
            $this->logger->info('Access token refreshed successfully');
        } catch (AuthException $e) {
            $this->logger->error('Failed to refresh access token', ['error' => $e->getMessage()]);
            throw new TokenInvalidException('Failed to refresh access token: ' . $e->getMessage(), 0, [], $e);
        }
    }

    /**
     * Loads the token from storage.
     */
    private function loadToken(): void
    {
        $data = $this->storage->load();
        if ($data) {
            $this->accessToken = new AccessToken($data);
        }
    }

    /**
     * Adds a subscriber to be notified when the token is refreshed.
     *
     * @param callable $callback
     * @param string|int|null $id
     * @return void
     */
    public function subscribeToRefresh(callable $callback, $id = null): void
    {
        if ($id !== null) {
            $this->refreshSubscribers[$id] = $callback;
        } else {
            $this->refreshSubscribers[] = $callback;
        }
    }

    /**
     * Notifies all subscribers of a token refresh.
     */
    private function notifySubscribers(AccessToken $token): void
    {
        foreach ($this->refreshSubscribers as $callback) {
            $callback($token);
        }
    }
}
