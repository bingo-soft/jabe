<?php

namespace BpmPlatform\Engine\Identity;

interface PasswordPolicyInterface
{
    public function getRules(): array;
}
