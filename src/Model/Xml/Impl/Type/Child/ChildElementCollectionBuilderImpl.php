<?php

namespace BpmPlatform\Model\Xml\Impl\Type\Child;

use BpmPlatform\Model\Xml\ModelInterface;
use BpmPlatform\Model\Xml\Exception\ModelException;
use BpmPlatform\Model\Xml\Impl\ModelBuildOperationInterface;
use BpmPlatform\Model\Xml\Impl\Type\Reference\{
    ElementReferenceCollectionBuilderImpl,
    IdsElementReferenceCollectionBuilderImpl,
    QNameElementReferenceCollectionBuilderImpl,
    UriElementReferenceCollectionBuilderImpl
};
use BpmPlatform\Model\Xml\Type\Child\{
    ChildElementCollectionBuilderInterface,
    ChildElementCollectionInterface
};
use BpmPlatform\Model\Xml\Type\ModelElementTypeInterface;
use BpmPlatform\Model\Xml\Type\Reference\ElementReferenceCollectionBuilderInterface;

class ChildElementCollectionBuilderImpl implements ChildElementCollectionBuilderInterface, ModelBuildOperationInterface
{
    protected $parentElementType;
    private $collection;
    protected $childElementType;
    private $referenceBuilder;
    private $modelBuildOperations = [];

    public function __construct(string $childElementTypeClass, ModelElementTypeInterface $parentElementType)
    {
        $this->childElementType = $childElementTypeClass;
        $this->parentElementType = $parentElementType;
        $this->collection = $this->createCollectionInstance();
    }

    protected function createCollectionInstance(): ChildElementCollectionImpl
    {
        return new ChildElementCollectionImpl(
            $this->childElementType,
            $this->parentElementType
        );
    }

    public function immutable(): ChildElementCollectionBuilderInterface
    {
        $this->collection->setImmutable();
        return $this;
    }

    public function required(): ChildElementCollectionBuilderInterface
    {
        $this->collection->setMinOccurs(1);
        return $this;
    }

    public function maxOccurs(int $i): ChildElementCollectionBuilderInterface
    {
        $this->collection->setMaxOccurs($i);
        return $this;
    }

    public function minOccurs(int $i): ChildElementCollectionBuilderInterface
    {
        $this->collection->setMinOccurs($i);
        return $this;
    }

    public function build(): ChildElementCollectionInterface
    {
        return $this->collection;
    }

    /**
     * @param mixed $referenceTargetType
     */
    public function qNameElementReferenceCollection(
        $referenceTargetType
    ): ElementReferenceCollectionBuilderInterface {
        $collection = $this->build();
        $builder = new QNameElementReferenceCollectionBuilderImpl(
            $this->childElementType,
            $referenceTargetType,
            $collection
        );
        $this->setReferenceBuilder($builder);
        return $builder;
    }

    /**
     * @param mixed $referenceTargetType
     */
    public function idElementReferenceCollection(
        $referenceTargetType
    ): ElementReferenceCollectionBuilderInterface {
        $collection = $this->build();
        $builder = new ElementReferenceCollectionBuilderImpl(
            $this->childElementType,
            $referenceTargetType,
            $collection
        );
        $this->setReferenceBuilder($builder);
        return $builder;
    }

    /**
     * @param mixed $referenceTargetType
     */
    public function idsElementReferenceCollection(
        $referenceTargetType
    ): ElementReferenceCollectionBuilderInterface {
        $collection = $this->build();
        $builder = new IdsElementReferenceCollectionBuilderImpl(
            $this->childElementType,
            $referenceTargetType,
            $collection
        );
        $this->setReferenceBuilder($builder);
        return $builder;
    }

    /**
     * @param mixed $referenceTargetType
     */
    public function uriElementReferenceCollection(
        $referenceTargetType
    ): ElementReferenceCollectionBuilderInterface {
        $collection = $this->build();
        $builder = new UriElementReferenceCollectionBuilderImpl(
            $this->childElementType,
            $referenceTargetType,
            $collection
        );
        $this->setReferenceBuilder($builder);
        return $builder;
    }

    protected function setReferenceBuilder(ElementReferenceCollectionBuilderInterface $referenceBuilder): void
    {
        if ($this->referenceBuilder != null) {
            new ModelException("An collection cannot have more than one reference");
        }
        $this->referebceBuilder = $referenceBuilder;
        $this->modelBuildOperations[] = $referenceBuilder;
    }

    public function performModelBuild(ModelInterface $model): void
    {
        $elementType = $model->getType($this->childElementType);
        if ($elementType == null) {
            throw new ModelException(
                sprintf(
                    "Undefined child element of type %s.",
                    $this->childElementType
                )
            );
        }
        $this->parentElementType->registerChildElementType($elementType);
        $this->parentElementType->registerChildElementCollection($this->collection);
        foreach ($this->modelBuildOperations as $modelBuildOperation) {
            $modelBuildOperation->performModelBuild($model);
        }
    }
}
