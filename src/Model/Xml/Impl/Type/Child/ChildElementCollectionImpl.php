<?php

namespace BpmPlatform\Model\Xml\Impl\Type\Child;

use BpmPlatform\Model\Xml\Exception\UnsupportedModelOperationException;
use BpmPlatform\Model\Xml\ModelInterface;
use BpmPlatform\Model\Xml\Impl\Instance\ModelElementInstanceImpl;
use BpmPlatform\Model\Xml\Impl\Type\ModelElementTypeImpl;
use BpmPlatform\Model\Xml\Impl\Util\ModelUtil;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Type\Child\{
    ChildElementCollectionInterface
};
use BpmPlatform\Model\Xml\Type\ModelElementTypeInterface;

class ChildElementCollectionImpl implements ChildElementCollectionInterface
{
    private $childElementTypeClass;
    private $parentElementType;
    private $minOccurs = 0;
    protected $maxOccurs = -1;
    private $isMutable = true;

    public function __construct(string $childElementTypeClass, ModelElementTypeImpl $parentElementType)
    {
        $this->childElementTypeClass = $childElementTypeClass;
        $this->parentElementType = $parentElementType;
    }

    public function setImmutable(): void
    {
        $this->setMutable(false);
    }

    public function setMutable(bool $isMutable): void
    {
        $this->isMutable = $isMutable;
    }

    public function isImmutable(): bool
    {
        return !$this->isMutable;
    }

    private function getView(ModelElementInstanceImpl $modelElement): array
    {
        return $modelElement->getDomElement()->getChildElementsByType(
            $modelElement->getModelInstance(),
            $this->childElementTypeClass
        );
    }

    public function getMinOccurs(): int
    {
        return $this->minOccurs;
    }

    public function setMinOccurs(int $minOccurs): void
    {
        $this->minOccurs = $minOccurs;
    }

    public function getMaxOccurs(): int
    {
        return $this->maxOccurs;
    }

    public function getChildElementType(): ModelElementTypeInterface
    {
        return $this->model->getType($this->childElementTypeClass);
    }

    public function getChildElementTypeClass(): string
    {
        return $this->childElementTypeClass;
    }

    public function getParentElementType(): ModelElementTypeInterface
    {
        return $this->parentElementType;
    }

    public function setMaxOccurs(int $maxOccurs): void
    {
        $this->maxOccurs = $maxOccurs;
    }

    public function performAddOperation(ModelElementInstanceImpl $modelElement, ModelElementInstanceInterface $e): void
    {
        $modelElement->addChildElement($e);
    }

    private function performRemoveOperation(
        ModelElementInstanceImpl $modelElement,
        ModelElementInstanceInterface $e
    ): bool {
        return $modelElement->removeChildElement($e);
    }

    private function performClearOperation(ModelElementInstanceImpl $modelElement, array $elementsToRemove): void
    {
        $modelElements = ModelUtil::getModelElementCollection($elementsToRemove, $modelElement->getModelInstance());
        foreach ($modelElements as $element) {
            $modelElement->removeChildElement($element);
        }
    }

    public function contains(ModelElementInstanceImpl $modelElement, ?ModelElementInstanceInterface $e): bool
    {
        if ($e == null) {
            return false;
        } else {
            return in_array($e, $this->getView($modelElement));
        }
    }

    public function containsAll(ModelElementInstanceImpl $modelElement, array $c): bool
    {
        foreach ($c as $elementToCheck) {
            if (!$this->contains($modelElement, $elementToCheck)) {
                return false;
            }
        }
        return true;
    }

    public function isEmpty(ModelElementInstanceImpl $modelElement): bool
    {
        return count($this->getView($modelElement)) == 0;
    }

    public function size(ModelElementInstanceImpl $modelElement): int
    {
        return count($this->getView($modelElement));
    }

    public function add(ModelElementInstanceImpl $modelElement, ModelElementInstanceInterface $e): bool
    {
        if (!$this->isMutable) {
            throw new UnsupportedModelOperationException("collection is immutable");
        }
        $this->performAddOperation($modelElement, $e);
        return true;
    }

    public function addAll(ModelElementInstanceImpl $modelElement, array $c): bool
    {
        if (!$this->isMutable) {
            throw new UnsupportedModelOperationException("collection is immutable");
        }
        $result = false;
        foreach ($c as $elementToAdd) {
            $result |= $this->add($modelElement, $elementToAdd);
        }
        return $result;
    }

    public function clear(ModelElementInstanceImpl $modelElement): void
    {
        if (!$this->isMutable) {
            throw new UnsupportedModelOperationException("collection is immutable");
        }
        $view = $this->getView($modelElement);
        $this->performClearOperation($modelElement, $view);
    }

    public function remove(ModelElementInstanceImpl $modelElement, ModelElementInstanceInterface $e): bool
    {
        if (!$this->isMutable) {
            throw new UnsupportedModelOperationException("collection is immutable");
        }
        return $this->performRemoveOperation($modelElement, $e);
    }

    public function removeAll(ModelElementInstanceImpl $modelElement, array $c): bool
    {
        if (!$this->isMutable) {
            throw new UnsupportedModelOperationException("collection is immutable");
        }
        $result = false;
        foreach ($c as $elementToRemove) {
            $result |= $this->remove($modelElement, $elementToRemove);
        }
        return $result;
    }

    public function retainAll(ModelElementInstanceImpl $modelElement, array $c): bool
    {
        throw new UnsupportedModelOperationException("retainAll not implemented");
    }

    public function get(ModelElementInstanceInterface $modelElement): array
    {
        return $this->getView($modelElement);
    }
}
