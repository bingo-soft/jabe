<?php

namespace Jabe\Impl\Persistence\Entity;

class SuspensionStateImpl extends SuspensionState
{
    public function __construct(int $suspensionCode, string $name)
    {
        $this->stateCode = $suspensionCode;
        $this->name = $name;
    }
}
