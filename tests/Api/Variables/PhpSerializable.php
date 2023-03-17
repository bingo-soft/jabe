<?php

namespace Tests\Api\Variables;

class PhpSerializable implements \Serializable
{
    private $property;

    public function __construct(string $property)
    {
        $this->property = $property;
    }

    public function serialize()
    {
        return json_encode([
            'property' => $this->property
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->property = $json->property;
    }

    public function getProperty(): string
    {
        return $this->property;
    }

    public function setProperty(string $property): void
    {
        $this->property = $property;
    }

    public function __toString()
    {
        return "PhpSerializable [property=" . $this->property . "]";
    }
}
