<?php

namespace BpmPlatform\Engine\Impl\Core\Delegate;

use BpmPlatform\Engine\Delegate\BaseDelegateExecutionInterface;

interface CoreActivityBehaviorInterface
{
    public function execute(BaseDelegateExecutionInterface $execution): void;
}
