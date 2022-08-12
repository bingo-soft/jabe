<?php

namespace Jabe\Identity;

interface TenantInterface
{
    public function getId(): string;

    public function setId(string $id): void;

    public function getName(): string;

    public function setName(string $name): void;
}
