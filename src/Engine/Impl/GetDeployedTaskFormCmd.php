<?php

namespace Jabe\Engine\Impl;

use Jabe\Engine\BadUserRequestException;
use Jabe\Engine\Form\FormDataInterface;
use Jabe\Engine\Impl\Cmd\{
    AbstractGetDeployedFormCmd,
    GetTaskFormCmd
};
use Jabe\Engine\Impl\Persistence\Entity\TaskEntity;
use Jabe\Engine\Impl\Util\EnsureUtil;

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
