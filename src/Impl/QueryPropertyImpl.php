<?php

namespace Jabe\Impl;

use Jabe\Query\QueryPropertyInterface;

class QueryPropertyImpl implements QueryPropertyInterface, \Serializable
{
    protected $name;
    protected $function;

    public function __construct(string $name, ?string $function = null)
    {
        $this->name = $name;
        $this->function = $function;
    }

    public function getName(): string
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

    public function serialize()
    {
        return json_encode([
            'name' => $this->name,
            'function' => $this->function
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->name = $json->name;
        $this->function = $json->function;
    }

    public function __toString()
    {
        return "QueryProperty["
            . "name=" . $this->name
            . ", function=" . $this->function
            . "]";
    }
}
