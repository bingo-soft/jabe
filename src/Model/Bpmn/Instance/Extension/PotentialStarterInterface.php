<?php

namespace Jabe\Model\Bpmn\Instance\Extension;

use Jabe\Model\Bpmn\Instance\{
    BpmnModelElementInstanceInterface,
    ResourceAssignmentExpressionInterface
};

interface PotentialStarterInterface extends BpmnModelElementInstanceInterface
{
    public function getResourceAssignmentExpression(): ResourceAssignmentExpressionInterface;

    public function setResourceAssignmentExpression(
        ResourceAssignmentExpressionInterface $resourceAssignmentExpression
    ): void;
}
