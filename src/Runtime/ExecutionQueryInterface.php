<?php

namespace Jabe\Runtime;

use Jabe\Query\QueryInterface;

interface ExecutionQueryInterface extends QueryInterface
{
    public function processDefinitionKey(?string $processDefinitionKey): QueryInterface;

    public function processDefinitionId(?string $processDefinitionId): QueryInterface;

    public function processInstanceId(?string $processInstanceId): QueryInterface;

    public function processInstanceBusinessKey(?string $processInstanceBusinessKey): QueryInterface;

    public function executionId(?string $executionId): QueryInterface;

    public function activityId(?string $activityId): QueryInterface;

    public function matchVariableNamesIgnoreCase(): QueryInterface;

    public function matchVariableValuesIgnoreCase(): QueryInterface;

    public function variableValueEquals(?string $name, $value): QueryInterface;

    public function variableValueNotEquals(?string $name, $value): QueryInterface;

    public function variableValueGreaterThan(?string $name, $value): QueryInterface;

    public function variableValueGreaterThanOrEqual(?string $name, $value): QueryInterface;

    public function variableValueLessThan(?string $name, $value): QueryInterface;

    public function variableValueLessThanOrEqual(?string $name, $value): QueryInterface;

    public function variableValueLike(?string $name, ?string $value): QueryInterface;

    public function processVariableValueEquals(?string $variableName, $variableValue): QueryInterface;

    public function processVariableValueNotEquals(?string $variableName, $variableValue): QueryInterface;

    // event subscriptions //////////////////////////////////////////////////

    public function signalEventSubscriptionName(?string $signalName): QueryInterface;

    public function messageEventSubscriptionName(?string $messageName): QueryInterface;

    public function messageEventSubscription(): QueryInterface;

    public function suspended(): QueryInterface;

    public function active(): QueryInterface;

    public function incidentType(?string $incidentType): QueryInterface;

    public function incidentId(?string $incidentId): QueryInterface;

    public function incidentMessage(?string $incidentMessage): QueryInterface;

    public function incidentMessageLike(?string $incidentMessageLike): QueryInterface;

    public function tenantIdIn(array $tenantIds): QueryInterface;

    public function withoutTenantId(): QueryInterface;

  //ordering //////////////////////////////////////////////////////////////
    public function orderByProcessInstanceId(): QueryInterface;

    public function orderByProcessDefinitionKey(): QueryInterface;

    public function orderByProcessDefinitionId(): QueryInterface;

    public function orderByTenantId(): QueryInterface;
}
