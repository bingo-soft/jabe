<?php

namespace Jabe\Test\Mock;

use El\{
    ELContext,
    ELResolver
};

class MockElResolver extends ELResolver
{
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
        return null;
    }

    public function getValue(?ELContext $context, $base, $property)
    {
        $object = Mocks::get($property);
        if ($object !== null) {
            $context->setPropertyResolved(true);
        }
        return $object;
    }

    public function isReadOnly(?ELContext $context, $base, $property): bool
    {
        return false;
    }

    public function setValue(?ELContext $context, $base, $property, $value): void
    {
    }
}
