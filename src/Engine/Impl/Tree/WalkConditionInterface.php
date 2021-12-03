<?php

namespace BpmPlatform\Engine\Impl\Tree;

interface WalkConditionInterface
{
    public function isFulfilled($element = null): bool;
}
