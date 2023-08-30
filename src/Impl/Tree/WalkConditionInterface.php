<?php

namespace Jabe\Impl\Tree;

interface WalkConditionInterface
{
    public function isFulfilled($element = null): bool;
}
