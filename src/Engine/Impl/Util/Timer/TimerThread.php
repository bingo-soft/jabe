<?php

namespace Jabe\Engine\Impl\Util\Timer;

class TimerThread extends \Thread
{
    /**
     * This flag is set to false by the reaper to inform us that there
     * are no more live references to our Timer object.  Once this flag
     * is true and there are no more tasks in our queue, there is no
     * work left for us to do, so we terminate gracefully.  Note that
     * this field is protected by queue's monitor!
     */
    public $newTasksMayBeScheduled = true;

    /**
     * Our Timer's queue.  We store this reference in preference to
     * a reference to the Timer so the reference graph remains acyclic.
     * Otherwise, the Timer would never be garbage-collected and this
     * thread would never go away.
     */
    private $queue;

    public function __construct(TaskQueue $queue)
    {
        $this->queue = $queue;
    }

    public function run(): void
    {
        try {
            $this->mainLoop();
        } finally {
            // Someone killed this Thread, behave as if Timer cancelled
            $this->queue->synchronized(function ($scope) {
                $scope->newTasksMayBeScheduled = false;
                $scope->queue->clear();  // Eliminate obsolete references
            }, $this);
        }
    }

    /**
     * The main timer loop.  (See class comment.)
     */
    private function mainLoop(): void
    {
        while (true) {
            try {
                $task = null;
                $taskFired = false;
                $this->queue->synchronized(function ($scope, $taskFired, $task) {
                    // Wait for queue to become non-empty
                    while ($scope->queue->isEmpty() && $scope->newTasksMayBeScheduled) {
                        $scope->queue->wait();
                    }
                    if ($scope->queue->isEmpty()) {
                        return; // Queue is empty and will forever remain; die
                    }

                    // Queue nonempty; look at first evt and do the right thing
                    $currentTime = null;
                    $executionTime = null;
                    $task = $scope->queue->getMin();
                    $task->lock->synchronized(function ($scope, $task, $currentTime, $executionTime, $taskFired) {
                        if ($task->state == TimerTask::CANCELLED) {
                            $scope->queue->removeMin();
                            $scope->mainLoop();// No action required, poll queue again
                        }
                        $currentTime = floor(microtime(true) * 1000);
                        $executionTime = $task->nextExecutionTime;
                        if ($taskFired = ($executionTime <= $currentTime)) {
                            if ($task->period == 0) { // Non-repeating, remove
                                $scope->queue->removeMin();
                                $task->state = TimerTask::EXECUTED;
                            } else { // Repeating task, reschedule
                                $scope->queue->rescheduleMin(
                                    $task->period < 0 ? $currentTime - $task->period
                                                : $executionTime + $task->period
                                );
                            }
                        }
                    }, $scope, $task, $currentTime, $executionTime, $taskFired);
                    if (!$taskFired) {// Task hasn't yet fired; wait
                        $scope->queue->wait(($executionTime - $currentTime) / 1000); //wait(time) where time is in microseconds
                    }
                }, $this, $taskFired, $task);
                if ($taskFired) {// Task fired; run it, holding no locks
                    $task->run();
                }
            } catch (\Exception $e) {
            }
        }
    }
}
