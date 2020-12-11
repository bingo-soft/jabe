<?php

namespace BpmPlatform\Model\Xml\Impl\Type\Attribute;

use BpmPlatform\Model\Xml\Type\ModelElementTypeInterface;

class StringAttribute extends AttributeImpl
{
    public function __construct(ModelElementTypeInterface $owningElementType)
    {
        parent::__construct($owningElementType);
    }

    /**
     * @return mixed
     */
    protected function convertXmlValueToModelValue(?string $rawValue)
    {
        return $rawValue;
    }

    protected function convertModelValueToXmlValue(string $modelValue): string
    {
        return strval($modelValue);
    }
}
