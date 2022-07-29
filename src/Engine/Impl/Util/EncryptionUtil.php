<?php

namespace Jabe\Engine\Impl\Util;

class EncryptionUtil
{
    public static function saltPassword(string $password, string $salt): string
    {
        return $password . $salt;
    }
}
