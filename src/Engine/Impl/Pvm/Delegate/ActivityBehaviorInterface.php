<?php

namespace BpmPlatform\Engine\Impl\Pvm\Delegate;

use BpmPlatform\Engine\Impl\Core\Delegate\CoreActivityBehaviorInterface;

interface ActivityBehaviorInterface extends CoreActivityBehaviorInterface
{
    public function execute(ActivityExecutionInterface $execution): void;
}
