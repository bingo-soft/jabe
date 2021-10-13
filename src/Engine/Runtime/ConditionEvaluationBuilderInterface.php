<?php

namespace BpmPlatform\Engine\Runtime;

interface ConditionEvaluationBuilderInterface
{
    public function processInstanceBusinessKey(string $businessKey): ConditionEvaluationBuilderInterface;

    public function processDefinitionId(string $processDefinitionId): ConditionEvaluationBuilderInterface;

    public function setVariable(string $variableName, $variableValue): ConditionEvaluationBuilderInterface;

    public function setVariables(array $variables): ConditionEvaluationBuilderInterface;

    public function tenantId(string $tenantId): ConditionEvaluationBuilderInterface;

    public function withoutTenantId(): ConditionEvaluationBuilderInterface;

    public function evaluateStartConditions(): array;
}
