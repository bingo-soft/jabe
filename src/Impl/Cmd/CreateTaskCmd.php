<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Persistence\Entity\TaskEntity;

class CreateTaskCmd implements CommandInterface
{
    protected $taskId;

    public function __construct(?string $taskId)
    {
        $this->taskId = $taskId;
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        $this->checkCreateTask($commandContext);

        return new TaskEntity($this->taskId);
    }

    protected function checkCreateTask(CommandContext $commandContext): void
    {
        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkCreateTask();
        }
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
