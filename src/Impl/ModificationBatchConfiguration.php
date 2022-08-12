<?php

namespace Jabe\Impl;

use Jabe\Impl\Batch\{
    BatchConfiguration,
    DeploymentMappings
};

class ModificationBatchConfiguration extends BatchConfiguration
{
    protected $instructions = [];
    protected $skipCustomListeners;
    protected $skipIoMappings;
    protected $processDefinitionId;

    public function __construct(
        array $ids,
        DeploymentMappings $mappings,
        string $processDefinitionId,
        array $instructions,
        bool $skipCustomListeners,
        bool $skipIoMappings
    ) {
        parent::__construct($ids, $mappings);
        $this->instructions = $instructions;
        $this->processDefinitionId = $processDefinitionId;
        $this->skipCustomListeners = $skipCustomListeners;
        $this->skipIoMappings = $skipIoMappings;
    }

    public function getInstructions(): array
    {
        return $this->instructions;
    }

    public function getProcessDefinitionId(): string
    {
        return $this->processDefinitionId;
    }

    public function isSkipCustomListeners(): bool
    {
        return $this->skipCustomListeners;
    }

    public function isSkipIoMappings(): bool
    {
        return $this->skipIoMappings;
    }
}
