<?php

namespace Jabe\Engine\Identity;

interface UserInterface
{
    public function getId(): ?string;

    public function setId(string $id): void;

    public function getFirstName(): ?string;

    public function setFirstName(string $firstName): void;

    public function getLastName(): ?string;

    public function setLastName(string $lastName): void;

    public function getEmail(): ?string;

    public function setEmail(string $email): void;

    public function getPassword(): ?string;

    public function setPassword(string $password): void;
}
