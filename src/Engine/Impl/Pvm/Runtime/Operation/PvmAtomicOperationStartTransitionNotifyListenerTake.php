<?php

namespace BpmPlatform\Engine\Impl\Pvm\Runtime\Operation;

class PvmAtomicOperationStartTransitionNotifyListenerTake extends AbstractPvmAtomicOperationTransitionNotifyListenerTake
{
    public function getCanonicalName(): string
    {
        return "start-transition-notify-listener-take";
    }
}
