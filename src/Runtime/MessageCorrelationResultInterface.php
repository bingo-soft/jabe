<?php

namespace Jabe\Runtime;

interface MessageCorrelationResultInterface
{
    /**
     * Returns the execution entity on which the message was correlated to.
     *
     * @return ExecutionInterface the execution
     */
    public function getExecution(): ?ExecutionInterface;

    /**
     * Returns the process instance id on which the message was correlated to.
     *
     * @return ProcessInstanceInterface the process instance id
     */
    public function getProcessInstance(): ProcessInstanceInterface;

    /**
     * Returns the result type of the message correlation result.
     * Indicates if either the message was correlated to a waiting execution
     * or to a process definition like a start event.
     *
     * @return string the result type of the message correlation result
     */
    public function getResultType(): ?string;
}
