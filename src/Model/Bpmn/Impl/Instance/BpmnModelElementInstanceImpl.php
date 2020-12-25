<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\Impl\Instance\{
    ModelElementInstanceImpl,
    ModelTypeInstanceContext
};
use BpmPlatform\Model\Bpmn\Builder\AbstractBaseElementBuilder;
use BpmPlatform\Model\Bpmn\Exception\BpmnModelException;
use BpmPlatform\Model\Bpmn\Instance\{
    BpmnModelElementInstanceInterface,
    ProcessInterface,
    SubProcessInterface
};

abstract class BpmnModelElementInstanceImpl extends ModelElementInstanceImpl implements BpmnModelElementInstance
{
    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public function builder(): AbstractBaseElementBuilder
    {
        throw new BpmnModelException("No builder implemented");
    }

    public function isScope(): bool
    {
        return $this instanceof ProcessInterface || $this instanceof SubProcessInterface;
    }

    public function getScope(): ?BpmnModelElementInstance
    {
        $parentElement = $this->getParentElement();
        if ($parentElement != null) {
            if ($parentElement->isScope()) {
                return $parentElement;
            } else {
                return $parentElement->getScope();
            }
        } else {
            return null;
        }
    }
}
