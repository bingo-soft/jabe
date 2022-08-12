<?php

namespace Jabe\Impl\Bpmn\Parser;

use Jabe\Impl\El\ExpressionInterface;

class MessageDefinition
{
    protected $id;
    protected $name;

    public function __construct(string $id, ExpressionInterface $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getExpression(): ExpressionInterface
    {
        return $this->name;
    }
}
