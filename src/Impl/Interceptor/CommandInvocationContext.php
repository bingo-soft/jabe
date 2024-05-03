<?php

namespace Jabe\Impl\Interceptor;

use Jabe\Application\{
    InvocationContext,
    ProcessApplicationReferenceInterface
};
use Jabe\ProcessEngineException;
use Jabe\Impl\ProcessEngineLogger;
use Jabe\Impl\Cfg\ProcessEngineConfigurationImpl;
use Jabe\Impl\Cmd\CommandLogger;
use Jabe\Impl\Context\{
    Context,
    ProcessApplicationContextUtil
};
use Jabe\Impl\Persistence\Entity\ExecutionEntity;
use Jabe\Impl\Pvm\Runtime\{
    AtomicOperation,
    AtomicOperationInterface
};

class CommandInvocationContext
{
    //private final static CommandLogger LOG = ProcessEngineLogger.CMD_LOGGER;

    protected $throwable;
    protected $command;
    protected bool $isExecuting = false;
    protected $queuedInvocations = [];
    protected $bpmnStackTrace;
    protected $processDataContext;

    public function __construct(CommandInterface $command, ProcessEngineConfigurationImpl $configuration)
    {
        $this->bpmnStackTrace = new BpmnStackTrace();
        $this->command = $command;
        $this->processDataContext = new ProcessDataContext($configuration);
    }

    public function getThrowable(): ?\Throwable
    {
        return $this->throwable;
    }

    public function getCommand(): CommandInterface
    {
        return $this->command;
    }

    public function trySetThrowable(\Throwable $t): void
    {
        if ($this->throwable === null) {
            $this->throwable = $t;
        } else {
            //LOG.maskedExceptionInCommandContext(throwable);
        }
    }

    public function performOperationAsync(AtomicOperationInterface $executionOperation, ExecutionEntity $execution): void
    {
        $this->performOperation($executionOperation, $execution, true);
    }

    public function performOperation(AtomicOperationInterface $executionOperation, ExecutionEntity $execution, ?bool $performAsync = false, ...$args): void
    {
        $invocation = new AtomicOperationInvocation($executionOperation, $execution, $performAsync);
        array_unshift($this->queuedInvocations, $invocation);
        $this->performNext(...$args);
    }

    public function performNext(...$args): void
    {
        $nextInvocation = $this->queuedInvocations[0];
        if ($nextInvocation->operation->isAsyncCapable() && $this->isExecuting) {
            return;
        }

        $targetProcessApplication = $this->getTargetProcessApplication($nextInvocation->execution);
        if ($this->requiresContextSwitch($targetProcessApplication)) {
            $scope = $this;
            Context::executeWithinProcessApplication(function () use ($scope) {
                $scope->performNext();
                return null;
            }, $targetProcessApplication, new InvocationContext($nextInvocation->execution));
        } else {
            if (!$nextInvocation->operation->isAsyncCapable()) {
                // if operation is not async capable, perform right away.
                $this->invokeNext(...$args);
            } else {
                try {
                    $this->isExecuting = true;
                    while (!empty($this->queuedInvocations)) {
                        // assumption: all operations are executed within the same process application...
                        $this->invokeNext(...$args);
                    }
                } finally {
                    $this->isExecuting = false;
                }
            }
        }
    }

    protected function invokeNext(...$args): void
    {
        $invocation = array_shift($this->queuedInvocations);
        try {
            if ($invocation !== null) {
                $invocation->execute($this->bpmnStackTrace, $this->processDataContext, ...$args);
            }
        } catch (\Throwable $e) {
            // log bpmn stacktrace
            $this->bpmnStackTrace->printStackTrace(Context::getProcessEngineConfiguration()->isBpmnStacktraceVerbose());
            // rethrow
            throw $e;
        }
    }

    protected function requiresContextSwitch(?ProcessApplicationReferenceInterface $processApplicationReference): bool
    {
        return ProcessApplicationContextUtil::requiresContextSwitch($processApplicationReference);
    }

    protected function getTargetProcessApplication(ExecutionEntity $execution): ?ProcessApplicationReferenceInterface
    {
        return ProcessApplicationContextUtil::getTargetProcessApplication($execution);
    }

    public function rethrow(): void
    {
        if ($this->throwable !== null) {
            $errorStack = [];
            for ($i = 0; $i < 50; $i += 1) {
                try {
                    $t = $this->throwable->getTrace()[$i];
                    $errorStack[] = sprintf("%s.%s.%s", $t['file'], $t['function'], $t['line']);
                } catch (\Throwable $tt) {
                }
            }
            fwrite(STDERR,
                sprintf("Exception while executing command: %s\nError stack: %s\n",
                    $this->throwable->getMessage(),
                    implode(' <= ', $errorStack)
                )
            );
            throw new ProcessEngineException(sprintf("exception while executing command: %s", implode(' <= ', $errorStack)), $this->throwable);
        }
    }

    public function getProcessDataContext(): ProcessDataContext
    {
        return $this->processDataContext;
    }
}
