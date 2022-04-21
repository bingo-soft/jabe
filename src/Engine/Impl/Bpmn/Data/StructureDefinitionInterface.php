<?php

namespace BpmPlatform\Engine\Impl\Bpmn\Data;

interface StructureDefinitionInterface
{
    /**
     * Obtains the id of this structure
     *
     * @return the id of this structure
     */
    public function getId(): string;

    /**
     * @return a new instance of this structure definition
     */
    public function createInstance(): StructureInstanceInterface;
}
