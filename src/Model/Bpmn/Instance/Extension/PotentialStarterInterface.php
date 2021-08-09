<?php

namespace BpmPlatform\Model\Bpmn\Instance\Extension;

use BpmPlatform\Model\Bpmn\Instance\{
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
