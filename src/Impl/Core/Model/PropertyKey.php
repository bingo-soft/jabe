<?php

namespace Jabe\Impl\Core\Model;

class PropertyKey
{
    protected $name;

    public function __construct(?string $name)
    {
        $this->name = $name;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function __toString()
    {
        return "PropertyKey [name=" . $this->name . "]";
    }
}
