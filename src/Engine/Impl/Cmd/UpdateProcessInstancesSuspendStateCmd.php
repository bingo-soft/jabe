<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\BadUserRequestException;
use BpmPlatform\Engine\Impl\UpdateProcessInstancesSuspensionStateBuilderImpl;
use BpmPlatform\Engine\Impl\Interceptor\{
    CommandExecutorInterface,
    CommandContext
};
use BpmPlatform\Engine\Impl\Runtime\UpdateProcessInstanceSuspensionStateBuilderImpl;
use BpmPlatform\Engine\Impl\Util\EnsureUtil;

class UpdateProcessInstancesSuspendStateCmd extends AbstractUpdateProcessInstancesSuspendStateCmd
{
    public function __construct(
        CommandExecutor $commandExecutor,
        UpdateProcessInstancesSuspensionStateBuilderImpl $builder,
        boolean $suspendstate
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
