<?php

namespace Jabe\Engine\Impl\Bpmn\Parser;

use Jabe\Engine\Impl\Pvm\PvmActivityInterface;

class EscalationEventDefinition
{
    protected $escalationHandler;
    protected $cancelActivity;

    protected $escalationCode;
    protected $escalationCodeVariable;

    public function __construct(PvmActivityInterface $escalationHandler, bool $cancelActivity)
    {
        $this->escalationHandler = $escalationHandler;
        $this->cancelActivity = $cancelActivity;
    }

    public function getEscalationCode(): string
    {
        return $this->escalationCode;
    }

    public function getEscalationHandler(): PvmActivityInterface
    {
        return $this->escalationHandler;
    }

    public function isCancelActivity(): bool
    {
        return $this->cancelActivity;
    }

    public function setEscalationCode(string $escalationCode): void
    {
        $this->escalationCode = $escalationCode;
    }

    public function getEscalationCodeVariable(): string
    {
        return $this->escalationCodeVariable;
    }

    public function setEscalationCodeVariable(string $escalationCodeVariable): void
    {
        $this->escalationCodeVariable = $escalationCodeVariable;
    }
}
