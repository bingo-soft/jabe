<?php

namespace BpmPlatform\Engine\Impl\Bpmn\Data;

class PrimitiveStructureDefinition implements StructureDefinitionInterface
{
    protected $id;

    protected $primitiveClass;

    public function __construct(string $id, string $primitiveClass)
    {
        $this->id = $id;
        $this->primitiveClass = $primitiveClass;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getPrimitiveClass(): string
    {
        return $this->primitiveClass;
    }

    public function createInstance(): StructureInstanceInterface
    {
        return new PrimitiveStructureInstance($this);
    }
}
