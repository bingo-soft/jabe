<?php

namespace Jabe\Impl\Tree;

use Jabe\Impl\Pvm\Process\{
    ActivityImpl,
    ScopeImpl
};

class FlowScopeWalker extends SingleReferenceWalker
{
    public function __construct(?ScopeImpl $startActivity)
    {
        parent::__construct($startActivity);
    }

    protected function nextElement()
    {
        $currentElement = $this->getCurrentElement();
        if ($currentElement !== null && is_a($currentElement, ActivityImpl::class)) {
            return $currentElement->getFlowScope();
        }
        return null;
    }
}
