<?php

namespace Jabe\Engine\Runtime;

use Jabe\Engine\Variable\VariableMapInterface;

interface MessageCorrelationResultWithVariablesInterface extends MessageCorrelationResultInterface
{
    public function getVariables(): VariableMapInterface;
}
