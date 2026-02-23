<?php

require '../vendor/autoload.php';

use BrightleafDigital\AsanaClient;
use BrightleafDigital\Auth\Scopes;
use Dotenv\Dotenv;


$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$clientId    = $_ENV['ASANA_CLIENT_ID'];
$clientSecret = $_ENV['ASANA_CLIENT_SECRET'];
$redirectUri  = $_ENV['ASANA_REDIRECT_URI'] ?? null;
$salt         = $_ENV['SALT'] ?? ($_ENV['PASSWORD'] ?? null);

$asanaClient = AsanaClient::OAuth($clientId, $clientSecret, $redirectUri, __DIR__ . '/token.json', null, $salt);

if ($asanaClient->getAccessToken()) {
    header('Location: workspaces.php');
    exit;
}

/*$authUrl = $asanaClient->getAuthorizationUrl();
header('Location: ' . $authUrl);
exit;*/

$scopes = [
    Scopes::ATTACHMENTS_WRITE,
    Scopes::PROJECTS_READ,
    Scopes::TASKS_READ,
    Scopes::TASKS_WRITE,
    Scopes::TASKS_DELETE,
    Scopes::USERS_READ,
    Scopes::WORKSPACES_READ
];
$authData = $asanaClient->getSecureAuthorizationUrl([]);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['oauth2_state'] = $authData['state'];
$_SESSION['oauth2_pkce_verifier'] = $authData['codeVerifier'];

header('Location: ' . $authData['url']);
exit;
