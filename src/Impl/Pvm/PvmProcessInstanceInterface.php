<?php

namespace Jabe\Impl\Pvm;

interface PvmProcessInstanceInterface extends PvmExecutionInterface
{
    public function start(?array $variables = null): void;

    public function findExecution(string $activityId): PvmExecutionInterface;

    public function findExecutions(string $activityId): array;

    public function findActiveActivityIds(): array;

    public function isEnded(): bool;

    public function deleteCascade(
        string $deleteReason,
        ?bool $skipCustomListeners = false,
        ?bool $skipIoMappings = false,
        ?bool $externallyTerminated = false,
        ?bool $skipSubprocesses = false
    ): void;
}
