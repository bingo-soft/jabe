<?php

namespace Jabe\Engine\Impl\Util\Concurrent;

class Worker extends \Swoole\Lock implements RunnableInterface
{
    /** Initial task to run.  Possibly null. */
    public $firstTask;
    /** Task counter */
    public $completedTasks = 0;

    /**
     * Creates with given first task.
     * @param firstTask the first task (null if none)
     */
    public function __construct(?RunnableInterface $firstTask, ExecutorServiceInterface $executor)
    {
        parent::__construct(SWOOLE_MUTEX);
        $this->firstTask = $firstTask;
        $this->executor = $executor;
        $scope = $this;
        $this->process = new InterruptibleProcess(function ($process) use ($scope) {
            //Создавать контекст, если еще не существует
            $scope->run();
        }, false);
        $this->process->useQueue(800, 2);
    }

    public function start(): void
    {
        $this->process->start();
    }

    /** Delegates main run loop to outer runWorker  */
    public function run(): void
    {
        $this->executor->runWorker($this);
    }
}
