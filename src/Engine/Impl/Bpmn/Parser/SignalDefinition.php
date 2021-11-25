<?php

namespace BpmPlatform\Engine\Impl\Bpmn\Parser;

use BpmPlatform\Engine\Impl\El\ExpressionInterface;

class SignalDefinition implements \Serializable
{
    protected $id;
    protected $name;

    public function serialize()
    {
        return json_encode([
            'id' => $this->id,
            'name' => serialize($this->name)
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->id = $json->id;
        $this->name = unserialize($json->name);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
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
