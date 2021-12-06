<?php

namespace BpmPlatform\Engine\Impl\Delegate;

use BpmPlatform\Engine\Application\{
    InvocationContext,
    ProcessApplicationReferenceInterface
};
use BpmPlatform\Engine\Delegate\BaseDelegateExecutionInterface;
use BpmPlatform\Engine\Impl\Cfg\ProcessEngineConfigurationImpl;
use BpmPlatform\Engine\Impl\Context\{
    Context,
    CoreExecutionContext,
    ProcessApplicationContextUtil
};
use BpmPlatform\Engine\Impl\Core\Instance\CoreExecution;
use BpmPlatform\Engine\Impl\Interceptor\{
    CommandContext,
    DelegateInterceptorInterface
};
use BpmPlatform\Engine\Impl\Persistence\Entity\ExecutionEntity;
use BpmPlatform\Engine\Impl\Repository\ResourceDefinitionEntityInterface;

class DefaultDelegateInterceptor implements DelegateInterceptorInterface
{
    public function handleInvocation(DelegateInvocation $invocation): void
    {
        $processApplication = $this->getProcessApplicationForInvocation($invocation);
        $scope = $this;
        if ($processApplication != null && ProcessApplicationContextUtil::requiresContextSwitch($processApplication)) {
            Context::executeWithinProcessApplication(
                function () use ($scope) {
                    $scope->handleInvocation($invocation);
                    return null;
                },
                $processApplication,
                new InvocationContext($invocation->getContextExecution())
            );
        } else {
            $this->handleInvocationInContext($invocation);
        }
    }

    protected function handleInvocationInContext(DelegateInvocation $invocation): void
    {
        $commandContext = Context::getCommandContext();
        $wasAuthorizationCheckEnabled = $commandContext->isAuthorizationCheckEnabled();
        $wasUserOperationLogEnabled = $commandContext->isUserOperationLogEnabled();
        $contextExecution = $invocation->getContextExecution();

        $configuration = Context::getProcessEngineConfiguration();

        $popExecutionContext = false;

        try {
            if (!$configuration->isAuthorizationEnabledForCustomCode()) {
                // the custom code should be executed without authorization
                $commandContext->disableAuthorizationCheck();
            }

            try {
                $commandContext->disableUserOperationLog();

                try {
                    if ($contextExecution != null && !$this->isCurrentContextExecution($contextExecution)) {
                        $popExecutionContext = $this->setExecutionContext($contextExecution);
                    }
                    $invocation->proceed();
                } finally {
                    if ($popExecutionContext) {
                        Context::removeExecutionContext();
                    }
                }
            } finally {
                if ($wasUserOperationLogEnabled) {
                    $commandContext->enableUserOperationLog();
                }
            }
        } finally {
            if ($wasAuthorizationCheckEnabled) {
                $commandContext->enableAuthorizationCheck();
            }
        }
    }

    /**
     * @return true if the execution context is modified by this invocation
     */
    protected function setExecutionContext(BaseDelegateExecutionInterface $execution): bool
    {
        if ($execution instanceof ExecutionEntity) {
            Context::setExecutionContext($execution);
            return true;
        }/* elseif ($execution instanceof CaseExecutionEntity) {
            Context.setExecutionContext((CaseExecutionEntity) execution);
            return true;
        }*/
        return false;
    }

    protected function isCurrentContextExecution(BaseDelegateExecutionInterface $execution): bool
    {
        $coreExecutionContext = Context::getCoreExecutionContext();
        return $coreExecutionContext != null && $coreExecutionContext->getExecution() == $execution;
    }

    protected function getProcessApplicationForInvocation(DelegateInvocation $invocation): ProcessApplicationReferenceInterface
    {
        $contextExecution = $invocation->getContextExecution();
        $contextResource = $invocation->getContextResource();

        if ($contextExecution != null) {
            return ProcessApplicationContextUtil::getTargetProcessApplication($contextExecution);
        } elseif ($contextResource != null) {
            return ProcessApplicationContextUtil::getTargetProcessApplication($contextResource);
        } else {
            return null;
        }
    }
}
