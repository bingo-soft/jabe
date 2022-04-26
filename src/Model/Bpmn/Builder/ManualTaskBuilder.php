<?php

namespace Jabe\Model\Bpmn\Builder;

use Jabe\Model\Bpmn\BpmnModelInstanceInterface;
use Jabe\Model\Bpmn\Instance\ManualTaskInterface;

class ManualTaskBuilder extends AbstractManualTaskBuilder
{
    public function __construct(
        BpmnModelInstanceInterface $modelInstance,
        ManualTaskInterface $element
    ) {
        parent::__construct($modelInstance, $element, ManualTaskBuilder::class);
    }
}
