<?php

namespace BpmPlatform\Engine\Impl\Core\Model;

class PropertyListKey
{
    protected $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function __toString()
    {
        return "PropertyListKey [name=" . $this->name . "]";
    }
}
