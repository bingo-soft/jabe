<?php

namespace Jabe\Engine\Impl\Interceptor;

use Jabe\Engine\Application\ProcessApplicationReferenceInterface;
use Jabe\Engine\Impl\ProcessEngineLogger;
use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Persistence\Entity\ExecutionEntity;
use Jabe\Engine\Impl\Pvm\Runtime\AtomicOperation;

class AtomicOperationInvocation
{
    //private final static ContextLogger LOG = ProcessEngineLogger.CONTEXT_LOGGER;

    protected $operation;

    protected $execution;

    protected $performAsync;

    // for logging
    protected $applicationContextName = null;
    protected $activityId = null;
    protected $activityName = null;

    public function __construct(AtomicOperation $operation, ExecutionEntity $execution, bool $performAsync)
    {
        $this->init($operation, $execution, $performAsync);
    }

    protected function init(AtomicOperation $operation, ExecutionEntity $execution, bool $performAsync): void
    {
        $this->operation = $operation;
        $this->execution = $execution;
        $this->performAsync = $performAsync;
    }

    public function execute(BpmnStackTrace $stackTrace, ProcessDataContext $processDataContext): void
    {
        if (
            $this->operation != AtomicOperation::activityStartCancelScope()
            && $this->operation != AtomicOperation::activityStartInterruptScope()
            && $this->operation != AtomicOperation::activityStartConcurrent()
            && $this->operation != AtomicOperation::deleteCascade()
        ) {
            // execution might be replaced in the meantime:
            $replacedBy = $execution->getReplacedBy();
            if ($replacedBy != null) {
                $this->execution = $replacedBy;
            }
        }

        //execution was canceled for example via terminate end event
        if (
            $this->execution->isCanceled()
            && (
                $this->operation == AtomicOperation::transitionNotifyListenerEnd()
                || $this->operation == AtomicOperation::activityNotifyListenerEnd()
            )
        ) {
            return;
        }

        // execution might have ended in the meanwhile
        if (
            $this->execution->isEnded()
            && (
                $this->operation == AtomicOperation::transitionNotifyListenerTake()
                || $this->operation == AtomicOperation::activityStartCreateScope()
            )
        ) {
            return;
        }

        $currentPa = Context::getCurrentProcessApplication();
        if ($currentPa != null) {
            $this->applicationContextName = $currentPa->getName();
        }
        $this->activityId = $execution->getActivityId();
        $this->activityName = $execution->getCurrentActivityName();
        $stackTrace->add($this);

        $popProcessDataContextSection = $processDataContext->pushSection($execution);

        try {
            Context::setExecutionContext($execution);
            if (!$this->performAsync) {
                //LOG.debugExecutingAtomicOperation(operation, execution);
                $operation->execute($execution);
            } else {
                $execution->scheduleAtomicOperationAsync($this);
            }
            if ($popProcessDataContextSection) {
                $processDataContext->popSection();
            }
        } finally {
            Context::removeExecutionContext();
        }
    }

    // getters / setters ////////////////////////////////////

    public function getOperation(): AtomicOperation
    {
        return $this->operation;
    }

    public function getExecution(): ?ExecutionEntity
    {
        return $this->execution;
    }

    public function isPerformAsync(): bool
    {
        return $this->performAsync;
    }

    public function getApplicationContextName(): string
    {
        return $this->applicationContextName;
    }

    public function getActivityId(): string
    {
        return $this->activityId;
    }

    public function getActivityName(): string
    {
        return $this->activityName;
    }
}
