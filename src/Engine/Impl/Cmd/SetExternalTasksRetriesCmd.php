<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\BadUserRequestException;
use BpmPlatform\Engine\Impl\Batch\BatchElementConfiguration;
use BpmPlatform\Engine\Impl\Interceptor\CommandContext;
use BpmPlatform\Engine\Impl\Util\EnsureUtil;

class SetExternalTasksRetriesCmd extends AbstractSetExternalTaskRetriesCmd
{
    public function __construct(UpdateExternalTaskRetriesBuilderImpl $builder)
    {
        parent::__construct($builder);
    }

    public function execute(CommandContext $commandContext)
    {
        $elementConfiguration = $this->collectExternalTaskIds($commandContext);
        $collectedIds = $elementConfiguration->getIds();
        EnsureUtil::ensureNotEmpty(BadUserRequestException::class, "externalTaskIds", $collectedIds);

        $instanceCount = count($collectedIds);
        $this->writeUserOperationLog($commandContext, $instanceCount, false);

        $retries = $this->builder->getRetries();
        foreach ($collectedIds as $externalTaskId) {
            (new SetExternalTaskRetriesCmd($externalTaskId, $retries, false))
                ->execute($commandContext);
        }

        return null;
    }
}
