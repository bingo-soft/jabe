<?php

namespace Jabe\Model\Bpmn\Impl\Instance\Extension;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Xml\Impl\Util\ModelUtil;
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Impl\Instance\BpmnModelElementInstanceImpl;
use Jabe\Model\Bpmn\Instance\Extension\GenericValueElementInterface;
use Jabe\Model\Bpmn\Instance\BpmnModelElementInstanceInterface;

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
