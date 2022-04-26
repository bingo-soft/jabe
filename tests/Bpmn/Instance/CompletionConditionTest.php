<?php

namespace Tests\Bpmn\Instance;

use Tests\Xml\Test\AbstractTypeAssumption;
use Jabe\Model\Bpmn\Instance\ExpressionInterface;

class CompletionConditionTest extends BpmnModelElementInstanceTest
{
    public function getTypeAssumption(): AbstractTypeAssumption
    {
        return new BpmnTypeAssumption($this->model, false, null, ExpressionInterface::class);
    }

    public function getChildElementAssumptions(): array
    {
        return [];
    }

    public function getAttributesAssumptions(): array
    {
        return [];
    }
}
