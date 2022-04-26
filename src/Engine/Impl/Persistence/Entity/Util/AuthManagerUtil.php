<?php

namespace Jabe\Engine\Impl\Persistence\Entity\Util;

class AuthManagerUtil
{
    public static function getVariablePermissions(bool $ensureSpecificVariablePermission): VariablePermissions
    {
        return new VariablePermissions($ensureSpecificVariablePermission);
    }
}
