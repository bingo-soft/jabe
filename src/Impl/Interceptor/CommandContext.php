<?php

namespace Jabe\Impl\Interceptor;

use Jabe\Application\{
    InvocationContext,
    ProcessApplicationReferenceInterface
};
use Jabe\{
    AuthorizationException,
    BadUserRequestException,
    IdentityServiceInterface,
    OptimisticLockingException,
    ProcessEngineException,
    TaskAlreadyClaimedException
};
use Jabe\Impl\ProcessEngineLogger;
use Jabe\Impl\Cfg\{
    ProcessEngineConfigurationImpl,
    TransactionContextInterface,
    TransactionContextFactoryInterface
};
use Jabe\Impl\Context\{
    Context,
    ProcessApplicationContextUtil
};
use Jabe\Impl\Db\EntityManager\DbEntityManager;
use Jabe\Impl\Db\Sql\DbSqlSession;
use Jabe\Impl\Form\Entity\FormDefinitionManager;
//use Jabe\Impl\History\Event\HistoricDecisionInstanceManager;
use Jabe\Impl\Identity\{
    Authentication,
    ReadOnlyIdentityProviderInterface,
    WritableIdentityProviderInterface
};
use Jabe\Impl\JobExecutor\FailedJobCommandFactoryInterface;
use Jabe\Impl\Optimize\OptimizeManager;
use Jabe\Impl\Persistence\Entity\{
    AttachmentManager,
    AuthorizationManager,
    BatchManager,
    ByteArrayManager,
    CommentManager,
    DeploymentManager,
    EventSubscriptionManager,
    ExecutionManager,
    ExternalTaskManager,
    FilterManager,
    HistoricActivityInstanceManager,
    HistoricBatchManager,
    //HistoricCaseActivityInstanceManager,
    //HistoricCaseInstanceManager,
    HistoricDetailManager,
    HistoricExternalTaskLogManager,
    HistoricIdentityLinkLogManager,
    HistoricIncidentManager,
    HistoricJobLogManager,
    HistoricProcessInstanceManager,
    HistoricStatisticsManager,
    HistoricTaskInstanceManager,
    HistoricVariableInstanceManager,
    IdentityInfoManager,
    IdentityLinkManager,
    IncidentManager,
    JobDefinitionManager,
    JobEntity,
    JobManager,
    MeterLogManager,
    ProcessDefinitionManager,
    PropertyManager,
    ReportManager,
    ResourceManager,
    SchemaLogManager,
    StatisticsManager,
    TableDataManager,
    TaskManager,
    TaskReportManager,
    TenantManager,
    UserOperationLogManager,
    VariableInstanceManager
};
use Jabe\Impl\Util\EnsureUtil;

class CommandContext
{
    //private final static ContextLogger LOG = ProcessEngineLogger.CONTEXT_LOGGER;

    protected bool $authorizationCheckEnabled = true;
    protected bool $userOperationLogEnabled = true;
    protected bool $tenantCheckEnabled = true;
    protected $restrictUserOperationLogToAuthenticatedUsers;

    protected $transactionContext;
    protected $sessionFactories = [];
    protected $sessions = [];
    protected $sessionList = [];
    protected $processEngineConfiguration;
    protected $failedJobCommandFactory;

    protected $currentJob = null;

    protected $commandContextListeners = [];

    protected $operationId;

    public function __construct(ProcessEngineConfigurationImpl $processEngineConfiguration, ?TransactionContextFactory $transactionContextFactory = null)
    {
        if ($transactionContextFactory == null) {
            $transactionContextFactory = $processEngineConfiguration->getTransactionContextFactory();
        }
        $this->processEngineConfiguration = $processEngineConfiguration;
        $this->failedJobCommandFactory = $processEngineConfiguration->getFailedJobCommandFactory();
        $this->sessionFactories = $processEngineConfiguration->getSessionFactories();
        $this->transactionContext = $transactionContextFactory->openTransactionContext($this);
        $this->restrictUserOperationLogToAuthenticatedUsers = $processEngineConfiguration->isRestrictUserOperationLogToAuthenticatedUsers();
    }

    public function performOperation(/*CmmnAtomicOperation*/$executionOperation, /*CaseExecutionEntity*/$execution): void
    {
        $targetProcessApplication = $this->getTargetProcessApplication($execution);

        if ($this->requiresContextSwitch($targetProcessApplication)) {
            $scope = $this;
            Context::executeWithinProcessApplication(function () use ($scope, $executionOperation, $execution) {
                $scope->performOperation($executionOperation, $execution);
            }, $targetProcessApplication, new InvocationContext($execution));
        } else {
            try {
                Context::setExecutionContext($execution);
                //LOG.debugExecutingAtomicOperation(executionOperation, execution);
                $executionOperation->execute($execution);
            } finally {
                Context::removeExecutionContext();
            }
        }
    }

    public function getProcessEngineConfiguration(): ProcessEngineConfigurationImpl
    {
        return $this->processEngineConfiguration;
    }

    protected function getTargetProcessApplication(/*CaseExecutionEntity*/$execution): ProcessApplicationReferenceInterface
    {
        return ProcessApplicationContextUtil::getTargetProcessApplication($execution);
    }

    protected function requiresContextSwitch(ProcessApplicationReferenceInterface $processApplicationReference): bool
    {
        return ProcessApplicationContextUtil::requiresContextSwitch($processApplicationReference);
    }

    public function close(CommandInvocationContext $commandInvocationContext): void
    {
        // the intention of this method is that all resources are closed properly,
        // even
        // if exceptions occur in close or flush methods of the sessions or the
        // transaction context.
        try {
            try {
                try {
                    if ($commandInvocationContext->getThrowable() == null) {
                        $this->fireCommandContextClose();
                        $this->flushSessions();
                    }
                } catch (\Throwable $exception) {
                    $commandInvocationContext->trySetThrowable($exception);
                } finally {
                    try {
                        if ($commandInvocationContext->getThrowable() == null) {
                            $this->transactionContext->commit();
                        }
                    } catch (\Throwable $exception) {
                        //if (DbSqlSession::isCrdbConcurrencyConflict($exception)) {
                        //    //$exception = ProcessEngineLogger.PERSISTENCE_LOGGER.crdbTransactionRetryExceptionOnCommit(exception);
                        //}
                        $commandInvocationContext->trySetThrowable($exception);
                    }

                    if ($commandInvocationContext->getThrowable() !== null) {
                        // fire command failed (must not fail itself)
                        $this->fireCommandFailed($commandInvocationContext->getThrowable());

                        if ($this->shouldLogCmdException()) {
                            if ($this->shouldLogInfo($commandInvocationContext->getThrowable())) {
                                //LOG.infoException(commandInvocationContext->getThrowable());
                            } elseif ($this->shouldLogFine($commandInvocationContext->getThrowable())) {
                                //LOG.debugException(commandInvocationContext->getThrowable());
                            } else {
                                //LOG.errorException(commandInvocationContext->getThrowable());
                            }
                        }
                        $this->transactionContext->rollback();
                    }
                }
            } catch (\Throwable $exception) {
                $commandInvocationContext->trySetThrowable($exception);
            } finally {
                $this->closeSessions($commandInvocationContext);
            }
        } catch (\Throwable $exception) {
            $commandInvocationContext->trySetThrowable($exception);
        }

        // rethrow the original exception if there was one
        $commandInvocationContext->rethrow();
    }

    protected function shouldLogInfo(\Throwable $exception): bool
    {
        return $exception instanceof TaskAlreadyClaimedException;
    }

    protected function shouldLogFine(\Throwable $exception): bool
    {
        return $exception instanceof OptimisticLockingException ||
            $exception instanceof BadUserRequestException ||
            $exception instanceof AuthorizationException;
    }

    protected function shouldLogCmdException(): bool
    {
        //return ProcessEngineLogger::shouldLogCmdException($processEngineConfiguration);
        return false;
    }

    protected function fireCommandContextClose(): void
    {
        foreach ($this->commandContextListeners as $listener) {
            $listener->onCommandContextClose($this);
        }
    }

    protected function fireCommandFailed(\Throwable $t): void
    {
        foreach ($this->commandContextListeners as $listener) {
            try {
                $listener->onCommandFailed($this, $t);
            } catch (\Throwable $ex) {
                //LOG.exceptionWhileInvokingOnCommandFailed(t);
            }
        }
    }

    protected function flushSessions(): void
    {
        for ($i = 0; $i < count($this->sessionList); $i += 1) {
            $this->sessionList[$i]->flush();
        }
    }

    protected function closeSessions(CommandInvocationContext $commandInvocationContext): void
    {
        foreach ($this->sessionList as $session) {
            try {
                $session->close();
            } catch (\Throwable $exception) {
                $commandInvocationContext->trySetThrowable($exception);
            }
        }
    }

    public function getSession($sessionClass)
    {
        $session = null;
        if (array_key_exists($sessionClass, $this->sessions)) {
            $session = $this->sessions[$sessionClass];
        }
        if ($session == null) {
            $sessionFactory = null;
            if (array_key_exists($sessionClass, $this->sessionFactories)) {
                $sessionFactory = $this->sessionFactories[$sessionClass];
            }
            EnsureUtil::ensureNotNull("no session factory configured for " . $sessionClass, "sessionFactory", $sessionFactory);
            $session = $sessionFactory->openSession();
            $this->sessions[$sessionClass] = $session;
            array_unshift($this->sessionList, $session);
        }

        return $session;
    }

    public function addSession(?string $sessionClass, DbSqlSession $session): void
    {
        $this->sessions[$sessionClass] = $session;
    }

    public function getDbEntityManager(): DbEntityManager
    {
        return $this->getSession(DbEntityManager::class);
    }

    public function getDbSqlSession(): DbSqlSession
    {
        return $this->getSession(DbSqlSession::class);
    }

    public function getDeploymentManager(): DeploymentManager
    {
        return $this->getSession(DeploymentManager::class);
    }

    public function getResourceManager(): ResourceManager
    {
        return $this->getSession(ResourceManager::class);
    }

    public function getByteArrayManager(): ByteArrayManager
    {
        return $this->getSession(ByteArrayManager::class);
    }

    public function getProcessDefinitionManager(): ProcessDefinitionManager
    {
        return $this->getSession(ProcessDefinitionManager::class);
    }

    public function getExecutionManager(): ExecutionManager
    {
        return $this->getSession(ExecutionManager::class);
    }

    public function getTaskManager(): TaskManager
    {
        return $this->getSession(TaskManager::class);
    }

    public function getTaskReportManager(): TaskReportManager
    {
        return $this->getSession(TaskReportManager::class);
    }

    public function getMeterLogManager(): MeterLogManager
    {
        return $this->getSession(MeterLogManager::class);
    }

    public function getIdentityLinkManager(): IdentityLinkManager
    {
        return $this->getSession(IdentityLinkManager::class);
    }

    public function getVariableInstanceManager(): VariableInstanceManager
    {
        return $this->getSession(VariableInstanceManager::class);
    }

    public function getHistoricProcessInstanceManager(): HistoricProcessInstanceManager
    {
        return $this->getSession(HistoricProcessInstanceManager::class);
    }

    /*public function getHistoricCaseInstanceManager(): HistoricCaseInstanceManager
    {
        return $this->getSession(HistoricCaseInstanceManager::class);
    }*/

    public function getHistoricDetailManager(): HistoricDetailManager
    {
        return $this->getSession(HistoricDetailManager::class);
    }

    public function getOperationLogManager(): UserOperationLogManager
    {
        return $this->getSession(UserOperationLogManager::class);
    }

    public function getHistoricVariableInstanceManager(): HistoricVariableInstanceManager
    {
        return $this->getSession(HistoricVariableInstanceManager::class);
    }

    public function getHistoricActivityInstanceManager(): HistoricActivityInstanceManager
    {
        return $this->getSession(HistoricActivityInstanceManager::class);
    }

    public function getHistoricCaseActivityInstanceManager(): HistoricCaseActivityInstanceManager
    {
        return $this->getSession(HistoricCaseActivityInstanceManager::class);
    }

    public function getHistoricTaskInstanceManager(): HistoricTaskInstanceManager
    {
        return $this->getSession(HistoricTaskInstanceManager::class);
    }

    public function getHistoricIncidentManager(): HistoricIncidentManager
    {
        return $this->getSession(HistoricIncidentManager::class);
    }

    public function getHistoricIdentityLinkManager(): HistoricIdentityLinkLogManager
    {
        return $this->getSession(HistoricIdentityLinkLogManager::class);
    }

    public function getJobManager(): JobManager
    {
        return $this->getSession(JobManager::class);
    }

    public function getBatchManager(): BatchManager
    {
        return $this->getSession(BatchManager::class);
    }

    public function getHistoricBatchManager(): HistoricBatchManager
    {
        return $this->getSession(HistoricBatchManager::class);
    }

    public function getJobDefinitionManager(): JobDefinitionManager
    {
        return $this->getSession(JobDefinitionManager::class);
    }

    public function getIncidentManager(): IncidentManager
    {
        return $this->getSession(IncidentManager::class);
    }

    public function getIdentityInfoManager(): IdentityInfoManager
    {
        return $this->getSession(IdentityInfoManager::class);
    }

    public function getAttachmentManager(): AttachmentManager
    {
        return $this->getSession(AttachmentManager::class);
    }

    public function getTableDataManager(): TableDataManager
    {
        return $this->getSession(TableDataManager::class);
    }

    public function getCommentManager(): CommentManager
    {
        return $this->getSession(CommentManager::class);
    }

    public function getEventSubscriptionManager(): EventSubscriptionManager
    {
        return $this->getSession(EventSubscriptionManager::class);
    }

    public function getSessionFactories(): array
    {
        return $this->sessionFactories;
    }

    public function getPropertyManager(): PropertyManager
    {
        return $this->getSession(PropertyManager::class);
    }

    public function getStatisticsManager(): StatisticsManager
    {
        return $this->getSession(StatisticsManager::class);
    }

    public function getHistoricStatisticsManager(): HistoricStatisticsManager
    {
        return $this->getSession(HistoricStatisticsManager::class);
    }

    public function getHistoricJobLogManager(): HistoricJobLogManager
    {
        return $this->getSession(HistoricJobLogManager::class);
    }

    public function getHistoricExternalTaskLogManager(): HistoricExternalTaskLogManager
    {
        return $this->getSession(HistoricExternalTaskLogManager::class);
    }

    public function getHistoricReportManager(): ReportManager
    {
        return $this->getSession(ReportManager::class);
    }

    public function getAuthorizationManager(): AuthorizationManager
    {
        return $this->getSession(AuthorizationManager::class);
    }

    public function getReadOnlyIdentityProvider(): ReadOnlyIdentityProviderInterface
    {
        return $this->getSession(ReadOnlyIdentityProviderInterface::class);
    }

    public function getWritableIdentityProvider(): WritableIdentityProviderInterface
    {
        return $this->getSession(WritableIdentityProviderInterface::class);
    }

    public function getTenantManager(): TenantManager
    {
        return $this->getSession(TenantManager::class);
    }

    public function getSchemaLogManager(): SchemaLogManager
    {
        return $this->getSession(SchemaLogManager::class);
    }

    public function getFormDefinitionManager(): FormDefinitionManager
    {
        return $this->getSession(FormDefinitionManager::class);
    }

    // CMMN /////////////////////////////////////////////////////////////////////

    /*public CaseDefinitionManager getCaseDefinitionManager() {
        return $this->getSession(CaseDefinitionManager::class);
    }

    public CaseExecutionManager getCaseExecutionManager() {
        return $this->getSession(CaseExecutionManager::class);
    }

    public CaseSentryPartManager getCaseSentryPartManager() {
        return $this->getSession(CaseSentryPartManager::class);
    }*/

    // DMN //////////////////////////////////////////////////////////////////////

    /*public DecisionDefinitionManager getDecisionDefinitionManager() {
        return $this->getSession(DecisionDefinitionManager::class);
    }

    public DecisionRequirementsDefinitionManager getDecisionRequirementsDefinitionManager() {
        return $this->getSession(DecisionRequirementsDefinitionManager::class);
    }

    public HistoricDecisionInstanceManager getHistoricDecisionInstanceManager() {
        return $this->getSession(HistoricDecisionInstanceManager::class);
    }*/

    // Filter ////////////////////////////////////////////////////////////////////

    public function getFilterManager(): FilterManager
    {
        return $this->getSession(FilterManager::class);
    }

    // External Tasks ////////////////////////////////////////////////////////////

    public function getExternalTaskManager(): ExternalTaskManager
    {
        return $this->getSession(ExternalTaskManager::class);
    }

    // getters and setters //////////////////////////////////////////////////////

    public function registerCommandContextListener(CommandContextListenerInterface $commandContextListener): void
    {
        $exists = false;
        foreach ($this->commandContextListeners as $listener) {
            if ($listener == $commandContextListener) {
                $exists = true;
            }
        }
        if (!$exists) {
            $this->commandContextListeners[] = $commandContextListener;
        }
    }

    public function getTransactionContext(): TransactionContextInterface
    {
        return $this->transactionContext;
    }

    public function getSessions(): array
    {
        return $this->sessions;
    }

    public function getFailedJobCommandFactory(): FailedJobCommandFactoryInterface
    {
        return $this->failedJobCommandFactory;
    }

    public function getAuthentication(): ?Authentication
    {
        $identityService = $this->processEngineConfiguration->getIdentityService();
        return $identityService->getCurrentAuthentication();
    }

    public function runWithoutAuthorization($command, ?CommandContext $commandContext = null)
    {
        if (is_callable($command)) {
            if ($commandContext == null) {
                $commandContext = Context::getCommandContext();
            }
            $authorizationEnabled = $commandContext->isAuthorizationCheckEnabled();
            /*try {*/
                $commandContext->disableAuthorizationCheck();
                return $command();
            /*} catch (\Exception $e) {*/
               //throw new ProcessEngineException($e->getMessage(), $e);
            /*} finally {
                if ($authorizationEnabled) {
                    $commandContext->enableAuthorizationCheck();
                }
            }*/
        } elseif ($command instanceof CommandInterface) {
            $commandContext = Context::getCommandContext();
            return $this->runWithoutAuthorization(
                function () use ($command, $commandContext) {
                    return $command->execute($commandContext);
                },
                $commandContext
            );
        }
    }

    public function getAuthenticatedUserId(): ?string
    {
        $identityService = $this->processEngineConfiguration->getIdentityService();
        $currentAuthentication = $identityService->getCurrentAuthentication();
        if ($currentAuthentication == null) {
            return null;
        } else {
            return $currentAuthentication->getUserId();
        }
    }

    public function getAuthenticatedGroupIds(): array
    {
        $identityService = $this->processEngineConfiguration->getIdentityService();
        $currentAuthentication = $identityService->getCurrentAuthentication();
        if ($currentAuthentication == null) {
            return [];
        } else {
            return $currentAuthentication->getGroupIds();
        }
    }

    public function enableAuthorizationCheck(): void
    {
        $this->authorizationCheckEnabled = true;
    }

    public function disableAuthorizationCheck(): void
    {
        $this->authorizationCheckEnabled = false;
    }

    public function isAuthorizationCheckEnabled(): bool
    {
        return $this->authorizationCheckEnabled;
    }

    public function setAuthorizationCheckEnabled(bool $authorizationCheckEnabled): void
    {
        $this->authorizationCheckEnabled = $authorizationCheckEnabled;
    }

    public function enableUserOperationLog(): void
    {
        $this->userOperationLogEnabled = true;
    }

    public function disableUserOperationLog(): void
    {
        $this->userOperationLogEnabled = false;
    }

    public function isUserOperationLogEnabled(): bool
    {
        return $this->userOperationLogEnabled;
    }

    public function setLogUserOperationEnabled(bool $userOperationLogEnabled): void
    {
        $this->userOperationLogEnabled = $userOperationLogEnabled;
    }

    public function enableTenantCheck(): void
    {
        $this->tenantCheckEnabled = true;
    }

    public function disableTenantCheck(): void
    {
        $this->tenantCheckEnabled = false;
    }

    public function setTenantCheckEnabled(bool $tenantCheckEnabled): void
    {
        $this->tenantCheckEnabled = $tenantCheckEnabled;
    }

    public function isTenantCheckEnabled(): bool
    {
        return $this->tenantCheckEnabled;
    }

    public function getCurrentJob(): JobEntity
    {
        return $this->currentJob;
    }

    public function setCurrentJob(JobEntity $currentJob): void
    {
        $this->currentJob = $currentJob;
    }

    public function isRestrictUserOperationLogToAuthenticatedUsers(): bool
    {
        return $this->restrictUserOperationLogToAuthenticatedUsers;
    }

    public function setRestrictUserOperationLogToAuthenticatedUsers(bool $restrictUserOperationLogToAuthenticatedUsers): void
    {
        $this->restrictUserOperationLogToAuthenticatedUsers = $restrictUserOperationLogToAuthenticatedUsers;
    }

    public function getOperationId(): ?string
    {
        if (!$this->getOperationLogManager()->isUserOperationLogEnabled()) {
            return null;
        }
        if ($this->operationId == null) {
            $this->operationId = Context::getProcessEngineConfiguration()->getIdGenerator()->getNextId();
        }
        return $this->operationId;
    }

    public function setOperationId(?string $operationId): void
    {
        $this->operationId = $operationId;
    }

    public function getOptimizeManager(): OptimizeManager
    {
        return $this->getSession(OptimizeManager::class);
    }

    public function executeWithOperationLogPrevented(CommandInterface $command): void
    {
        $initialLegacyRestrictions = $this->isRestrictUserOperationLogToAuthenticatedUsers();

        $this->disableUserOperationLog();
        $this->setRestrictUserOperationLogToAuthenticatedUsers(true);

        try {
            $command->execute($this);
        } finally {
            $this->enableUserOperationLog();
            $this->setRestrictUserOperationLogToAuthenticatedUsers($initialLegacyRestrictions);
        }
    }
}
