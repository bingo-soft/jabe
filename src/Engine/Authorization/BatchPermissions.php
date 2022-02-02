<?php

namespace BpmPlatform\Engine\Authorization;

use BpmPlatform\Engine\Authorization\Exception\PermissionNotFound;

/**
 * The set of built-in Permissions for Resources::BATCH.
 */
class BatchPermissions implements PermissionInterface
{
    use PermissionTrait;

    private static $NONE;

    public static function none(): PermissionInterface
    {
        if (self::$NONE == null) {
            self::$NONE = new ProcessDefinitionPermissions("NONE", 0);
        }
        return self::$NONE;
    }

    private static $ALL;

    public static function all(): PermissionInterface
    {
        if (self::$ALL == null) {
            self::$ALL = new ProcessDefinitionPermissions("ALL", PHP_INT_MAX);
        }
        return self::$ALL;
    }

    private static $READ;

    public static function read(): PermissionInterface
    {
        if (self::$READ == null) {
            self::$READ = new ProcessDefinitionPermissions("READ", 2);
        }
        return self::$READ;
    }

    private static $UPDATE;

    public static function update(): PermissionInterface
    {
        if (self::$UPDATE == null) {
            self::$UPDATE = new ProcessDefinitionPermissions("UPDATE", 4);
        }
        return self::$UPDATE;
    }

    private static $CREATE;

    public static function create(): PermissionInterface
    {
        if (self::$CREATE == null) {
            self::$CREATE = new ProcessDefinitionPermissions("CREATE", 8);
        }
        return self::$CREATE;
    }

    private static $DELETE;

    public static function delete(): PermissionInterface
    {
        if (self::$DELETE == null) {
            self::$DELETE = new ProcessDefinitionPermissions("DELETE", 16);
        }
        return self::$DELETE;
    }

    private static $CREATE_BATCH_MIGRATE_PROCESS_INSTANCES;

    public static function createBatchMigrateProcessInstances(): PermissionInterface
    {
        if (self::$CREATE_BATCH_MIGRATE_PROCESS_INSTANCES == null) {
            self::$CREATE_BATCH_MIGRATE_PROCESS_INSTANCES = new ProcessDefinitionPermissions("CREATE_BATCH_MIGRATE_PROCESS_INSTANCES", 32);
        }
        return self::$CREATE_BATCH_MIGRATE_PROCESS_INSTANCES;
    }

    private static $CREATE_BATCH_MODIFY_PROCESS_INSTANCES;

    public static function createBatchModifyProcessInstances(): PermissionInterface
    {
        if (self::$CREATE_BATCH_MODIFY_PROCESS_INSTANCES == null) {
            self::$CREATE_BATCH_MODIFY_PROCESS_INSTANCES = new ProcessDefinitionPermissions("CREATE_BATCH_MODIFY_PROCESS_INSTANCES", 64);
        }
        return self::$CREATE_BATCH_MODIFY_PROCESS_INSTANCES;
    }

    private static $CREATE_BATCH_RESTART_PROCESS_INSTANCES;

    public static function createBatchRestartProcessInstances(): PermissionInterface
    {
        if (self::$CREATE_BATCH_RESTART_PROCESS_INSTANCES == null) {
            self::$CREATE_BATCH_RESTART_PROCESS_INSTANCES = new ProcessDefinitionPermissions("CREATE_BATCH_RESTART_PROCESS_INSTANCES", 128);
        }
        return self::$CREATE_BATCH_RESTART_PROCESS_INSTANCES;
    }

    private static $CREATE_BATCH_DELETE_RUNNING_PROCESS_INSTANCES;

    public static function createBatchDeleteRunningProcessInstances(): PermissionInterface
    {
        if (self::$CREATE_BATCH_DELETE_RUNNING_PROCESS_INSTANCES == null) {
            self::$CREATE_BATCH_DELETE_RUNNING_PROCESS_INSTANCES = new ProcessDefinitionPermissions("CREATE_BATCH_DELETE_RUNNING_PROCESS_INSTANCES", 256);
        }
        return self::$CREATE_BATCH_DELETE_RUNNING_PROCESS_INSTANCES;
    }

    private static $CREATE_BATCH_DELETE_FINISHED_PROCESS_INSTANCES;

    public static function createBatchDeleteFinishedProcessInstances(): PermissionInterface
    {
        if (self::$CREATE_BATCH_DELETE_FINISHED_PROCESS_INSTANCES == null) {
            self::$CREATE_BATCH_DELETE_FINISHED_PROCESS_INSTANCES = new ProcessDefinitionPermissions("CREATE_BATCH_DELETE_FINISHED_PROCESS_INSTANCES", 512);
        }
        return self::$CREATE_BATCH_DELETE_FINISHED_PROCESS_INSTANCES;
    }

    private static $CREATE_BATCH_DELETE_DECISION_INSTANCES;

    public static function createBatchDeleteDecisionInstances(): PermissionInterface
    {
        if (self::$CREATE_BATCH_DELETE_DECISION_INSTANCES == null) {
            self::$CREATE_BATCH_DELETE_DECISION_INSTANCES = new ProcessDefinitionPermissions("CREATE_BATCH_DELETE_DECISION_INSTANCES", 1024);
        }
        return self::$CREATE_BATCH_DELETE_DECISION_INSTANCES;
    }

    private static $CREATE_BATCH_SET_JOB_RETRIES;

    public static function createBatchSetJobRetries(): PermissionInterface
    {
        if (self::$CREATE_BATCH_SET_JOB_RETRIES == null) {
            self::$CREATE_BATCH_SET_JOB_RETRIES = new ProcessDefinitionPermissions("CREATE_BATCH_SET_JOB_RETRIES", 2048);
        }
        return self::$CREATE_BATCH_SET_JOB_RETRIES;
    }

    private static $READ_HISTORY;

    public static function readHistory(): PermissionInterface
    {
        if (self::$READ_HISTORY == null) {
            self::$READ_HISTORY = new ProcessDefinitionPermissions("READ_HISTORY", 4096);
        }
        return self::$READ_HISTORY;
    }

    private static $DELETE_HISTORY;

    public static function deleteHistory(): PermissionInterface
    {
        if (self::$DELETE_HISTORY == null) {
            self::$DELETE_HISTORY = new ProcessDefinitionPermissions("DELETE_HISTORY", 8192);
        }
        return self::$DELETE_HISTORY;
    }

    private static $CREATE_BATCH_SET_EXTERNAL_TASK_RETRIES;

    public static function createBatchSetExternalTaskRetries(): PermissionInterface
    {
        if (self::$CREATE_BATCH_SET_EXTERNAL_TASK_RETRIES == null) {
            self::$CREATE_BATCH_SET_EXTERNAL_TASK_RETRIES = new ProcessDefinitionPermissions("CREATE_BATCH_SET_EXTERNAL_TASK_RETRIES", 16384);
        }
        return self::$CREATE_BATCH_SET_EXTERNAL_TASK_RETRIES;
    }

    private static $CREATE_BATCH_UPDATE_PROCESS_INSTANCES_SUSPEND;

    public static function createBatchUpdateProcessInstancesSuspend(): PermissionInterface
    {
        if (self::$CREATE_BATCH_UPDATE_PROCESS_INSTANCES_SUSPEND == null) {
            self::$CREATE_BATCH_UPDATE_PROCESS_INSTANCES_SUSPEND = new ProcessDefinitionPermissions("CREATE_BATCH_UPDATE_PROCESS_INSTANCES_SUSPEND", 32768);
        }
        return self::$CREATE_BATCH_UPDATE_PROCESS_INSTANCES_SUSPEND;
    }

    private static $CREATE_BATCH_SET_REMOVAL_TIME;

    public static function createBatchSetRemovalTime(): PermissionInterface
    {
        if (self::$CREATE_BATCH_SET_REMOVAL_TIME == null) {
            self::$CREATE_BATCH_SET_REMOVAL_TIME = new ProcessDefinitionPermissions("CREATE_BATCH_SET_REMOVAL_TIME", 65536);
        }
        return self::$CREATE_BATCH_SET_REMOVAL_TIME;
    }

    private static $CREATE_BATCH_SET_VARIABLES;

    public static function createBatchSetVariables(): PermissionInterface
    {
        if (self::$CREATE_BATCH_SET_VARIABLES == null) {
            self::$CREATE_BATCH_SET_VARIABLES = new ProcessDefinitionPermissions("CREATE_BATCH_SET_VARIABLES", 131072);
        }
        return self::$CREATE_BATCH_SET_VARIABLES;
    }

    private static $RESOURCES;

    public static function resources(): array
    {
        if (self::$RESOURCES == null) {
            self::$RESOURCES = [ Resources::batch() ];
        }
        return self::$RESOURCES;
    }

    public function getTypes(): array
    {
        return self::resources();
    }

    private function __construct(string $name, int $id)
    {
        $this->name = $name;
        $this->id = $id;
    }
}
