<?php

namespace Jabe\Authorization;

/**
 * The set of built-in Permissions for tasks.
 */
class TaskPermissions implements PermissionInterface
{
    use PermissionTrait;

    private static $NONE;

    public static function none(): PermissionInterface
    {
        if (self::$NONE === null) {
            self::$NONE = new TaskPermissions("NONE", 0);
        }
        return self::$NONE;
    }

    private static $ALL;

    public static function all(): PermissionInterface
    {
        if (self::$ALL === null) {
            self::$ALL = new TaskPermissions("ALL", PHP_INT_MAX);
        }
        return self::$ALL;
    }

    private static $READ;

    public static function read(): PermissionInterface
    {
        if (self::$READ === null) {
            self::$READ = new TaskPermissions("READ", 2);
        }
        return self::$READ;
    }

    private static $UPDATE;

    public static function update(): PermissionInterface
    {
        if (self::$UPDATE === null) {
            self::$UPDATE = new TaskPermissions("UPDATE", 4);
        }
        return self::$UPDATE;
    }

    private static $CREATE;

    public static function create(): PermissionInterface
    {
        if (self::$CREATE === null) {
            self::$CREATE = new TaskPermissions("CREATE", 8);
        }
        return self::$CREATE;
    }

    private static $DELETE;

    public static function delete(): PermissionInterface
    {
        if (self::$DELETE === null) {
            self::$DELETE = new TaskPermissions("DELETE", 16);
        }
        return self::$DELETE;
    }

    private static $UPDATE_VARIABLE;

    public static function updateVariable(): PermissionInterface
    {
        if (self::$UPDATE_VARIABLE === null) {
            self::$UPDATE_VARIABLE = new TaskPermissions("UPDATE_VARIABLE", 32);
        }
        return self::$UPDATE_VARIABLE;
    }

    private static $READ_VARIABLE;

    public static function readVariable(): PermissionInterface
    {
        if (self::$READ_VARIABLE === null) {
            self::$READ_VARIABLE = new TaskPermissions("READ_VARIABLE", 64);
        }
        return self::$READ_VARIABLE;
    }

    //@DEPRECATED
    private static $READ_HISTORY;

    public static function readHistory(): PermissionInterface
    {
        if (self::$READ_HISTORY === null) {
            self::$READ_HISTORY = new TaskPermissions("READ_HISTORY", 4096);
        }
        return self::$READ_HISTORY;
    }

    private static $TASK_WORK;

    public static function taskWork(): PermissionInterface
    {
        if (self::$TASK_WORK === null) {
            self::$TASK_WORK = new TaskPermissions("TASK_WORK", 16384);
        }
        return self::$TASK_WORK;
    }

    private static $TASK_ASSIGN;

    public static function taskAssign(): PermissionInterface
    {
        if (self::$TASK_ASSIGN === null) {
            self::$TASK_ASSIGN = new TaskPermissions("TASK_ASSIGN", 32768);
        }
        return self::$TASK_ASSIGN;
    }

    private static $RESOURCES;

    public static function resources(): array
    {
        if (self::$RESOURCES === null) {
            self::$RESOURCES = [ Resources::task() ];
        }
        return self::$RESOURCES;
    }

    private function __construct(?string $name, int $id)
    {
        $this->name = $name;
        $this->id = $id;
    }

    public function getTypes(): array
    {
        return self::resources();
    }
}
