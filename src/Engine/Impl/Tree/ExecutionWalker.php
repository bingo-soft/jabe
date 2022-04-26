<?php

namespace Jabe\Engine\Impl\Tree;

use Jabe\Engine\Impl\Pvm\Runtime\PvmExecutionImpl;

class ExecutionWalker extends SingleReferenceWalker
{
    public function __construct(PvmExecutionImpl $initialElement)
    {
        parent::__construct($initialElement);
    }

    protected function nextElement()
    {
        return $this->getCurrentElement()->getParent();
    }
}
