<?php

namespace BpmPlatform\Engine\Impl\Persistence;

use BpmPlatform\Engine\Authorization\{
    PermissionInterface,
    ResourceInterface
};
use BpmPlatform\Engine\Impl\AbstractQuery;
use BpmPlatform\Engine\Impl\Cfg\Auth\ResourceAuthorizationProviderInterface;
use BpmPlatform\Engine\Impl\Context\Context;
use BpmPlatform\Engine\Impl\Db\DbEntityInterface;
use BpmPlatform\Engine\Impl\Db\EntityManager\DbEntityManager;
use BpmPlatform\Engine\Impl\Db\Sql\DbSqlSession;
use BpmPlatform\Engine\Impl\Form\Entity\FormDefinitionManager;
use BpmPlatform\Engine\Impl\Identity\Authentication;
use BpmPlatform\Engine\Impl\Interceptor\{
    CommandContext,
    SessionInterface
};
use BpmPlatform\Engine\Impl\Persistence\Entity\{
    AttachmentManager,
    AuthorizationEntity,
    AuthorizationManager,
    BatchManager,
    ByteArrayManager,
    DeploymentManager,
    EventSubscriptionManager,
    ExecutionManager,
    HistoricActivityInstanceManager,
    HistoricBatchManager,
    HistoricDetailManager,
    HistoricExternalTaskLogManager,
    HistoricIdentityLinkLogManager,
    HistoricIncidentManager,
    HistoricJobLogManager,
    HistoricProcessInstanceManager,
    ReportManager,
    HistoricTaskInstanceManager,
    HistoricVariableInstanceManager,
    IdentityInfoManager,
    IdentityLinkManager,
    JobDefinitionManager,
    JobManager,
    ProcessDefinitionManager,
    ResourceManager,
    TaskManager,
    TaskReportManager,
    TenantManager,
    UserOperationLogManager,
    VariableInstanceManager
};

abstract class AbstractManager implements SessionInterface
{
    public function insert(DbEntityInterface $dbEntity): void
    {
        $this->getDbEntityManager()->insert($dbEntity);
    }

    public function delete(DbEntityInterface $dbEntity): void
    {
        $this->getDbEntityManager()->delete($dbEntity);
    }

    protected function getDbEntityManager(): DbEntityManager
    {
        return $this->getSession(DbEntityManager::class);
    }

    protected function getDbSqlSession(): DbSqlSession
    {
        return $this->getSession(DbSqlSession::class);
    }

    protected function getSession(string $sessionClass)
    {
        return Context::getCommandContext()->getSession($sessionClass);
    }

    protected function getDeploymentManager(): DeploymentManager
    {
        return $this->getSession(DeploymentManager::class);
    }

    protected function getResourceManager(): ResourceManager
    {
        return $this->getSession(ResourceManager::class);
    }

    protected function getByteArrayManager(): ByteArrayManager
    {
        return $this->getSession(ByteArrayManager::class);
    }

    protected function getProcessDefinitionManager(): ProcessDefinitionManager
    {
        return $this->getSession(ProcessDefinitionManager::class);
    }

    /*protected CaseDefinitionManager getCaseDefinitionManager() {
        return $this->getSession(CaseDefinitionManager::class);
    }

    protected DecisionDefinitionManager getDecisionDefinitionManager() {
        return $this->getSession(DecisionDefinitionManager::class);
    }

    protected DecisionRequirementsDefinitionManager getDecisionRequirementsDefinitionManager() {
        return $this->getSession(DecisionRequirementsDefinitionManager::class);
    }*/

    protected function getFormDefinitionManager(): FormDefinitionManager
    {
        return $this->getSession(FormDefinitionManager::class);
    }

    /*protected HistoricDecisionInstanceManager getHistoricDecisionInstanceManager() {
        return $this->getSession(HistoricDecisionInstanceManager::class);
    }

    protected CaseExecutionManager getCaseInstanceManager() {
        return $this->getSession(CaseExecutionManager::class);
    }

    protected CaseExecutionManager getCaseExecutionManager() {
        return $this->getSession(CaseExecutionManager::class);
    }*/

    protected function getProcessInstanceManager(): ExecutionManager
    {
        return $this->getSession(ExecutionManager::class);
    }

    protected function getTaskManager(): TaskManager
    {
        return $this->getSession(TaskManager::class);
    }

    protected function getTaskReportManager(): TaskReportManager
    {
        return $this->getSession(TaskReportManager::class);
    }

    protected function getIdentityLinkManager(): IdentityLinkManager
    {
        return $this->getSession(IdentityLinkManager::class);
    }

    protected function getVariableInstanceManager(): VariableInstanceManager
    {
        return $this->getSession(VariableInstanceManager::class);
    }

    protected function getHistoricProcessInstanceManager(): HistoricProcessInstanceManager
    {
        return $this->getSession(HistoricProcessInstanceManager::class);
    }

    /*protected HistoricCaseInstanceManager getHistoricCaseInstanceManager() {
        return $this->getSession(HistoricCaseInstanceManager::class);
    }*/

    protected function getHistoricDetailManager(): HistoricDetailManager
    {
        return $this->getSession(HistoricDetailManager::class);
    }

    protected function getHistoricVariableInstanceManager(): HistoricVariableInstanceManager
    {
        return $this->getSession(HistoricVariableInstanceManager::class);
    }

    protected function getHistoricActivityInstanceManager(): HistoricActivityInstanceManager
    {
        return $this->getSession(HistoricActivityInstanceManager::class);
    }

    /*protected HistoricCaseActivityInstanceManager getHistoricCaseActivityInstanceManager() {
        return $this->getSession(HistoricCaseActivityInstanceManager::class);
    }*/

    protected function getHistoricTaskInstanceManager(): HistoricTaskInstanceManager
    {
        return $this->getSession(HistoricTaskInstanceManager::class);
    }

    protected function getHistoricIncidentManager(): HistoricIncidentManager
    {
        return $this->getSession(HistoricIncidentManager::class);
    }

    protected function getHistoricIdentityLinkManager(): HistoricIdentityLinkLogManager
    {
        return $this->getSession(HistoricIdentityLinkLogManager::class);
    }

    protected function getHistoricJobLogManager(): HistoricJobLogManager
    {
        return $this->getSession(HistoricJobLogManager::class);
    }

    protected function getHistoricExternalTaskLogManager(): HistoricExternalTaskLogManager
    {
        return $this->getSession(HistoricExternalTaskLogManager::class);
    }

    protected function getJobManager(): JobManager
    {
        return $this->getSession(JobManager::class);
    }

    protected function getJobDefinitionManager(): JobDefinitionManager
    {
        return $this->getSession(JobDefinitionManager::class);
    }

    protected function getUserOperationLogManager(): UserOperationLogManager
    {
        return $this->getSession(UserOperationLogManager::class);
    }

    protected function getEventSubscriptionManager(): EventSubscriptionManager
    {
        return $this->getSession(EventSubscriptionManager::class);
    }

    protected function getIdentityInfoManager(): IdentityInfoManager
    {
        return $this->getSession(IdentityInfoManager::class);
    }

    protected function getAttachmentManager(): AttachmentManager
    {
        return $this->getSession(AttachmentManager::class);
    }

    protected function getHistoricReportManager(): ReportManager
    {
        return $this->getSession(ReportManager::class);
    }

    protected function getBatchManager(): ReportManager
    {
        return $this->getSession(BatchManager::class);
    }

    protected function getHistoricBatchManager(): HistoricBatchManager
    {
        return $this->getSession(HistoricBatchManager::class);
    }

    protected function getTenantManager(): TenantManager
    {
        return $this->getSession(TenantManager::class);
    }

    public function close(): void
    {
    }

    public function flush(): void
    {
    }

    // authorizations ///////////////////////////////////////

    protected function getCommandContext(): CommandContext
    {
        return Context::getCommandContext();
    }

    protected function getAuthorizationManager(): AuthorizationManager
    {
        return $this->getSession(AuthorizationManager::class);
    }

    protected function configureQuery(AbstractQuery $query, ResourceInterface $resource): void
    {
        $this->getAuthorizationManager()->configureQuery($query, $resource);
    }

    protected function checkAuthorization(PermissionInterface $permission, ResourceInterface $resource, string $resourceId): void
    {
        $this->getAuthorizationManager()->checkAuthorization($permission, $resource, $resourceId);
    }

    public function isAuthorizationEnabled(): bool
    {
        return Context::getProcessEngineConfiguration()->isAuthorizationEnabled();
    }

    protected function getCurrentAuthentication(): Authentication
    {
        return Context::getCommandContext()->getAuthentication();
    }

    protected function getResourceAuthorizationProvider(): ResourceAuthorizationProviderInterface
    {
        return Context::getProcessEngineConfiguration()
            ->getResourceAuthorizationProvider();
    }

    protected function deleteAuthorizations(ResourceInterface $resource, string $resourceId): void
    {
        $this->getAuthorizationManager()->deleteAuthorizationsByResourceId($resource, $resourceId);
    }

    protected function deleteAuthorizationsForUser(ResourceInterface $resource, string $resourceId, string $userId): void
    {
        $this->getAuthorizationManager()->deleteAuthorizationsByResourceIdAndUserId($resource, $resourceId, $userId);
    }

    protected function deleteAuthorizationsForGroup(ResourceInterface $resource, string $resourceId, string $groupId): void
    {
        $this->getAuthorizationManager()->deleteAuthorizationsByResourceIdAndGroupId($resource, $resourceId, $groupId);
    }

    public function saveDefaultAuthorizations(array $authorizations): void
    {
        if (!empty($authorizations)) {
            $scope = $this;
            Context::getCommandContext()->runWithoutAuthorization(function () use ($scope, $authorizations) {
                $authorizationManager = $scope->getAuthorizationManager();
                foreach ($authorizations as $authorization) {
                    if ($authorization->getId() == null) {
                        $authorizationManager->insert($authorization);
                    } else {
                        $authorizationManager->update($authorization);
                    }
                }
                return null;
            });
        }
    }

    public function deleteDefaultAuthorizations(array $authorizations): void
    {
        if (!empty($authorizations)) {
            $scope = $this;
            Context::getCommandContext()->runWithoutAuthorization(function () use ($scope, $authorizations) {
                $authorizationManager = $scope->getAuthorizationManager();
                foreach ($authorizations as $authorization) {
                    $authorizationManager->delete($authorization);
                }
                return null;
            });
        }
    }
}
