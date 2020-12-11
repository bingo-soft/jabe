<?php

namespace BpmPlatform\Model\Xml\Impl\Type\Child;

use BpmPlatform\Model\Xml\Impl\Instance\ModelElementInstanceImpl;
use BpmPlatform\Model\Xml\Impl\Type\ModelElementTypeImpl;
use BpmPlatform\Model\Xml\Impl\Util\ModelUtil;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Type\Child\ChildElementInterface;

class ChildElementImpl extends ChildElementCollectionImpl implements ChildElementInterface
{
    public function __construct(string $childElementTypeChild, ModelElementTypeImpl $parentElementType)
    {
        parent::__construct($childElementTypeChild, $parentElementType);
        $this->maxOccurs = 1;
    }

    public function performAddOperation(ModelElementInstanceImpl $modelElement, ModelElementInstanceInterface $e): void
    {
        $modelElement->setUniqueChildElementByNameNs($e);
    }

    public function setChild(
        ModelElementInstanceInterface $element,
        ModelElementInstanceInterface $newChildElement
    ): void {
        $this->performAddOperation($element, $newChildElement);
    }

    public function getChild(ModelElementInstanceInterface $elementInstanceImpl): ?ModelElementInstanceInterface
    {
        $childElement = $elementInstanceImpl->getUniqueChildElementByType($this->childElementTypeClass);
        if ($childElement != null) {
            ModelUtil::ensureInstanceOf($childElement, $this->childElementTypeClass);
            return $childElement;
        } else {
            return null;
        }
    }

    public function removeChild(ModelElementInstanceInterface $element): bool
    {
        $childElement = $this->getChild($element);
        return $element->removeChildElement($childElement);
    }
}
