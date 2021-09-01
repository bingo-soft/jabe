<?php

namespace BpmPlatform\Engine\Authorization;

use BpmPlatform\Engine\Authorization\Exception\PermissionNotFound;

/**
 * The set of built-in Permissions for Resources::OPTIMIZE.
 */
class OptimizePermissions extends AbstractPermissions
{
    public const NONE = 0;
    public const ALL = PHP_INT_MAX;
    public const EDIT = 2;
    public const SHARE = 4;

    private const RESOURCES = [ Resources::OPTIMIZE ];

    public function getTypes(): array
    {
        return self::RESOURCES;
    }
}
