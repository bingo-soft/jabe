<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance\Extension;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Xml\Impl\Util\ModelUtil;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\BpmnModelElementInstanceInterface;
use BpmPlatform\Model\Bpmn\Impl\Instance\BpmnModelElementInstanceImpl;
use BpmPlatform\Model\Bpmn\Instance\Extension\{
    ListInterface,
    ValueInterface
};

class ListImpl extends BpmnModelElementInstanceImpl implements ListInterface
{
    protected static $valueChild;

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            ListInterface::class,
            BpmnModelConstants::EXTENSION_ELEMENT_LIST
        )
        ->namespaceUri(BpmnModelConstants::EXTENSION_NS)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new ListImpl($instanceContext);
                }
            }
        );

        $sequenceBuilder = $typeBuilder->sequence();

        self::$valueChild = $sequenceBuilder->element(ValueInterface::class)
        ->build();

        $typeBuilder->build();
    }

    public function getValue(): ?ValueInterface
    {
        return self::$valueAttribute->getValue($this);
    }

    public function setValue(ValueInterface $value): void
    {
        self::$valueAttribute->setValue($this, $value);
    }

    public function getValues(): array
    {
        return $this->getElements();
    }

    public function getElements(): array
    {
        return ModelUtil::getModelElementCollection(
            $this->getDomElement()->getChildElements(),
            $this->getModelInstance()
        );
    }

    public function size(): int
    {
        return count($this->getElement());
    }

    public function isEmpty(): bool
    {
        return $this->size() == 0;
    }

    public function contains(ModelElementInstanceInterface $el): bool
    {
        foreach ($this->getElements() as $elementToCheck) {
            if ($elementToCheck->equals($el)) {
                return true;
            }
        }
        return false;
    }

    public function add(ModelElementInstanceInterface $el): bool
    {
        $this->getDomElement()->appendChild($el->getDomElement());
        return true;
    }

    public function remove(ModelElementInstanceInterface $el): bool
    {
        ModelUtil::ensureInstanceOf($el, BpmnModelElementInstanceInterface::class);
        return $this->getDomElement()->removeChild($el->getDomElement());
    }

    public function containsAll(array $c): bool
    {
        foreach ($c as $o) {
            if (!$this->contains($o)) {
                return false;
            }
        }
        return true;
    }

    public function addAll(array $c): bool
    {
        foreach ($c as $o) {
            $this->add($o);
        }
        return true;
    }

    public function removeAll(array $c): bool
    {
        $result = false;
        foreach ($c as $o) {
            $result |= $this->remove($o);
        }
        return $result;
    }

    public function clear(): void
    {
        $domElement = $this->getDomElement();
        $childElements = $domElement->getChildElements();
        foreach ($childElements as $childElement) {
            $this->domElement->removeChild($childElement);
        }
    }
}
