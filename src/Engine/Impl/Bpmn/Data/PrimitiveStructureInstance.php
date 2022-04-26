<?php

namespace Jabe\Engine\Impl\Bpmn\Data;

class PrimitiveStructureInstance implements StructureInstanceInterface
{
    protected $primitive;

    protected $definition;

    public function __construct(PrimitiveStructureDefinitionInterface $definition, $primitive = null)
    {
        $this->definition = $definition;
        $this->primitive = $primitive;
    }

    public function getPrimitive()
    {
        return $this->primitive;
    }

    public function toArray(): array
    {
        return [ $this->primitive ];
    }

    public function loadFrom(array $array): void
    {
        for ($i = 0; $i < count($array); $i += 1) {
            $object = $array[$i];
            if ($this->definition->getPrimitiveClass() == gettype($object)) {
                $this->primitive = $object;
                break;
            }
        }
    }
}
