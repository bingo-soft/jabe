<?php

namespace Jabe\Model\Bpmn\Instance;

interface ResourceAssignmentExpressionInterface extends BaseElementInterface
{
    public function getExpression(): ExpressionInterface;

    public function setExpression(ExpressionInterface $expression): void;
}
