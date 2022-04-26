<?php

namespace Jabe\Model\Xml\Impl\Type\Reference;

use Jabe\Model\Xml\Exception\ModelException;
use Jabe\Model\Xml\Impl\Instance\ModelElementInstanceImpl;
use Jabe\Model\Xml\Impl\Util\StringUtil;
use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;
use Jabe\Model\Xml\Type\Child\ChildElementCollectionInterface;

class IdsElementReferenceCollectionImpl extends ElementReferenceCollectionImpl
{
    protected $separator = " ";

    public function __construct(ChildElementCollectionInterface $referenceSourceCollection)
    {
        parent::__construct($referenceSourceCollection);
    }

    protected function getReferenceIdentifiers(ModelElementInstanceInterface $referenceSourceElement): array
    {
        $referenceIdentifiers = $this->getReferenceIdentifier($referenceSourceElement);
        return StringUtil::splitListBySeparator($referenceIdentifiers, $this->separator);
    }

    protected function setReferenceIdentifiers(
        ModelElementInstanceInterface $referenceSourceElement,
        array $referenceIdentifiers
    ): void {
        $referenceIdentifier = StringUtil::joinList($referenceIdentifiers, $this->separator);
        $referenceSourceElement->setTextContent($referenceIdentifier);
    }

    protected function getView(ModelElementInstanceImpl $referenceSourceParentElement): array
    {
        $document = $referenceSourceParentElement->getModelInstance()->getDocument();
        $referenceSourceElements = $this->getReferenceSourceCollection()->get($referenceSourceParentElement);
        $referenceTargetElements = [];
        foreach ($referenceSourceElements as $referenceSourceElement) {
            $identifiers = $this->getReferenceIdentifiers($referenceSourceElement);
            foreach ($identifiers as $identifier) {
                $referenceTargetElement = $document->getElementById($identifier);
                if ($referenceTargetElement != null) {
                    $referenceTargetElements[] = $referenceTargetElement;
                } else {
                    throw new ModelException(sprintf("Unable to find a model element instance for id %s", $identifier));
                }
            }
        }
        return $referenceTargetElements;
    }

    protected function updateReference(
        ModelElementInstanceInterface $referenceSourceElement,
        ?string $oldIdentifier,
        string $newIdentifier
    ): void {
        $referenceIdentifiers = $this->getReferenceIdentifiers($referenceSourceElement);
        if (($key = array_search($oldIdentifier, $referenceIdentifiers)) !== false) {
            $referenceIdentifiers[$key] = $newIdentifier;
            $this->setReferenceIdentifiers($referenceSourceElement, $referenceIdentifiers);
        }
    }

    /**
     * @param ModelElementInstanceInterface $referenceTargetElement
     * @param mixed $referenceIdentifier
     */
    public function referencedElementRemoved(
        ModelElementInstanceInterface $referenceTargetElement,
        $referenceIdentifier
    ): void {
        $referenceSourceElements = $this->findReferenceSourceElements($referenceTargetElement);
        foreach ($referenceSourceElements as $referenceSourceElement) {
            $referenceIdentifiers = $this->getReferenceIdentifiers($referenceSourceElement);
            if (($key = array_search($referenceIdentifier, $referenceIdentifiers)) !== false) {
                if (count($referenceIdentifiers) == 1) {
                    $this->removeReference($referenceSourceElement, $referenceTargetElement);
                } else {
                    unset($referenceIdentifiers[$key]);
                    $this->setReferenceIdentifiers($referenceSourceElement, $referenceIdentifiers);
                }
            }
        }
    }
}
