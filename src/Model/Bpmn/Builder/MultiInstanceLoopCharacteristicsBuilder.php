<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\MultiInstanceLoopCharacteristicsInterface;

class MultiInstanceLoopCharacteristicsBuilder extends AbstractMultiInstanceLoopCharacteristicsBuilder
{
    public function __construct(
        BpmnModelInstanceInterface $modelInstance,
        MultiInstanceLoopCharacteristicsInterface $element
    ) {
        parent::__construct($modelInstance, $element, MultiInstanceLoopCharacteristicsBuilder::class);
    }
}
