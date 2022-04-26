<?php

namespace Jabe\Model\Bpmn\Instance;

interface TransactionInterface extends SubProcessInterface
{
    public function getMethod(): string;

    public function setMethod(string $method): void;
}
