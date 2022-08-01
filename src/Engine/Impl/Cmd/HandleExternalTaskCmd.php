<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\BadUserRequestException;
use Jabe\Engine\Impl\Interceptor\CommandContext;
use Jabe\Engine\Impl\Persistence\Entity\ExternalTaskEntity;
use Jabe\Engine\Impl\Util\EnsureUtil;

abstract class HandleExternalTaskCmd extends ExternalTaskCmd
{
    /**
     * The reported worker id.
     */
    protected $workerId;

    public function __construct(string $externalTaskId, string $workerId)
    {
        parent::__construct($externalTaskId);
        $this->workerId = $workerId;
    }

    public function execute(CommandContext $commandContext)
    {
        $this->validateInput();

        $externalTask = $commandContext->getExternalTaskManager()->findExternalTaskById($this->externalTaskId);
        EnsureUtil::ensureNotNull("Cannot find external task with id " . $this->externalTaskId, "externalTask", $externalTask);

        if ($this->validateWorkerViolation($externalTask)) {
            throw new BadUserRequestException($this->getErrorMessageOnWrongWorkerAccess() . "'. It is locked by worker '" . $externalTask->getWorkerId() . "'.");
        }

        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkUpdateProcessInstanceById($externalTask->getProcessInstanceId());
        }

        $this->executeTask($externalTask);

        return null;
    }

    /**
     * Returns the error message. Which is used to create an specific message
     *  for the BadUserRequestException if an worker has no rights to execute commands of the external task.
     *
     * @return string the specific error message
     */
    abstract public function getErrorMessageOnWrongWorkerAccess(): string;

    /**
     * Validates the current input of the command.
     */
    protected function validateInput(): void
    {
        EnsureUtil::ensureNotNull("workerId", "workerId", $this->workerId);
    }

    /**
     * Validates the caller's workerId against the workerId of the external task.
     */
    protected function validateWorkerViolation(ExternalTaskEntity $externalTask): bool
    {
        return $this->workerId != $externalTask->getWorkerId();
    }
}
