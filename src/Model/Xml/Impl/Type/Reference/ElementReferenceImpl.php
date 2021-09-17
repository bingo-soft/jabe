<?php

namespace BpmPlatform\Model\Xml\Impl\Type\Reference;

use BpmPlatform\Model\Xml\Exception\{
    ModelException,
    ModelReferenceException
};
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Impl\Instance\ModelElementInstanceImpl;
use BpmPlatform\Model\Xml\Type\Child\ChildElementInterface;
use BpmPlatform\Model\Xml\Type\Reference\ElementReferenceInterface;

class ElementReferenceImpl extends ElementReferenceCollectionImpl implements ElementReferenceInterface
{
    public function __construct(ChildElementInterface $referenceSourceCollection)
    {
        parent::__construct($referenceSourceCollection);
    }

    private function getReferenceSourceChild(): ChildElementInterface
    {
        return $this->getReferenceSourceCollection();
    }

    public function getReferenceSource(
        ModelElementInstanceInterface $referenceSourceParent
    ): ?ModelElementInstanceInterface {
        return $this->getReferenceSourceChild()->getChild($referenceSourceParent);
    }

    private function setReferenceSource(
        ModelElementInstanceInterface $referenceSourceParent,
        ModelElementInstanceInterface $referenceSource
    ): void {
        $this->getReferenceSourceChild()->setChild($referenceSourceParent, $referenceSource);
    }

    /**
     * @return mixed
     */
    public function getReferenceTargetElement(
        ModelElementInstanceInterface $referenceSourceParentElement
    ) {
        $referenceSource = $this->getReferenceSource($referenceSourceParentElement);
        if ($referenceSource != null) {
            $identifier = $this->getReferenceIdentifier($referenceSource);
            $referenceTargetElement = $referenceSourceParentElement->getModelInstance()
                ->getModelElementById($identifier);
            if ($referenceTargetElement != null) {
                return $referenceTargetElement;
            } else {
                throw new ModelException(sprintf("Unable to find a model element instance for id %s", $identifier));
            }
        } else {
            return null;
        }
    }

    /**
     * @param ModelElementInstanceInterface $referenceSourceElement
     * @param mixed $referenceTargetElement
     */
    public function setReferenceTargetElement(
        ModelElementInstanceInterface $referenceSourceParentElement,
        $referenceTargetElement
    ): void {
        $modelInstance = $referenceSourceParentElement->getModelInstance();
        $identifier = $this->referenceTargetAttribute->getValue($referenceTargetElement);
        $existingElement = $modelInstance->getModelElementById($identifier);
        if ($existingElement == null || !$existingElement->equals($referenceTargetElement)) {
            throw new ModelReferenceException("Cannot create reference to model element");
        } else {
            $referenceSourceElement = $modelInstance->newInstance($this->getReferenceSourceElementType());
            $this->setReferenceSource($referenceSourceParentElement, $referenceSourceElement);
            $this->setReferenceIdentifier($referenceSourceElement, $identifier);
        }
    }

    public function clearReferenceTargetElement(ModelElementInstanceImpl $referenceSourceParentElement): void
    {
        $this->getReferenceSourceChild()->removeChild($referenceSourceParentElement);
    }
}
