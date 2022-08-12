<?php

namespace Jabe\Impl\Pvm\Delegate;

interface SignallableActivityBehaviorInterface extends ActivityBehaviorInterface
{
    public function signal(ActivityExecutionInterface $execution, string $signalEvent, $signalData): void;
}
