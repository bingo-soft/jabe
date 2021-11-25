<?php

namespace BpmPlatform\Engine\Impl\Bpmn\Parser;

class FieldDeclaration
{
    protected $name;
    protected $type;
    protected $value;

    public function __construct(?string $name = null, ?string $type = null, $value = null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->value = $value;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value): void
    {
        $this->value = $value;
    }
}
