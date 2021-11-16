<?php

namespace BpmPlatform\Engine\Impl\Delegate;

use BpmPlatform\Engine\Delegate\BaseDelegateExecutionInterface;
use BpmPlatform\Engine\Impl\Interceptor\DelegateInterceptorInterface;
use BpmPlatform\Engine\Impl\Repository\ResourceDefinitionEntityInterface;

abstract class DelegateInvocation
{
    protected $invocationResult;
    protected $contextExecution;
    protected $contextResource;

    /**
     * Provide a context execution or resource definition in which context the invocation
     *   should be performed. If both parameters are null, the invocation is performed in the
     *   current context.
     *
     * @param contextExecution set to an execution
     */
    public function __construct(?BaseDelegateExecutionInterface $contextExecution = null, ?ResourceDefinitionEntityInterface $contextResource = null)
    {
        // This constructor forces sub classes to call it, thereby making it more visible
        // whether a context switch is going to be performed for them.
        $this->contextExecution = $contextExecution;
        $this->contextResource = $contextResource;
    }

    /**
     * make the invocation proceed, performing the actual invocation of the user
     * code.
     *
     * @throws Exception
     *           the exception thrown by the user code
     */
    public function proceed(): void
    {
        $this->invoke();
    }

    abstract protected function invoke();

    /**
     * @return the result of the invocation (can be null if the invocation does
     *         not return a result)
     */
    public function getInvocationResult()
    {
        return $this->invocationResult;
    }

    /**
     * returns the execution in which context this delegate is invoked. may be null
     */
    public function getContextExecution(): ?BaseDelegateExecutionInterface
    {
        return $this->contextExecution;
    }

    public function getContextResource(): ?ResourceDefinitionEntity
    {
        return $this->contextResource;
    }
}
