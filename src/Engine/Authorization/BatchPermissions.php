<?php

namespace BpmPlatform\Engine\Authorization;

use BpmPlatform\Engine\Authorization\Exception\PermissionNotFound;

/**
 * The set of built-in Permissions for Resources::BATCH.
 */
class BatchPermissions extends AbstractPermissions
{
    public const NONE = 0;
    public const ALL = PHP_INT_MAX;
    public const READ = 2;
    public const UPDATE = 4;
    public const CREATE = 8;
    public const DELETE = 16;
    public const CREATE_BATCH_MIGRATE_PROCESS_INSTANCES = 32;
    public const CREATE_BATCH_MODIFY_PROCESS_INSTANCES = 64;
    public const CREATE_BATCH_RESTART_PROCESS_INSTANCES = 128;
    public const CREATE_BATCH_DELETE_RUNNING_PROCESS_INSTANCES = 256;
    public const CREATE_BATCH_DELETE_FINISHED_PROCESS_INSTANCES = 512;
    public const CREATE_BATCH_DELETE_DECISION_INSTANCES = 1024;
    public const CREATE_BATCH_SET_JOB_RETRIES = 2048;
    public const READ_HISTORY = 4096;
    public const DELETE_HISTORY = 8192;
    public const CREATE_BATCH_SET_EXTERNAL_TASK_RETRIES = 16384;
    public const CREATE_BATCH_UPDATE_PROCESS_INSTANCES_SUSPEND = 32768;
    public const CREATE_BATCH_SET_REMOVAL_TIME = 65536;
    public const CREATE_BATCH_SET_VARIABLES = 131072;

    private const RESOURCES = [ Resources::BATCH ];
}
