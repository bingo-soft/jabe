<?php

namespace Jabe\Impl\Tree;

use Jabe\Impl\Pvm\Runtime\PvmExecutionImpl;

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
