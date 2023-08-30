<?php

namespace Jabe\Runtime;

use Jabe\Variable\VariableMapInterface;

interface MessageCorrelationResultWithVariablesInterface extends MessageCorrelationResultInterface
{
    public function getVariables(): VariableMapInterface;
}
