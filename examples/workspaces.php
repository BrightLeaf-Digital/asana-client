<?php

use BrightleafDigital\AsanaClient;
use BrightleafDigital\Exceptions\ApiException;
use BrightleafDigital\Exceptions\TokenInvalidException;
use BrightleafDigital\Storage\TokenStorageInterface;
use Dotenv\Dotenv;

require '../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$clientId     = $_ENV['ASANA_CLIENT_ID'];
$clientSecret = $_ENV['ASANA_CLIENT_SECRET'];
$redirectUri  = $_ENV['ASANA_REDIRECT_URI'] ?? null;
$salt         = $_ENV['SALT'] ?? ($_ENV['PASSWORD'] ?? null);

$asanaClient = AsanaClient::OAuth($clientId, $clientSecret, $redirectUri, __DIR__ . '/token.json', null, $salt);

$asanaClient->subscribeToTokenRefresh(function ($token) use ($asanaClient) {
    $asanaClient->getContainer()
        ->get(TokenStorageInterface::class)
        ->save($token->jsonSerialize());
});

try {
    $me = $asanaClient->users()->getCurrentUser();
    $name = $me['name'];
    ?>
        <h1>Hello, <?= $name ?>!</h1>
    <?php
    $workspaces = $asanaClient->workspaces()->getWorkspaces();
    foreach ($workspaces as $workspace) {
        echo '<a href="projects.php?workspace=' . $workspace['gid'] . '">' . $workspace['name'] . '</a><br>';
    }
} catch (ApiException $e) {
    echo 'Error: ' . $e->getMessage();
}
