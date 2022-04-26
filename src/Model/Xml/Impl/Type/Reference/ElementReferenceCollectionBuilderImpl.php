<?php

namespace Jabe\Model\Xml\Impl\Type\Reference;

use Jabe\Model\Xml\ModelInterface;
use Jabe\Model\Xml\Exception\ModelException;
use Jabe\Model\Xml\Impl\Type\Child\ChildElementCollectionImpl;
use Jabe\Model\Xml\Type\Reference\{
    ElementReferenceCollectionBuilderInterface,
    ElementReferenceCollectionInterface
};

class ElementReferenceCollectionBuilderImpl implements ElementReferenceCollectionBuilderInterface
{
    private $childElementType;
    private $referenceTargetClass;
    protected $elementReferenceCollectionImpl;

    public function __construct(
        string $childElementType,
        string $referenceTargetClass,
        ChildElementCollectionImpl $collection
    ) {
        $this->childElementType = $childElementType;
        $this->referenceTargetClass = $referenceTargetClass;
        $this->elementReferenceCollectionImpl = new ElementReferenceCollectionImpl($collection);
    }

    public function build(): ElementReferenceCollectionInterface
    {
        return $this->elementReferenceCollectionImpl;
    }

    public function performModelBuild(ModelInterface $model): void
    {
        $referenceTargetType = $model->getType($this->referenceTargetClass);
        $referenceSourceType = $model->getType($this->childElementType);
        $this->elementReferenceCollectionImpl->setReferenceTargetElementType($referenceTargetType);
        $this->elementReferenceCollectionImpl->setReferenceSourceElementType($referenceSourceType);

        $idAttribute = $referenceTargetType->getAttribute("id");
        if ($idAttribute != null) {
            $idAttribute->registerIncoming($this->elementReferenceCollectionImpl);
            $this->elementReferenceCollectionImpl->setReferenceTargetAttribute($idAttribute);
        } else {
            throw new ModelException(sprintf("Unable to find id attribute of %s", $this->referenceTargetClass));
        }
    }
}
