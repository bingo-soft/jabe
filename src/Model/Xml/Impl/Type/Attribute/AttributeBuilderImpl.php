<?php

namespace Jabe\Model\Xml\Impl\Type\Attribute;

use Jabe\Model\Xml\ModelInterface;
use Jabe\Model\Xml\Impl\ModelBuildOperationInterface;
use Jabe\Model\Xml\Impl\Type\ModelElementTypeImpl;
use Jabe\Model\Xml\Type\Attribute\{
    AttributeInterface,
    AttributeBuilderInterface
};

abstract class AttributeBuilderImpl implements AttributeBuilderInterface, ModelBuildOperationInterface
{
    private $attribute;
    private $modelType;

    public function __construct(string $attributeName, ModelElementTypeImpl $modelType, AttributeImpl $attribute)
    {
        $this->modelType = $modelType;
        $this->attribute = $attribute;
        $this->attribute->setAttributeName($attributeName);
    }

    public function namespace(string $namespaceUri): AttributeBuilderInterface
    {
        $this->attribute->setNamespaceUri($namespaceUri);
        return $this;
    }

    public function idAttribute(): AttributeBuilderInterface
    {
        $this->attribute->setId();
        return $this;
    }

    /**
     * @param mixed $defaultValue
     */
    public function defaultValue($defaultValue): AttributeBuilderInterface
    {
        $this->attribute->setDefaultValue($defaultValue);
        return $this;
    }

    public function required(): AttributeBuilderInterface
    {
        $this->attribute->setRequired(true);
        return $this;
    }

    public function build(): AttributeInterface
    {
        $this->modelType->registerAttribute($this->attribute);
        return $this->attribute;
    }

    public function performModelBuild(ModelInterface $model): void
    {
        //do nothing
    }
}
