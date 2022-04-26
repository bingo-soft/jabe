<?php

namespace Jabe\Engine\Impl\Tree;

use Jabe\Engine\Impl\Pvm\PvmScopeInterface;
use Jabe\Engine\Impl\Pvm\Delegate\ActivityExecutionInterface;

class ActivityExecutionTuple
{
    private $execution;
    private $scope;

    public function __construct(PvmScopeInterface $scope, ActivityExecutionInterface $execution)
    {
        $this->execution = $execution;
        $this->scope = $scope;
    }

    public function getExecution(): ?ActivityExecutionInterface
    {
        return $this->execution;
    }

    public function getScope(): PvmScopeInterface
    {
        return $this->scope;
    }
}
