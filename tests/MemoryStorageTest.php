<?php

namespace BrightleafDigital\Tests;

use BrightleafDigital\AsanaClient;
use BrightleafDigital\Storage\MemoryTokenStorage;
use PHPUnit\Framework\TestCase;

class MemoryStorageTest extends TestCase
{
    private string $tokenFile;

    protected function setUp(): void
    {
        $this->tokenFile = getcwd() . '/token.json';
        if (file_exists($this->tokenFile)) {
            unlink($this->tokenFile);
        }
    }

    protected function tearDown(): void
    {
        if (file_exists($this->tokenFile)) {
            unlink($this->tokenFile);
        }
    }

    public function testWithPATDoesNotCreateFileWhenStorageIsFalse(): void
    {
        $client = AsanaClient::withPAT('my-pat', false);

        $this->assertSame('my-pat', $client->getAccessToken()->getToken());
        $this->assertFileDoesNotExist(
            $this->tokenFile,
            'token.json should not be created when using memory storage (false)'
        );
    }

    public function testWithAccessTokenDoesNotCreateFileWhenStorageIsFalse(): void
    {
        $tokenData = ['access_token' => 'abc', 'expires' => time() + 3600];
        $client = AsanaClient::withAccessToken('id', 'secret', $tokenData, false);

        $this->assertSame('abc', $client->getAccessToken()->getToken());
        $this->assertFileDoesNotExist(
            $this->tokenFile,
            'token.json should not be created when using memory storage (false)'
        );
    }

    public function testWithPATCreatesFileByDefault(): void
    {
        // This test ensures we didn't break backward compatibility
        $client = AsanaClient::withPAT('my-pat-default');

        $this->assertSame('my-pat-default', $client->getAccessToken()->getToken());
        $this->assertFileExists($this->tokenFile, 'token.json should be created by default');
    }

    public function testWithCustomMemoryStorage(): void
    {
        $storage = new MemoryTokenStorage();
        $client = AsanaClient::withPAT('custom-mem', $storage);

        $this->assertSame('custom-mem', $client->getAccessToken()->getToken());
        $this->assertFileDoesNotExist($this->tokenFile);

        // Verify storage was actually used
        $this->assertSame('custom-mem', $storage->load()['access_token']);
    }
}
