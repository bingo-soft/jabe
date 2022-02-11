<?php

namespace BpmPlatform\Engine\Impl\Form\Engine;

class StringWriter
{
    private $value;

    public function set(string $value): void
    {
        $this->value = $value;
    }

    public function write(string $part): void
    {
        $this->value = $this->value . $part;
    }

    public function __toString()
    {
        return $this->value;
    }
}
