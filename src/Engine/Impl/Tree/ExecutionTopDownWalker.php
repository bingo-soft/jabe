<?php

namespace Jabe\Engine\Impl\Tree;

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
