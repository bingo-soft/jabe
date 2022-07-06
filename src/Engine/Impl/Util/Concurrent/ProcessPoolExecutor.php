<?php

namespace Jabe\Engine\Impl\Util\Concurrent;

class ProcessPoolExecutor implements ExecutorServiceInterface
{
    private $ctl;
    private const COUNT_BITS = ( PHP_INT_SIZE * 4 ) - 3;
    private const CAPACITY   = (1 << self::COUNT_BITS) - 1;

    // runState is stored in the high-order bits
    private const RUNNING    = -1 << self::COUNT_BITS;
    private const SHUTDOWN   =  0 << self::COUNT_BITS;
    private const STOP       =  1 << self::COUNT_BITS;
    private const TIDYING    =  2 << self::COUNT_BITS;
    private const TERMINATED =  3 << self::COUNT_BITS;
    private const ONLY_ONE = true;

    private $workQueue;
    private $queueSize;

    private $mainLock;

    /**
     * Set containing all worker processes in pool
     */
    private $workers = [];

    /**
     * Tracks largest attained pool size. Accessed only under
     * mainLock.
     */
    private $largestPoolSize;

    /*
     * All user control parameters are declared as volatiles so that
     * ongoing actions are based on freshest values, but without need
     * for locking, since no internal invariants depend on them
     * changing synchronously with respect to other actions.
     */

    /**
     * Handler called when saturated or shutdown in execute.
     */
    private $handler;

    /**
     * Timeout in nanoseconds for idle processes
     */
    private $keepAliveTime;

    /**
     * Maximum pool size.
     */
    private $poolSize;

    // Packing and unpacking ctl
    private static function runStateOf(int $c): int
    {
        return $c & ~self::CAPACITY;
    }

    private static function workerCountOf(int $c): int
    {
        return $c & self::CAPACITY;
    }

    private static function ctlOf(int $rs, int $wc)
    {
        return $rs | $wc;
    }

    /*
     * Bit field accessors that don't require unpacking ctl.
     * These depend on the bit layout and on workerCount being never negative.
     */
    private static function runStateLessThan(int $c, int $s): bool
    {
        return $c < $s;
    }

    private static function runStateAtLeast(int $c, int $s): bool
    {
        return $c >= $s;
    }

    private static function isRunning(int $c): bool
    {
        return $c < self::SHUTDOWN;
    }

    /**
     * Attempt to CAS-increment the workerCount field of ctl.
     */
    private function compareAndIncrementWorkerCount(int $expect): bool
    {
        return $this->ctl->cmpset($expect, $expect + 1);
    }

    /**
     * Attempt to CAS-decrement the workerCount field of ctl.
     */
    private function compareAndDecrementWorkerCount(int $expect): bool
    {
        return $this->ctl->cmpset($expect, $expect - 1);
    }

    /**
     * Decrements the workerCount field of ctl.
     */
    private function decrementWorkerCount(): void
    {
        do {
        } while (!$this->compareAndDecrementWorkerCount($this->ctl->get()));
    }

    /**
     * Transitions runState to given target, or leaves it alone if
     * already at least the given target.
     *
     * @param targetState the desired state, either SHUTDOWN or STOP
     *        (but not TIDYING or TERMINATED -- use tryTerminate for that)
     */
    private function advanceRunState(int $targetState): void
    {
        for (;;) {
            $c = $this->ctl->get();
            if (
                self::runStateAtLeast($c, $targetState) ||
                $this->ctl->cmpset($c, self::ctlOf($targetState, self::workerCountOf($c)))
            ) {
                break;
            }
        }
    }

    /**
     * Attempt to CAS-increment the queue size.
     */
    private function compareAndIncrementQueueSize(int $expect): bool
    {
        return $this->queueSize->cmpset($expect, $expect + 1);
    }

    /**
     * Attempt to CAS-decrement the queue size.
     */
    private function compareAndDecrementQueueSize(int $expect): bool
    {
        if ($expect > 0) {
            return $this->queueSize->cmpset($expect, $expect - 1);
        }
        return false;
    }

    /**
     * Transitions to TERMINATED state if either (SHUTDOWN and pool
     * and queue empty) or (STOP and pool empty).  If otherwise
     * eligible to terminate but workerCount is nonzero, interrupts an
     * idle worker to ensure that shutdown signals propagate. This
     * method must be called following any action that might make
     * termination possible -- reducing worker count or removing tasks
     * from the queue during shutdown. The method is non-private to
     * allow access from ScheduledThreadPoolExecutor.
     */
    public function tryTerminate(): void
    {
        for (;;) {
            $c = $this->ctl->get();
            if (
                self::isRunning($c) ||
                self::runStateAtLeast($c, self::TIDYING) ||
                (self::runStateOf($c) == self::SHUTDOWN && !$this->workQueue->isEmpty())
            ) {
                return;
            }
            if (self::workerCountOf($c) != 0) { // Eligible to terminate
                $this->interruptIdleWorkers(self::ONLY_ONE);
                return;
            }

            $mainLock = $this->mainLock;
            $mainLock->trylock();
            try {
                if ($this->ctl->cmpset($c, self::ctlOf(self::TIDYING, 0))) {
                    try {
                        $this->terminated();
                    } finally {
                        $this->ctl->set(self::ctlOf(self::TERMINATED, 0));
                        //termination.signalAll();
                    }
                    return;
                }
            } finally {
                $mainLock->unlock();
            }
            // else retry on failed CAS
        }
    }

    private function checkShutdownAccess(): void
    {
    }

    /**
     * Interrupts all processes, even if active.
     */
    private function interruptWorkers(): void
    {
        $mainLock = $this->mainLock;
        $mainLock->trylock();
        try {
            foreach ($this->workers as $w) {
                try {
                    $w->interrupt();
                } catch (\Exception $ignore) {
                    //
                }
            }
        } finally {
            $mainLock->unlock();
        }
    }

    /**
     * Interrupts processes that might be waiting for tasks
     */
    private function interruptIdleWorkers(bool $onlyOne = false): void
    {
        $mainLock = $this->mainLock;
        $mainLock->trylock();
        try {
            foreach ($this->workers as $w) {
                $t = $w->process;
                if (!$t->isInterrupted() && $w->trylock()) {
                    try {
                        $t->interrupt();
                    } finally {
                        $w->unlock();
                    }
                }
                if ($onlyOne) {
                    break;
                }
            }
        } finally {
            $mainLock->unlock();
        }
    }

    /**
     * Invokes the rejected execution handler for the given command.
     * Package-protected for use by ScheduledThreadPoolExecutor.
     */
    private function reject(RunnableInterface $command): void
    {
    }

    private function drainQueue(): array
    {
        $q = $this->workQueue;
        $taskList = [];
        $q->drainTo($taskList);
        if ($this->queueSize->get() != 0) {
            $runnable = [];
            foreach ($q->toArray($runnable) as $r) {
                if ($q->remove($r)) {
                    $this->compareAndDecrementQueueSize($q->size() + 1);
                    $taskList[] = $r;
                }
            }
        }
        return $taskList;
    }

    private function addWorker(?RunnableInterface $firstTask)
    {
        for (;;) {
            $c = $this->ctl->get();
            $rs = self::runStateOf($c);

            // Check if queue empty only if necessary.
            if (
                $rs >= self::SHUTDOWN &&
                ! (
                    $rs == self::SHUTDOWN &&
                    $firstTask === null &&
                    $this->queueSize->get() != 0
                )
            ) {
                return false;
            }

            for (;;) {
                $wc = self::workerCountOf($c);
                if (
                    $wc >= self::CAPACITY ||
                    $wc >= $this->poolSize
                ) {
                    return false;
                }
                if ($this->compareAndIncrementWorkerCount($c)) {
                    break 2;
                }
                $c = $this->ctl->get();  // Re-read ctl
                if (self::runStateOf($c) != $rs) {
                    continue 2;
                }
                // else CAS failed due to workerCount change; retry inner loop
            }
        }

        $w = new Worker($firstTask, $this);
        $t = $w->process;

        $this->mainLock->trylock();
        try {
            // Recheck while holding lock.
            // Back out on ThreadFactory failure or if
            // shut down before lock acquired.
            $c = $this->ctl->get();
            $rs = self::runStateOf($c);

            if (
                $t === null ||
                ($rs >= self::SHUTDOWN &&
                 !($rs == self::SHUTDOWN && $firstTask === null))
            ) {
                $this->decrementWorkerCount();
                $this->tryTerminate();
                return false;
            }

            $this->workers[] = $w;

            $s = count($this->workers);
            if ($s > $this->largestPoolSize) {
                $this->largestPoolSize = $s;
            }
        } finally {
            $this->mainLock->unlock();
        }
        $t->start();
        return true;
    }

    private function processWorkerExit(Worker $w, bool $completedAbruptly): void
    {
        if ($completedAbruptly) {// If abrupt, then workerCount wasn't adjusted
            $this->decrementWorkerCount();
        }

        $this->mainLock->trylock();
        try {
            foreach ($this->workers as $key => $val) {
                if ($val == $w) {
                    unset($this->workers[$key]);
                    break;
                }
            }
        } finally {
            $this->mainLock->unlock();
        }

        $this->tryTerminate();

        $c = $this->ctl->get();
        if (self::runStateLessThan($c, self::STOP)) {
            if (!$completedAbruptly) {
                $min = $this->poolSize;
                if ($min == 0 && $this->queueSize->get() != 0) {
                    $min = 1;
                }
                if (self::workerCountOf($c) >= $min) {
                    return; // replacement not needed
                }
            }
            $this->addWorker(null);
        }
    }

    private function getTask(InterruptibleProcess $process): ?RunnableInterface
    {
        $timedOut = false;
        for (;;) {
            $c = $this->ctl->get();
            $rs = self::runStateOf($c);

            // Check if queue empty only if necessary.
            if ($rs >= self::SHUTDOWN && ($rs >= self::STOP || $this->queueSize->get() == 0)) { //$this->workQueue->isEmpty() - does not work, because workQueue is not shared
                $this->decrementWorkerCount();
                return null;
            }

            $timed = false;

            for (;;) {
                $wc = self::workerCountOf($c);
                $timed = $wc > $this->poolSize;

                if ($wc <= $this->poolSize && !($timedOut && $timed)) {
                    break;
                }
                if ($this->compareAndDecrementWorkerCount($c)) {
                    return null;
                }
                $c = $this->ctl->get();  // Re-read ctl
                if (self::runStateOf($c) != $rs) {
                    continue 2;
                }
                // else CAS failed due to workerCount change; retry inner loop
            }
            try {
                $r = $timed ?
                    $this->workQueue->poll($this->keepAliveTime, TimeUnit::NANOSECONDS, $process) :
                    $this->workQueue->take($process);
                if ($r !== null) {
                    return unserialize($r);
                }
                $timedOut = true;
            } catch (\Exception $retry) {
                $timedOut = false;
            }
        }
    }

    public function runWorker(Worker $w): void
    {
        $firstTask = $w->firstTask;
        $queuedTask = null;
        $w->firstTask = null;
        $completedAbruptly = true;
        try {
            while ($firstTask !== null || ($queuedTask = $this->getTask($w->process)) !== null) {
                $w->trylock();
                try {
                    $thrown = null;
                    try {
                        if ($firstTask !== null) {
                            $firstTask->run();
                        } elseif ($queuedTask !== null) {
                            //take care
                            $this->compareAndDecrementQueueSize($this->queueSize->get());
                            $queuedTask->run();
                        }
                    } catch (\Exception $x) {
                        $thrown = $x;
                        throw $x;
                    }
                } finally {
                    $firstTask = null;
                    $queuedTask = null;
                    $w->firstTask = null;
                    $w->unlock();
                }
            }
            $completedAbruptly = false;
        } finally {
            $this->processWorkerExit($w, $completedAbruptly);
        }
    }

    public function __construct(
        int $poolSize,
        int $keepAliveTime,
        string $unit,
        BlockingQueueInterface $workQueue
    ) {
        $this->ctl = new \Swoole\Atomic\Long(self::ctlOf(self::RUNNING, 0));
        $this->mainLock = new \Swoole\Lock(SWOOLE_MUTEX);
        $this->queueSize = new \Swoole\Atomic\Long(0);

        if (
            $poolSize <= 0 || $keepAliveTime < 0
        ) {
            throw new \Exception("Illegal argument");
        }
        $this->poolSize = $poolSize;
        $this->workQueue = $workQueue;
        $this->keepAliveTime = TimeUnit::toNanos($keepAliveTime, $unit);
    }

    public function execute(RunnableInterface $command): void
    {
        $c = $this->ctl->get();
        if (self::workerCountOf($c) < $this->poolSize) {
            if ($this->addWorker($command)) {
                return;
            }
            $c = $this->ctl->get();
        }
        if (self::isRunning($c)) {
            $process = $this->workers[rand(0, count($this->workers) - 1)]->process;
            if ($this->workQueue->offer($command, $process)) {
                $this->compareAndIncrementQueueSize($this->workQueue->size() - 1);
                $recheck = $this->ctl->get();
                if (!self::isRunning($recheck) && $this->remove($command)) {
                    $this->reject($command);
                } elseif (self::workerCountOf($recheck) == 0) {
                    $this->addWorker(null);
                }
            }
        } elseif (!$this->addWorker($command)) {
            $this->reject($command);
        }
    }

    public function remove(RunnableInterface $task): bool
    {
        $removed = $this->workQueue->remove($task);
        if ($removed) {
            $this->compareAndDecrementQueueSize($this->workQueue->size() + 1);
        }
        $this->tryTerminate(); // In case SHUTDOWN and now empty
        return $removed;
    }

    /**
     * Performs any further cleanup following run state transition on
     * invocation of shutdown.  A no-op here, but used by
     * ScheduledThreadPoolExecutor to cancel delayed tasks.
     */
    public function onShutdown(): void
    {
    }

    /**
     * State check needed by ScheduledThreadPoolExecutor to
     * enable running tasks during shutdown.
     *
     * @param shutdownOK true if should return true if SHUTDOWN
     */
    public function isRunningOrShutdown(bool $shutdownOK): bool
    {
        $rs = self::runStateOf($this->ctl->get());
        return $rs == self::RUNNING || ($rs == self::SHUTDOWN && $shutdownOK);
    }

    public function shutdown(): void
    {
        $this->mainLock->trylock();
        try {
            $this->checkShutdownAccess();
            $this->advanceRunState(self::SHUTDOWN);
            $this->interruptIdleWorkers();
            $this->onShutdown();
        } finally {
            $this->mainLock->unlock();
        }
        $this->tryTerminate();
    }

    public function shutdownNow(): array
    {
        $tasks = [];
        $this->mainLock->trylock();
        try {
            $this->checkShutdownAccess();
            $this->advanceRunState(self::STOP);
            $this->interruptWorkers();
            $tasks = $this->drainQueue();
        } finally {
            $this->mainLock->unlock();
        }
        $this->tryTerminate();
        return $tasks;
    }

    public function isShutdown(): bool
    {
        return !self::isRunning($this->ctl->get());
    }

    public function isTerminating(): bool
    {
        $c = $this->ctl->get();
        return !self::isRunning($c) && self::runStateLessThan($c, self::TERMINATED);
    }

    public function isTerminated(): bool
    {
        return self::runStateAtLeast($this->ctl->get(), self::TERMINATED);
    }

    public function awaitTermination(int $timeout, string $unit)
    {
        $nanos = TimeUnit::toNanos($timeout, $unit);
        $this->mainLock->trylock();
        try {
            for (;;) {
                if (self::runStateAtLeast($this->ctl->get(), self::TERMINATED)) {
                    return true;
                }
                if ($nanos <= 0) {
                    return false;
                }
                time_nanosleep(0, $nanos);
                $nanos = -1;
            }
        } finally {
            $this->mainLock->unlock();
        }
    }

    protected function terminated(): void
    {
    }
}
