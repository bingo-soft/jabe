<?php

namespace Jabe\Identity;

interface GroupInterface extends \Serializable
{
    public function getId(): ?string;

    public function setId(?string $id): void;

    public function getName(): ?string;

    public function setName(?string $name): void;

    public function getType(): ?string;

    public function setType(?string $type): void;
}
