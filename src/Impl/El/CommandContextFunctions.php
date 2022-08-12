<?php

namespace Jabe\Impl\El;

use Jabe\Impl\Context\Context;

class CommandContextFunctions
{
    public const CURRENT_USER = "currentUser";
    public const CURRENT_USER_GROUPS = "currentUserGroups";

    public static function currentUser(): ?string
    {
        $commandContext = Context::getCommandContext();
        if ($commandContext !== null) {
            return $commandContext->getAuthenticatedUserId();
        } else {
            return null;
        }
    }

    public static function currentUserGroups(): array
    {
        $commandContext = Context::getCommandContext();
        if ($commandContext !== null) {
            return $commandContext->getAuthenticatedGroupIds();
        } else {
            return null;
        }
    }
}
