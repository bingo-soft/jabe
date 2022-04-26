<?php

namespace Jabe\Engine\Identity;

interface PasswordPolicyInterface
{
    public function getRules(): array;
}
