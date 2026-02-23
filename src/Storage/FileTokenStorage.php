<?php

namespace BrightleafDigital\Storage;

use BrightleafDigital\Utils\CryptoUtils;
use Exception;

/**
 * File-based implementation of TokenStorageInterface with optional encryption.
 */
class FileTokenStorage implements TokenStorageInterface
{
    /**
     * @var string
     */
    private string $path;

    /**
     * @var string|null
     */
    private ?string $password;

    /**
     * @param string $path
     * @param string|null $password
     */
    public function __construct(string $path, ?string $password = null)
    {
        $this->path = $path;
        $this->password = $password;
    }

    /**
     * @inheritDoc
     */
    public function load(): ?array
    {
        if (!file_exists($this->path)) {
            return null;
        }

        $content = file_get_contents($this->path);
        if ($content === false) {
            return null;
        }

        $token = json_decode($content, true);
        if (!$token || !$this->password) {
            return $token;
        }

        // Decrypt sensitive fields if password is provided
        try {
            if (isset($token['access_token'])) {
                $token['access_token'] = CryptoUtils::decrypt($token['access_token'], $this->password);
            }
            if (isset($token['refresh_token'])) {
                $token['refresh_token'] = CryptoUtils::decrypt($token['refresh_token'], $this->password);
            }
        } catch (Exception $e) {
            // If decryption fails, we treat it as if the token is invalid/not found
            return null;
        }

        return $token;
    }

    /**
     * @inheritDoc
     * @throws Exception If required OpenSSL functions are unavailable or encryption fails.
     */
    public function save(array $token): void
    {
        if ($this->password) {
            if (isset($token['access_token'])) {
                $token['access_token'] = CryptoUtils::encrypt($token['access_token'], $this->password);
            }
            if (isset($token['refresh_token'])) {
                $token['refresh_token'] = CryptoUtils::encrypt($token['refresh_token'], $this->password);
            }
        }

        file_put_contents($this->path, json_encode($token, JSON_PRETTY_PRINT));
    }

    /**
     * @inheritDoc
     */
    public function clear(): void
    {
        if (file_exists($this->path)) {
            unlink($this->path);
        }
    }
}
