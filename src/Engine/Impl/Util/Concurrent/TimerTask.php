<?php

namespace Jabe\Engine\Impl\Util\Concurrent;

abstract class TimerTask implements RunnableInterface
{
    /**
     * The state of this task, chosen from the constants below.
     */
    protected $state = self::VIRGIN;

    /**
     * This task has not yet been scheduled.
     */
    public const VIRGIN = 0;

    /**
     * This task is scheduled for execution.  If it is a non-repeating task,
     * it has not yet been executed.
     */
    public const SCHEDULED   = 1;

    /**
     * This non-repeating task has already executed (or is currently
     * executing) and has not been cancelled.
     */
    public const EXECUTED    = 2;

    /**
     * This task has been cancelled (with a call to TimerTask.cancel).
     */
    public const CANCELLED   = 3;

    /**
     * Next execution time for this task in the format returned by
     * System.currentTimeMillis, assuming this task is scheduled for execution.
     * For repeating tasks, this field is updated prior to each task execution.
     */
    protected $nextExecutionTime;

    /**
     * Period in milliseconds for repeating tasks.  A positive value indicates
     * fixed-rate execution.  A negative value indicates fixed-delay execution.
     * A value of 0 indicates a non-repeating task.
     */
    protected $period = 0;

    /**
     * The action to be performed by this timer task.
     */
    abstract public function run(): void;

    public function cancel(): bool
    {
        $this->result = ($this->state == self::SCHEDULED);
        $this->state = self::CANCELLED;
        return $this->result;
    }

    public function scheduledExecutionTime(): int
    {
        return ($this->period < 0 ? $this->nextExecutionTime + $this->period
                               : $this->nextExecutionTime - $this->period);
    }
}
