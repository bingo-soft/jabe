<?php

namespace Tests\Util;

use PHPUnit\Framework\TestCase;
use Jabe\ProcessEngineInterface;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandExecutorInterface
};
use Jabe\Impl\Util\ClockUtil;
use Jabe\Runtime\{
    ActivityInstanceInterface,
    JobInterface
};

abstract class PluggableProcessEngineTest extends TestCase
{
    protected $engineRule;
    protected $testRule;

    //@Rule
    //public RuleChain ruleChain = RuleChain.outerRule(engineRule).around(testRule);

    protected $processEngine;
    protected $processEngineConfiguration;
    protected $repositoryService;
    protected $runtimeService;
    protected $taskService;
    protected $formService;
    protected $historyService;
    protected $identityService;
    protected $managementService;
    protected $authorizationService;
    //protected CaseService caseService;
    protected $filterService;
    protected $externalTaskService;
    //protected DecisionService decisionService;
    protected bool $initialized = false;

    protected $currentTime;

    protected function setUp(): void
    {
        $this->initializeServices();
        $this->engineRule->starting(get_class($this), $this->getName());
        $this->testRule->starting();
    }

    public function initializeServices(): void
    {
        if (!$this->initialized) {
            $this->engineRule = new ProvidedProcessEngineRule();
            $this->engineRule->apply();

            $this->testRule = new ProcessEngineTestRule($this->engineRule);

            $this->processEngine = $this->engineRule->getProcessEngine();
            $this->processEngineConfiguration = $this->engineRule->getProcessEngineConfiguration();

            if ($this->currentTime !== null) {
                ClockUtil::setCurrentTime($this->currentTime, ...$this->processEngineConfiguration->getJobExecutorState());
            }

            $this->repositoryService = $this->processEngine->getRepositoryService();
            $this->runtimeService = $this->processEngine->getRuntimeService();
            $this->taskService = $this->processEngine->getTaskService();
            $this->formService = $this->processEngine->getFormService();
            $this->historyService = $this->processEngine->getHistoryService();
            $this->identityService = $this->processEngine->getIdentityService();
            $this->managementService = $this->processEngine->getManagementService();
            $this->authorizationService = $this->processEngine->getAuthorizationService();
            //caseService = $this->processEngine->getCaseService();
            $this->filterService = $this->processEngine->getFilterService();
            $this->externalTaskService = $this->processEngine->getExternalTaskService();
            //decisionService = $this->processEngine->getDecisionService();
            $this->initialized = true;
        }
    }

    public function getProcessEngine(): ProcessEngineInterface
    {
        return $this->processEngine;
    }

    public function areJobsAvailable(): bool
    {
        $list = $this->managementService->createJobQuery()->list();
        foreach ($list as $job) {
            if (!$job->isSuspended() && $job->getRetries() > 0 && ($job->getDuedate() == null || ClockUtil::getCurrentTime()->getTimestamp() > (new \DateTime($job->getDuedate()))->getTimestamp())) {
                return true;
            }
        }
        return false;
    }

    protected function getInstancesForActivityId(ActivityInstanceInterface $activityInstance, ?string $activityId): array
    {
        $result = [];
        if ($activityInstance->getActivityId() == $activityId) {
            $result[] = $activityInstance;
        }
        foreach ($activityInstance->getChildActivityInstances() as $childInstance) {
            $result = array_merge($result, $this->getInstancesForActivityId($childInstance, $activityId));
        }
        return $result;
    }

    protected function deleteHistoryCleanupJobs(): void
    {
        $jobs = $this->historyService->findHistoryCleanupJobs();
        foreach ($jobs as $job) {
            $command = new class ($job) implements CommandInterface
            {
                private $job;

                public function __construct(JobInterface $job)
                {
                    $this->job = $job;
                }

                public function execute(CommandContext $commandContext, ...$args)
                {
                    $commandContext->getJobManager()->deleteJob($this->job);
                    return null;
                }

                public function isRetryable(): bool
                {
                    return false;
                }
            };

            $this->processEngineConfiguration->getCommandExecutorTxRequired()->execute($command);
        }
    }
}
