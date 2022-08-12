<?php

namespace Jabe\Impl;

use Jabe\Form\FormDataInterface;
use Jabe\Impl\Cmd\{
    AbstractGetDeployedFormCmd,
    GetTaskFormCmd
};
use Jabe\Impl\Util\EnsureUtil;

class GetDeployedTaskFormCmd extends AbstractGetDeployedFormCmd
{
    protected $taskId;

    public function __construct(string $taskId)
    {
        EnsureUtil::ensureNotNull("Task id cannot be null", "taskId", $taskId);
        $this->taskId = $taskId;
    }

    protected function getFormData(): FormDataInterface
    {
        $commandContext = $this->commandContext;
        $taskId = $this->taskId;
        return $this->commandContext->runWithoutAuthorization(function () use ($commandContext, $taskId) {
            $cmd = new GetTaskFormCmd($taskId);
            return $cmd->execute($commandContext);
        });
    }

    protected function checkAuthorization(): void
    {
        $taskEntity = $this->commandContext->getTaskManager()->findTaskById($this->taskId);
        foreach ($this->commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkReadTask($taskEntity);
        }
    }
}
