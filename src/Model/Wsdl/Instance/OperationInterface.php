<?php

namespace Jabe\Model\Wsdl\Instance;

interface OperationInterface extends BaseElementInterface
{
    public function getName(): string;

    public function setName(string $name): void;
}
