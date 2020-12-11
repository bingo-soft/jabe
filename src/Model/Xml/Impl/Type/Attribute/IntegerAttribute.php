<?php

namespace BpmPlatform\Model\Xml\Impl\Type\Attribute;

use BpmPlatform\Model\Xml\Impl\Util\ModelUtil;
use BpmPlatform\Model\Xml\Type\ModelElementTypeInterface;

class IntegerAttribute extends AttributeImpl
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
                return intval($rawValue);
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    protected function convertModelValueToXmlValue(int $modelValue): string
    {
        return strval($modelValue);
    }
}
