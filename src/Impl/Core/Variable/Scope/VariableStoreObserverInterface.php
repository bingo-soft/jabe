<?php

namespace Jabe\Impl\Core\Variable\Scope;

interface VariableStoreObserverInterface
{
    public function onAdd(/*mixed*/$variable): void;

    public function onRemove(/*mixed*/$variable): void;
}
