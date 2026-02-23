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
    $projects = $asanaClient->projects()->getProjects(
        $_GET['workspace']
    );

    foreach ($projects as $project) {
        echo '<h3>' . htmlspecialchars($project['name']) . '</h3>';

        $urlEncodedGid = urlencode($project['gid']);

        $href = "tasks.php?project=$urlEncodedGid";
        echo '<a href="' . htmlspecialchars($href) . '">Tasks</a><br>';

        $href = "sections.php?project=$urlEncodedGid";
        echo '<a href="' . htmlspecialchars($href) . '">Sections</a><br>';

        $href = "memberships.php?project=$urlEncodedGid";
        echo '<a href="' . htmlspecialchars($href) . '">Memberships</a><br>';

        $href = "createTask.php?project=$urlEncodedGid";
        echo '<a href="' . htmlspecialchars($href) . '">Create Task</a><br>';

        $href = "customFields.php?project=$urlEncodedGid";
        echo '<a href="' . htmlspecialchars($href) . '">Custom Fields</a><br>';
        echo '<hr>';
    }
} catch (ApiException $e) {
    echo 'Error: ' . $e->getMessage();
}
