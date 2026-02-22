<?php

require_once __DIR__ . '/../vendor/autoload.php';

use BrightleafDigital\AsanaClient;

/**
 * Example Webhook Handler for Asana
 *
 * This script demonstrates how to handle Asana webhooks, including:
 * 1. Handling the initial handshake
 * 2. Verifying the signature of subsequent event deliveries
 * 3. Processing the events
 */

// Your Asana configuration (not strictly needed for signature verification alone,
// but needed if you want to make further API calls)
$clientId = getenv('ASANA_CLIENT_ID');
$clientSecret = getenv('ASANA_CLIENT_SECRET');
$redirectUri = getenv('ASANA_REDIRECT_URI');

$client = new AsanaClient($clientId, $clientSecret, $redirectUri);
$webhooks = $client->webhooks();

// 1. Get the request details
$headers = getallheaders();
$requestBody = file_get_contents('php://input');

// 2. Handle Handshake
// When creating a webhook, Asana sends a test request with X-Hook-Secret.
// You must respond with 200 OK and the same secret in the header.
if ($webhooks->isHandshakeRequest($headers)) {
    $secret = $webhooks->getHandshakeSecret($headers);

    // In a real application, you should store this secret associated with the
    // resource/webhook GID to verify future requests.
    // file_put_contents('webhook_secret.txt', $secret);

    header('X-Hook-Secret: ' . $secret);
    http_response_code(200);
    exit;
}

// 3. Handle Event Delivery
// Subsequent requests will have X-Hook-Signature instead of X-Hook-Secret.
$signature = $headers['X-Hook-Signature'] ?? $headers['x-hook-signature'] ?? '';

// You should retrieve the secret you stored during the handshake for this webhook.
$storedSecret = getenv('ASANA_WEBHOOK_SECRET'); // Or from database/file

if (empty($signature) || empty($storedSecret)) {
    http_response_code(401);
    echo "Missing signature or secret";
    exit;
}

$eventData = $webhooks->handleWebhookEvent($requestBody, $signature, $storedSecret);

if ($eventData === null) {
    // Signature verification failed
    http_response_code(401);
    echo "Invalid signature";
    exit;
}

// 4. Process Events
$events = $eventData['events'] ?? [];
foreach ($events as $event) {
    $action = $event['action'] ?? '';
    $resource = $event['resource'] ?? [];
    $resourceType = $resource['resource_type'] ?? '';
    $resourceGid = $resource['gid'] ?? '';

    error_log("Received event: $action on $resourceType ($resourceGid)");

    // Example: if a task changed, maybe fetch the full task details
    if ($resourceType === 'task' && $action === 'changed') {
        // $task = $client->tasks()->getTask($resourceGid);
        // Do something with the task
    }
}

// Always respond with 200 OK to acknowledge receipt
http_response_code(200);
echo "OK";
