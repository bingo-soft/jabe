<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Persistence\Entity\{
    ExternalTaskEntity,
    PropertyChange
};
use Jabe\Impl\Util\EnsureUtil;

abstract class ExternalTaskCmd implements CommandInterface
{
    /**
     * The corresponding external task id.
     */
    protected $externalTaskId;

    public function __construct(?string $externalTaskId)
    {
        $this->externalTaskId = $externalTaskId;
    }

    public function execute(CommandContext $commandContext)
    {
        EnsureUtil::ensureNotNull("externalTaskId", "externalTaskId", $this->externalTaskId);
        $this->validateInput();

        $externalTask = $commandContext->getExternalTaskManager()->findExternalTaskById($this->externalTaskId);
        EnsureUtil::ensureNotNull(
            "Cannot find external task with id " . $this->externalTaskId,
            "externalTask",
            $externalTask
        );

        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkUpdateProcessInstanceById($externalTask->getProcessInstanceId());
        }

        $this->writeUserOperationLog(
            $commandContext,
            $externalTask,
            $this->getUserOperationLogOperationType(),
            $this->getUserOperationLogPropertyChanges($externalTask)
        );

        $this->executeTask($externalTask);

        return null;
    }

    public function writeUserOperationLog(
        CommandContext $commandContext,
        ExternalTaskEntity $externalTask,
        ?string $operationType,
        ?array $propertyChanges = []
    ): void {
        if ($operationType !== null) {
            $commandContext->getOperationLogManager()->logExternalTaskOperation(
                $operationType,
                $externalTask,
                empty($propertyChanges) ? [PropertyChange::emptyChange()] : $propertyChanges
            );
        }
    }

    protected function getUserOperationLogOperationType(): ?string
    {
        return null;
    }

    protected function getUserOperationLogPropertyChanges(ExternalTaskEntity $externalTask): array
    {
        return [];
    }

    /**
     * Executes the specific external task commands, which belongs to the current sub class.
     *
     * @param externalTask the external task which is used for the command execution
     */
    abstract protected function executeTask(ExternalTaskEntity $externalTask);

    /**
     * Validates the current input of the command.
     */
    abstract protected function validateInput(): void;

    public function isRetryable(): bool
    {
        return false;
    }
}
