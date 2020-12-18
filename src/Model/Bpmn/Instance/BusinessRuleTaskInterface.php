<?php

namespace BpmPlatform\Model\Bpmn\Instance;

use BpmPlatform\Model\Bpmn\Builder\BusinessRuleTaskBuilder;

interface BusinessRuleTaskInterface extends TaskInterface
{
    public function builder(): BusinessRuleTaskBuilder;

    public function getImplementation(): string;

    public function setImplementation(string $implementation): void;

    public function getClass(): string;

    public function setClass(string $class): void;

    public function getDelegateExpression(): string;

    public function setDelegateExpression(string $expression): void;

    public function getExpression(): string;

    public function setExpression(string $expression): void;

    public function getResultVariable(): string;

    public function setResultVariable(string $resultVariable): void;

    public function getType(): string;

    public function setType(string $type): void;

    public function getTopic(): string;

    public function setTopic(string $topic): void;

    public function getDecisionRef(): string;

    public function setDecisionRef(string $decisionRef): void;

    public function getDecisionRefBinding(): string;

    public function setDecisionRefBinding(string $decisionRefBinding): void;

    public function getDecisionRefVersion(): string;

    public function setDecisionRefVersion(string $decisionRefVersion): void;

    public function getDecisionRefVersionTag(): string;

    public function setDecisionRefVersionTag(string $decisionRefVersionTag): void;

    public function getDecisionRefTenantId(): string;

    public function setDecisionRefTenantId(string $decisionRefTenantId): void;

    public function getMapDecisionResult(): string;

    public function setMapDecisionResult(string $mapDecisionResult): void;

    public function getTaskPriority(): string;

    public function setTaskPriority(string $taskPriority): void;
}
