<?php

namespace BrightleafDigital\Api;

use BrightleafDigital\Exceptions\ApiException;
use BrightleafDigital\Exceptions\RateLimitException;
use BrightleafDigital\Exceptions\ValidationException;
use BrightleafDigital\Http\HttpClientInterface;

/**
 * Service class for Task Templates-related API operations.
 *
 * @link https://developers.asana.com/reference/task-templates
 */
class TaskTemplatesApiService extends BaseApiService
{
    /**
     * Get multiple task templates
     * GET /task_templates
     * Returns the compact task template records for some filtered set of task templates.
     * You must specify a `project`
     * API Documentation: https://developers.asana.com/reference/gettasktemplates
     *
     * @param string $projectGid The project to filter task templates on.
     * @param array $options Optional parameters to customize the request:
     * - limit (int): Results per page. The number of objects to return per page. The value must be between 1 and 100.
     * - offset (string): Offset token.
     * - opt_fields (string): A comma-separated list of fields to include in the response.
     * - opt_pretty (bool): Returns formatted JSON if true.
     * @param int $responseType The type of response to return.
     *
     * @return array The response data.
     * @throws ApiException If the API request fails due to network issues, insufficient permissions, or rate limits.
     * @throws RateLimitException If the Asana API rate limit is exceeded.
     * @throws ValidationException If the project GID is empty or invalid.
     */
    public function getTaskTemplates(
        string $projectGid,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($projectGid, 'Project GID');
        $options['project'] = $projectGid;
        return $this->getResources('task_templates', $options, $responseType);
    }

    /**
     * Get a task template
     * GET /task_templates/{task_template_gid}
     * Returns the complete task template record for a single task template.
     * API Documentation: https://developers.asana.com/reference/gettasktemplate
     *
     * @param string $taskTemplateGid Globally unique identifier for the task template.
     * @param array $options Query parameters for formatting results.
     * @param int $responseType The type of response to return.
     *
     * @return array The response data.
     * @throws ApiException If the API request fails due to network issues, insufficient permissions, or rate limits.
     * @throws RateLimitException If the Asana API rate limit is exceeded.
     * @throws ValidationException If the task template GID is empty or invalid.
     */
    public function getTaskTemplate(
        string $taskTemplateGid,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($taskTemplateGid, 'Task Template GID');
        return $this->getResource('task_templates', $taskTemplateGid, $options, $responseType);
    }

    /**
     * Delete a task template
     * DELETE /task_templates/{task_template_gid}
     * A specific, existing task template can be deleted by making a DELETE request on the
     * URL for that task template.
     * API Documentation: https://developers.asana.com/reference/deletetasktemplate
     *
     * @param string $taskTemplateGid Globally unique identifier for the task template.
     * @param int $responseType The type of response to return.
     *
     * @return array The response data (empty object).
     * @throws ApiException If the API request fails due to network issues, insufficient permissions, or rate limits.
     * @throws RateLimitException If the Asana API rate limit is exceeded.
     * @throws ValidationException If the task template GID is empty or invalid.
     */
    public function deleteTaskTemplate(
        string $taskTemplateGid,
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($taskTemplateGid, 'Task Template GID');
        return $this->deleteResource('task_templates', $taskTemplateGid, $responseType);
    }

    /**
     * Instantiate a task from a task template
     * POST /task_templates/{task_template_gid}/instantiateTask
     * Creates and returns a job that will asynchronously handle the task instantiation.
     * API Documentation: https://developers.asana.com/reference/instantiatetask
     *
     * @param string $taskTemplateGid Globally unique identifier for the task template.
     * @param string $name The name of the new task. If not provided, the name of the task template will be used.
     * @param array $options Query parameters for formatting results.
     * @param int $responseType The type of response to return.
     *
     * @return array The response data (JobResponse).
     * @throws ApiException If the API request fails due to network issues, insufficient permissions, or rate limits.
     * @throws RateLimitException If the Asana API rate limit is exceeded.
     * @throws ValidationException If the task template GID is empty or invalid.
     */
    public function instantiateTask(
        string $taskTemplateGid,
        string $name = '',
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($taskTemplateGid, 'Task Template GID');
        $data = ['name' => $name];
        return $this->createResource(
            "task_templates/$taskTemplateGid/instantiateTask",
            $data,
            $options,
            $responseType
        );
    }
}
