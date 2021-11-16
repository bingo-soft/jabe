<?php

namespace BpmPlatform\Engine\Impl\Expression;

class ObjectProperties
{
    private $map;

    public function __construct(string $class)
    {
        $ref = new \ReflectionClass($class);
        $this->map = $ref->getProperties();
    }

    public function getProperty(string $name): ?\ReflectionProperty
    {
        foreach ($this->map as $property) {
            if ($property->getName() == $name) {
                return $property;
            }
        }
        return null;
    }
}
