<?php

namespace Jabe\Engine\Impl\JobExecutor;

use Jabe\Engine\ProcessEngineException;
use Jabe\Engine\Delegate\{
    ExpressionInterface,
    VariableScopeInterface
};
use Jabe\Engine\Impl\Bpmn\Helper\BpmnProperties;
use Jabe\Engine\Impl\Calendar\BusinessCalendarInterface;
use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\El\StartProcessVariableScope;
use Jabe\Engine\Impl\Persistence\Entity\{
    ExecutionEntity,
    TimerEntity
};
use Jabe\Engine\Impl\Pvm\PvmScopeInterface;
use Jabe\Engine\Impl\Util\ClockUtil;

class TimerDeclarationImpl extends JobDeclaration
{
    protected $description;
    protected $type;

    protected $repeat;
    protected $isInterruptingTimer; // For boundary timers
    protected $eventScopeActivityId = null;
    protected $isParallelMultiInstance;

    protected $rawJobHandlerConfiguration;

    public function __construct(?ExpressionInterface $expression, string $type, string $jobHandlerType)
    {
        parent::__construct($jobHandlerType);
        $this->description = $expression;
        $this->type = $type;
    }

    public function isInterruptingTimer(): bool
    {
        return $this->isInterruptingTimer;
    }

    public function setInterruptingTimer(bool $isInterruptingTimer): void
    {
        $this->isInterruptingTimer = $isInterruptingTimer;
    }

    public function getRepeat(): string
    {
        return $repeat;
    }

    public function setEventScopeActivityId(string $eventScopeActivityId): void
    {
        $this->eventScopeActivityId = $eventScopeActivityId;
    }

    public function getEventScopeActivityId(): ?string
    {
        return $this->eventScopeActivityId;
    }

    protected function newJobInstance($execution = null): TimerEntity
    {
        $timer = new TimerEntity($this);
        if ($execution !== null) {
            $timer->setExecution($execution);
        }
        return $timer;
    }

    public function setRawJobHandlerConfiguration(string $rawJobHandlerConfiguration): void
    {
        $this->rawJobHandlerConfiguration = $rawJobHandlerConfiguration;
    }

    public function updateJob(TimerEntity $timer): void
    {
        $this->initializeConfiguration($timer->getExecution(), $timer);
    }

    protected function initializeConfiguration(ExecutionEntity $context, TimerEntity $job): void
    {
        $dueDateString = $this->resolveAndSetDuedate($context, $job, false);

        if ($this->type == TimerDeclarationType::CYCLE && $jobHandlerType != TimerCatchIntermediateEventJobHandler::TYPE) {
            // See ACT-1427: A boundary timer with a cancelActivity='true', doesn't need to repeat itself
            if (!$this->isInterruptingTimer) {
                $prepared = $this->prepareRepeat($dueDateString);
                $job->setRepeat($prepared);
            }
        }
    }

    public function resolveAndSetDuedate(ExecutionEntity $context, TimerEntity $job, bool $creationDateBased): string
    {
        $businessCalendar = Context::getProcessEngineConfiguration()
            ->getBusinessCalendarManager()
            ->getBusinessCalendar(TimerDeclarationType::calendarName($this->type));

        if ($this->description === null) {
            throw new ProcessEngineException("Timer '" . $context->getActivityId() . "' was not configured with a valid duration/time");
        }

        $dueDateString = null;
        $duedate = null;

        // ACT-1415: timer-declaration on start-event may contain expressions NOT
        // evaluating variables but other context, evaluating should happen nevertheless
        $scopeForExpression = $context;
        if ($scopeForExpression === null) {
            $scopeForExpression = StartProcessVariableScope::getSharedInstance();
        }

        $dueDateValue = $this->description->getValue($scopeForExpression);
        if (is_string($dueDateValue)) {
            $dueDateString = $dueDateValue;
        } elseif ($dueDateValue instanceof \DateTime) {
            $duedate = dueDateValue;
        } else {
            throw new ProcessEngineException("Timer '" . $context->getActivityId() . "' was not configured with a valid duration/time, either hand in a java.util.Date or a String in format 'yyyy-MM-dd'T'hh:mm:ss'");
        }

        if ($duedate === null) {
            if ($this->creationDateBased) {
                if ($job->getCreateTime() === null) {
                    throw new ProcessEngineException("Timer '" . $context->getActivityId() . "' has no creation time and cannot be recalculated based on creation date. Either recalculate on your own or trigger recalculation with creationDateBased set to false.");
                }
                $duedate = $businessCalendar->resolveDuedate($dueDateString, $job->getCreateTime());
            } else {
                $duedate = $businessCalendar->resolveDuedate($dueDateString);
            }
        }

        $job->setDuedate($duedate);
        return $dueDateString;
    }

    protected function postInitialize(ExecutionEntity $execution, TimerEntity $timer): void
    {
        $this->initializeConfiguration($execution, $timer);
    }

    protected function prepareRepeat(string $dueDate): string
    {
        if (str_starts_with($dueDate, "R") && count(explode("/", $dueDate)) == 2) {
            return str_replace("/", "/" . ClockUtil::getCurrentTime()->format("c")  . "/", $dueDate);
        }
        return $dueDate;
    }

    public function createTimerInstance(ExecutionEntity $execution): TimerEntity
    {
        return $this->createTimer($execution);
    }

    public function createStartTimerInstance(string $deploymentId): TimerEntity
    {
        return $this->createTimer($deploymentId);
    }

    public function createTimer($context): TimerEntity
    {
        if (is_string($context)) {
            $timer = parent::createJobInstance(null);
            $timer->setDeploymentId($context);
        } else {
            $timer = parent::createJobInstance($context);
        }
        $this->scheduleTimer($timer);
        return $timer;
    }

    protected function scheduleTimer(TimerEntity $timer): void
    {
        Context::getCommandContext()
            ->getJobManager()
            ->schedule($timer);
    }

    protected function resolveExecution(ExecutionEntity $context): ExecutionEntity
    {
        return $context;
    }

    protected function resolveJobHandlerConfiguration(ExecutionEntity $context): JobHandlerConfigurationInterface
    {
        return $this->resolveJobHandler()->newConfiguration($this->rawJobHandlerConfiguration);
    }

    /**
     * @return all timers declared in the given scope
     */
    public static function getDeclarationsForScope(?PvmScopeInterface $scope): array
    {
        if ($scope === null) {
            return [];
        }

        $props = $scope->getProperties();
        if (array_key_exists(BpmnProperties::TIMER_DECLARATIONS, $props)) {
            return $props[BpmnProperties::TIMER_DECLARATIONS];
        } else {
            return [];
        }
    }

    /**
     * @return all timeout listeners declared in the given scope
     */
    public static function getTimeoutListenerDeclarationsForScope(?PvmScopeInterface $scope): array
    {
        if ($scope === null) {
            return [];
        }

        $props = $scope->getProperties();
        if (array_key_exists(BpmnProperties::TIMEOUT_LISTENER_DECLARATIONS, $props)) {
            return $props[BpmnProperties::TIMEOUT_LISTENER_DECLARATIONS];
        } else {
            return [];
        }
    }
}
