<?php

namespace Jabe\Engine\Impl\Core\Delegate;

use Jabe\Engine\Delegate\BaseDelegateExecutionInterface;

interface CoreActivityBehaviorInterface
{
    public function execute(BaseDelegateExecutionInterface $execution): void;
}
