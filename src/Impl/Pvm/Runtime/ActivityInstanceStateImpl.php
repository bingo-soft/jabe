<?php

namespace Jabe\Impl\Pvm\Runtime;

class ActivityInstanceStateImpl extends ActivityInstanceState
{
    public $stateCode;
    protected $name;

    public function __construct(int $suspensionCode, string $string)
    {
        $this->stateCode = $suspensionCode;
        $this->name = $string;
    }

    public function getStateCode(): int
    {
        return $this->stateCode;
    }

    public function __toString()
    {
        return $this->name;
    }
}
