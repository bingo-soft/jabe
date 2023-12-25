<?php

namespace Jabe\Impl;

use Jabe\{
    AuthorizationServiceInterface,
    ExternalTaskServiceInterface,
    FilterServiceInterface,
    FormServiceInterface,
    HistoryServiceInterface,
    IdentityServiceInterface,
    ManagementServiceInterface,
    OptimisticLockingException,
    ProcessEngineInterface,
    ProcessEngines,
    RepositoryServiceInterface,
    RuntimeServiceInterface,
    TaskServiceInterface
};
use Jabe\Impl\Cfg\{
    ProcessEngineConfigurationImpl,
    TransactionContextFactoryInterface
};
use Jabe\Impl\Db\Sql\DbSqlSessionFactory;
use Jabe\Impl\History\Event\SimpleIpBasedProvider;
use Jabe\Impl\Interceptor\{
    CommandExecutorInterface,
    SessionFactoryInterface
};
use Jabe\Impl\JobExecutor\JobExecutor;

class ProcessEngineImpl implements ProcessEngineInterface
{

    /** external task conditions used to signal long polling in rest API */
    //private static $EXT_TASK_CONDITIONS;
    //private final static ProcessEngineLogger LOG = ProcessEngineLogger.INSTANCE;

    protected $name;

    protected $repositoryService;
    protected $runtimeService;
    protected $historicDataService;
    protected $identityService;
    protected $taskService;
    protected $formService;
    protected $managementService;
    protected $authorizationService;
    //protected CaseService caseService;
    protected $filterService;
    protected $externalTaskService;
    //protected DecisionService decisionService;

    protected $databaseSchemaUpdate;
    protected $jobExecutor;
    protected $commandExecutor;
    protected $commandExecutorSchemaOperations;
    protected $sessionFactories = [];
    protected $expressionManager;
    protected $historyLevel;
    protected $transactionContextFactory;
    protected $processEngineConfiguration;

    public function __construct(ProcessEngineConfigurationImpl $processEngineConfiguration)
    {
        /*if (self::$EXT_TASK_CONDITIONS === null) {
            self::$EXT_TASK_CONDITIONS = new CompositeCondition();
        }*/
        $this->processEngineConfiguration = $processEngineConfiguration;
        $this->name = $processEngineConfiguration->getProcessEngineName();
        $this->repositoryService = $processEngineConfiguration->getRepositoryService();
        $this->runtimeService = $processEngineConfiguration->getRuntimeService();
        $this->historicDataService = $processEngineConfiguration->getHistoryService();
        $this->identityService = $processEngineConfiguration->getIdentityService();
        $this->taskService = $processEngineConfiguration->getTaskService();
        $this->formService = $processEngineConfiguration->getFormService();
        $this->managementService = $processEngineConfiguration->getManagementService();
        $this->authorizationService = $processEngineConfiguration->getAuthorizationService();
        //$this->caseService = $processEngineConfiguration->getCaseService();
        $this->filterService = $processEngineConfiguration->getFilterService();
        $this->externalTaskService = $processEngineConfiguration->getExternalTaskService();
        //$this->decisionService = $processEngineConfiguration->getDecisionService();

        $this->databaseSchemaUpdate = $processEngineConfiguration->getDatabaseSchemaUpdate();
        $this->jobExecutor = $processEngineConfiguration->getJobExecutor();
        $this->commandExecutor = $processEngineConfiguration->getCommandExecutorTxRequired();
        $this->commandExecutorSchemaOperations = $processEngineConfiguration->getCommandExecutorSchemaOperations();
        $this->sessionFactories = $processEngineConfiguration->getSessionFactories();
        $this->historyLevel = $processEngineConfiguration->getHistoryLevel();
        $this->transactionContextFactory = $processEngineConfiguration->getTransactionContextFactory();

        $this->executeSchemaOperations();

        if ($this->name === null) {
            //LOG.processEngineCreated(ProcessEngines.NAME_DEFAULT);
        } else {
            //LOG.processEngineCreated(name);
        }

        ProcessEngines::registerProcessEngine($this);

        if (($this->jobExecutor !== null)) {
            // register process engine with Job Executor
            $this->jobExecutor->registerProcessEngine($this);
        }

        if ($this->processEngineConfiguration->isMetricsEnabled()) {
            $reporterId = null;
            // only use a deprecated, custom MetricsReporterIdProvider,
            // if no static hostname AND custom HostnameProvider are set.
            // See ProcessEngineConfigurationImpl#initHostname()
            if (
                $this->processEngineConfiguration->getMetricsReporterIdProvider() !== null
                && $this->processEngineConfiguration->getHostnameProvider() instanceof SimpleIpBasedProvider
            ) {
                $reporterId = $this->processEngineConfiguration->getMetricsReporterIdProvider()->provideId($this);
            } else {
                $reporterId = $this->processEngineConfiguration->getHostname();
            }

            $dbMetricsReporter = $this->processEngineConfiguration->getDbMetricsReporter();
            $dbMetricsReporter->setReporterId($reporterId);

            if ($this->processEngineConfiguration->isDbMetricsReporterActivate()) {
                $dbMetricsReporter->start();
            }
        }
    }

    //@TODO
    protected function executeSchemaOperations(): void
    {
        /*$this->commandExecutorSchemaOperations->execute($this->processEngineConfiguration->getSchemaOperationsCommand());
        $this->commandExecutorSchemaOperations->execute($this->processEngineConfiguration->getHistoryLevelCommand());

        try {
            $this->commandExecutorSchemaOperations->execute($this->processEngineConfiguration->getProcessEngineBootstrapCommand());
        } catch (\Throwable $ole) {
            throw $ole;
            // if an OLE occurred during the process engine bootstrap, we suppress it
            // since all the data has already been persisted by a previous process engine bootstrap
            // LOG.historyCleanupJobReconfigurationFailure(ole);
            $databaseType = $this->getProcessEngineConfiguration()->getDatabaseType();
            if (DbSqlSessionFactory.CRDB.equals(databaseType)) {
                // on CRDB, we want to re-throw the OLE to the caller
                // when the CRDB Command retries are exausted
                throw ole;
            }
        }*/
    }

    public function close(): void
    {
        ProcessEngines::unregister($this);

        if ($this->processEngineConfiguration->isMetricsEnabled()) {
            $this->processEngineConfiguration->getDbMetricsReporter()->stop();
        }

        $telemetryReporter = $this->processEngineConfiguration->getTelemetryReporter();
        if ($telemetryReporter !== null) {
            $telemetryReporter->stop();
        }

        if (($this->jobExecutor !== null)) {
            // unregister process engine with Job Executor
            $this->jobExecutor->unregisterProcessEngine($this);
        }

        $this->commandExecutorSchemaOperations->execute(new SchemaOperationProcessEngineClose());

        $this->processEngineConfiguration->close();

        //LOG.processEngineClosed(name);
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getProcessEngineConfiguration(): ProcessEngineConfigurationImpl
    {
        return $this->processEngineConfiguration;
    }

    public function getIdentityService(): IdentityServiceInterface
    {
        return $this->identityService;
    }

    public function getManagementService(): ManagementServiceInterface
    {
        return $this->managementService;
    }

    public function getTaskService(): TaskServiceInterface
    {
        return $this->taskService;
    }

    public function getHistoryService(): HistoryServiceInterface
    {
        return $this->historicDataService;
    }

    public function getRuntimeService(): RuntimeServiceInterface
    {
        return $this->runtimeService;
    }

    public function getRepositoryService(): RepositoryServiceInterface
    {
        return $this->repositoryService;
    }

    public function getFormService(): FormServiceInterface
    {
        return $this->formService;
    }

    public function getAuthorizationService(): AuthorizationServiceInterface
    {
        return $this->authorizationService;
    }

    /*public CaseService getCaseService() {
        return caseService;
    }*/

    public function getFilterService(): FilterServiceInterface
    {
        return $this->filterService;
    }

    public function getExternalTaskService(): ExternalTaskServiceInterface
    {
        return $this->externalTaskService;
    }

    /*public DecisionService getDecisionService() {
        return decisionService;
    }*/
}
