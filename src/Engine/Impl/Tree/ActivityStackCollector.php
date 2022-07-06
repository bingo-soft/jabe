<?php

namespace Jabe\Engine\Impl\Tree;

use Jabe\Engine\Impl\Pvm\PvmActivityInterface;
use Jabe\Engine\Impl\Pvm\Process\ScopeImpl;

class ActivityStackCollector implements TreeVisitorInterface
{
    protected $activityStack = [];

    public function visit(/*ScopeImpl */$scope): void
    {
        if ($scope !== null && is_a($scope, PvmActivity::class, true)) {
            $this->activityStack[] = $scope;
        }
    }

    public function getActivityStack(): array
    {
        return $this->activityStack;
    }
}
