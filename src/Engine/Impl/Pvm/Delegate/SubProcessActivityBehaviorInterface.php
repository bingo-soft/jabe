<?php

namespace BpmPlatform\Engine\Impl\Pvm\Delegate;

use BpmPlatform\Engine\Delegate\VariableScopeInterface;

interface SubProcessActivityBehaviorInterface extends ActivityBehaviorInterface
{
    /**
     * Pass the output variables from the process instance of the subprocess to the given execution.
     * This should be called before the process instance is destroyed.
     *
     * @param targetExecution execution of the calling process instance to pass the variables to
     * @param calledElementInstance instance of the called element that serves as the variable source
     */
    public function passOutputVariables(ActivityExecutionInterface $targetExecution, VariableScopeInterface $calledElementInstance): void;

    /**
     * Called after the process instance is destroyed for
     * this activity to perform its outgoing control flow logic.
     *
     * @param execution
     * @throws java.lang.Exception
     */
    public function completed(ActivityExecutionInterface $execution): void;
}
