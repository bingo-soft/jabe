<?php

namespace Jabe\History;

class JobStateImpl implements JobStateInterface
{
    public int $stateCode = 0;
    protected ?string $name;

    public function __construct(int $stateCode, ?string $string)
    {
        $this->stateCode = $stateCode;
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

    public static function created(): JobStateInterface
    {
        return new JobStateImpl(0, "created");
    }

    public static function failed(): JobStateInterface
    {
        return new JobStateImpl(1, "failed");
    }

    public static function successful(): JobStateInterface
    {
        return new JobStateImpl(2, "successful");
    }

    public static function deleted(): JobStateInterface
    {
        return new JobStateImpl(3, "successful");
    }
}
