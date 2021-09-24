<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance\Extension;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Impl\Util\ModelUtil;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Impl\Instance\BpmnModelElementInstanceImpl;
use BpmPlatform\Model\Bpmn\Instance\Extension\GenericValueElementInterface;
use BpmPlatform\Model\Bpmn\Instance\BpmnModelElementInstanceInterface;

class GenericValueElementImpl extends BpmnModelElementInstanceImpl implements GenericValueElementInterface
{
    public function getValue(): ?BpmnModelElementInstanceInterface
    {
        $childElements = $this->getDomElement()->getChildElements();
        if (empty($childElements)) {
            return null;
        } else {
            return ModelUtil::getModelElement($childElements[0], $this->modelInstance);
        }
    }

    public function removeValue(): void
    {
        $domElement = $this->getDomElement();
        $childElements = $domElement->getChildElements();
        foreach ($childElements as $childElement) {
            $domElement->removeChild($childElement);
        }
    }

    public function setValue(BpmnModelElementInstanceInterface $value): void
    {
        $this->removeValue();
        $this->getDomElement()->appendChild($value->getDomElement());
    }
}
