<?php

namespace Jabe\Model\Bpmn\Builder;

use Jabe\Model\Bpmn\BpmnModelInstanceInterface;
use Jabe\Model\Bpmn\Instance\MultiInstanceLoopCharacteristicsInterface;

class MultiInstanceLoopCharacteristicsBuilder extends AbstractMultiInstanceLoopCharacteristicsBuilder
{
    public function __construct(
        BpmnModelInstanceInterface $modelInstance,
        MultiInstanceLoopCharacteristicsInterface $element
    ) {
        parent::__construct($modelInstance, $element, MultiInstanceLoopCharacteristicsBuilder::class);
    }
}
