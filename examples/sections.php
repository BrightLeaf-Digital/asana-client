<?php

use BrightleafDigital\AsanaClient;
use BrightleafDigital\Exceptions\ApiException;
use BrightleafDigital\Exceptions\TokenInvalidException;
use BrightleafDigital\Exceptions\ValidationException;
use BrightleafDigital\Http\HttpClientInterface;
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
    $sections = $asanaClient->sections()->getSectionsForProject(
        $_GET['project'],
        ['opt_pretty' => true],
        HttpClientInterface::RESPONSE_FULL
    );
    echo '<pre>';
    print_r($sections['body']['data']);
    echo '</pre>';
} catch (ApiException $e) {
    echo 'Error: ' . $e->getMessage();
} catch (ValidationException $e) {
}
