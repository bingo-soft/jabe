<?php

namespace BpmPlatform\Model\Xml\Impl\Type\Child;

use BpmPlatform\Model\Xml\ModelInterface;
use BpmPlatform\Model\Xml\Impl\ModelBuildOperationInterface;
use BpmPlatform\Model\Xml\Impl\Type\ModelElementTypeImpl;
use BpmPlatform\Model\Xml\Impl\Type\Child\ChildElementBuilderImpl;
use BpmPlatform\Model\Xml\Type\Child\{
    ChildElementBuilderInterface,
    ChildElementCollectionBuilderInterface,
    SequenceBuilderInterface
};

class SequenceBuilderImpl implements SequenceBuilderInterface, ModelBuildOperationInterface
{
    private $elementType;
    private $modelBuildOperations = [];

    public function __construct(ModelElementTypeImpl $modelType)
    {
        $this->elementType = $modelType;
    }

    /**
     * @param mixed $childElementType
     */
    public function element($childElementType): ChildElementBuilderInterface
    {
        $builder = new ChildElementBuilderImpl($childElementType, $this->elementType);
        $this->modelBuildOperations[] = $builder;
        return $builder;
    }

    /**
     * @param mixed $childElementType
     */
    public function elementCollection($childElementType): ChildElementCollectionBuilderInterface
    {
        $builder = new ChildElementCollectionBuilderImpl($childElementType, $this->elementType);
        $this->modelBuildOperations[] = $builder;
        return $builder;
    }

    public function performModelBuild(ModelInterface $model): void
    {
        foreach ($this->modelBuildOperations as $operation) {
            $operation->performModelBuild($model);
        }
    }
}
