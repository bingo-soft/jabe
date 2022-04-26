<?php

namespace Jabe\Model\Bpmn\Builder;

use Jabe\Model\Bpmn\BpmnModelInstanceInterface;
use Jabe\Model\Bpmn\Instance\ServiceTaskInterface;

class ServiceTaskBuilder extends AbstractServiceTaskBuilder
{
    public function __construct(
        BpmnModelInstanceInterface $modelInstance,
        ServiceTaskInterface $element
    ) {
        parent::__construct($modelInstance, $element, ServiceTaskBuilder::class);
    }
}
