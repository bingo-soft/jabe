<?php

namespace Jabe\Engine\Impl\Util\El;

class BeanProperties
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
