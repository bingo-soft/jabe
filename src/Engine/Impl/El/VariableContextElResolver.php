<?php

namespace BpmPlatform\Engine\Impl\El;

use BpmPlatform\Engine\Impl\Util\El\{
    ELContext,
    ELResolver
};
use BpmPlatform\Engine\Variable\Context\VariableContextInterface;
use BpmPlatform\Engine\Variable\Value\TypedValueInterface;

class VariableContextElResolver extends ELResolver
{
    public static $VAR_CTX_KEY = "variableContext";


    public function getCommonPropertyType(?ELContext $context, $base): ?string
    {
        return "object";
    }

    public function getFeatureDescriptors(?ELContext $context, $base): ?array
    {
        return null;
    }

    public function getType(?ELContext $context, $base, $property)
    {
        return "object";
    }

    public function getValue(?ELContext $context, $base, $property)
    {
        if ($base == null) {
            $variableContext = $context->getContext(VariableContextInterface::class);
            if ($variableContext != null) {
                if (self::$VAR_CTX_KEY == $property) {
                    $context->setPropertyResolved(true);
                    return $variableContext;
                }
                $typedValue = $variableContext->resolve(strval($property));
                if ($typedValue != null) {
                    $context->setPropertyResolved(true);
                    return $this->unpack($typedValue);
                }
            }
        }
        return null;
    }

    public function isReadOnly(?ELContext $context, $base, $property): bool
    {
        return true;
    }

    public function setValue(?ELContext $context, $base, $property, $value): void
    {
    }

    protected function unpack(TypedValueInterface $typedValue)
    {
        if ($typedValue != null) {
            return $typedValue->getValue();
        }
        return null;
    }
}
