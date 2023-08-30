<?php

namespace Jabe\Impl\Bpmn\Parser;

use Jabe\Impl\El\ExpressionInterface;

class SignalDefinition
{
    protected $id;
    protected $name;

    public function __serialize(): array
    {
        return [
            'id' => $this->id,
            'name' => serialize($this->name)
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->id = $data['id'];
        $this->name = unserialize($data['name']);
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    public function getName(): ?string
    {
        return $this->name->getExpressionText();
    }

    public function getExpression(): ExpressionInterface
    {
        return $this->name;
    }

    public function setExpression(ExpressionInterface $name): void
    {
        $this->name = $name;
    }
}
