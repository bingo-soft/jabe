<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\BusinessRuleTaskInterface;

class BusinessRuleTaskBuilder extends AbstractBusinessRuleTaskBuilder
{
    public function __construct(
        BpmnModelInstanceInterface $modelInstance,
        BusinessRuleTaskInterface $element
    ) {
        parent::__construct($modelInstance, $element, BusinessRuleTaskBuilder::class);
    }
}
