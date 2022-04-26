<?php

namespace Jabe\Model\Xml\Impl\Type\Child;

use Jabe\Model\Xml\Impl\Instance\ModelElementInstanceImpl;
use Jabe\Model\Xml\Impl\Type\ModelElementTypeImpl;
use Jabe\Model\Xml\Impl\Util\ModelUtil;
use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;
use Jabe\Model\Xml\Type\Child\ChildElementInterface;

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
