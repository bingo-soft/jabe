<?php

namespace BpmPlatform\Engine\Runtime;

use BpmPlatform\Engine\Variable\VariableMapInterface;

interface MessageCorrelationResultWithVariablesInterface extends MessageCorrelationResultInterface
{
    public function getVariables(): VariableMap;
}
