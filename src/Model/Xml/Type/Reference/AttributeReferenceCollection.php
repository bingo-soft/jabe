<?php

namespace BpmPlatform\Model\Xml\Type\Reference;

use BpmPlatform\Model\Xml\Exception\ModelException;
use BpmPlatform\Model\Xml\Impl\Type\Attribute\AttributeImpl;
use BpmPlatform\Model\Xml\Impl\Type\Reference\AttributeReferenceImpl;
use BpmPlatform\Model\Xml\Impl\Util\{
    ModelUtil,
    StringUtil
};
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;

abstract class AttributeReferenceCollection extends AttributeReferenceImpl implements AttributeReferenceInterface
{
    protected $separator = " ";

    public function __construct(AttributeImpl $referenceSourceAttribute)
    {
        parent::__construct($referenceSourceAttribute);
    }

    protected function updateReference(
        ModelElementInstanceInterface $referenceSourceElement,
        ?string $oldIdentifier,
        string $newIdentifier
    ): void {
        $referencingIdentifier = $this->getReferenceIdentifier($referenceSourceElement);
        $references = StringUtil::splitListBySeparator($referencingIdentifier, $this->separator);
        if ($oldIdentifier != null && in_array($oldIdentifier, $references)) {
            $referencingIdentifier = str_replace($oldIdentifier, $newIdentifier, $referencingIdentifier);
            $this->setReferenceIdentifier($referenceSourceElement, $newIdentifier);
        }
    }

    protected function removeReference(
        ModelElementInstanceInterface $referenceSourceElement,
        ModelElementInstanceInterface $referenceTargetElement
    ): void {
        $identifier = $this->getReferenceIdentifier($referenceSourceElement);
        $references = StringUtil::splitListBySeparator($identifier, $this->separator);
        $identifierToRemove = $this->getTargetElementIdentifier($referenceTargetElement);
        if (($key = array_search($identifierToRemove, $references)) !== false) {
            unset($references[$key]);
        }
        $identifier = StringUtil::joinList($references, $this->separator);
        $this->setReferenceIdentifier($referenceSourceElement, $identifier);
    }

    abstract protected function getTargetElementIdentifier(
        ModelElementInstanceInterface $eferenceTargetElement
    ): string;

    public function getReferenceTargetElements(ModelElementInstanceInterface $referenceSourceElement): array
    {
        $document = $referenceSourceElement->getModelInstance()->getDocument();
        $identifier = $this->getReferenceIdentifier($referenceSourceElement);
        $references = StringUtil::splitListBySeparator($identifier, $this->separator);
        $referenceTargetElements = [];
        foreach ($references as $reference) {
            $referenceTargetElement = $document->getElementById($reference);
            if ($referenceTargetElement != null) {
                $referenceTargetElements[] = $referenceTargetElement;
            } else {
                throw new ModelException(sprintf("Unable to find a model element instance for id %s", $identifier));
            }
        }
        return ModelUtil::getModelElementCollection(
            $referenceTargetElements,
            $referenceSourceElement->getModelInstance()
        );
    }

    protected function performClearOperation(ModelElementInstanceInterface $referenceSourceElement): void
    {
        $this->setReferenceIdentifier($referenceSourceElement, "");
    }

    protected function setReferenceIdentifier(
        ModelElementInstanceInterface $referenceSourceElement,
        ?string $referenceIdentifier
    ): void {
        if (!empty($referenceIdentifier)) {
            parent::setReferenceIdentifier($referenceSourceElement, $referenceIdentifier);
        } else {
            $this->referenceSourceAttribute->removeAttribute($referenceSourceElement);
        }
    }

    protected function performRemoveOperation(
        ModelElementInstanceInterface $referenceSourceElement,
        ModelElementInstanceInterface $obj
    ): void {
        $this->removeReference($referenceSourceElement, $obj);
    }

    protected function performAddOperation(
        ModelElementInstanceInterface $referenceSourceElement,
        ModelElementInstanceInterface $referenceTargetElement
    ): void {
        $identifier = $this->getReferenceIdentifier($referenceSourceElement);
        $references = StringUtil::splitListBySeparator($identifier, $this->separator);
        $targetIdentifier = $this->getTargetElementIdentifier($referenceTargetElement);
        $references[] = $targetIdentifier;
        $identifier = StringUtil::joinList($references, $this->separator);
        $this->setReferenceIdentifier($referenceSourceElement, $identifier);
    }

    public function add(ModelElementInstanceInterface $modelElement, ModelElementInstanceInterface $e): bool
    {
        $this->performAddOperation($modelElement, $e);
        return true;
    }

    public function remove(ModelElementInstanceInterface $modelElement, ModelElementInstanceInterface $e): bool
    {
        $this->performRemoveOperation($modelElement, $e);
        return true;
    }

    public function clear(ModelElementInstanceInterface $modelElement): bool
    {
        $this->performClearOperation($modelElement);
        return true;
    }
}
