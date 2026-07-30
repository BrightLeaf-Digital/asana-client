<?php

namespace BrightleafDigital\Tests\Storage;

use BrightleafDigital\Storage\FileTokenStorage;
use PHPUnit\Framework\TestCase;

class FileTokenStorageTest extends TestCase
{
    private string $path;

    protected function setUp(): void
    {
        $this->path = sys_get_temp_dir() . '/asana-client-file-storage-test.json';
        @unlink($this->path);
    }

    protected function tearDown(): void
    {
        @unlink($this->path);
    }

    public function testSaveAndLoadRoundTripWithEncryption(): void
    {
        $storage = new FileTokenStorage($this->path, 'salty');
        $token = ['access_token' => 'abc', 'refresh_token' => 'def', 'expires' => 1234567890];

        $storage->save($token);

        $this->assertSame($token, $storage->load());
        $this->assertStringNotContainsString('abc', (string) file_get_contents($this->path));
    }

    public function testSavingAnUnchangedTokenDoesNotRewriteTheFile(): void
    {
        // Encryption uses a fresh IV per write, so a rewrite is always visible in the file contents.
        $storage = new FileTokenStorage($this->path, 'salty');
        $token = ['access_token' => 'abc', 'refresh_token' => 'def', 'expires' => 1234567890];

        $storage->save($token);
        $first = file_get_contents($this->path);

        $storage->save($token);

        $this->assertSame($first, file_get_contents($this->path));
    }

    public function testSavingAnUnchangedTokenIgnoresKeyOrder(): void
    {
        $storage = new FileTokenStorage($this->path, 'salty');

        $storage->save(['access_token' => 'abc', 'expires' => 1234567890]);
        $first = file_get_contents($this->path);

        $storage->save(['expires' => 1234567890, 'access_token' => 'abc']);

        $this->assertSame($first, file_get_contents($this->path));
    }

    public function testSavingAChangedTokenRewritesTheFile(): void
    {
        $storage = new FileTokenStorage($this->path, 'salty');

        $storage->save(['access_token' => 'abc', 'expires' => 1234567890]);
        $storage->save(['access_token' => 'xyz', 'expires' => 1234567890]);

        $this->assertSame(['access_token' => 'xyz', 'expires' => 1234567890], $storage->load());
    }
}
