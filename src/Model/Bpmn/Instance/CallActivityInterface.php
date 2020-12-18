<?php

namespace BpmPlatform\Model\Bpmn\Instance;

use BpmPlatform\Model\Bpmn\Builder\CallActivityBuilder;

interface CallActivityInterface extends ActivityInterface
{
    public function builder(): CallActivityBuilder;

    public function getCalledElement(): string;

    public function setCalledElement(string $calledElement): void;

    public function isAsync(): bool;

    public function setAsync(bool $isAsync): void;

    public function getCalledElementBinding(): string;

    public function setCalledElementBinding(string $calledElementBinding): void;

    public function getCalledElementVersion(): string;

    public function setCalledElementVersion(string $calledElementVersion): void;

    public function getCalledElementVersionTag(): string;

    public function setCalledElementVersionTag(string $calledElementVersionTag): void;

    public function getCaseRef(): string;

    public function setCaseRef(string $caseRef): void;

    public function getCaseBinding(): string;

    public function setCaseBinding(string $caseBinding): void;

    public function getCaseVersion(): string;

    public function setCaseVersion(string $caseVersion): void;

    public function getCalledElementTenantId(): string;

    public function setCalledElementTenantId(string $tenantId): void;

    public function getCaseTenantId(): string;

    public function setCaseTenantId(string $tenantId): void;

    public function getVariableMappingClass(): string;

    public function setVariableMappingClass(string $class): void;

    public function getVariableMappingDelegateExpression(): string;

    public function setVariableMappingDelegateExpression(string $expression): void;
}
