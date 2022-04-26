<?php

namespace Jabe\Engine\Authorization;

use Jabe\Engine\Authorization\Exception\PermissionNotFound;

/**
 * The set of built-in Permissions for Resources::PROCESS_DEFINITION.
 */
class ProcessDefinitionPermissions implements PermissionInterface
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

    private static $DELETE;

    public static function delete(): PermissionInterface
    {
        if (self::$DELETE == null) {
            self::$DELETE = new ProcessDefinitionPermissions("DELETE", 16);
        }
        return self::$DELETE;
    }

    private static $RETRY_JOB;

    public static function retryJob(): PermissionInterface
    {
        if (self::$RETRY_JOB == null) {
            self::$RETRY_JOB = new ProcessDefinitionPermissions("RETRY_JOB", 32);
        }
        return self::$RETRY_JOB;
    }

    private static $READ_TASK;

    public static function readTask(): PermissionInterface
    {
        if (self::$READ_TASK == null) {
            self::$READ_TASK = new ProcessDefinitionPermissions("READ_TASK", 64);
        }
        return self::$READ_TASK;
    }

    private static $UPDATE_TASK;

    public static function updateTask(): PermissionInterface
    {
        if (self::$UPDATE_TASK == null) {
            self::$UPDATE_TASK = new ProcessDefinitionPermissions("UPDATE_TASK", 128);
        }
        return self::$UPDATE_TASK;
    }

    private static $CREATE_INSTANCE;

    public static function createInstance(): PermissionInterface
    {
        if (self::$CREATE_INSTANCE == null) {
            self::$CREATE_INSTANCE = new ProcessDefinitionPermissions("CREATE_INSTANCE", 256);
        }
        return self::$CREATE_INSTANCE;
    }

    private static $READ_INSTANCE;

    public static function readInstance(): PermissionInterface
    {
        if (self::$READ_INSTANCE == null) {
            self::$READ_INSTANCE = new ProcessDefinitionPermissions("READ_INSTANCE", 512);
        }
        return self::$READ_INSTANCE;
    }

    private static $UPDATE_INSTANCE;

    public static function updateInstance(): PermissionInterface
    {
        if (self::$UPDATE_INSTANCE == null) {
            self::$UPDATE_INSTANCE = new ProcessDefinitionPermissions("UPDATE_INSTANCE", 1024);
        }
        return self::$UPDATE_INSTANCE;
    }

    private static $DELETE_INSTANCE;

    public static function deleteInstance(): PermissionInterface
    {
        if (self::$DELETE_INSTANCE == null) {
            self::$DELETE_INSTANCE = new ProcessDefinitionPermissions("DELETE_INSTANCE", 2048);
        }
        return self::$DELETE_INSTANCE;
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

    private static $TASK_WORK;

    public static function taskWork(): PermissionInterface
    {
        if (self::$TASK_WORK == null) {
            self::$TASK_WORK = new ProcessDefinitionPermissions("TASK_WORK", 16384);
        }
        return self::$TASK_WORK;
    }

    private static $TASK_ASSIGN;

    public static function taskAssign(): PermissionInterface
    {
        if (self::$TASK_ASSIGN == null) {
            self::$TASK_ASSIGN = new ProcessDefinitionPermissions("TASK_ASSIGN", 32768);
        }
        return self::$TASK_ASSIGN;
    }

    private static $MIGRATE_INSTANCE;

    public static function migrateInstance(): PermissionInterface
    {
        if (self::$MIGRATE_INSTANCE == null) {
            self::$MIGRATE_INSTANCE = new ProcessDefinitionPermissions("MIGRATE_INSTANCE", 65536);
        }
        return self::$MIGRATE_INSTANCE;
    }

    private static $SUSPEND_INSTANCE;

    public static function suspendInstance(): PermissionInterface
    {
        if (self::$SUSPEND_INSTANCE == null) {
            self::$SUSPEND_INSTANCE = new ProcessDefinitionPermissions("SUSPEND_INSTANCE", 131072);
        }
        return self::$SUSPEND_INSTANCE;
    }

    private static $UPDATE_INSTANCE_VARIABLE;

    public static function updateInstanceVariable(): PermissionInterface
    {
        if (self::$UPDATE_INSTANCE_VARIABLE == null) {
            self::$UPDATE_INSTANCE_VARIABLE = new ProcessDefinitionPermissions("UPDATE_INSTANCE_VARIABLE", 262144);
        }
        return self::$UPDATE_INSTANCE_VARIABLE;
    }

    private static $UPDATE_TASK_VARIABLE;

    public static function updateTaskVariable(): PermissionInterface
    {
        if (self::$UPDATE_TASK_VARIABLE == null) {
            self::$UPDATE_TASK_VARIABLE = new ProcessDefinitionPermissions("UPDATE_TASK_VARIABLE", 524288);
        }
        return self::$UPDATE_TASK_VARIABLE;
    }

    private static $SUSPEND;

    public static function suspend(): PermissionInterface
    {
        if (self::$SUSPEND == null) {
            self::$SUSPEND = new ProcessDefinitionPermissions("SUSPEND", 1048576);
        }
        return self::$SUSPEND;
    }

    private static $READ_INSTANCE_VARIABLE = 2097152;

    public static function readInstanceVariable(): PermissionInterface
    {
        if (self::$READ_INSTANCE_VARIABLE == null) {
            self::$READ_INSTANCE_VARIABLE = new ProcessDefinitionPermissions("READ_INSTANCE_VARIABLE", 2097152);
        }
        return self::$READ_INSTANCE_VARIABLE;
    }

    private static $READ_HISTORY_VARIABLE;

    public static function readHistoryVariable(): PermissionInterface
    {
        if (self::$READ_INSTANCE_VARIABLE == null) {
            self::$READ_INSTANCE_VARIABLE = new ProcessDefinitionPermissions("READ_HISTORY_VARIABLE", 4194304);
        }
        return self::$READ_INSTANCE_VARIABLE;
    }

    private static $READ_TASK_VARIABLE;

    public static function readTaskVariable(): PermissionInterface
    {
        if (self::$READ_TASK_VARIABLE == null) {
            self::$READ_TASK_VARIABLE = new ProcessDefinitionPermissions("READ_TASK_VARIABLE", 8388608);
        }
        return self::$READ_TASK_VARIABLE;
    }

    private static $UPDATE_HISTORY;

    public static function updateHistory(): PermissionInterface
    {
        if (self::$UPDATE_HISTORY == null) {
            self::$UPDATE_HISTORY = new ProcessDefinitionPermissions("UPDATE_HISTORY", 16777216);
        }
        return self::$UPDATE_HISTORY;
    }

    private static $RESOURCES;

    public static function resources(): array
    {
        if (self::$RESOURCES == null) {
            self::$RESOURCES = [ Resources::processDefinition() ];
        }
        return self::$RESOURCES;
    }

    private function __construct(string $name, int $id)
    {
        $this->name = $name;
        $this->id = $id;
    }

    public function getTypes(): array
    {
        return self::resources();
    }
}
