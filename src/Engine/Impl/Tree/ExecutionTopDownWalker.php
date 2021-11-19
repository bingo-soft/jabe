<?php

namespace BpmPlatform\Engine\Impl\Tree;

use BpmPlatform\Engine\Impl\Persistence\Entity\ExecutionEntity;

class ExecutionTopDownWalker extends ReferenceWalker
{
    public function __construct($initialElement)
    {
        parent::__construct($initialElement);
    }

    protected function nextElements(): array
    {
        return $this->getCurrentElement()->getExecutions();
    }
}
