<?php

namespace Jabe\Model\Xml\Impl\Type\Attribute;

use Jabe\Model\Xml\Type\ModelElementTypeInterface;

class EnumAttribute extends AttributeImpl
{
    private $type;

    public function __construct(ModelElementTypeInterface $owningElementType, string $type)
    {
        parent::__construct($owningElementType);
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    protected function convertXmlValueToModelValue(?string $rawValue)
    {
        if ($rawValue != null) {
            $class = new \ReflectionClass($this->type);
            $constants = $class->getConstants();
            if (in_array($rawValue, $constants)) {
                return $rawValue;
            }
        }
        return null;
    }

    /**
     * @param mixed $modelValue;
     */
    protected function convertModelValueToXmlValue($modelValue): ?string
    {
        $class = new \ReflectionClass($this->type);
        $constants = $class->getConstants();
        if (in_array($modelValue, $constants)) {
            return $modelValue;
        }
        return null;
    }
}
