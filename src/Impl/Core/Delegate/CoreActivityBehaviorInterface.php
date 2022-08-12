<?php

namespace Jabe\Impl\Core\Delegate;

use Jabe\Delegate\BaseDelegateExecutionInterface;

interface CoreActivityBehaviorInterface
{
    public function execute(BaseDelegateExecutionInterface $execution): void;
}
