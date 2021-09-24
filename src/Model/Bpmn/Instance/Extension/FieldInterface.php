<?php

namespace BpmPlatform\Model\Bpmn\Instance\Extension;

use BpmPlatform\Model\Bpmn\Instance\BpmnModelElementInstanceInterface;

interface FieldInterface extends BpmnModelElementInstanceInterface
{
    public function getName(): string;

    public function setName(string $name): void;

    public function getExpression(): string;

    public function setExpression(string $expression): void;

    public function getStringValue(): string;

    public function setStringValue(string $stringValue): void;

    public function getString(): StringInterface;

    public function setString(StringInterface $string): void;

    public function getExpressionChild(): ExpressionInterface;

    public function setExpressionChild(ExpressionInterface $expression): void;
}
