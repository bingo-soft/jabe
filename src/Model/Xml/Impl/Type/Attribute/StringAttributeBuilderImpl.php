<?php

namespace Jabe\Model\Xml\Impl\Type\Attribute;

use Jabe\Model\Xml\ModelInterface;
use Jabe\Model\Xml\Exception\ModelException;
use Jabe\Model\Xml\Impl\Type\ModelElementTypeImpl;
use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;
use Jabe\Model\Xml\Type\Attribute\StringAttributeBuilderInterface;
use Jabe\Model\Xml\Type\Reference\{
    AttributeReferenceBuilderInterface,
    AttributeReferenceCollectionInterface,
    AttributeReferenceCollectionBuilderInterface
};
use Jabe\Model\Xml\Impl\Type\Reference\{
    AttributeReferenceBuilderImpl,
    AttributeReferenceCollectionBuilderImpl,
    QNameAttributeReferenceBuilderImpl
};

class StringAttributeBuilderImpl extends AttributeBuilderImpl implements StringAttributeBuilderInterface
{
    private $referenceBuilder;

    public function __construct(string $attributeName, ModelElementTypeImpl $modelType)
    {
        parent::__construct($attributeName, $modelType, new StringAttribute($modelType));
    }

    public function namespace(string $namespaceUri): StringAttributeBuilderInterface
    {
        return parent::namespace($namespaceUri);
    }

    /**
     * @param mixed $defaultValue
     */
    public function defaultValue($defaultValue): StringAttributeBuilderInterface
    {
        return parent::defaultValue($defaultValue);
    }

    public function required(): StringAttributeBuilderInterface
    {
        return parent::required();
    }

    public function idAttribute(): StringAttributeBuilderInterface
    {
        return parent::idAttribute();
    }

    public function qNameAttributeReference(string $referenceTargetElement): AttributeReferenceBuilderInterface
    {
        $attribute = $this->build();
        $referenceBuilder = new QNameAttributeReferenceBuilderImpl($attribute, $referenceTargetElement);
        $this->setAttributeReference($referenceBuilder);
        return $referenceBuilder;
    }

    public function idAttributeReference(string $referenceTargetElement): AttributeReferenceBuilderInterface
    {
        $attribute = $this->build();
        $referenceBuilder = new AttributeReferenceBuilderImpl($attribute, $referenceTargetElement);
        $this->setAttributeReference($referenceBuilder);
        return $referenceBuilder;
    }

    public function idAttributeReferenceCollection(
        string $referenceTargetElement,
        string $attributeReferenceCollection
    ): AttributeReferenceCollectionBuilderInterface {
        $attribute = $this->build();
        $referenceBuilder = new AttributeReferenceCollectionBuilderImpl(
            $attribute,
            $referenceTargetElement,
            $attributeReferenceCollection
        );
        $this->setAttributeReference($referenceBuilder);
        return $referenceBuilder;
    }

    protected function setAttributeReference(AttributeReferenceBuilderInterface $referenceBuilder): void
    {
        if ($this->referenceBuilder != null) {
            throw new ModelException("An attribute cannot have more than one reference");
        }
        $this->referenceBuilder = $referenceBuilder;
    }

    public function performModelBuild(ModelInterface $model): void
    {
        parent::performModelBuild($model);
        if ($this->referenceBuilder != null) {
            $this->referenceBuilder->performModelBuild($model);
        }
    }
}
