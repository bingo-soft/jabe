<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Engine\Impl\Persistence\Entity\TaskEntity;

class CreateTaskCmd implements CommandInterface
{
    protected $taskId;

    public function __construct(string $taskId)
    {
        $this->taskId = $taskId;
    }

    public function execute(CommandContext $commandContext)
    {
        $this->checkCreateTask($commandContext);

        return new TaskEntity($taskId);
    }

    protected function checkCreateTask(CommandContext $commandContext): void
    {
        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkCreateTask();
        }
    }
}
