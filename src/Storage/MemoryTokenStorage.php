<?php

namespace BrightleafDigital\Storage;

/**
 * In-memory implementation of TokenStorageInterface.
 * Tokens are only stored for the duration of the script execution.
 */
class MemoryTokenStorage implements TokenStorageInterface
{
    /**
     * @var array|null
     */
    private ?array $token = null;

    /**
     * @inheritDoc
     */
    public function load(): ?array
    {
        return $this->token;
    }

    /**
     * @inheritDoc
     */
    public function save(array $token): void
    {
        $this->token = $token;
    }

    /**
     * @inheritDoc
     */
    public function clear(): void
    {
        $this->token = null;
    }
}
