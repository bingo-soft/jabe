<?php

namespace BpmPlatform\Engine\Impl\Variable;

use BpmPlatform\Engine\Variable\Type\{
    ValueTypeInterface,
    ValueTypeResolverInterface,
    ValueTypeTrait
};

class ValueTypeResolverImpl implements ValueTypeResolverInterface
{
    protected $knownTypes = [];

    public function __construct()
    {
        $this->addType(ValueTypeTrait::getBoolean());
        $this->addType(ValueTypeTrait::getDate());
        $this->addType(ValueTypeTrait::getDouble());
        $this->addType(ValueTypeTrait::getInteger());
        $this->addType(ValueTypeTrait::getNull());
        $this->addType(ValueTypeTrait::getString());
        $this->addType(ValueTypeTrait::getObject());
        $this->addType(ValueTypeTrait::getNumber());
        $this->addType(ValueTypeTrait::getFile());
    }

    public function addType(ValueTypeInterface $type): void
    {
        $this->knownTypes[$type->getName()] = $type;
    }

    public function typeForName(string $typeName): ValueTypeInterface
    {
        return $this->knownTypes[$typeName];
    }

    public function getSubTypes(ValueTypeInterface $type): array
    {
        $types = [];

        $validParents = [];
        $validParents[] = $type;

        foreach ($knownTypes as $knownType) {
            if (in_array($knownType->getParent(), $validParents)) {
                $validParents[] = $knownType;

                if (!$knownType->isAbstract()) {
                    $types[] = $knownType;
                }
            }
        }

        return $types;
    }
}
