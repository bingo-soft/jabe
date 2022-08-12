<?php

namespace Jabe\Impl\Context;

use Jabe\Impl\Persistence\Entity\ExecutionEntity;

class BpmnExecutionContext extends ExecutionContext
{
    public function __construct(ExecutionEntity $execution)
    {
        parent::__construct($execution);
    }
}
