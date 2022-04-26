<?php

namespace Jabe\Engine\Authorization;

use Jabe\Engine\Authorization\Exception\PermissionNotFound;

/**
 * The set of built-in Permission types.
 */
class Permissions implements PermissionInterface
{
    use PermissionTrait;

    private static $NONE;

    public static function none(): PermissionInterface
    {
        if (self::$NONE == null) {
            self::$NONE = new Permissions(
                "NONE",
                0,
                [
                    Resources::APPLICATION,
                    Resources::USER,
                    Resources::GROUP,
                    Resources::GROUP_MEMBERSHIP,
                    Resources::AUTHORIZATION,
                    Resources::PROCESS_DEFINITION,
                    Resources::TASK,
                    Resources::PROCESS_INSTANCE,
                    Resources::DEPLOYMENT,
                    Resources::DECISION_DEFINITION,
                    Resources::TENANT,
                    Resources::TENANT_MEMBERSHIP,
                    Resources::BATCH,
                    Resources::DECISION_REQUIREMENTS_DEFINITION,
                    Resources::REPORT,
                    Resources::DASHBOARD,
                    Resources::OPERATION_LOG_CATEGORY,
                    Resources::OPTIMIZE,
                    Resources::HISTORIC_TASK,
                    Resources::HISTORIC_PROCESS_INSTANCE
                ]
            );
        }
        return self::$NONE;
    }

    private static $ALL;

    public static function all(): PermissionInterface
    {
        if (self::$ALL == null) {
            self::$ALL = new Permissions(
                "ALL",
                PHP_INT_MAX,
                [
                    Resources::APPLICATION,
                    Resources::USER,
                    Resources::GROUP,
                    Resources::GROUP_MEMBERSHIP,
                    Resources::AUTHORIZATION,
                    Resources::PROCESS_DEFINITION,
                    Resources::TASK,
                    Resources::PROCESS_INSTANCE,
                    Resources::DEPLOYMENT,
                    Resources::DECISION_DEFINITION,
                    Resources::TENANT,
                    Resources::TENANT_MEMBERSHIP,
                    Resources::BATCH,
                    Resources::DECISION_REQUIREMENTS_DEFINITION,
                    Resources::REPORT,
                    Resources::DASHBOARD,
                    Resources::OPERATION_LOG_CATEGORY,
                    Resources::OPTIMIZE,
                    Resources::HISTORIC_TASK,
                    Resources::HISTORIC_PROCESS_INSTANCE
                ]
            );
        }
        return self::$ALL;
    }

    private static $READ;

    public static function read(): PermissionInterface
    {
        if (self::$READ == null) {
            self::$READ = new Permissions(
                "READ",
                2,
                [
                    Resources::USER,
                    Resources::GROUP,
                    Resources::AUTHORIZATION,
                    Resources::PROCESS_DEFINITION,
                    Resources::TASK,
                    Resources::PROCESS_INSTANCE,
                    Resources::DEPLOYMENT,
                    Resources::DECISION_DEFINITION,
                    Resources::TENANT,
                    Resources::BATCH,
                    Resources::DECISION_REQUIREMENTS_DEFINITION,
                    Resources::REPORT,
                    Resources::DASHBOARD
                ]
            );
        }
        return self::$READ;
    }

    private static $UPDATE;

    public static function update(): PermissionInterface
    {
        if (self::$UPDATE == null) {
            self::$UPDATE = new Permissions(
                "UPDATE",
                4,
                [
                    Resources::USER,
                    Resources::GROUP,
                    Resources::AUTHORIZATION,
                    Resources::PROCESS_DEFINITION,
                    Resources::TASK,
                    Resources::PROCESS_INSTANCE,
                    Resources::DECISION_DEFINITION,
                    Resources::TENANT,
                    Resources::BATCH,
                    Resources::REPORT,
                    Resources::DASHBOARD
                ]
            );
        }
        return self::$UPDATE;
    }

    private static $CREATE;

    public static function create(): PermissionInterface
    {
        if (self::$CREATE == null) {
            self::$CREATE = new Permissions(
                "CREATE",
                8,
                [
                    Resources::USER,
                    Resources::GROUP,
                    Resources::GROUP_MEMBERSHIP,
                    Resources::AUTHORIZATION,
                    Resources::TASK,
                    Resources::PROCESS_INSTANCE,
                    Resources::DEPLOYMENT,
                    Resources::TENANT,
                    Resources::TENANT_MEMBERSHIP,
                    Resources::BATCH,
                    Resources::REPORT,
                    Resources::DASHBOARD
                ]
            );
        }
        return self::$CREATE;
    }

    private static $DELETE;

    public static function delete(): PermissionInterface
    {
        if (self::$DELETE == null) {
            self::$DELETE = new Permissions(
                "DELETE",
                16,
                [
                    Resources::USER,
                    Resources::GROUP,
                    Resources::GROUP_MEMBERSHIP,
                    Resources::AUTHORIZATION,
                    Resources::PROCESS_DEFINITION,
                    Resources::TASK,
                    Resources::PROCESS_INSTANCE,
                    Resources::DEPLOYMENT,
                    Resources::TENANT,
                    Resources::TENANT_MEMBERSHIP,
                    Resources::BATCH,
                    Resources::REPORT,
                    Resources::DASHBOARD
                ]
            );
        }
        return self::$DELETE;
    }

    private static $ACCESS;

    public static function access(): PermissionInterface
    {
        if (self::$ACCESS == null) {
            self::$ACCESS = new Permissions(
                "ACCESS",
                32,
                [
                    Resources::APPLICATION
                ]
            );
        }
        return self::$ACCESS;
    }

    private static $READ_TASK;

    public static function readTask(): PermissionInterface
    {
        if (self::$READ_TASK == null) {
            self::$READ_TASK = new Permissions(
                "READ_TASK",
                64,
                [
                    Resources::PROCESS_DEFINITION
                ]
            );
        }
        return self::$READ_TASK;
    }

    private static $UPDATE_TASK;

    public static function updateTask(): PermissionInterface
    {
        if (self::$UPDATE_TASK == null) {
            self::$UPDATE_TASK = new Permissions(
                "UPDATE_TASK",
                128,
                [
                    Resources::PROCESS_DEFINITION
                ]
            );
        }
        return self::$UPDATE_TASK;
    }

    private static $CREATE_INSTANCE;

    public static function createInstance(): PermissionInterface
    {
        if (self::$CREATE_INSTANCE == null) {
            self::$CREATE_INSTANCE = new Permissions(
                "CREATE_INSTANCE",
                256,
                [
                    Resources::DECISION_DEFINITION,
                    Resources::PROCESS_DEFINITION
                ]
            );
        }
        return self::$CREATE_INSTANCE;
    }

    private static $READ_INSTANCE;

    public static function readInstance(): PermissionInterface
    {
        if (self::$READ_INSTANCE == null) {
            self::$READ_INSTANCE = new Permissions(
                "READ_INSTANCE",
                512,
                [
                    Resources::PROCESS_DEFINITION
                ]
            );
        }
        return self::$READ_INSTANCE;
    }

    private static $UPDATE_INSTANCE;

    public static function updateInstance(): PermissionInterface
    {
        if (self::$UPDATE_INSTANCE == null) {
            self::$UPDATE_INSTANCE = new Permissions(
                "UPDATE_INSTANCE",
                1024,
                [
                    Resources::PROCESS_DEFINITION
                ]
            );
        }
        return self::$UPDATE_INSTANCE;
    }

    private static $DELETE_INSTANCE;

    public static function deleteInstance(): PermissionInterface
    {
        if (self::$DELETE_INSTANCE == null) {
            self::$DELETE_INSTANCE = new Permissions(
                "DELETE_INSTANCE",
                2048,
                [
                    Resources::PROCESS_DEFINITION
                ]
            );
        }
        return self::$DELETE_INSTANCE;
    }

    private static $READ_HISTORY;

    public static function readHistory(): PermissionInterface
    {
        if (self::$READ_HISTORY == null) {
            self::$READ_HISTORY = new Permissions(
                "READ_HISTORY",
                4096,
                [
                    Resources::BATCH,
                    Resources::DECISION_DEFINITION,
                    Resources::PROCESS_DEFINITION,
                    Resources::TASK
                ]
            );
        }
        return self::$READ_HISTORY;
    }

    private static $DELETE_HISTORY;

    public static function deleteHistory(): PermissionInterface
    {
        if (self::$DELETE_HISTORY == null) {
            self::$DELETE_HISTORY = new Permissions(
                "DELETE_HISTORY",
                8192,
                [
                    Resources::BATCH,
                    Resources::DECISION_DEFINITION,
                    Resources::PROCESS_DEFINITION
                ]
            );
        }
        return self::$DELETE_HISTORY;
    }

    private static $TASK_WORK;

    public static function taskWork(): PermissionInterface
    {
        if (self::$TASK_WORK == null) {
            self::$TASK_WORK = new Permissions(
                "TASK_WORK",
                16384,
                [
                    Resources::PROCESS_DEFINITION,
                    Resources::TASK
                ]
            );
        }
        return self::$TASK_WORK;
    }

    private static $TASK_ASSIGN;

    public static function taskAssign(): PermissionInterface
    {
        if (self::$TASK_ASSIGN == null) {
            self::$TASK_ASSIGN = new Permissions(
                "TASK_ASSIGN",
                32768,
                [
                    Resources::PROCESS_DEFINITION,
                    Resources::TASK
                ]
            );
        }
        return self::$TASK_ASSIGN;
    }

    private static $MIGRATE_INSTANCE;

    public static function migrateInstance(): PermissionInterface
    {
        if (self::$MIGRATE_INSTANCE == null) {
            self::$MIGRATE_INSTANCE = new Permissions(
                "MIGRATE_INSTANCE",
                65536,
                [
                    Resources::PROCESS_DEFINITION
                ]
            );
        }
        return self::$MIGRATE_INSTANCE;
    }

    private $resourceTypes = [];

    private function __construct(string $name, int $id, ?array $resourceTypes = null)
    {
        $this->name = $name;
        $this->id = $id;
        if ($resourceTypes != null) {
            $this->resourceTypes = $resourceTypes;
        }
    }

    public function __toString()
    {
        return $this->name;
    }

    public function getTypes(): array
    {
        return $this->resourceTypes;
    }
}
