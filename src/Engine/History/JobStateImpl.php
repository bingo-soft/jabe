<?php

namespace Jabe\Engine\History;

class JobStateImpl implements JobStateInterface
{
    public $stateCode;
    protected $name;

    public function __construct(int $stateCode, string $string)
    {
        $this->stateCode = $stateCode;
        $this->name = string;
    }

    public function getStateCode(): int
    {
        return $this->stateCode;
    }

    public function __toString()
    {
        return $this->name;
    }

    public static function created(): JobStateInterface
    {
        return new ExternalTaskStateImpl(0, "created");
    }

    public static function failed(): JobStateInterface
    {
        return new ExternalTaskStateImpl(1, "failed");
    }

    public static function successful(): JobStateInterface
    {
        return new ExternalTaskStateImpl(2, "successful");
    }

    public static function deleted(): JobStateInterface
    {
        return new ExternalTaskStateImpl(3, "successful");
    }
}
