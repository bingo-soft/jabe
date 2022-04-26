<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\BadUserRequestException;
use Jabe\Engine\Impl\Batch\BatchElementConfiguration;
use Jabe\Engine\Impl\Interceptor\CommandContext;
use Jabe\Engine\Impl\Util\EnsureUtil;

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
