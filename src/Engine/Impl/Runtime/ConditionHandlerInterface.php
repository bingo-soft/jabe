<?php

namespace BpmPlatform\Engine\Impl\Runtime;

use BpmPlatform\Engine\Impl\Interceptor\CommandContext;

interface ConditionHandlerInterface
{
    /**
     * Evaluates conditions of process definition with a conditional start event
     *
     * @param conditionSet
     * @return all matched process definitions and respective activities containing evaluated to true conditions
     */
    public function evaluateStartCondition(CommandContext $commandContext, ConditionSet $conditionSet): array;
}
