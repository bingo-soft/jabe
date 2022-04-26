<?php

namespace Jabe\Model\Xml\Impl\Type\Attribute;

use Jabe\Model\Xml\Type\ModelElementTypeInterface;

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

    /**
     * @param mixed $modelValue;
     */
    protected function convertModelValueToXmlValue($modelValue): string
    {
        return strval($modelValue);
    }
}
