<?php

namespace BrightleafDigital\Auth;

use BrightleafDigital\Exceptions\AuthException;
use Exception;
use League\OAuth2\Client\Token\AccessToken;

/**
 * Interface for the Asana OAuth authentication handler.
 */
interface AuthHandlerInterface
{
    /**
     * Returns authorization data like URL, state, and PKCE verifier (if enabled)
     *
     * @param array $options
     * @param bool $enableState
     * @param bool $enablePKCE
     * @return array ['url' => string, 'state' => string|null, 'codeVerifier' => string|null]
     * @throws Exception If secure parameter generation (random_bytes) fails
     */
    public function getSecureAuthorizationUrl(array $options, bool $enableState = true, bool $enablePKCE = true): array;

    /**
     * Handles the callback and retrieves an access token.
     *
     * @param string $authorizationCode The code returned by the OAuth callback
     * @param string|null $codeVerifier The PKCE code verifier (optional)
     * @return AccessToken
     * @throws AuthException If the OAuth provider request fails or returns an error
     */
    public function handleCallback(string $authorizationCode, ?string $codeVerifier = null): AccessToken;

    /**
     * Retrieves the authorization URL for initiating the authentication process.
     *
     * @param array $options
     * @return string The authorization URL.
     */
    public function getAuthorizationUrl(array $options): string;

    /**
     * Retrieves an access token using the provided authorization code.
     *
     * @param string $authorizationCode The authorization code.
     * @return AccessToken
     * @throws AuthException If the OAuth provider request fails or returns an error
     */
    public function getAccessToken(string $authorizationCode): AccessToken;

    /**
     * Refreshes the access token.
     *
     * @param AccessToken $token The current access token.
     * @return AccessToken The newly refreshed access token.
     * @throws AuthException If the OAuth provider request fails or returns an error
     */
    public function refreshToken(AccessToken $token): AccessToken;
}
