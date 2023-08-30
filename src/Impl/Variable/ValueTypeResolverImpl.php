<?php

namespace Jabe\Impl\Variable;

use Jabe\Variable\Type\{
    ValueTypeInterface,
    ValueTypeResolverInterface,
    ValueType
};

class ValueTypeResolverImpl implements ValueTypeResolverInterface
{
    protected $knownTypes = [];

    public function __construct()
    {
        $this->addType(ValueType::getBoolean());
        $this->addType(ValueType::getDate());
        $this->addType(ValueType::getDouble());
        $this->addType(ValueType::getInteger());
        $this->addType(ValueType::getNull());
        $this->addType(ValueType::getString());
        $this->addType(ValueType::getObject());
        $this->addType(ValueType::getLong());
        $this->addType(ValueType::getFile());
    }

    public function addType(ValueTypeInterface $type): void
    {
        $this->knownTypes[$type->getName()] = $type;
    }

    public function typeForName(?string $typeName): ValueTypeInterface
    {
        return $this->knownTypes[$typeName];
    }

    public function getSubTypes(ValueTypeInterface $type): array
    {
        $types = [];

        $validParents = [];
        $validParents[] = $type;

        foreach ($this->knownTypes as $knownType) {
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
