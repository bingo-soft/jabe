<?php

namespace BpmPlatform\Model\Xml\Impl\Type\Attribute;

use BpmPlatform\Model\Xml\Impl\Util\ModelUtil;
use BpmPlatform\Model\Xml\Type\ModelElementTypeInterface;

class BooleanAttribute extends AttributeImpl
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
        return ModelUtil::valueAsBoolean($rawValue);
    }

    /**
     * @param mixed $modelValue;
     */
    protected function convertModelValueToXmlValue($modelValue): string
    {
        return ModelUtil::valueAsString($modelValue);
    }
}
