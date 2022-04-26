<?php

namespace Jabe\Model\Bpmn\Instance;

interface ErrorEventDefinitionInterface extends EventDefinitionInterface
{
    public function getError(): ?ErrorInterface;

    public function setError(ErrorInterface $error): void;

    public function getErrorCodeVariable(): string;

    public function setErrorCodeVariable(string $errorCodeVariable): void;

    public function setErrorMessageVariable(string $errorCauseVariable): void;

    public function getErrorMessageVariable(): string;
}
