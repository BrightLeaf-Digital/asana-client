<?php

namespace BrightleafDigital\Api;

use BrightleafDigital\Exceptions\ApiException;
use BrightleafDigital\Exceptions\RateLimitException;
use BrightleafDigital\Exceptions\ValidationException;
use BrightleafDigital\Http\HttpClientInterface;

/**
 * Service class for Agent (AI Teammate) API operations.
 *
 * @link https://developers.asana.com/reference/agents
 */
class AgentApiService extends BaseApiService
{
    /**
     * Get agents in a workspace
     * GET /workspaces/{workspace_gid}/agents
     * Returns a compact list of all agents in the workspace.
     * Requires the Asana-Enable: ai_teammate_actors feature flag.
     * API Documentation: https://developers.asana.com/reference/getagentsinworkspace
     *
     * @param string $workspaceGid Globally unique identifier for the workspace.
     * @param array $options Optional parameters:
     * - opt_fields (string): Comma-separated list of fields to return.
     * - opt_pretty (bool): Pretty-print JSON.
     * - limit (int): Results per page (1–100).
     * - offset (string): Pagination offset token.
     * @param int $responseType The type of response to return.
     *
     * @return array The response data.
     * @throws ApiException
     * @throws RateLimitException
     * @throws ValidationException If workspace GID is empty or non-numeric.
     */
    public function getAgentsInWorkspace(
        string $workspaceGid,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($workspaceGid, 'Workspace GID');
        return $this->getResources("workspaces/$workspaceGid/agents", $options, $responseType);
    }

    /**
     * Get a single agent
     * GET /agents/{agent_gid}
     * Returns the full record for a single agent.
     * Requires the Asana-Enable: ai_teammate_actors feature flag.
     * API Documentation: https://developers.asana.com/reference/getagent
     *
     * @param string $agentGid Globally unique identifier for the agent.
     * @param array $options Optional parameters:
     * - opt_fields (string): Comma-separated list of fields to return.
     *   Available fields: gid, resource_type, resource_subtype, name, description,
     *   behavior_guidance, workspace, created_by, photo
     * - opt_pretty (bool): Pretty-print JSON.
     * @param int $responseType The type of response to return.
     *
     * @return array The response data.
     * @throws ApiException
     * @throws RateLimitException
     * @throws ValidationException If agent GID is empty or non-numeric.
     */
    public function getAgent(
        string $agentGid,
        array $options = [],
        int $responseType = HttpClientInterface::RESPONSE_DATA
    ): array {
        $this->validateGid($agentGid, 'Agent GID');
        return $this->getResource('agents', $agentGid, $options, $responseType);
    }
}
