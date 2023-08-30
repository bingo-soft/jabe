<?php

namespace Jabe\Impl\Pvm\Delegate;

interface SignallableActivityBehaviorInterface extends ActivityBehaviorInterface
{
    public function signal(/*ActivityExecutionInterface*/$execution, ?string $signalEvent = null, $signalData = null, array $processVariables = []): void;
}
