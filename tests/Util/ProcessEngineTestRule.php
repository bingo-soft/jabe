<?php

namespace Tests\Util;

use Bpmn\BpmnModelInstanceInterface;
use Jabe\{
    AuthorizationServiceInterface,
    HistoryServiceInterface,
    ProcessEngineInterface,
    TaskServiceInterface
};
use Jabe\Authorization\{
    AuthorizationInterface,
    PermissionInterface,
    ResourceInterface
};
use Jabe\Delegate\ExpressionInterface;
use Jabe\Impl\Cfg\ProcessEngineConfigurationImpl;
use Jabe\Impl\Db\Sql\DbSqlSessionFactory;
use Jabe\Impl\El\FixedValue;
use Jabe\Impl\History\{
    HistoryLevel,
    HistoryLevelInterface
};
use Jabe\Impl\Interceptor\CommandInterface;
use Jabe\Impl\JobExecutor\JobExecutor;
use Jabe\Impl\Persistence\Entity\{
    JobEntity,
    JobManager
};
use Jabe\Impl\Util\ClockUtil;
use Jabe\Repository\{
    DeploymentInterface,
    DeploymentBuilderInterface,
    DeploymentWithDefinitionsInterface,
    ProcessDefinitionInterface
};
use Jabe\Runtime\{
    JobInterface,
    ProcessInstanceInterface
};
use Jabe\Task\TaskInterface;
use Jabe\Test\ProcessEngineRule;
use Jabe\Variable\VariableMapInterface;

class ProcessEngineTestRule
{
    public const DEFAULT_BPMN_RESOURCE_NAME = "process.bpmn20.xml";
    protected $processEngineRule;
    protected $processEngine;

    public function __construct(ProcessEngineRule $processEngineRule)
    {
        $this->processEngineRule = $processEngineRule;
    }

    public function starting(): void
    {
        $this->processEngine = $this->processEngineRule->getProcessEngine();
    }

    public function finished(): void
    {
        $this->processEngine = null;
    }

    public function assertProcessEnded(?string $processInstanceId): void
    {
        $processInstance = $this->processEngine
            ->getRuntimeService()
            ->createProcessInstanceQuery()
            ->processInstanceId($processInstanceId)
            ->singleResult();

        assert($processInstance == null, "Process instance with id " . $processInstanceId . " is not finished");
    }

    public function assertProcessNotEnded(?string $processInstanceId): void
    {
        $processInstance = $this->processEngine
            ->getRuntimeService()
            ->createProcessInstanceQuery()
            ->processInstanceId($processInstanceId)
            ->singleResult();

        if ($processInstance == null) {
            throw new \Exception("Expected process instance '" . $processInstanceId . "' to be still active but it was not in the db");
        }
    }

    public function deploy(...$instances): DeploymentWithDefinitionsInterface
    {
        if ($instances[0] instanceof DeploymentBuilderInterface && count($instances) == 1) {
            $deployment = $instances[0]->deployWithResult();
            $this->processEngineRule->manageDeployment($deployment);
            return $deployment;
        } elseif (is_string($instances[0])) {
            return $this->deploy($this->createDeploymentBuilder(), [], $instances);
        } elseif ($instances[0] instanceof BpmnModelInstanceInterface && count($instances) == 2) {
            return $this->deploy($this->createDeploymentBuilder(), [$instances[0]], [$instances[1]]);
        } elseif ($instances[0] instanceof BpmnModelInstanceInterface) {
            $models = [];
            $it = 0;
            foreach ($instances as $el) {
                if ($el instanceof BpmnModelInstanceInterface) {
                    $it += 1;
                    $models[] = $el;
                } else {
                    break;
                }
            }
            return $this->deploy($this->createDeploymentBuilder(), $models, []);
        } elseif ($instances[0] instanceof DeploymentBuilderInterface && count($instances) == 3) {
            $i = 0;
            foreach ($instances[1] as $bpmnModelInstance) {
                $instances[0]->addModelInstance($i . "_" . self::DEFAULT_BPMN_RESOURCE_NAME, $bpmnModelInstance);
                $i += 1;
            }
            foreach ($instances[2] as $resource) {
                $instances[0]->addClasspathResource($resource);
            }
            return $this->deploy($instances[0]);
        }
    }

    public function deployForTenant(?string $tenantId, ...$instances): DeploymentInterface
    {
        if ($instances[0] instanceof BpmnModelInstanceInterface && count($instances) == 1) {
            return $this->deploy($this->createDeploymentBuilder()->tenantId($tenantId), [$instances[0]], []);
        } elseif (is_string($instances[0])) {
            return $this->deploy($this->createDeploymentBuilder()->tenantId($tenantId), [], $instances);
        } elseif ($instances[0] instanceof BpmnModelInstanceInterface && count($instances) == 2) {
            return $this->deploy($this->createDeploymentBuilder()->tenantId($tenantId), [$instances[0]], [$instances[1]]);
        }
    }

    public function deployAndGetDefinition(BpmnModelInstanceInterface|string $instance): ProcessDefinitionInterface
    {
        return $this->deployForTenantAndGetDefinition(null, $instance);
    }

    public function deployForTenantAndGetDefinition(?string $tenant, BpmnModelInstanceInterface|string $instance): ProcessDefinitionInterface
    {
        if (is_string($instance)) {
            $deployment = $this->deploy($this->createDeploymentBuilder()->tenantId($tenant), [], [ $instance ]);
        } else {
            $deployment = $this->deploy($this->createDeploymentBuilder()->tenantId($tenant), [ $instance ], [ ]);
        }
        return $this->processEngineRule->getRepositoryService()
                ->createProcessDefinitionQuery()
                ->deploymentId($deployment->getId())
                ->singleResult();
    }

    protected function createDeploymentBuilder(): DeploymentBuilderInterface
    {
        return $this->processEngine->getRepositoryService()->createDeployment();
    }

    public function waitForJobExecutorToProcessAllJobs(string $processInstanceId, int $maxMillisToWait = 0): void
    {
        $processEngineConfiguration = $this->processEngine->getProcessEngineConfiguration();
        $jobExecutor = $processEngineConfiguration->getJobExecutor();
        $jobExecutor->start();

        try {
            $areJobsAvailable = true;
            $isTimeLimitExceeded = false;
            $intervalMillis = 1;
            try {
                $cur = time();
                while ($areJobsAvailable && !$isTimeLimitExceeded) {
                    usleep($intervalMillis * 1000);
                    $areJobsAvailable = $this->areJobsAvailable($processInstanceId);
                    $isTimeLimitExceeded = (time() - $cur) * 1000 >= $maxMillisToWait;
                }
            } catch (\Exception $e) {
            } finally {
            }
            if ($areJobsAvailable) {
                throw new \Exception("time limit of " . $maxMillisToWait . " was exceeded");
            }
        } finally {
            $jobExecutor->shutdown();
        }
    }

    protected function areJobsAvailable(string $processInstanceId): bool
    {
        $list = $this->processEngine->getManagementService()->createJobQuery()->processInstanceId($processInstanceId)->list();
        foreach ($list as $job) {
            if (!$job->isSuspended() && $job->getRetries() > 0 && ($job->getDuedate() == null || ClockUtil::getCurrentTime()->getTimestamp() > (new \DateTime($job->getDuedate()))->getTimestamp())) {
                return true;
            }
        }
        return false;
    }

    /**
     * Execute all available jobs recursively till no more jobs found or the number of executions is higher than expected.
     *
     * @param expectedExecutions number of expected job executions
     *
     * @throws AssertionFailedError when execute less or more jobs than expected
     *
     * @see #executeAvailableJobs()
     */
    public function executeAvailableJobs(string $processId, ...$args): void
    {
        if (is_int($args[0]) && count($args) == 1) {
            $jobsExecuted = 0;
            $expectedExecutions = $args[0];
            $recursive = true;
        } elseif (is_int($args[0]) && count($args) == 2) {
            $jobsExecuted = 0;
            $expectedExecutions = $args[0];
            $recursive = $args[1];
        } elseif (is_int($args[0]) && count($args) == 3) {
            $jobsExecuted = $args[0];
            $expectedExecutions = $args[1];
            $recursive = $args[2];
        } elseif (count($args) == 0) {
            $jobsExecuted = 0;
            $expectedExecutions = PHP_INT_MAX;
            $recursive = true;
        }

        $jobs = $this->processEngine->getManagementService()->createJobQuery()->processInstanceId($processId)->withRetriesLeft()->list();
        if (empty($jobs)) {
            if ($expectedExecutions != PHP_INT_MAX) {
                assert($expectedExecutions == $jobsExecuted, "executed less jobs than expected.");
            }
            return;
        }

        foreach ($jobs as $job) {
            try {
                $this->processEngine->getManagementService()->executeJob($job->getId());
                $jobsExecuted += 1;
            } catch (\Exception $e) {
            }
        }

        assert($jobsExecuted <= $expectedExecutions, "executed more jobs than expected.");

        if ($recursive) {
            $this->executeAvailableJobs($processId, $jobsExecuted, $expectedExecutions, $recursive);
        }
    }

    public function completeTask(?string $taskKey): void
    {
        $taskService = $this->processEngine->getTaskService();
        $task = $taskService->createTaskQuery()->taskDefinitionKey($taskKey)->singleResult();
        assert(!empty($task), "Expected a task with key '" . $taskKey . "' to exist");
        $taskService->complete($task->getId());
    }

    public function completeAnyTask(?string $taskKey): void
    {
        $taskService = $this->processEngine->getTaskService();
        $tasks = $taskService->createTaskQuery()->taskDefinitionKey($taskKey)->list();
        assert(!empty($tasks));
        $taskService->complete($tasks[0]->getId());
    }

    public function setAnyVariable(?string $executionId): void
    {
        $this->setVariable($executionId, "any", "any");
    }

    public function setVariable(?string $executionId, ?string $varName, $varValue): void
    {
        $this->processEngine->getRuntimeService()->setVariable($executionId, $varName, $varValue);
    }

    public function correlateMessage(?string $messageName): void
    {
        $this->processEngine->getRuntimeService()->createMessageCorrelation($messageName)->correlate();
    }

    public function sendSignal(?string $signalName): void
    {
        $this->processEngine->getRuntimeService()->signalEventReceived($signalName);
    }

    public function isHistoryLevelNone(): bool
    {
        $historyLevel = $this->processEngineRule->getProcessEngineConfiguration()->getHistoryLevel();
        return HistoryLevel::historyLevelNone() == $historyLevel;
    }

    public function isHistoryLevelActivity(): bool
    {
        $historyLevel = $this->processEngineRule->getProcessEngineConfiguration()->getHistoryLevel();
        return HistoryLevel::historyLevelActivity() == $historyLevel;
    }

    public function isHistoryLevelAudit(): bool
    {
        $historyLevel = $this->processEngineRule->getProcessEngineConfiguration()->getHistoryLevel();
        return HistoryLevel::historyLevelAudit() == $historyLevel;
    }

    public function isHistoryLevelFull(): bool
    {
        $historyLevel = $this->processEngineRule->getProcessEngineConfiguration()->getHistoryLevel();
        return HistoryLevel::historyLevelFull() == $historyLevel;
    }

    public function deleteHistoryCleanupJobs(): void
    {
        $historyService = $this->processEngine->getHistoryService();
        $jobs = $historyService->findHistoryCleanupJobs();
        foreach ($jobs as $job) {
            $jobId = $job->getId();

            $this->processEngineRule
            ->getProcessEngineConfiguration()
            ->getCommandExecutorTxRequired()
            ->execute(new class ($job) implements CommandInterface {
                private $job;

                public function __construct(JobInterface $job)
                {
                    $this->job = $job;
                }

                public function execute(CommandContext $commandContext, ...$args)
                {
                    $jobManager = $commandContext->getJobManager();

                    $jobEntity = $jobManager->findJobById($jobId);

                    $jobEntity->delete();
                    $commandContext->getHistoricJobLogManager()->deleteHistoricJobLogByJobId($job->getId());
                    return null;
                }

                public function isRetryable(): bool
                {
                    return false;
                }
            });
        }
    }

    public function getDatabaseType(): ?string
    {
        return $this->processEngineRule->getProcessEngineConfiguration()
            ->getDbSqlSessionFactory()
            ->getDatabaseType();
    }

    public function deleteAllAuthorizations(): void
    {
        $authorizationService = $this->processEngine->getAuthorizationService();

        $auths = $authorizationService->createAuthorizationQuery()->list();
        foreach ($auths as $auth) {
            $authorizationService->deleteAuthorization($auth->getId());
        }
    }

    public function deleteAllStandaloneTasks(): void
    {
        $taskService = $this->processEngine->getTaskService();

        $tasks = $taskService->createTaskQuery()->list();
        foreach ($tasks as $task) {
            if ($task->getProcessInstanceId() === null) {
                $taskService->deleteTask($task->getId(), true);
            }
        }
    }

    public function createGrantAuthorization(?string $userId, ResourceInterface $resource, ?string $resourceId, PermissionInterface ...$permissions): void
    {
        $authorizationService = $this->processEngine->getAuthorizationService();
        $processInstanceAuthorization = $authorizationService->createNewAuthorization(AuthorizationInterface::AUTH_TYPE_GRANT);
        $processInstanceAuthorization->setResource($resource);
        $processInstanceAuthorization->setResourceId($resourceId);
        $processInstanceAuthorization->setPermissionsFromSupplied($permissions);
        $processInstanceAuthorization->setUserId($userId);
        $authorizationService->saveAuthorization($processInstanceAuthorization);
    }
}
