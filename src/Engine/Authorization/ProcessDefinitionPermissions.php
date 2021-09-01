<?php

namespace BpmPlatform\Engine\Authorization;

use BpmPlatform\Engine\Authorization\Exception\PermissionNotFound;

/**
 * The set of built-in Permissions for Resources::PROCESS_DEFINITION.
 */
class ProcessDefinitionPermissions extends AbstractPermissions
{
    public const NONE = 0;
    public const ALL = PHP_INT_MAX;
    public const READ = 2;
    public const UPDATE = 4;
    public const DELETE = 16;
    public const RETRY_JOB = 32;
    public const READ_TASK = 64;
    public const UPDATE_TASK = 128;
    public const CREATE_INSTANCE = 256;
    public const READ_INSTANCE = 512;
    public const UPDATE_INSTANCE = 1024;
    public const DELETE_INSTANCE = 2048;
    public const READ_HISTORY = 4096;
    public const DELETE_HISTORY = 8192;
    public const TASK_WORK = 16384;
    public const TASK_ASSIGN = 32768;
    public const MIGRATE_INSTANCE = 65536;
    public const SUSPEND_INSTANCE = 131072;
    public const UPDATE_INSTANCE_VARIABLE = 262144;
    public const UPDATE_TASK_VARIABLE = 524288;
    public const SUSPEND = 1048576;
    public const READ_INSTANCE_VARIABLE = 2097152;
    public const READ_HISTORY_VARIABLE = 4194304;
    public const READ_TASK_VARIABLE = 8388608;
    public const UPDATE_HISTORY = 16777216;

    private const RESOURCES = [ Resources::PROCESS_DEFINITION ];

    public function getTypes(): array
    {
        return self::RESOURCES;
    }
}
