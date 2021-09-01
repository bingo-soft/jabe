<?php

namespace BpmPlatform\Engine\Authorization;

use BpmPlatform\Engine\Authorization\Exception\PermissionNotFound;

/**
 * The set of built-in Permissions for Resources::PROCESS_INSTANCE.
 */
class ProcessInstancePermissions extends AbstractPermissions
{
    public const NONE = 0;
    public const ALL = PHP_INT_MAX;
    public const READ = 2;
    public const UPDATE = 4;
    public const CREATE = 8;
    public const DELETE = 16;
    public const RETRY_JOB = 32;
    public const SUSPEND = 64;
    public const UPDATE_VARIABLE = 128;

    private const RESOURCES = [ Resources::PROCESS_INSTANCE ];

    public function getTypes(): array
    {
        return self::RESOURCES;
    }
}
