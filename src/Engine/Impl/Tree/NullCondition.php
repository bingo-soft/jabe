<?php

namespace BpmPlatform\Engine\Impl\Tree;

class NullCondition implements WalkConditionInterface
{
    public function isFulfilled($element = null): bool
    {
        return $element == null;
    }

    public static function notNull(): WalkConditionInterface
    {
        return new NullCondition();
    }
}
