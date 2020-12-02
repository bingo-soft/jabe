<?php

namespace BpmPlatform\Engine\Authorization;

trait PermissionTrait
{
    private $name;
    private $id;

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue(): int
    {
        return $this->id;
    }

    public function getTypes(): array
    {
        if (property_exists($this, 'resourceTypes')) {
            return $this->resourceTypes;
        }
        return self::RESOURCES;
    }

    public static function forName(string $name): PermissionInterface
    {
        return new self($name);
    }
}
