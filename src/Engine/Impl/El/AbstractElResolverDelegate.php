<?php

namespace Jabe\Engine\Impl\El;

use El\{
    ELContext,
    ELResolver
};

abstract class AbstractElResolverDelegate extends ELResolver
{
    abstract protected function getElResolverDelegate(): ?ELResolver;

    public function getCommonPropertyType(?ELContext $context, $base): ?string
    {
        $delegate = $this->getElResolverDelegate();
        if ($delegate === null) {
            return null;
        } else {
            return $delegate->getCommonPropertyType($context, $base);
        }
    }

    public function getFeatureDescriptors(?ELContext $context, $base): ?array
    {
        $delegate = $this->getElResolverDelegate();
        if ($delegate === null) {
            return [];
        } else {
            return $delegate->getFeatureDescriptors($context, $base);
        }
    }

    public function getType(?ELContext $context, $base, $property)
    {
        $context->setPropertyResolved(false);
        $delegate = $this->getElResolverDelegate();
        if ($delegate === null) {
            return null;
        } else {
            return $delegate->getType($context, $base, $property);
        }
    }

    public function getValue(?ELContext $context, $base, $property)
    {
        $context->setPropertyResolved(false);
        $delegate = $this->getElResolverDelegate();
        if ($delegate === null) {
            return null;
        } else {
            return $delegate->getValue($context, $base, $property);
        }
    }

    public function isReadOnly(?ELContext $context, $base, $property): bool
    {
        $context->setPropertyResolved(false);
        $delegate = $this->getElResolverDelegate();
        if ($delegate === null) {
            return null;
        } else {
            return $delegate->isReadOnly($context, $base, $property);
        }
    }

    public function setValue(?ELContext $context, $base, $property, $value): void
    {
        $context->setPropertyResolved(false);
        $delegate = $this->getElResolverDelegate();
        if ($delegate !== null) {
            $delegate->setValue($context, $base, $property, $value);
        }
    }

    public function invoke(?ELContext $context, $base, $method, ?array $paramTypes = [], ?array $params = [])
    {
        $context->setPropertyResolved(false);
        $delegate = $this->getElResolverDelegate();
        if ($delegate === null) {
            return null;
        } else {
            return $delegate->invoke($context, $base, $method, $paramTypes, $params);
        }
    }
}
