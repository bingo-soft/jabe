<?php

namespace Jabe\Engine\Impl\Util\El;

class ValueReference implements \Serializable
{
    private $base;
    private $property;

    public function __construct($base, $property)
    {
        $this->base = $base;
        $this->property = $property;
    }

    public function serialize()
    {
        return json_encode([
            'base' => serialize($this->base),
            'property' => serialize($this->property)
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->base = unserialize($json->base);
        $this->property = unserialize($json->property);
    }

    public function getBase()
    {
        return $this->base;
    }

    public function getProperty()
    {
        return $this->property;
    }
}
