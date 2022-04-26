<?php

namespace Jabe\Model\Xml\Type\Attribute;

interface AttributeBuilderInterface
{
    public function namespace(string $namespaceUri): AttributeBuilderInterface;

    /**
     * @param mixed $defaultValue
     */
    public function defaultValue($defaultValue): AttributeBuilderInterface;

    public function required(): AttributeBuilderInterface;

    public function idAttribute(): AttributeBuilderInterface;

    public function build(): AttributeInterface;
}
