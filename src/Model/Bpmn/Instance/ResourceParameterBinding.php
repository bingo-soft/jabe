<?php

namespace BpmPlatform\Model\Bpmn\Instance;

interface ResourceParameterBinding extends BaseElementInterface
{
    public function getParameter(): ResourceParameterInterface;

    public function setParameter(ResourceParameterInterface $parameter): void;

    public function getExpression(): ExpressionInterface;

    public function setExpression(ExpressionInterface $expression): void;
}
