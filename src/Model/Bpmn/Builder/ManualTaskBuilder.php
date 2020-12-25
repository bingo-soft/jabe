<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\ManualTaskInterface;

class ManualTaskBuilder extends AbstractManualTaskBuilder
{
    public function __construct(
        BpmnModelInstanceInterface $modelInstance,
        ManualTaskInterface $element
    ) {
        parent::__construct($modelInstance, $element, ManualTaskBuilder::class);
    }
}
