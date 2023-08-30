<?php

namespace Jabe\Test;

use Jabe\{
    AuthorizationServiceInterface,
    ExternalTaskServiceInterface,
    FilterServiceInterface,
    FormServiceInterface,
    HistoryServiceInterface,
    IdentityServiceInterface,
    ManagementServiceInterface,
    ProcessEngineInterface,
    ProcessEngineServicesInterface,
    RepositoryServiceInterface,
    RuntimeServiceInterface,
    TaskServiceInterface
};
use Jabe\Impl\Cfg\ProcessEngineConfigurationImpl;
use Jabe\Impl\Test\TestHelper;
use Jabe\Respository\DeploymentInterface;
use Jabe\Test\Deployment;

class ProcessEngineRule implements ProcessEngineServicesInterface
{
    protected $configurationResource = "engine.cfg.xml";
    protected $deploymentId = null;
    protected $additionalDeployments = [];

    protected bool $ensureCleanAfterTest = false;
    protected $processEngine;
    protected $processEngineConfiguration;
    protected $repositoryService;
    protected $runtimeService;
    protected $taskService;
    protected $historyService;
    protected $identityService;
    protected $managementService;
    protected $formService;
    protected $filterService;
    protected $authorizationService;
    //protected CaseService caseService;
    protected $externalTaskService;
    //protected DecisionService decisionService;

    public function __construct(string|ProcessEngineInterface|bool $arg1 = null, ?bool $ensureCleanAfterTest = false)
    {
        if (is_string($arg1)) {
            $this->configurationResource = $arg1;
        } elseif ($arg1 instanceof ProcessEngineInterface) {
            $this->processEngine = $arg1;
        } elseif (is_bool($arg1)) {
            $this->ensureCleanAfterTest = $arg1;
        }
        if (!is_bool($arg1)) {
            $this->ensureCleanAfterTest = $ensureCleanAfterTest;
        }
    }

    public function starting(?string $testClass, ?string $methodName): void
    {
        $ref = new \ReflectionClass($testClass);
        $method = $ref->getMethod($methodName);
        $attrs = $method->getAttributes(Deployment::class);
        if (empty($attrs)) {
            $refMethod = $ref->getMethod($methodName);
            $attrs = $ref->getAttributes(Deployment::class);
        }
        $attribute = null;
        if (!empty($attrs)) {
            $attribute = $attrs[0]->newInstance();
        }
        if ($attribute !== null) {
            $this->deploymentId = TestHelper::annotationDeploymentSetUp(
                $this->processEngine,
                $testClass,
                $methodName,
                $attribute
            );
        }
    }

    public function apply(): void
    {
        if ($this->processEngine == null) {
            $this->initializeProcessEngine();
        }

        $this->initializeServices();
    }

    protected function initializeProcessEngine(): void
    {
        $this->processEngine = TestHelper::getProcessEngine($this->configurationResource);
    }

    protected function initializeServices(): void
    {
        $this->processEngineConfiguration = $this->processEngine->getProcessEngineConfiguration();
        $this->repositoryService = $this->processEngine->getRepositoryService();
        $this->runtimeService = $this->processEngine->getRuntimeService();
        $this->taskService = $this->processEngine->getTaskService();
        $this->historyService = $this->processEngine->getHistoryService();
        $this->identityService = $this->processEngine->getIdentityService();
        $this->managementService = $this->processEngine->getManagementService();
        $this->formService = $this->processEngine->getFormService();
        $this->authorizationService = $this->processEngine->getAuthorizationService();
        //caseService = processEngine.getCaseService();
        $this->filterService = $this->processEngine->getFilterService();
        $this->externalTaskService = $this->processEngine->getExternalTaskService();
        //decisionService = processEngine.getDecisionService();*/
    }

    protected function clearServiceReferences(): void
    {
        $this->processEngineConfiguration = null;
        $this->repositoryService = null;
        $this->runtimeService = null;
        $this->taskService = null;
        $this->formService = null;
        $this->historyService = null;
        $this->identityService = null;
        $this->managementService = null;
        $this->authorizationService = null;
        //caseService = null;
        $this->filterService = null;
        $this->externalTaskService = null;
        //decisionService = null;
    }

    public function finished(?string $testClass, ?string $methodName): void
    {
        $this->identityService->clearAuthentication();
        $this->processEngine->getProcessEngineConfiguration()->setTenantCheckEnabled(true);

        TestHelper::annotationDeploymentTearDown($this->processEngine, $this->deploymentId, $testClass, $methodName);
        foreach ($this->additionalDeployments as $additionalDeployment) {
            TestHelper::deleteDeployment($this->processEngine, $additionalDeployment);
        }

        if ($this->ensureCleanAfterTest) {
            TestHelper::assertAndEnsureCleanDbAndCache($this->processEngine);
        }

        TestHelper::resetIdGenerator($this->processEngineConfiguration);
        ClockUtil::reset();

        $this->clearServiceReferences();
        //PlatformTelemetryRegistry.clear();
    }

    public function setCurrentTime(\DateTime $currentTime): void
    {
        ClockUtil::setCurrentTime($currentTime, ...$this->processEngine->getProcessEngineConfiguration()->getJobExecutorState());
    }

    public function getConfigurationResource(): ?string
    {
        return $this->configurationResource;
    }

    public function setConfigurationResource(?string $configurationResource): void
    {
        $this->configurationResource = $configurationResource;
    }

    public function getProcessEngine(): ProcessEngineInterface
    {
        return $this->processEngine;
    }

    public function setProcessEngine(ProcessEngineInterface $processEngine): void
    {
        $this->processEngine = $processEngine;
    }

    public function getProcessEngineConfiguration(): ?ProcessEngineConfigurationImpl
    {
        return $this->processEngineConfiguration;
    }

    public function setProcessEngineConfiguration(ProcessEngineConfigurationImpl $processEngineConfiguration): void
    {
        $this->processEngineConfiguration = $processEngineConfiguration;
    }

    public function getRuntimeService(): RuntimeServiceInterface
    {
        return $this->runtimeService;
    }

    public function setRuntimeService(RuntimeServiceInterface $runtimeService): void
    {
        $this->runtimeService = $runtimeService;
    }

    public function getRepositoryService(): RepositoryServiceInterface
    {
        return $this->repositoryService;
    }

    public function setRepositoryService(RepositoryServiceInterface $repositoryService): void
    {
        $this->repositoryService = $repositoryService;
    }

    public function getFormService(): FormServiceInterface
    {
        return $this->formService;
    }

    public function setFormService(FormServiceInterface $formService): void
    {
        $this->formService = $formService;
    }

    public function getTaskService(): TaskServiceInterface
    {
        return $this->taskService;
    }

    public function setTaskService(TaskServiceInterface $taskService): void
    {
        $this->taskService = $taskService;
    }

    public function getHistoryService(): HistoryServiceInterface
    {
        return $this->historyService;
    }

    public function setHistoryService(TaskServiceInterface $taskService): void
    {
        $this->historyService = $historyService;
    }

    public function getIdentityService(): IdentityServiceInterface
    {
        return $this->identityService;
    }

    public function setIdentityService(IdentityServiceInterface $identityService): void
    {
        $this->identityService = $identityService;
    }

    public function getManagementService(): ManagementServiceInterface
    {
        return $this->managementService;
    }

    public function setManagementService(ManagementServiceInterface $managementService): void
    {
        $this->managementService = $managementService;
    }

    public function getAuthorizationService(): AuthorizationServiceInterface
    {
        return $this->authorizationService;
    }

    public function setAuthorizationService(AuthorizationServiceInterface $authorizationService): void
    {
        $this->authorizationService = $authorizationService;
    }

    public function getFilterService(): FilterServiceInterface
    {
        return $this->filterService;
    }

    public function setFilterService(FilterServiceInterface $filterService): void
    {
        $this->filterService = $filterService;
    }

    public function getExternalTaskService(): ExternalTaskServiceInterface
    {
        return $this->externalTaskService;
    }

    public function setExternalTaskService(ExternalTaskServiceInterface $externalTaskService): void
    {
        $this->externalTaskService = $externalTaskService;
    }

    public function manageDeployment(/*DeploymentInterface*/$deployment): void
    {
        $this->additionalDeployments[] = $deployment->getId();
    }
}
