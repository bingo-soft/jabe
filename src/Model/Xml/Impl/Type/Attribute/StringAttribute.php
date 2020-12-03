<?php

namespace BpmPlatform\Model\Xml\Impl\Type\Attribute;

use BpmPlatform\Model\Xml\Type\ModelElementTypeInterface;

class StringAttribute extends AttributeImpl
{
    public function __construct(ModelElementTypeInterface $owningElementType)
    {
        parent::__construct($owningElementType);
    }

    public function convertXmlValueToModelValue(string $rawValue): string
    {
        return $this->rawValue;
    }

    public function convertModelValueToXmlValue(string $modelValue): string
    {
        return $this->modelValue;
    }
}
