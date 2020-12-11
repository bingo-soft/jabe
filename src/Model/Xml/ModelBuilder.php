<?php

namespace BpmPlatform\Model\Xml;

use BpmPlatform\Model\Xml\Impl\ModelBuilderImpl;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Type\{
    ModelElementTypeInterface,
    ModelElementTypeBuilderInterface
};

abstract class ModelBuilder
{
    abstract public function alternativeNamespace(string $alternativeNs, string $actualNs): ModelBuilder;

    abstract public function defineType(
        string $modelInstanceType,
        string $typeName
    ): ModelElementTypeBuilderInterface;

    abstract public function defineGenericType(string $typeName, string $typeNamespaceUri): ModelElementTypeInterface;

    abstract public function build(): ModelInterface;

    public static function createInstance(string $modelName): ModelBuilder
    {
        return new ModelBuilderImpl($modelName);
    }
}
