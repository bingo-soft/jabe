<?php

namespace BpmPlatform\Engine\Authorization;

use BpmPlatform\Engine\Authorization\Exception\PermissionNotFound;

/**
 * The set of built-in Permissions for tasks.
 */
class TaskPermissions extends AbstractPermissions
{
    public const NONE = 0;
    public const ALL = PHP_INT_MAX;
    public const READ = 2;
    public const UPDATE = 4;
    public const CREATE = 8;
    public const DELETE = 16;
    public const UPDATE_VARIABLE = 32;
    public const READ_VARIABLE = 64;

    //@DEPRECATED
    public const READ_HISTORY = 4096;

    public const TASK_WORK = 16384;
    public const TASK_ASSIGN = 32768;

    private const RESOURCES = [ Resources::TASK ];

    public function getTypes(): array
    {
        return self::RESOURCES;
    }
}
