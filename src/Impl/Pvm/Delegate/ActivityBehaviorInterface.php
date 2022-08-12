<?php

namespace Jabe\Impl\Pvm\Delegate;

use Jabe\Impl\Core\Delegate\CoreActivityBehaviorInterface;

interface ActivityBehaviorInterface extends CoreActivityBehaviorInterface
{
    public function execute(ActivityExecutionInterface $execution): void;
}
