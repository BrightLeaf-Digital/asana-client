<?php

namespace BrightleafDigital\Auth;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use UnexpectedValueException;

class AsanaOAuthHandler
{
    /**
     * @property OAuth2Provider $provider The OAuth2 provider instance for Asana authentication
     */
    private OAuth2Provider $provider;

    /**
     * @var LoggerInterface PSR-3 compatible logger instance
     */
    private LoggerInterface $logger;

    /**
     * Initializes the OAuth2 provider with the given client configuration.
     *
     * @param string $clientId The client identifier issued by the authorization server.
     * @param string $clientSecret The client secret associated with the client ID.
     * @param string $redirectUri The URI the authorization server redirects to after authorization.
     * @param LoggerInterface|null $logger PSR-3 compatible logger instance.
     * @return void
     */
    public function __construct(string $clientId, string $clientSecret, string $redirectUri, ?LoggerInterface $logger = null)
    {
        $this->provider = new OAuth2Provider([
            'clientId'     => $clientId,
            'clientSecret' => $clientSecret,
            'redirectUri'  => $redirectUri,
        ]);
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * Returns authorization data like URL, state, and PKCE verifier (if enabled)
     *
     * @param array $options
     * @param bool $enableState
     * @param bool $enablePKCE
     * @return array ['url' => string, 'state' => string|null, 'codeVerifier' => string|null]
     */
    public function getSecureAuthorizationUrl(array $options, bool $enableState = true, bool $enablePKCE = true): array
    {
        $this->logger->info('Generating secure authorization URL', [
            'options' => $options,
            'enable_state' => $enableState,
            'enable_pkce' => $enablePKCE,
        ]);

        return $this->provider->getSecureAuthorizationUrl($options, $enableState, $enablePKCE);
    }

    /**
     * Handles the callback and retrieves an access token.
     * Validates state and uses code_verifier if PKCE is enabled.
     *
     * @param string $authorizationCode The code returned by the OAuth callback
     * @param string|null $codeVerifier The PKCE code verifier (optional)
     *
     * @return AccessToken
     *
     * @throws GuzzleException
     * @throws IdentityProviderException
     * @throws UnexpectedValueException
     */
    public function handleCallback(string $authorizationCode, ?string $codeVerifier = null): AccessToken
    {
        $this->logger->info('Processing OAuth callback');

        try {
            $token = $this->provider->getAccessToken('authorization_code', [
                'code' => $authorizationCode,
                'code_verifier' => $codeVerifier, // Optional for PKCE
            ]);

            $this->logger->info('OAuth token obtained successfully', [
                'expires' => $token->getExpires()
            ]);

            return new AccessToken($token->jsonSerialize());
        } catch (Exception $e) {
            $this->logger->error('OAuth callback failed', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Retrieves the authorization URL for initiating the authentication process.
     *
     * @return string The authorization URL.
     */
    public function getAuthorizationUrl(array $options): string
    {
        $this->logger->info('Generating authorization URL', [
            'options' => $options,
        ]);

        return $this->provider->getAuthorizationUrl($options);
    }

    /**
     * Retrieves an access token using the provided authorization code.
     *
     * @param string $authorizationCode The authorization code received from the authorization server.
     *
     * @return AccessToken The access token details, typically including token type, expiry, and other information.
     * @throws GuzzleException
     * @throws IdentityProviderException
     */
    public function getAccessToken(string $authorizationCode): AccessToken
    {
        $this->logger->info('Retrieving access token using authorization code');

        try {
            $token = $this->provider->getAccessToken('authorization_code', [
                'code' => $authorizationCode,
            ]);

            $this->logger->info('OAuth token obtained successfully', [
                'expires' => $token->getExpires()
            ]);

            return new AccessToken($token->jsonSerialize());
        } catch (Exception $e) {
            $this->logger->error('Failed to retrieve access token', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Refreshes the access token using the provided token's refresh token.
     *
     * @param AccessToken $token The current access token that contains the refresh token needed for renewal.
     *
     * @return AccessToken The newly refreshed access token.
     * @throws GuzzleException
     * @throws IdentityProviderException
     */
    public function refreshToken(AccessToken $token): AccessToken
    {
        $this->logger->info('Refreshing access token');

        try {
            // Get the new token from the provider
            $newToken = $this->provider->getAccessToken('refresh_token', [
                'refresh_token' => $token->getRefreshToken(),
            ]);

            // Ensure the new token retains the current refresh token
            $tokenData = $newToken->jsonSerialize();
            $tokenData['refresh_token'] = $token->getRefreshToken();

            $this->logger->info('OAuth token refreshed successfully', [
                'expires' => $newToken->getExpires()
            ]);

            // Return a new AccessToken instance with the updated data
            return new AccessToken($tokenData);
        } catch (Exception $e) {
            $this->logger->error('Failed to refresh access token', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
