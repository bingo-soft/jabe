<?php

namespace Jabe\Impl\Bpmn\Parser;

use Jabe\Impl\El\ExpressionInterface;

class ExtensionErrorEventDefinition extends ErrorEventDefinition
{
    private $expression;

    public function __construct(string $handlerActivityId, ExpressionInterface $expression)
    {
        parent::__construct($handlerActivityId);
        $this->expression = $expression;
    }

    public function getExpression(): ExpressionInterface
    {
        return $this->expression;
    }

    public function setExpression(ExpressionInterface $expression): void
    {
        $this->expression = $expression;
    }
}
