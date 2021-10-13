<?php

namespace BpmPlatform\Engine\Variable\Value\Builder;

use BpmPlatform\Engine\Variable\Value\TypedValueInterface;

interface TypedValueBuilderInterface
{
    public function create(): TypedValueInterface;

    public function setTransient(bool $isTransient): TypedValueBuilderInterface;
}
