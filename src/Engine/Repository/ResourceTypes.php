<?php

namespace BpmPlatform\Engine\Repository;

class ResourceTypes implements ResourceTypeInterface
{
    public const REPOSITORY = 1;

    public const RUNTIME = 2;

    public const HISTORY = 3;

    // implmentation //////////////////////////

    private $name;
    private $id;

    private function __construct(string $name, int $id)
    {
        $this->name = $name;
        $this->id = $id;
    }

    public function __toString()
    {
        return $this->name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue(): int
    {
        return $this->id;
    }

    public static function forName(string $name): ResourceTypeInterface
    {
        $type = new ResourceTypes($name, constant("self::$name"));
        return $type;
    }
}
