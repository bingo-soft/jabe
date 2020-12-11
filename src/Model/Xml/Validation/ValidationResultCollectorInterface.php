<?php

namespace BpmPlatform\Model\Xml\Validation;

interface ValidationResultCollectorInterface
{
    public function addError(int $code, string $message): void;

    public function addWarning(int $code, string $message): void;
}
