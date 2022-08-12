<?php

namespace Jabe\Delegate;

interface DelegateCaseExecutionInterface extends BaseDelegateExecutionInterface, ProcessEngineServicesAwareInterface, CmmnModelExecutionContextInterface
{
    public function getId(): string;

    public function getCaseInstanceId(): string;

    public function getEventName(): string;

    public function getCaseBusinessKey(): string;

    public function getCaseDefinitionId(): string;

    public function getParentId(): ?string;

    public function getActivityId(): string;

    public function getActivityName(): string;

    public function getTenantId(): ?string;

    public function isAvailable(): bool;

    public function isEnabled(): bool;

    public function isDisabled(): bool;

    public function isActive(): bool;

    public function isSuspended(): bool;

    public function isTerminated(): bool;

    public function isCompleted(): bool;

    public function isFailed(): bool;

    public function isClosed(): bool;
}
