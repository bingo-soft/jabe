<?php

namespace Jabe\Engine\Impl\El;

use Jabe\Engine\ProcessEngineException;
use Jabe\Engine\Impl\Util\El\{
    ELContext,
    ELResolver
};

class ReadOnlyMapELResolver extends ELResolver
{
    protected $wrappedMap = [];

    public function __construct(array $map)
    {
        $this->wrappedMap = $map;
    }

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
            if (array_key_exists($property, $this->wrappedMap)) {
                $context->setPropertyResolved(true);
                return $this->wrappedMap[$property];
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
        if ($base == null) {
            if (array_key_exists($property, $this->wrappedMap)) {
                throw new ProcessEngineException("Cannot set value of '" . $property . "', it's readonly!");
            }
        }
    }
}
