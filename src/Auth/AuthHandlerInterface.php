<?php

namespace BrightleafDigital\Auth;

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
     */
    public function getSecureAuthorizationUrl(array $options, bool $enableState = true, bool $enablePKCE = true): array;

    /**
     * Handles the callback and retrieves an access token.
     *
     * @param string $authorizationCode The code returned by the OAuth callback
     * @param string|null $codeVerifier The PKCE code verifier (optional)
     * @return AccessToken
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
     */
    public function getAccessToken(string $authorizationCode): AccessToken;

    /**
     * Refreshes the access token.
     *
     * @param AccessToken $token The current access token.
     * @return AccessToken The newly refreshed access token.
     */
    public function refreshToken(AccessToken $token): AccessToken;
}
