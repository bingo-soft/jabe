<?php

namespace Jabe\Impl\Tree;

use Jabe\Impl\Pvm\Runtime\{
    LegacyBehavior,
    PvmExecutionImpl
};

class LeafActivityInstanceExecutionCollector implements TreeVisitorInterface
{
    protected $leaves = [];

    public function visit(/*PvmExecutionImpl */$obj): void
    {
        if (empty($obj->getNonEventScopeExecutions()) || ($obj->getActivity() !== null && !LegacyBehavior::hasInvalidIntermediaryActivityId($obj))) {
            $this->leaves[] = $obj;
        }
    }

    public function getLeaves(): array
    {
        return $this->leaves;
    }

    public function removeLeaf(/*PvmExecutionImpl */$obj): void
    {
        foreach ($this->leaves as $key => $leaf) {
            if ($leaf == $obj) {
                unset($this->leaves[$key]);
            }
        }
    }
}
