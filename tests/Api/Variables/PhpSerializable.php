<?php

namespace Tests\Api\Variables;

class PhpSerializable
{
    private $property;

    public function __construct(string $property)
    {
        $this->property = $property;
    }

    public function __serialize(): array
    {
        return [
            'property' => $this->property
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->property = $data['property'];
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
