<?php

namespace Jabe\Impl;

use Jabe\Impl\Batch\{
    BatchConfiguration,
    DeploymentMapping
};

class RestartProcessInstancesBatchConfiguration extends BatchConfiguration
{
    protected $instructions = [];
    protected $processDefinitionId;
    protected $initialVariables;
    protected $skipCustomListeners;
    protected $skipIoMappings;
    protected $withoutBusinessKey;

    public function __construct(
        array $processInstanceIds,
        ?DeploymentMappings $mappings,
        array $instructions,
        ?string $processDefinitionId,
        bool $initialVariables,
        bool $skipCustomListeners,
        bool $skipIoMappings,
        bool $withoutBusinessKey
    ) {
        parent::__construct($processInstanceIds, $mappings);
        $this->instructions = $instructions;
        $this->processDefinitionId = $processDefinitionId;
        $this->initialVariables = $initialVariables;
        $this->skipCustomListeners = $skipCustomListeners;
        $this->skipIoMappings = $skipIoMappings;
        $this->withoutBusinessKey = $withoutBusinessKey;
    }

    public function getInstructions(): array
    {
        return $this->instructions;
    }

    public function setInstructions(array $instructions): void
    {
        $this->instructions = $instructions;
    }

    public function getProcessDefinitionId(): ?string
    {
        return $this->processDefinitionId;
    }

    public function setProcessDefinitionId(?string $processDefinitionId): void
    {
        $this->processDefinitionId = $processDefinitionId;
    }

    public function isInitialVariables(): bool
    {
        return $this->initialVariables;
    }

    public function setInitialVariables(bool $initialVariables): void
    {
        $this->initialVariables = $initialVariables;
    }

    public function isSkipCustomListeners(): bool
    {
        return $this->skipCustomListeners;
    }

    public function setSkipCustomListeners(bool $skipCustomListeners): void
    {
        $this->skipCustomListeners = $skipCustomListeners;
    }

    public function isSkipIoMappings(): bool
    {
        return $this->skipIoMappings;
    }

    public function setSkipIoMappings(bool $skipIoMappings): void
    {
        $this->skipIoMappings = $skipIoMappings;
    }

    public function isWithoutBusinessKey(): bool
    {
        return $this->withoutBusinessKey;
    }

    public function setWithoutBusinessKey(bool $withoutBusinessKey): bool
    {
        $this->withoutBusinessKey = $withoutBusinessKey;
    }
}
