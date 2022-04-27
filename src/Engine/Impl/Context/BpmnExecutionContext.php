<?php

namespace Jabe\Engine\Impl\Context;

use Jabe\Engine\Impl\Persistence\Entity\ExecutionEntity;

class BpmnExecutionContext extends ExecutionContext
{
    public function __construct(ExecutionEntity $execution)
    {
        parent::__construct($execution);
    }
}
