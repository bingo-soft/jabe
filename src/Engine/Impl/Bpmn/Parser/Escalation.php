<?php

namespace Jabe\Engine\Impl\Bpmn\Parser;

class Escalation
{
    protected $id;
    protected $name;
    protected $escalationCode;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getEscalationCode(): string
    {
        return $this->escalationCode;
    }

    public function setEscalationCode(string $escalationCode): void
    {
        $this->escalationCode = $escalationCode;
    }

    public function getId(): string
    {
        return $this->id;
    }
}
