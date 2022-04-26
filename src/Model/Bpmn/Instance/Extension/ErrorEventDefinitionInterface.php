<?php

namespace Jabe\Model\Bpmn\Instance\Extension;

use Jabe\Model\Bpmn\Instance\ErrorEventDefinitionInterface as BaseErrorEventDefinitionInterface;

interface ErrorEventDefinitionInterface extends BaseErrorEventDefinitionInterface
{
    public function getExpression(): string;

    public function setExpression(string $expression): void;
}
