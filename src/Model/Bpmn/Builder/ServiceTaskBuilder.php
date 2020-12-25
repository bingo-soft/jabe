<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\ServiceTaskInterface;

class ServiceTaskBuilder extends AbstractServiceTaskBuilder
{
    public function __construct(
        BpmnModelInstanceInterface $modelInstance,
        ServiceTaskInterface $element
    ) {
        parent::__construct($modelInstance, $element, ServiceTaskBuilder::class);
    }
}
