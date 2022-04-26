<?php

namespace Jabe\Model\Xml\Impl\Type;

use Jabe\Model\Xml\ModelInterface;
use Jabe\Model\Xml\Exception\ModelException;
use Jabe\Model\Xml\Impl\{
    ModelBuildOperationInterface,
    ModelImpl
};
use  Jabe\Model\Xml\Impl\Type\Attribute\{
    BooleanAttributeBuilder,
    StringAttributeBuilderImpl,
    IntegerAttributeBuilder,
    DoubleAttributeBuilder,
    EnumAttributeBuilder,
    NamedEnumAttributeBuilder
};
use  Jabe\Model\Xml\Impl\Type\Child\SequenceBuilderImpl;
use Jabe\Model\Xml\Type\{
    ModelElementTypeBuilderInterface,
    ModelTypeInstanceProviderInterface,
    ModelElementTypeInterface
};
use Jabe\Model\Xml\Type\Attribute\{
    AttributeBuilderInterface,
    StringAttributeBuilderInterface
};
use Jabe\Model\Xml\Type\Child\SequenceBuilderInterface;

class ModelElementTypeBuilderImpl implements ModelElementTypeBuilderInterface, ModelBuildOperationInterface
{
    private $modelType;
    private $model;
    private $instanceType;
    private $modelBuildOperations = [];
    private $extendedType;

    public function __construct(string $instanceType, string $name, ModelImpl $model)
    {
        $this->instanceType = $instanceType;
        $this->model = $model;
        $this->modelType = new ModelElementTypeImpl($model, $name, $instanceType);
    }

    public function extendsType(string $extendedType): ModelElementTypeBuilderInterface
    {
        $this->extendedType = $extendedType;
        return $this;
    }

    public function instanceProvider(
        ModelTypeInstanceProviderInterface $instanceProvider
    ): ModelElementTypeBuilderInterface {
        $this->modelType->setInstanceProvider($instanceProvider);
        return $this;
    }

    public function namespaceUri(string $namespaceUri): ModelElementTypeBuilderInterface
    {
        $this->modelType->setTypeNamespace($namespaceUri);
        return $this;
    }

    public function booleanAttribute(string $attributeName): AttributeBuilderInterface
    {
        $builder = new BooleanAttributeBuilder($attributeName, $this->modelType);
        $this->modelBuildOperations[] = $builder;
        return $builder;
    }

    public function stringAttribute(string $attributeName): StringAttributeBuilderInterface
    {
        $builder = new StringAttributeBuilderImpl($attributeName, $this->modelType);
        $this->modelBuildOperations[] = $builder;
        return $builder;
    }

    public function integerAttribute(string $attributeName): AttributeBuilderInterface
    {
        $builder = new IntegerAttributeBuilder($attributeName, $this->modelType);
        $this->modelBuildOperations[] = $builder;
        return $builder;
    }

    public function doubleAttribute(string $attributeName): AttributeBuilderInterface
    {
        $builder = new DoubleAttributeBuilder($attributeName, $this->modelType);
        $this->modelBuildOperations[] = $builder;
        return $builder;
    }

    /**
     * @param string $attributeName
     * @param mixed $enumType
     */
    public function enumAttribute(string $attributeName, $enumType): AttributeBuilderInterface
    {
        $builder = new EnumAttributeBuilder($attributeName, $this->modelType, $enumType);
        $this->modelBuildOperations[] = $builder;
        return $builder;
    }

    /**
     * @param string $attributeName
     * @param mixed $enumType
     */
    public function namedEnumAttribute(string $attributeName, $enumType): AttributeBuilderInterface
    {
        $builder = new NamedEnumAttributeBuilder($attributeName, $this->modelType, $enumType);
        $this->modelBuildOperations[] = $builder;
        return $builder;
    }

    public function sequence(): SequenceBuilderInterface
    {
        $builder = new SequenceBuilderImpl($this->modelType);
        $this->modelBuildOperations[] = $builder;
        return $builder;
    }

    public function build(): ModelElementTypeInterface
    {
        $this->model->registerType($this->modelType, $this->instanceType);
        return $this->modelType;
    }

    public function abstractType(): ModelElementTypeBuilderInterface
    {
        $this->modelType->setAbstract(true);
        return $this;
    }

    public function buildTypeHierarchy(ModelInterface $model): void
    {
        if ($this->extendedType != null) {
            $extendedModelElementType = $this->model->getType($this->extendedType);
            if ($extendedModelElementType == null) {
                throw new ModelException(
                    sprintf(
                        "Type is defined to extend %s but no such type is defined.",
                        //$this->modelType,
                        $this->extendedType
                    )
                );
            } else {
                $this->modelType->setBaseType($extendedModelElementType);
                $extendedModelElementType->registerExtendingType($this->modelType);
            }
        }
    }

    public function performModelBuild(ModelInterface $model): void
    {
        foreach ($this->modelBuildOperations as $operation) {
            $operation->performModelBuild($this->model);
        }
    }
}
