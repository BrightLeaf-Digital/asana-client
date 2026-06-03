<?php

namespace BrightleafDigital\Storage;

use Exception;

/**
 * Interface for OAuth token storage.
 */
interface TokenStorageInterface
{
    /**
     * Loads the token from storage.
     *
     * @return array|null The token data or null if not found.
     */
    public function load(): ?array;

    /**
     * Saves the token to storage.
     *
     * @param array $token The token data to save.
     * @return void
     * @throws Exception If an implementation that encrypts tokens fails to encrypt before saving.
     */
    public function save(array $token): void;

    /**
     * Clears the token from storage.
     *
     * @return void
     */
    public function clear(): void;
}
