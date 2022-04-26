<?php

namespace Jabe\Model\Bpmn\Builder;

use Jabe\Model\Bpmn\BpmnModelInstanceInterface;
use Jabe\Model\Bpmn\Instance\BusinessRuleTaskInterface;

class BusinessRuleTaskBuilder extends AbstractBusinessRuleTaskBuilder
{
    public function __construct(
        BpmnModelInstanceInterface $modelInstance,
        BusinessRuleTaskInterface $element
    ) {
        parent::__construct($modelInstance, $element, BusinessRuleTaskBuilder::class);
    }
}
