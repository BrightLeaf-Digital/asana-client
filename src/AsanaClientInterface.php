<?php

namespace BrightleafDigital;

use BrightleafDigital\Api\AllocationsApiService;
use BrightleafDigital\Api\AuditLogApiService;
use BrightleafDigital\Api\BudgetsApiService;
use BrightleafDigital\Api\RulesApiService;
use BrightleafDigital\Api\AttachmentApiService;
use BrightleafDigital\Api\BatchApiService;
use BrightleafDigital\Api\CustomFieldApiService;
use BrightleafDigital\Api\EventsApiService;
use BrightleafDigital\Api\GoalsApiService;
use BrightleafDigital\Api\MembershipApiService;
use BrightleafDigital\Api\PortfolioMembershipsApiService;
use BrightleafDigital\Api\PortfoliosApiService;
use BrightleafDigital\Api\ProjectStatusesApiService;
use BrightleafDigital\Api\ProjectMembershipsApiService;
use BrightleafDigital\Api\ReactionsApiService;
use BrightleafDigital\Api\TeamMembershipsApiService;
use BrightleafDigital\Api\WorkspaceMembershipsApiService;
use BrightleafDigital\Api\ProjectApiService;
use BrightleafDigital\Api\ProjectTemplatesApiService;
use BrightleafDigital\Api\SectionApiService;
use BrightleafDigital\Api\StatusUpdatesApiService;
use BrightleafDigital\Api\StoriesApiService;
use BrightleafDigital\Api\TagsApiService;
use BrightleafDigital\Api\TaskApiService;
use BrightleafDigital\Api\TeamsApiService;
use BrightleafDigital\Api\TimeTrackingEntriesApiService;
use BrightleafDigital\Api\UserApiService;
use BrightleafDigital\Api\UserTaskListsApiService;
use BrightleafDigital\Api\WebhooksApiService;
use BrightleafDigital\Api\WorkspaceApiService;
use BrightleafDigital\Api\TaskTemplatesApiService;
use BrightleafDigital\Api\OrganizationExportsApiService;
use BrightleafDigital\Api\AccessRequestsApiService;
use BrightleafDigital\Api\ProjectBriefsApiService;
use BrightleafDigital\Api\GoalRelationshipsApiService;
use BrightleafDigital\Api\CustomTypesApiService;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Container\ContainerInterface;

/**
 * Interface for the Asana Client facade.
 */
interface AsanaClientInterface
{
    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface;

    /**
     * @param array $options
     * @return string
     */
    public function getAuthorizationUrl(array $options = []): string;

    /**
     * @param array $options
     * @param bool $enableState
     * @param bool $enablePKCE
     * @return array
     */
    public function getSecureAuthorizationUrl(array $options, bool $enableState = true, bool $enablePKCE = true): array;

    /**
     * @param string $code
     * @param string|null $codeVerifier
     * @return AccessToken
     */
    public function handleCallback(string $code, ?string $codeVerifier = null): AccessToken;

    /**
     * @param AccessToken $token
     */
    public function setAccessToken(AccessToken $token): void;

    /**
     * @return AccessToken|null
     */
    public function getAccessToken(): ?AccessToken;

    /**
     * Refresh the current token.
     */
    public function refreshToken(): void;

    /**
     * Subscribe to token refresh events.
     *
     * @param callable $callback
     * @param string|int|null $id
     */
    public function subscribeToTokenRefresh(callable $callback, $id = null): void;

    /**
     * Webhook Handshake Helpers
     */
    public function isHandshake(array $headers): bool;

    /**
     * @param array $headers
     * @return string
     */
    public function handleHandshake(array $headers): string;
    /**
     * @return TaskApiService
     */
    public function tasks(): TaskApiService;

    /**
     * @return ProjectApiService
     */
    public function projects(): ProjectApiService;

    /**
     * @return UserApiService
     */
    public function users(): UserApiService;

    /**
     * @return TagsApiService
     */
    public function tags(): TagsApiService;

    /**
     * @return SectionApiService
     */
    public function sections(): SectionApiService;

    /**
     * @return MembershipApiService
     */
    public function memberships(): MembershipApiService;

    /**
     * @return PortfolioMembershipsApiService
     */
    public function portfolioMemberships(): PortfolioMembershipsApiService;

    /**
     * @return ProjectMembershipsApiService
     */
    public function projectMemberships(): ProjectMembershipsApiService;

    /**
     * @return ReactionsApiService
     */
    public function reactions(): ReactionsApiService;

    /**
     * @return TeamMembershipsApiService
     */
    public function teamMemberships(): TeamMembershipsApiService;

    /**
     * @return WorkspaceMembershipsApiService
     */
    public function workspaceMemberships(): WorkspaceMembershipsApiService;

    /**
     * @return AllocationsApiService
     */
    public function allocations(): AllocationsApiService;

    /**
     * @return AuditLogApiService
     */
    public function auditLog(): AuditLogApiService;

    /**
     * @return BudgetsApiService
     */
    public function budgets(): BudgetsApiService;

    /**
     * @return RulesApiService
     */
    public function rules(): RulesApiService;

    /**
     * @return AttachmentApiService
     */
    public function attachments(): AttachmentApiService;

    /**
     * @return BatchApiService
     */
    public function batch(): BatchApiService;

    /**
     * @return CustomFieldApiService
     */
    public function customFields(): CustomFieldApiService;

    /**
     * @return EventsApiService
     */
    public function events(): EventsApiService;

    /**
     * @return GoalsApiService
     */
    public function goals(): GoalsApiService;

    /**
     * @return PortfoliosApiService
     */
    public function portfolios(): PortfoliosApiService;

    /**
     * @return ProjectStatusesApiService
     */
    public function projectStatuses(): ProjectStatusesApiService;

    /**
     * @return ProjectTemplatesApiService
     */
    public function projectTemplates(): ProjectTemplatesApiService;

    /**
     * @return StatusUpdatesApiService
     */
    public function statusUpdates(): StatusUpdatesApiService;

    /**
     * @return StoriesApiService
     */
    public function stories(): StoriesApiService;

    /**
     * @return TeamsApiService
     */
    public function teams(): TeamsApiService;

    /**
     * @return TimeTrackingEntriesApiService
     */
    public function timeTrackingEntries(): TimeTrackingEntriesApiService;

    /**
     * @return UserTaskListsApiService
     */
    public function userTaskLists(): UserTaskListsApiService;

    /**
     * @return WebhooksApiService
     */
    public function webhooks(): WebhooksApiService;

    /**
     * @return WorkspaceApiService
     */
    public function workspaces(): WorkspaceApiService;

    /**
     * @return TaskTemplatesApiService
     */
    public function taskTemplates(): TaskTemplatesApiService;

    /**
     * @return OrganizationExportsApiService
     */
    public function organizationExports(): OrganizationExportsApiService;

    /**
     * @return AccessRequestsApiService
     */
    public function accessRequests(): AccessRequestsApiService;

    /**
     * @return ProjectBriefsApiService
     */
    public function projectBriefs(): ProjectBriefsApiService;

    /**
     * @return GoalRelationshipsApiService
     */
    public function goalRelationships(): GoalRelationshipsApiService;

    /**
     * @return CustomTypesApiService
     */
    public function customTypes(): CustomTypesApiService;
}
