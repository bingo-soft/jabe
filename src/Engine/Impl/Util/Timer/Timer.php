<?php

namespace Jabe\Engine\Impl\Util\Timer;

class Timer
{
    /**
     * The timer task queue.  This data structure is shared with the timer
     * thread.  The timer produces tasks, via its various schedule calls,
     * and the timer thread consumes, executing timer tasks as appropriate,
     * and removing them from the queue when they're obsolete.
     */
    private $queue;

    /**
     * The timer thread.
     */
    private $thread;

    /**
     * This object causes the timer's task execution thread to exit
     * gracefully when there are no live references to the Timer object and no
     * tasks in the timer queue.  It is used in preference to a finalizer on
     * Timer as such a finalizer would be susceptible to a subclass's
     * finalizer forgetting to call it.
     */
    /*private final Object threadReaper = new Object() {
        protected void finalize() throws Throwable {
            synchronized(queue) {
                thread.newTasksMayBeScheduled = false;
                queue.notify(); // In case queue is empty.
            }
        }
    };*/

    /**
     * This ID is used to generate thread names.
     */
    private static $nextSerialNumber;

    private static function serialNumber(): int
    {
        if (self::$nextSerialNumber == null) {
            self::$nextSerialNumber = new AtomicInteger(0);
        }
        return self::$nextSerialNumber->getAndIncrement();
    }

    /**
     * Creates a new timer whose associated thread has the specified name.
     * The associated thread does <i>not</i>
     * {@linkplain Thread#setDaemon run as a daemon}.
     *
     * @param name the name of the associated thread
     * @throws NullPointerException if {@code name} is null
     * @since 1.5
     */
    public function __construct(string $name = null)
    {
        $this->queue = new TaskQueue();
        $this->thread = new TimerThread($this->queue);
        if ($name == null) {
            $name = "Timer-" . self::serialNumber();
        }
        $this->thread->setName($name);
        $this->thread->start();
    }

    /**
     * Schedules the specified task for execution after the specified delay.
     *
     * @param task  task to be scheduled.
     * @param delay delay in milliseconds before task is to be executed.
     * @throws IllegalArgumentException if <tt>delay</tt> is negative, or
     *         <tt>delay + System.currentTimeMillis()</tt> is negative.
     * @throws IllegalStateException if task was already scheduled or
     *         cancelled, timer was cancelled, or timer thread terminated.
     * @throws NullPointerException if {@code task} is null
     */
    public function schedule(TimerTask $task, $delayOrDatetime, $period = null): void
    {
        if (is_int($delayOrDatetime) && $period == null) {
            if ($delayOrDatetime < 0) {
                throw new \Exception("Negative delay.");
            } else {
                $this->sched($task, floor(microtime(true) * 1000) + $delayOrDatetime, 0);
            }
        } elseif ($delayOrDatetime instanceof \DateTime && $period == null) {
            $this->sched($task, $delayOrDatetime->getTimestamp() * 1000, 0);
        } elseif (is_int($delayOrDatetime) && is_int($period)) {
            if ($delayOrDatetime < 0) {
                throw new \Exception("Negative delay.");
            }
            if ($period <= 0) {
                throw new \Exception("Non-positive period.");
            }
            $this->sched($task, floor(microtime(true) * 1000) + $delayOrDatetime, -$period);
        } elseif ($delayOrDatetime instanceof \DateTime && is_int($period)) {
            if ($period <= 0) {
                throw new \Exception("Non-positive period.");
            }
            $this->sched($task, $delayOrDatetime->getTimestamp() * 1000, -$period);
        }
    }

    /**
     * Schedules the specified task for repeated <i>fixed-rate execution</i>,
     * beginning after the specified delay.  Subsequent executions take place
     * at approximately regular intervals, separated by the specified period.
     *
     * <p>In fixed-rate execution, each execution is scheduled relative to the
     * scheduled execution time of the initial execution.  If an execution is
     * delayed for any reason (such as garbage collection or other background
     * activity), two or more executions will occur in rapid succession to
     * "catch up."  In the long run, the frequency of execution will be
     * exactly the reciprocal of the specified period (assuming the system
     * clock underlying <tt>Object.wait(long)</tt> is accurate).
     *
     * <p>Fixed-rate execution is appropriate for recurring activities that
     * are sensitive to <i>absolute</i> time, such as ringing a chime every
     * hour on the hour, or running scheduled maintenance every day at a
     * particular time.  It is also appropriate for recurring activities
     * where the total time to perform a fixed number of executions is
     * important, such as a countdown timer that ticks once every second for
     * ten seconds.  Finally, fixed-rate execution is appropriate for
     * scheduling multiple repeating timer tasks that must remain synchronized
     * with respect to one another.
     *
     * @param task   task to be scheduled.
     * @param delay  delay in milliseconds before task is to be executed.
     * @param period time in milliseconds between successive task executions.
     * @throws IllegalArgumentException if {@code delay < 0}, or
     *         {@code delay + System.currentTimeMillis() < 0}, or
     *         {@code period <= 0}
     * @throws IllegalStateException if task was already scheduled or
     *         cancelled, timer was cancelled, or timer thread terminated.
     * @throws NullPointerException if {@code task} is null
     */
    public function scheduleAtFixedRate(TimerTask $task, $delayOrTime, int $period): void
    {
        if (is_int($delayOrTime)) {
            if ($delayOrTime < 0) {
                throw new \Exception("Negative delay.");
            }
            if ($period <= 0) {
                throw new \Exception("Non-positive period.");
            }
            $this->sched($task, floor(microtime(true) * 1000) + $delayOrTime, $period);
        } elseif ($delayOrTime instanceof \DateTime) {
            if ($period <= 0) {
                throw new \Exception("Non-positive period.");
            }
            $this->sched($task, $delayOrDatetime->getTimestamp() * 1000, $period);
        }
    }

    /**
     * Schedule the specified timer task for execution at the specified
     * time with the specified period, in milliseconds.  If period is
     * positive, the task is scheduled for repeated execution; if period is
     * zero, the task is scheduled for one-time execution. Time is specified
     * in Date.getTime() format.  This method checks timer state, task state,
     * and initial execution time, but not period.
     *
     * @throws IllegalArgumentException if <tt>time</tt> is negative.
     * @throws IllegalStateException if task was already scheduled or
     *         cancelled, timer was cancelled, or timer thread terminated.
     * @throws NullPointerException if {@code task} is null
     */
    private function sched(TimerTask $task, int $time, int $period): void
    {
        if ($time < 0) {
            throw new \Exception("Illegal execution time.");
        }

        // Constrain value of period sufficiently to prevent numeric
        // overflow while still being effectively infinitely large.
        if (abs($period) > (PHP_INT_MAX >> 1)) {
            $period >>= 1;
        }

        $this->queue->synchronized(function ($scope, $task, $period) {
            if (!$scope->thread->newTasksMayBeScheduled) {
                throw new \Exception("Timer already cancelled.");
            }

            $task->lock->synchronized(function ($task, $period) {
                if ($task->state != TimerTask::VIRGIN) {
                    throw new \Exception("Task already scheduled or cancelled");
                }
                $task->nextExecutionTime = $time;
                $task->period = $period;
                $task->state = TimerTask::SCHEDULED;
            }, $task, $period);

            $scope->queue->add($task);
            if ($scope->queue->getMin() == $task) {
                $scope->queue->notify();
            }
        }, $this, $task, $period);
    }

    /**
     * Terminates this timer, discarding any currently scheduled tasks.
     * Does not interfere with a currently executing task (if it exists).
     * Once a timer has been terminated, its execution thread terminates
     * gracefully, and no more tasks may be scheduled on it.
     *
     * <p>Note that calling this method from within the run method of a
     * timer task that was invoked by this timer absolutely guarantees that
     * the ongoing task execution is the last task execution that will ever
     * be performed by this timer.
     *
     * <p>This method may be called repeatedly; the second and subsequent
     * calls have no effect.
     */
    public function cancel(): void
    {
        $this->queue->synchronized(function ($scope) {
            $scope->thread->newTasksMayBeScheduled = false;
            $scope->queue->clear();
            $scope->queue->notify();  // In case queue was already empty.
        }, $this);
    }

    /**
     * Removes all cancelled tasks from this timer's task queue.  <i>Calling
     * this method has no effect on the behavior of the timer</i>, but
     * eliminates the references to the cancelled tasks from the queue.
     * If there are no external references to these tasks, they become
     * eligible for garbage collection.
     *
     * <p>Most programs will have no need to call this method.
     * It is designed for use by the rare application that cancels a large
     * number of tasks.  Calling this method trades time for space: the
     * runtime of the method may be proportional to n + c log n, where n
     * is the number of tasks in the queue and c is the number of cancelled
     * tasks.
     *
     * <p>Note that it is permissible to call this method from within a
     * a task scheduled on this timer.
     *
     * @return the number of tasks removed from the queue.
     * @since 1.5
     */
    public function purge(): int
    {
        $result = 0;

        $this->queue->synchronized(function ($scope, $result) {
            for ($i = $scope->queue->size(); $i > 0; $i -= 1) {
                if ($scope->queue->get($i)->state == TimerTask::CANCELLED) {
                    $scope->queue->quickRemove($i);
                    $result += 1;
                }
            }

            if ($result != 0) {
                $scope->queue->heapify();
            }
        }, $this, $result);

        return $result;
    }
}
