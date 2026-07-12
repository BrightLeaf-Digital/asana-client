<?php

/**
 * Example: fetch every task in the current user's "My Tasks" list, group the
 * tasks by their My Tasks section, and let the user export the tasks from the
 * sections they select as a CSV download.
 *
 * Flow:
 *   1. GET  -> render each section as a checkbox with its tasks underneath.
 *   2. POST -> re-fetch, keep only the selected sections, stream a CSV.
 *
 * Note on "sections in My Tasks":
 * My Tasks is a user task list backed by a project. The section a task sits in
 * *within My Tasks* is exposed on the task via `assignee_section` (per-assignee),
 * NOT via the task's `projects`/`memberships` to other projects. We therefore
 * request `assignee_section.name` and group on it. Tasks with no section fall
 * under "(No section)". `completed_since=now` limits the list to incomplete
 * tasks, which mirrors the default My Tasks view; drop it to include completed.
 */

use BrightleafDigital\AsanaClient;
use BrightleafDigital\Exceptions\ApiException;
use BrightleafDigital\Exceptions\ValidationException;
use BrightleafDigital\Http\HttpClientInterface;
use Dotenv\Dotenv;

require '../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$clientId     = $_ENV['ASANA_CLIENT_ID'];
$clientSecret = $_ENV['ASANA_CLIENT_SECRET'];
$redirectUri  = $_ENV['ASANA_REDIRECT_URI'] ?? null;
$salt         = $_ENV['SALT'] ?? ($_ENV['PASSWORD'] ?? null);

$asanaClient = AsanaClient::OAuth($clientId, $clientSecret, $redirectUri, __DIR__ . '/token.json', null, $salt);

try {
    // Resolve the workspace: use ?workspace=<gid> if given, else the user's first.
    $workspaceGid = $_GET['workspace'] ?? null;
    if ($workspaceGid === null) {
        $workspaces   = $asanaClient->workspaces()->getWorkspaces();
        $workspaceGid = $workspaces[0]['gid'] ?? null;
        if ($workspaceGid === null) {
            throw new RuntimeException('No workspace available for this user.');
        }
    }

    // Resolve the current user's "My Tasks" list for that workspace.
    $userTaskList    = $asanaClient->userTaskLists()->getUserTaskListForUser('me', $workspaceGid);
    $userTaskListGid = $userTaskList['gid'];

    // Fetch every task in the list, following pagination.
    $tasks  = [];
    $offset = null;
    do {
        $options = [
            'opt_fields'      => 'name,completed,due_on,assignee_section.name',
            'completed_since' => 'now', // incomplete tasks only; remove for the full history
            'limit'           => 100,
        ];
        if ($offset !== null) {
            $options['offset'] = $offset;
        }

        $response = $asanaClient->tasks()->getTasksByUserTaskList(
            $userTaskListGid,
            $options,
            HttpClientInterface::RESPONSE_NORMAL
        );

        $tasks  = array_merge($tasks, $response['data'] ?? []);
        $offset = $response['next_page']['offset'] ?? null;
    } while ($offset !== null);

    // Group tasks by their My Tasks section name, preserving first-seen order.
    $groupedTasks = [];
    foreach ($tasks as $task) {
        $section = $task['assignee_section']['name'] ?? '(No section)';
        $groupedTasks[$section][] = $task;
    }

    // ---- Export branch: stream selected sections as CSV -------------------
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'export') {
        $selected = $_POST['sections'] ?? [];
        if (!is_array($selected)) {
            $selected = [$selected];
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="my-tasks-export.csv"');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['Section', 'Task', 'Due On', 'Completed', 'GID']);

        foreach ($selected as $sectionName) {
            foreach ($groupedTasks[$sectionName] ?? [] as $task) {
                fputcsv($out, [
                    $sectionName,
                    $task['name'] ?? '',
                    $task['due_on'] ?? '',
                    !empty($task['completed']) ? 'yes' : 'no',
                    $task['gid'] ?? '',
                ]);
            }
        }

        fclose($out);
        exit;
    }

    // ---- Display branch: checkbox per section -----------------------------
    echo '<h1>My Tasks by section</h1>';
    echo '<p>Select the sections you want to export, then click Export CSV.</p>';
    echo '<form method="POST">';
    echo '<input type="hidden" name="action" value="export">';

    foreach ($groupedTasks as $sectionName => $sectionTasks) {
        $safeName = htmlspecialchars($sectionName);
        echo '<fieldset style="margin-bottom:1em;">';
        echo '<legend>';
        echo '<label><input type="checkbox" name="sections[]" value="' . $safeName . '"> ';
        echo '<strong>' . $safeName . '</strong> (' . count($sectionTasks) . ')';
        echo '</label>';
        echo '</legend>';
        echo '<ul>';
        foreach ($sectionTasks as $task) {
            $line = htmlspecialchars($task['name'] ?? '(unnamed)');
            if (!empty($task['due_on'])) {
                $line .= ' &mdash; due ' . htmlspecialchars($task['due_on']);
            }
            echo '<li>' . $line . '</li>';
        }
        echo '</ul>';
        echo '</fieldset>';
    }

    echo '<button type="submit">Export CSV</button>';
    echo '</form>';
} catch (ApiException | ValidationException | RuntimeException $e) {
    echo 'Error: ' . htmlspecialchars($e->getMessage());
}
