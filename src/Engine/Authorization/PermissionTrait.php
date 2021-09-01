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
}
