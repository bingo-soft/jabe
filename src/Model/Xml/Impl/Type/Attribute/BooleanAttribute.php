<?php

namespace Jabe\Model\Xml\Impl\Type\Attribute;

use Jabe\Model\Xml\Impl\Util\ModelUtil;
use Jabe\Model\Xml\Type\ModelElementTypeInterface;

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
        return $modelValue === null ? "false" : json_encode($modelValue);
    }
}
