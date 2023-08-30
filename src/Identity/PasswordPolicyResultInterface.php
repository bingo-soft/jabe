<?php

namespace Jabe\Identity;

interface PasswordPolicyResultInterface
{
    public function isValid(): bool;

    public function getViolatedRules(): array;

    public function getFulfilledRules(): array;
}
