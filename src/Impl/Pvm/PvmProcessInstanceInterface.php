<?php

namespace Jabe\Impl\Pvm;

use Jabe\Variable\VariableMapInterface;

interface PvmProcessInstanceInterface extends PvmExecutionInterface
{
    public function start(?VariableMapInterface $variables = null, ?VariableMapInterface $formProperties = null): void;

    public function findExecution(?string $activityId): ?PvmExecutionInterface;

    public function findExecutions(?string $activityId): array;

    public function findActiveActivityIds(): array;

    public function isEnded(): bool;

    public function deleteCascade(
        ?string $deleteReason,
        ?bool $skipCustomListeners = false,
        ?bool $skipIoMappings = false,
        ?bool $externallyTerminated = false,
        ?bool $skipSubprocesses = false
    ): void;
}
