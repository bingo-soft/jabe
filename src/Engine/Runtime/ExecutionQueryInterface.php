<?php

namespace Jabe\Engine\Runtime;

use Jabe\Engine\Query\QueryInterface;

interface ExecutionQueryInterface extends QueryInterface
{
    public function processDefinitionKey(string $processDefinitionKey): ExecutionQueryInterface;

    public function processDefinitionId(string $processDefinitionId): ExecutionQueryInterface;

    public function processInstanceId(string $processInstanceId): ExecutionQueryInterface;

    public function processInstanceBusinessKey(string $processInstanceBusinessKey): ExecutionQueryInterface;

    public function executionId(string $executionId): ExecutionQueryInterface;

    public function activityId(string $activityId): ExecutionQueryInterface;

    public function matchVariableNamesIgnoreCase(): ExecutionQueryInterface;

    public function matchVariableValuesIgnoreCase(): ExecutionQueryInterface;

    public function variableValueEquals(string $name, $value): ExecutionQueryInterface;

    public function variableValueNotEquals(string $name, $value): ExecutionQueryInterface;

    public function variableValueGreaterThan(string $name, $value): ExecutionQueryInterface;

    public function variableValueGreaterThanOrEqual(string $name, $value): ExecutionQueryInterface;

    public function variableValueLessThan(string $name, $value): ExecutionQueryInterface;

    public function variableValueLessThanOrEqual(string $name, $value): ExecutionQueryInterface;

    public function variableValueLike(string $name, string $value): ExecutionQueryInterface;

    public function processVariableValueEquals(string $variableName, $variableValue): ExecutionQueryInterface;

    public function processVariableValueNotEquals(string $variableName, $variableValue): ExecutionQueryInterface;

    // event subscriptions //////////////////////////////////////////////////

    public function signalEventSubscriptionName(string $signalName): ExecutionQueryInterface;

    public function messageEventSubscriptionName(string $messageName): ExecutionQueryInterface;

    public function messageEventSubscription(): ExecutionQueryInterface;

    public function suspended(): ExecutionQueryInterface;

    public function active(): ExecutionQueryInterface;

    public function incidentType(string $incidentType): ExecutionQueryInterface;

    public function incidentId(string $incidentId): ExecutionQueryInterface;

    public function incidentMessage(string $incidentMessage): ExecutionQueryInterface;

    public function incidentMessageLike(string $incidentMessageLike): ExecutionQueryInterface;

    public function tenantIdIn(array $tenantIds): ExecutionQueryInterface;

    public function withoutTenantId(): ExecutionQueryInterface;

  //ordering //////////////////////////////////////////////////////////////
    public function orderByProcessInstanceId(): ExecutionQueryInterface;

    public function orderByProcessDefinitionKey(): ExecutionQueryInterface;

    public function orderByProcessDefinitionId(): ExecutionQueryInterface;

    public function orderByTenantId(): ExecutionQueryInterface;
}
