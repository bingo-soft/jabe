<?php

namespace Jabe\Engine\Impl\Pvm;

interface PvmTransitionInterface extends PvmProcessElementInterface
{
    public function getSource(): PvmActivityInterface;

    public function getDestination(): PvmActivityInterface;
}
