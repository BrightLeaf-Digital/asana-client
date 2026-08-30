<?php

namespace BrightleafDigital\Api;

use BrightleafDigital\Exceptions\ApiException;
use BrightleafDigital\Exceptions\RateLimitException;
use BrightleafDigital\Exceptions\ValidationException;
use BrightleafDigital\Http\HttpClientInterface;

/**
 * Service class for Custom Types-related API operations.
 *
 * Custom types extend Asana objects with product-specific behaviour. They apply to tasks,
 * projects and portfolios, and are defined per workspace rather than per object type.
 *
 * @link https://developers.asana.com/reference/custom-types
 */
class CustomTypesApiService extends BaseApiService
{
    /**
     * Get all custom types associated with an object
     * GET /custom_types
     * Returns a list of custom types associated with an object. Exactly one of `project` or
     * `workspace` must be provided. When a workspace is provided, all custom types in the
     * workspace are listed, including types created by Asana products.
     * API Documentation: https://developers.asana.com/reference/getcustomtypes
     *
     * Requires the `custom_types:read` OAuth scope.
     *
     * @param string|null $projectGid Globally unique identifier for the project to filter custom
     *                                types by. Pass null and supply `workspace` in $options to
     *                                list every custom type in a workspace instead.
     * @param array $options Optional query parameters:
     * - workspace (string): GID of the workspace to list custom types for. Mutually exclusive
     *   with $projectGid; exactly one of the two must be supplied.
     * - limit (int): Results per page (1-100).
     * - offset (string): Offset token.
     * - opt_fields (string): Comma-separated list of fields to include. Beyond the compact
     *   representation this endpoint supports `asana_created_type_identifier`, `name`,
     *   `status_options` and its sub-fields.
     * - opt_pretty (bool): Pretty JSON.
     * @param int $responseType The type of response to return.
     *
     * @return array The response data. Each custom type includes:
     * - gid: Unique identifier of the custom type
     * - resource_type: Always "custom_type"
     * - name: Name of the custom type
     * - asana_created_type_identifier (opt-in): A stable identifier such as "COMMAND_TICKET" or
     *   "SERVICE_QUEUE" when the type was created by an Asana product, otherwise null. New
     *   values are added over time, so treat unrecognized values as valid.
     * @throws ApiException
     * @throws RateLimitException
     * @throws ValidationException If neither or both of project and workspace are supplied, or
     *                             either GID is invalid.
     */
    public function getCustomTypes(
        ?string $projectGid = null,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $workspaceGid = $options['workspace'] ?? null;

        if ($projectGid !== null && $workspaceGid !== null) {
            throw new ValidationException(
                'Provide exactly one of a project GID or a "workspace" option, not both.'
            );
        }

        if ($projectGid === null && $workspaceGid === null) {
            throw new ValidationException(
                'You must provide either a project GID or a "workspace" option.'
            );
        }

        if ($projectGid !== null) {
            $this->validateGid($projectGid, 'Project GID');
            $options['project'] = $projectGid;
        } else {
            $this->validateGid((string) $workspaceGid, 'Workspace GID');
        }

        return $this->getResources('custom_types', $options, $responseType);
    }

    /**
     * Get all custom types in a workspace
     * GET /custom_types?workspace={workspace_gid}
     * Convenience wrapper around {@see CustomTypesApiService::getCustomTypes()} that lists every
     * custom type in a workspace, including types created by Asana products.
     * API Documentation: https://developers.asana.com/reference/getcustomtypes
     *
     * Requires the `custom_types:read` OAuth scope.
     *
     * @param string $workspaceGid Globally unique identifier for the workspace.
     * @param array $options Optional query parameters:
     * - limit (int): Results per page (1-100).
     * - offset (string): Offset token.
     * - opt_fields (string): Comma-separated list of fields to include.
     * - opt_pretty (bool): Pretty JSON.
     * @param int $responseType The type of response to return.
     *
     * @return array The response data.
     * @throws ApiException
     * @throws RateLimitException
     * @throws ValidationException If the workspace GID is empty or invalid.
     */
    public function getCustomTypesForWorkspace(
        string $workspaceGid,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($workspaceGid, 'Workspace GID');
        $options['workspace'] = $workspaceGid;
        unset($options['project']);

        return $this->getResources('custom_types', $options, $responseType);
    }

    /**
     * Get a custom type
     * GET /custom_types/{custom_type_gid}
     * Returns the complete custom type record for a single custom type.
     * API Documentation: https://developers.asana.com/reference/getcustomtype
     *
     * Requires the `custom_types:read` OAuth scope.
     *
     * @param string $customTypeGid Globally unique identifier for the custom type.
     * @param array $options Optional query parameters:
     * - opt_fields (string): Comma-separated list of fields to include.
     * - opt_pretty (bool): Pretty JSON.
     * @param int $responseType The type of response to return.
     *
     * @return array The response data.
     * @throws ApiException
     * @throws RateLimitException
     * @throws ValidationException If the custom type GID is empty or invalid.
     */
    public function getCustomType(
        string $customTypeGid,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($customTypeGid, 'Custom Type GID');
        return $this->getResource('custom_types', $customTypeGid, $options, $responseType);
    }
}
