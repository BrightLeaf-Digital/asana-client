<?php

use BrightleafDigital\AsanaClient;
use BrightleafDigital\Exceptions\ApiException;
use BrightleafDigital\Exceptions\TokenInvalidException;
use BrightleafDigital\Exceptions\ValidationException;
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
    $taskGid = $_GET['task'] ?? null;
    if (!$taskGid) {
        throw new ValidationException('Task parameter is required');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['attachment'])) {
        $file = $_FILES['attachment'];
        if ($file['error'] === UPLOAD_ERR_OK) {
            $attachment = $asanaClient->attachments()->uploadAttachment(
                $taskGid,
                $file['tmp_name'],
            );
            echo '<h1>Attachment uploaded successfully!</h1>';
            echo '<pre>';
            print_r($attachment);
            echo '</pre>';
        } else {
            throw new RuntimeException('File upload failed with error code: ' . $file['error']);
        }
    }
    ?>
    <form method="POST" enctype="multipart/form-data">
        <div>
            <label for="attachment">File:</label>
            <input type="file" id="attachment" name="attachment" required>
        </div>
        <button type="submit">Upload Attachment</button>
    </form>
    <p><a href="viewTask.php?task=<?php echo htmlspecialchars($taskGid); ?>">Back to task</a></p>
    <?php
} catch (ApiException | RuntimeException | ValidationException $e) {
    echo 'Error: ' . $e->getMessage();
}
