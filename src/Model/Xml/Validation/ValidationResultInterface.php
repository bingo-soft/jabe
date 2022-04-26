<?php

namespace Jabe\Model\Xml\Validation;

use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;

interface ValidationResultInterface
{
    public function getType(): string;

    public function getElement(): ModelElementInstanceInterface;

    public function getMessage(): string;

    public function getCode(): int;
}
