<?php

namespace Jabe\Model\Xml\Impl\Type\Attribute;

use Jabe\Model\Xml\Impl\Util\ModelUtil;
use Jabe\Model\Xml\Type\ModelElementTypeInterface;

class DoubleAttribute extends AttributeImpl
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
        if ($rawValue != null) {
            if (is_numeric($rawValue)) {
                return floatval($rawValue);
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    /**
     * @param mixed $modelValue;
     */
    protected function convertModelValueToXmlValue($modelValue): string
    {
        return strval($modelValue);
    }
}
