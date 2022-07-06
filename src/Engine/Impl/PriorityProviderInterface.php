<?php

namespace Jabe\Engine\Impl;

use Jabe\Engine\Impl\Persistence\Entity\ExecutionEntity;

interface PriorityProviderInterface
{

    /**
     * @param execution may be null when the job is not created in the context of a
     *   running process instance (e.g. a timer start event)
     * @param param extra parameter to determine priority on
     * @param jobDefinitionId the job definition id if related to a job
     * @return int the determined priority
     */
    public function determinePriority(ExecutionEntity $execution, $param, string $jobDefinitionId): int;
}
