<?php

namespace Jabe\Model\Wsdl\Instance;

interface PortInterface extends BaseElementInterface
{
    public function getName(): string;

    public function setName(string $name): void;

    public function getBinding(): string;

    public function setBinding(string $binding): void;
}
