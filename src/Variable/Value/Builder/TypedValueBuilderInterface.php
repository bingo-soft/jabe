<?php

namespace Jabe\Variable\Value\Builder;

use Jabe\Variable\Value\TypedValueInterface;

interface TypedValueBuilderInterface
{
    public function create(): TypedValueInterface;

    public function setTransient(bool $isTransient): TypedValueBuilderInterface;
}
