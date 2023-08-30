<?php

namespace Jabe\Impl;

use Jabe\Query\QueryPropertyInterface;

class QueryPropertyImpl implements QueryPropertyInterface
{
    protected $name;
    protected $function;

    public function __construct(?string $name, ?string $function = null)
    {
        $this->name = $name;
        $this->function = $function;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getFunction(): ?string
    {
        return $this->function;
    }

    public function equals($obj): bool
    {
        if ($this == $obj) {
            return true;
        }
        if ($obj === null) {
            return false;
        }
        if (get_class($this) != get_class($obj)) {
            return false;
        }
        return $this->name == $obj->name && $this->function == $obj->function;
    }

    public function __serialize(): array
    {
        return [
            'name' => $this->name,
            'function' => $this->function
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->name = $data['name'];
        $this->function = $data['function'];
    }

    public function __toString()
    {
        return "QueryProperty["
            . "name=" . $this->name
            . ", function=" . $this->function
            . "]";
    }
}
