<?php

namespace Jabe\Engine\Impl\Tree;

use Jabe\Engine\Impl\Pvm\Runtime\PvmExecutionImpl;

class ScopeExecutionCollector implements TreeVisitorInterface
{
    protected $scopeExecutions = [];

    public function visit(/*PvmExecutionImpl */$obj): void
    {
        if ($obj->isScope()) {
            $this->scopeExecutions[]  = $obj;
        }
    }

    public function getScopeExecutions(): array
    {
        return $this->scopeExecutions;
    }
}
