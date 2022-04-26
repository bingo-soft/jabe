<?php

namespace Jabe\Engine\Impl\Pvm\Delegate;

interface SignallableActivityBehaviorInterface extends ActivityBehaviorInterface
{
    public function signal(ActivityExecutionInterface $execution, string $signalEvent, $signalData): void;
}
