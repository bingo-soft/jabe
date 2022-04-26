<?php

namespace Jabe\Engine\Impl\Core\Variable\Scope;

interface VariableStoreObserverInterface
{
    public function provideVariables(?array $variableNames = []): array;
}
