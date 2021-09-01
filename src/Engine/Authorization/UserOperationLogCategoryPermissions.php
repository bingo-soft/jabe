<?php

namespace BpmPlatform\Engine\Authorization;

use BpmPlatform\Engine\Authorization\Exception\PermissionNotFound;

/**
 * The set of built-in Permissions for Resources::OPERATION_LOG_CATEGORY.
 */
class UserOperationLogCategoryPermissions extends AbstractPermissions
{
    public const NONE = 0;
    public const ALL = PHP_INT_MAX;
    public const READ = 2;
    public const UPDATE = 4;
    public const DELETE = 16;

    private const RESOURCES = [ Resources::OPERATION_LOG_CATEGORY ];

    public function getTypes(): array
    {
        return self::RESOURCES;
    }
}
