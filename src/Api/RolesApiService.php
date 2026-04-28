<?php

namespace BrightleafDigital\Api;

use BrightleafDigital\Exceptions\ApiException;
use BrightleafDigital\Exceptions\RateLimitException;
use BrightleafDigital\Exceptions\ValidationException;
use BrightleafDigital\Http\HttpClientInterface;

class RolesApiService extends BaseApiService
{
    /**
     * Get multiple roles
     * GET /roles
     * Returns a paginated list of all roles in the given workspace.
     * Requires Admin or Super Admin role and the `roles:read` OAuth scope.
     * API Documentation: https://developers.asana.com/reference/getroles
     *
     * @param string $workspaceGid The unique global ID of the workspace.
     *                             Example: "12345"
     * @param array $options Optional parameters to customize the request:
     *
     * Pagination parameters:
     * - limit (int): Maximum number of roles to return. Default is 20, max is 100
     * - offset (string): Offset token for pagination
     *
     * Display parameters:
     * - opt_fields (string): Comma-separated fields to include in the response
     *   (e.g., "name,description,is_standard_role")
     * - opt_pretty (bool): Returns formatted JSON if true
     *
     * @param int $responseType The type of response to return:
     * - HttpClientInterface::RESPONSE_FULL (1): Full response with status, headers, etc.
     * - HttpClientInterface::RESPONSE_NORMAL (2): Complete decoded JSON body
     * - HttpClientInterface::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type.
     *
     * If $responseType is HttpClientInterface::RESPONSE_DATA (default):
     * - Just the data array containing the list of roles with fields including:
     *   - gid: Unique identifier of the role
     *   - resource_type: Always "role"
     *   - name: Name of the role
     *   - description: Description of the role
     *   - is_standard_role: Boolean distinguishing system roles from custom roles
     *
     * @throws ApiException
     * @throws RateLimitException
     * @throws ValidationException
     */
    public function getRoles(
        string $workspaceGid,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($workspaceGid, 'Workspace GID');

        $options['workspace'] = $workspaceGid;

        return $this->getResources('roles', $options, $responseType);
    }

    /**
     * Get a role
     * GET /roles/{role_gid}
     * Returns the full record for a single role.
     * Requires Admin or Super Admin role and the `roles:read` OAuth scope.
     * API Documentation: https://developers.asana.com/reference/getrole
     *
     * @param string $roleGid The unique global ID of the role.
     *                        Example: "12345"
     * @param array $options Optional parameters to customize the request:
     * - opt_fields (string): Comma-separated fields to include in the response
     *   (e.g., "name,description,is_standard_role")
     * - opt_pretty (bool): Returns formatted JSON if true
     *
     * @param int $responseType The type of response to return:
     * - HttpClientInterface::RESPONSE_FULL (1): Full response with status, headers, etc.
     * - HttpClientInterface::RESPONSE_NORMAL (2): Complete decoded JSON body
     * - HttpClientInterface::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type.
     *
     * If $responseType is HttpClientInterface::RESPONSE_DATA (default):
     * - Just the data object containing the role details including:
     *   - gid: Unique identifier of the role
     *   - resource_type: Always "role"
     *   - name: Name of the role
     *   - description: Description of the role
     *   - is_standard_role: Boolean; true for system roles, false for custom roles
     *
     * @throws ApiException
     * @throws RateLimitException
     * @throws ValidationException
     */
    public function getRole(
        string $roleGid,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($roleGid, 'Role GID');

        return $this->getResource('roles', $roleGid, $options, $responseType);
    }

    /**
     * Create a role
     * POST /roles
     * Creates a new custom role in the specified workspace.
     * Requires the "Manage roles" permission and Enterprise+ licensing.
     * Requires the `roles:write` OAuth scope.
     * API Documentation: https://developers.asana.com/reference/createrole
     *
     * @param array $data Data for creating the role. Supported fields include:
     *                    Required:
     * - workspace (string): GID of the workspace to create the role in.
     *   Example: "12345"
     * - name (string): Name of the new role.
     *   Example: "Project Reviewer"
     * - description (string): Description of the role and its purpose.
     *   Example: "Can review and comment on projects"
     * - base_role_type (string): The base role type to derive permissions from.
     *   Example: "member"
     *                    Optional:
     * - permissions (object): Fine-grained permission overrides for the role.
     *   Example: ["workspace" => "12345", "name" => "Project Reviewer",
     *             "description" => "...", "base_role_type" => "member"]
     * @param array $options Optional parameters to customize the request:
     * - opt_fields (string): Comma-separated fields to include in the response
     * - opt_pretty (bool): Returns formatted JSON if true
     *
     * @param int $responseType The type of response to return:
     * - HttpClientInterface::RESPONSE_FULL (1): Full response with status, headers, etc.
     * - HttpClientInterface::RESPONSE_NORMAL (2): Complete decoded JSON body
     * - HttpClientInterface::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type.
     *
     * @throws ApiException
     * @throws RateLimitException
     * @throws ValidationException If required fields are missing
     */
    public function createRole(
        array $data,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateRequiredFields($data, ['workspace', 'name', 'description', 'base_role_type'], 'role creation');

        return $this->createResource('roles', $data, $options, $responseType);
    }

    /**
     * Update a role
     * PUT /roles/{role_gid}
     * Updates the properties of an existing custom role. Only the fields provided
     * will be updated; any unspecified fields will remain unchanged.
     * Requires the "Manage roles" permission and Enterprise+ licensing.
     * Requires the `roles:write` OAuth scope.
     * API Documentation: https://developers.asana.com/reference/updaterole
     *
     * @param string $roleGid The unique global ID of the role to update.
     *                        Example: "12345"
     * @param array $data Properties to update. Can include:
     * - name (string): New name for the role
     * - description (string): New description for the role
     * - permissions (object): Updated fine-grained permission overrides
     *   Example: ["name" => "Updated Role Name"]
     * @param array $options Optional parameters to customize the request:
     * - opt_fields (string): Comma-separated fields to include in the response
     * - opt_pretty (bool): Returns formatted JSON if true
     *
     * @param int $responseType The type of response to return:
     * - HttpClientInterface::RESPONSE_FULL (1): Full response with status, headers, etc.
     * - HttpClientInterface::RESPONSE_NORMAL (2): Complete decoded JSON body
     * - HttpClientInterface::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type.
     *
     * @throws ApiException
     * @throws RateLimitException
     * @throws ValidationException
     */
    public function updateRole(
        string $roleGid,
        array $data,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($roleGid, 'Role GID');

        return $this->updateResource('roles', $roleGid, $data, $options, $responseType);
    }

    /**
     * Delete a role
     * DELETE /roles/{role_gid}
     * Deletes a custom role. Only roles with no assigned users can be deleted.
     * Requires the "Manage roles" permission and Enterprise+ licensing.
     * Requires the `roles:delete` OAuth scope.
     * API Documentation: https://developers.asana.com/reference/deleterole
     *
     * @param string $roleGid The unique global ID of the role to delete.
     *                        Example: "12345"
     * @param int $responseType The type of response to return:
     * - HttpClientInterface::RESPONSE_FULL (1): Full response with status, headers, etc.
     * - HttpClientInterface::RESPONSE_NORMAL (2): Complete decoded JSON body
     * - HttpClientInterface::RESPONSE_DATA (3): Only the data subset (default)
     *
     * @return array The response data based on the specified response type.
     *
     * If $responseType is HttpClientInterface::RESPONSE_DATA (default):
     * - Just the data object (empty JSON object {}) indicating successful deletion
     *
     * @throws ApiException
     * @throws RateLimitException
     * @throws ValidationException
     */
    public function deleteRole(
        string $roleGid,
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($roleGid, 'Role GID');

        return $this->deleteResource('roles', $roleGid, $responseType);
    }
}
