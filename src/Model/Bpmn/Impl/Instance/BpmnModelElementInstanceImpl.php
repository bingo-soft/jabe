<?php

namespace Jabe\Model\Bpmn\Impl\Instance;

use Jabe\Model\Xml\Impl\Instance\{
    ModelElementInstanceImpl,
    ModelTypeInstanceContext
};
use Jabe\Model\Bpmn\Builder\AbstractBaseElementBuilder;
use Jabe\Model\Bpmn\Exception\BpmnModelException;
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Instance\{
    BpmnModelElementInstanceInterface,
    ProcessInterface,
    SubProcessInterface
};

abstract class BpmnModelElementInstanceImpl extends ModelElementInstanceImpl implements BpmnModelElementInstanceInterface
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

    public function getScope(): ?BpmnModelElementInstanceInterface
    {
        $parentElement = $this->getParentElement();
        if ($parentElement !== null) {
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
