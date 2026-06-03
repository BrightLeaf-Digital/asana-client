<?php

namespace BrightleafDigital;

use BrightleafDigital\Api\AgentApiService;
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
use BrightleafDigital\Api\ExportsApiService;
use BrightleafDigital\Api\JobsApiService;
use BrightleafDigital\Api\RatesApiService;
use BrightleafDigital\Api\TimePeriodsApiService;
use BrightleafDigital\Api\TypeaheadApiService;
use BrightleafDigital\Api\CustomFieldSettingsApiService;
use BrightleafDigital\Api\OooEntriesApiService;
use BrightleafDigital\Api\ProjectPortfolioSettingsApiService;
use BrightleafDigital\Api\RolesApiService;
use BrightleafDigital\Api\TimesheetApprovalStatusesApiService;
use BrightleafDigital\Api\TimeTrackingCategoriesApiService;
use BrightleafDigital\Exceptions\AuthException;
use BrightleafDigital\Exceptions\TokenInvalidException;
use Exception;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

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
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getAuthorizationUrl(array $options = []): string;

    /**
     * @param array $options
     * @param bool $enableState
     * @param bool $enablePKCE
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception If secure parameter generation (random_bytes) fails
     */
    public function getSecureAuthorizationUrl(array $options, bool $enableState = true, bool $enablePKCE = true): array;

    /**
     * @param string $code
     * @param string|null $codeVerifier
     * @return AccessToken
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws AuthException If the OAuth callback fails to obtain a token
     * @throws Exception If token encryption fails while saving the token to storage
     */
    public function handleCallback(string $code, ?string $codeVerifier = null): AccessToken;

    /**
     * @param AccessToken $token
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception If token encryption fails while saving the token to storage
     */
    public function setAccessToken(AccessToken $token): void;

    /**
     * @return AccessToken|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getAccessToken(): ?AccessToken;

    /**
     * Refresh the current token.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws TokenInvalidException If no token is available or the refresh fails
     * @throws Exception If token encryption fails while saving the refreshed token to storage
     */
    public function refreshToken(): void;

    /**
     * Subscribe to token refresh events.
     *
     * @param callable $callback
     * @param string|int|null $id
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
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
     * @param string $flag
     * @return static
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function enableFeatureFlag(string $flag): static;

    /**
     * @return AgentApiService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function agents(): AgentApiService;

    /**
     * @return TaskApiService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function tasks(): TaskApiService;

    /**
     * @return ProjectApiService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function projects(): ProjectApiService;

    /**
     * @return UserApiService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function users(): UserApiService;

    /**
     * @return TagsApiService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function tags(): TagsApiService;

    /**
     * @return SectionApiService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function sections(): SectionApiService;

    /**
     * @return MembershipApiService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function memberships(): MembershipApiService;

    /**
     * @return PortfolioMembershipsApiService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function portfolioMemberships(): PortfolioMembershipsApiService;

    /**
     * @return ProjectMembershipsApiService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function projectMemberships(): ProjectMembershipsApiService;

    /**
     * @return ReactionsApiService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function reactions(): ReactionsApiService;

    /**
     * @return TeamMembershipsApiService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function teamMemberships(): TeamMembershipsApiService;

    /**
     * @return WorkspaceMembershipsApiService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function workspaceMemberships(): WorkspaceMembershipsApiService;

    /**
     * @return AllocationsApiService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function allocations(): AllocationsApiService;

    /**
     * @return AuditLogApiService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function auditLog(): AuditLogApiService;

    /**
     * @return BudgetsApiService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function budgets(): BudgetsApiService;

    /**
     * @return RulesApiService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function rules(): RulesApiService;

    /**
     * @return AttachmentApiService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function attachments(): AttachmentApiService;

    /**
     * @return BatchApiService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function batch(): BatchApiService;

    /**
     * @return CustomFieldApiService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function customFields(): CustomFieldApiService;

    /**
     * @return EventsApiService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function events(): EventsApiService;

    /**
     * @return GoalsApiService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function goals(): GoalsApiService;

    /**
     * @return PortfoliosApiService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function portfolios(): PortfoliosApiService;

    /**
     * @return ProjectStatusesApiService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function projectStatuses(): ProjectStatusesApiService;

    /**
     * @return ProjectTemplatesApiService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function projectTemplates(): ProjectTemplatesApiService;

    /**
     * @return StatusUpdatesApiService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function statusUpdates(): StatusUpdatesApiService;

    /**
     * @return StoriesApiService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function stories(): StoriesApiService;

    /**
     * @return TeamsApiService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function teams(): TeamsApiService;

    /**
     * @return TimeTrackingEntriesApiService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function timeTrackingEntries(): TimeTrackingEntriesApiService;

    /**
     * @return UserTaskListsApiService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function userTaskLists(): UserTaskListsApiService;

    /**
     * @return WebhooksApiService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function webhooks(): WebhooksApiService;

    /**
     * @return WorkspaceApiService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function workspaces(): WorkspaceApiService;

    /**
     * @return TaskTemplatesApiService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function taskTemplates(): TaskTemplatesApiService;

    /**
     * @return OrganizationExportsApiService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function organizationExports(): OrganizationExportsApiService;

    /**
     * @return AccessRequestsApiService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function accessRequests(): AccessRequestsApiService;

    /**
     * @return ProjectBriefsApiService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function projectBriefs(): ProjectBriefsApiService;

    /**
     * @return GoalRelationshipsApiService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function goalRelationships(): GoalRelationshipsApiService;

    /**
     * @return CustomTypesApiService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function customTypes(): CustomTypesApiService;

    /**
     * @return ExportsApiService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function exports(): ExportsApiService;

    /**
     * @return JobsApiService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function jobs(): JobsApiService;

    /**
     * @return RatesApiService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function rates(): RatesApiService;

    /**
     * @return TimePeriodsApiService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function timePeriods(): TimePeriodsApiService;

    /**
     * @return TypeaheadApiService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function typeahead(): TypeaheadApiService;

    /**
     * @return CustomFieldSettingsApiService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function customFieldSettings(): CustomFieldSettingsApiService;

    /**
     * @return RolesApiService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function roles(): RolesApiService;

    /**
     * @return TimesheetApprovalStatusesApiService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function timesheetApprovalStatuses(): TimesheetApprovalStatusesApiService;

    /**
     * @return TimeTrackingCategoriesApiService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function timeTrackingCategories(): TimeTrackingCategoriesApiService;

    /**
     * @return OooEntriesApiService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function oooEntries(): OooEntriesApiService;

    /**
     * @return ProjectPortfolioSettingsApiService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function projectPortfolioSettings(): ProjectPortfolioSettingsApiService;
}
