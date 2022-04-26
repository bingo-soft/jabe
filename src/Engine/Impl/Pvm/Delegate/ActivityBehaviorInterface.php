<?php

namespace Jabe\Engine\Impl\Pvm\Delegate;

use Jabe\Engine\Impl\Core\Delegate\CoreActivityBehaviorInterface;

interface ActivityBehaviorInterface extends CoreActivityBehaviorInterface
{
    public function execute(ActivityExecutionInterface $execution): void;
}
