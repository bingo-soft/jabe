<?php

namespace Jabe\Model\Xml\Type;

use Jabe\Model\Xml\Type\Attribute\{
    AttributeBuilderInterface,
    StringAttributeBuilderInterface
};
use Jabe\Model\Xml\Type\Child\SequenceBuilderInterface;

interface ModelElementTypeBuilderInterface
{
    public function namespaceUri(string $namespaceUri): ModelElementTypeBuilderInterface;

    public function instanceProvider(
        ModelTypeInstanceProviderInterface $instanceProvider
    ): ModelElementTypeBuilderInterface;

    public function abstractType(): ModelElementTypeBuilderInterface;

    public function booleanAttribute(string $attributeName): AttributeBuilderInterface;

    public function stringAttribute(string $attributeName): StringAttributeBuilderInterface;

    public function integerAttribute(string $attributeName): AttributeBuilderInterface;

    public function doubleAttribute(string $attributeName): AttributeBuilderInterface;

    /**
     * @param string $attributeName
     * @param mixed $enumType
     */
    public function enumAttribute(string $attributeName, $enumType): AttributeBuilderInterface;

    /**
     * @param string $attributeName
     * @param mixed $enumType
     */
    public function namedEnumAttribute(string $attributeName, $enumType): AttributeBuilderInterface;

    public function sequence(): SequenceBuilderInterface;

    public function build(): ModelElementTypeInterface;
}
