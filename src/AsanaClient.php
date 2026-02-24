<?php

namespace BrightleafDigital;

use BrightleafDigital\Api\AttachmentApiService;
use BrightleafDigital\Api\BatchApiService;
use BrightleafDigital\Api\CustomFieldApiService;
use BrightleafDigital\Api\EventsApiService;
use BrightleafDigital\Api\GoalsApiService;
use BrightleafDigital\Api\MembershipApiService;
use BrightleafDigital\Api\PortfoliosApiService;
use BrightleafDigital\Api\ProjectApiService;
use BrightleafDigital\Api\ProjectTemplatesApiService;
use BrightleafDigital\Api\SectionApiService;
use BrightleafDigital\Api\StatusUpdatesApiService;
use BrightleafDigital\Api\StoriesApiService;
use BrightleafDigital\Api\TagsApiService;
use BrightleafDigital\Api\TaskApiService;
use BrightleafDigital\Api\TeamsApiService;
use BrightleafDigital\Api\TimeTrackingEntriesApiService;
use BrightleafDigital\Api\UserApiService;
use BrightleafDigital\Api\UserTaskListsApiService;
use BrightleafDigital\Api\WebhooksApiService;
use BrightleafDigital\Api\WorkspaceApiService;
use BrightleafDigital\Auth\AsanaOAuthHandler;
use BrightleafDigital\Auth\AuthHandlerInterface;
use BrightleafDigital\Auth\TokenManager;
use BrightleafDigital\Container\ServiceContainer;
use BrightleafDigital\Http\AsanaApiClient;
use BrightleafDigital\Http\HttpClientInterface;
use BrightleafDigital\Storage\FileTokenStorage;
use BrightleafDigital\Storage\TokenStorageInterface;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * The main client for interacting with the Asana API.
 * This class acts as a facade for all Asana API services and handles authentication and configuration.
 */
class AsanaClient implements AsanaClientInterface
{
    private ContainerInterface $container;

    /**
     * Initializes the Asana client with a dependency injection container.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Bootstraps a default AsanaClient with OAuth configuration.
     *
     * @param string|null $clientId
     * @param string|null $clientSecret
     * @param string|null $redirectUri
     * @param string|null $tokenStoragePath
     * @param LoggerInterface|null $logger
     * @param string|null $salt Optional salt/password for encrypting tokens.
     *
     * @return AsanaClient
     */
    public static function OAuth(
        ?string $clientId = null,
        ?string $clientSecret = null,
        ?string $redirectUri = null,
        ?string $tokenStoragePath = null,
        ?LoggerInterface $logger = null,
        ?string $salt = null
    ): self {
        $container = new ServiceContainer();
        $logger = $logger ?? new NullLogger();
        $container->set(LoggerInterface::class, $logger);

        $path = $tokenStoragePath ?? getcwd() . '/token.json';
        $container->set(TokenStorageInterface::class, new FileTokenStorage($path, $salt));

        if ($clientId && $clientSecret) {
            $container->set(
                AuthHandlerInterface::class,
                new AsanaOAuthHandler($clientId, $clientSecret, (string)$redirectUri, $logger)
            );
        }

        $container->set(TokenManager::class, function ($c) {
            return new TokenManager(
                $c->get(TokenStorageInterface::class),
                $c->has(AuthHandlerInterface::class) ? $c->get(AuthHandlerInterface::class) : null,
                $c->get(LoggerInterface::class)
            );
        });

        $container->set(HttpClientInterface::class, function ($c) {
            return new AsanaApiClient(
                [$c->get(TokenManager::class), 'getAccessTokenString'],
                $c->get(LoggerInterface::class)
            );
        });

        // Register all services
        self::registerServices($container);

        return new self($container);
    }

    /**
     * Bootstraps a default AsanaClient with a Personal Access Token (PAT).
     *
     * @param string $personalAccessToken The Personal Access Token (PAT) to use for authentication.
     * @param string|null $tokenStoragePath Optional path to store the token securely.
     * @param LoggerInterface|null $logger Optional logger instance for logging.
     * @param string|null $salt Optional salt for token storage encryption.
     *
     * @return self The configured AsanaClient instance.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function withPAT(
        string $personalAccessToken,
        ?string $tokenStoragePath = null,
        ?LoggerInterface $logger = null,
        ?string $salt = null
    ): self {
        $client = self::OAuth(null, null, null, $tokenStoragePath, $logger, $salt);
        $tokenManager = $client->getContainer()->get(TokenManager::class);
        $tokenManager->setAccessToken(new AccessToken(['access_token' => $personalAccessToken]));
        return $client;
    }

    /**
     * Bootstraps a default AsanaClient with an existing access token.
     *
     * @param string $clientId The client ID for the OAuth application.
     * @param string $clientSecret The client secret for the OAuth application.
     * @param array $token The access token to use for authentication.
     * @param string|null $tokenStoragePath Optional path to store the token securely.
     * @param LoggerInterface|null $logger Optional logger instance for logging.
     * @param string|null $salt Optional salt for token storage encryption.
     *
     * @return self The configured AsanaClient instance.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function withAccessToken(
        string $clientId,
        string $clientSecret,
        array $token,
        ?string $tokenStoragePath = null,
        ?LoggerInterface $logger = null,
        ?string $salt = null
    ): self {
        $client = self::OAuth($clientId, $clientSecret, '', $tokenStoragePath, $logger, $salt);
        $tokenManager = $client->getContainer()->get(TokenManager::class);
        $tokenManager->setAccessToken(new AccessToken($token));
        return $client;
    }

    private static function registerServices(ServiceContainer $container): void
    {
        $services = [
            TaskApiService::class,
            ProjectApiService::class,
            UserApiService::class,
            TagsApiService::class,
            SectionApiService::class,
            MembershipApiService::class,
            AttachmentApiService::class,
            BatchApiService::class,
            CustomFieldApiService::class,
            EventsApiService::class,
            GoalsApiService::class,
            PortfoliosApiService::class,
            ProjectTemplatesApiService::class,
            StatusUpdatesApiService::class,
            StoriesApiService::class,
            TeamsApiService::class,
            TimeTrackingEntriesApiService::class,
            UserTaskListsApiService::class,
            WebhooksApiService::class,
            WorkspaceApiService::class,
        ];

        foreach ($services as $service) {
            $container->set($service, function ($c) use ($service) {
                return new $service($c->get(HttpClientInterface::class));
            });
        }
    }

    /**
     * Returns the underlying container.
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    // --- Service Accessors ---

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function tasks(): TaskApiService
    {
        return $this->container->get(TaskApiService::class);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function projects(): ProjectApiService
    {
        return $this->container->get(ProjectApiService::class);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function users(): UserApiService
    {
        return $this->container->get(UserApiService::class);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function tags(): TagsApiService
    {
        return $this->container->get(TagsApiService::class);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function sections(): SectionApiService
    {
        return $this->container->get(SectionApiService::class);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function memberships(): MembershipApiService
    {
        return $this->container->get(MembershipApiService::class);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function attachments(): AttachmentApiService
    {
        return $this->container->get(AttachmentApiService::class);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function batch(): BatchApiService
    {
        return $this->container->get(BatchApiService::class);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function customFields(): CustomFieldApiService
    {
        return $this->container->get(CustomFieldApiService::class);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function events(): EventsApiService
    {
        return $this->container->get(EventsApiService::class);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function goals(): GoalsApiService
    {
        return $this->container->get(GoalsApiService::class);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function portfolios(): PortfoliosApiService
    {
        return $this->container->get(PortfoliosApiService::class);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function projectTemplates(): ProjectTemplatesApiService
    {
        return $this->container->get(ProjectTemplatesApiService::class);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function statusUpdates(): StatusUpdatesApiService
    {
        return $this->container->get(StatusUpdatesApiService::class);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function stories(): StoriesApiService
    {
        return $this->container->get(StoriesApiService::class);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function teams(): TeamsApiService
    {
        return $this->container->get(TeamsApiService::class);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function timeTrackingEntries(): TimeTrackingEntriesApiService
    {
        return $this->container->get(TimeTrackingEntriesApiService::class);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function userTaskLists(): UserTaskListsApiService
    {
        return $this->container->get(UserTaskListsApiService::class);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function webhooks(): WebhooksApiService
    {
        return $this->container->get(WebhooksApiService::class);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function workspaces(): WorkspaceApiService
    {
        return $this->container->get(WorkspaceApiService::class);
    }

    // --- Authentication & Helper Methods ---

    /**
     * Generates and returns the authorization URL.
     *
     * @param array $options Optional parameters for generating the authorization URL.
     *
     * @return string The generated authorization URL.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getAuthorizationUrl(array $options = []): string
    {
        return $this->container->get(AuthHandlerInterface::class)->getAuthorizationUrl($options);
    }

    /**
     * Returns authorization data like URL, state, and PKCE verifier (if enabled).
     *
     * @param array $options Optional parameters for generating the authorization URL.
     * @param bool $enableState Whether to enable CSRF protection using the state parameter.
     * @param bool $enablePKCE Whether to enable PKCE for enhanced security.
     *
     * @return array ['url' => string, 'state' => string|null, 'codeVerifier' => string|null]
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getSecureAuthorizationUrl(array $options, bool $enableState = true, bool $enablePKCE = true): array
    {
        return $this->container->get(AuthHandlerInterface::class)
            ->getSecureAuthorizationUrl($options, $enableState, $enablePKCE);
    }

    /**
     * Handles the callback process to retrieve and store an access token.
     *
     * @param string $code The authorization code received from the authentication flow.
     * @param string|null $codeVerifier An optional code verifier for PKCE (Proof Key for Code Exchange).
     *
     * @return AccessToken The retrieved access token.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function handleCallback(string $code, ?string $codeVerifier = null): AccessToken
    {
        $token = $this->container->get(AuthHandlerInterface::class)->handleCallback($code, $codeVerifier);
        $this->container->get(TokenManager::class)->setAccessToken($token);
        return $token;
    }

    /**
     * Sets the access token in the token manager.
     *
     * @param AccessToken $token The access token to be set.
     *
     * @return void
     * @throws ContainerExceptionInterface
     */
    public function setAccessToken(AccessToken $token): void
    {
        $this->container->get(TokenManager::class)->setAccessToken($token);
    }

    /**
     * Retrieves the access token.
     *
     * @return AccessToken|null The access token or null if not available.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getAccessToken(): ?AccessToken
    {
        return $this->container->get(TokenManager::class)->getAccessToken();
    }

    /**
     * Refreshes the authentication token by delegating the operation to the TokenManager.
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function refreshToken(): void
    {
        $this->container->get(TokenManager::class)->refreshToken();
    }

    /**
     * Subscribe to token refresh events.
     *
     * @param callable $callback
     * @param string|int|null $id
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function subscribeToTokenRefresh(callable $callback, $id = null): void
    {
        $this->container->get(TokenManager::class)->subscribeToRefresh($callback, $id);
    }

    /**
     * Webhook Handshake Helpers
     */
    public function isHandshake(array $headers): bool
    {
        return isset($headers['X-Hook-Secret']) || isset($headers['x-hook-secret']);
    }

    public function handleHandshake(array $headers): string
    {
        return $headers['X-Hook-Secret'] ?? $headers['x-hook-secret'] ?? '';
    }
}
