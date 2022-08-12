<?php

namespace Jabe\Identity;

interface PasswordPolicyInterface
{
    public function getRules(): array;
}
