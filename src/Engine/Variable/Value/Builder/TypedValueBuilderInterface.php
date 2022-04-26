<?php

namespace Jabe\Engine\Variable\Value\Builder;

use Jabe\Engine\Variable\Value\TypedValueInterface;

interface TypedValueBuilderInterface
{
    public function create(): TypedValueInterface;

    public function setTransient(bool $isTransient): TypedValueBuilderInterface;
}
