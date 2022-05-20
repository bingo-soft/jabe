<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\BadUserRequestException;
use Jabe\Engine\Impl\UpdateProcessInstancesSuspensionStateBuilderImpl;
use Jabe\Engine\Impl\Interceptor\{
    CommandExecutorInterface,
    CommandContext
};
use Jabe\Engine\Impl\Runtime\UpdateProcessInstanceSuspensionStateBuilderImpl;
use Jabe\Engine\Impl\Util\EnsureUtil;

class UpdateProcessInstancesSuspendStateCmd extends AbstractUpdateProcessInstancesSuspendStateCmd
{
    public function __construct(
        CommandExecutorInterface $commandExecutor,
        UpdateProcessInstancesSuspensionStateBuilderImpl $builder,
        bool $suspendstate
    ) {
        parent::__construct($commandExecutor, $builder, $suspendstate);
    }

    public function execute(CommandContext $commandContext)
    {
        $processInstanceIds = $this->collectProcessInstanceIds($commandContext)->getIds();

        EnsureUtil::ensureNotEmpty("No process instance ids given", "Process Instance ids", $processInstanceIds);
        EnsureUtil::ensureNotContainsNull("Cannot be null.", "Process Instance ids", $processInstanceIds);

        $this->writeUserOperationLog($commandContext, count($processInstanceIds), false);

        $suspensionStateBuilder = new UpdateProcessInstanceSuspensionStateBuilderImpl($this->commandExecutor);
        if ($this->suspending) {
            // suspending
            foreach ($processInstanceIds as $processInstanceId) {
                $suspensionStateBuilder->byProcessInstanceId($processInstanceId)->suspend();
            }
        } else {
            // activating
            foreach ($processInstanceIds as $processInstanceId) {
                $suspensionStateBuilder->byProcessInstanceId($processInstanceId)->activate();
            }
        }

        return null;
    }
}
