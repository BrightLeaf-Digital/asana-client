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
    $custom_fields = $asanaClient->customFields()->getCustomFieldSettingsForProject(
        $_GET['project']
    );
    if (empty($custom_fields)) {
        echo "No custom fields found for project";
    }
    foreach ($custom_fields as $custom_field) {
        echo "<pre>";
        var_dump($custom_field);
        echo "</pre>";
        $gid = urlencode($custom_field['custom_field']['gid']);
        $href = "customField.php?gid=$gid";
        echo '<a href="' . htmlspecialchars($href) . '">View Custom field</a>';
    }
} catch (ApiException $e) {
    echo 'Error: ' . $e->getMessage();
}
