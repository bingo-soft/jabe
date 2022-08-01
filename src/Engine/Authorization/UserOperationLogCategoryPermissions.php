<?php

namespace Jabe\Engine\Authorization;

/**
 * The set of built-in Permissions for Resources::OPERATION_LOG_CATEGORY.
 */
class UserOperationLogCategoryPermissions implements PermissionInterface
{
    use PermissionTrait;

    private static $NONE;

    public static function none(): PermissionInterface
    {
        if (self::$NONE === null) {
            self::$NONE = new UserOperationLogCategoryPermissions("NONE", 0);
        }
        return self::$NONE;
    }

    private static $ALL;

    public static function all(): PermissionInterface
    {
        if (self::$ALL === null) {
            self::$ALL = new UserOperationLogCategoryPermissions("ALL", PHP_INT_MAX);
        }
        return self::$ALL;
    }

    private static $READ;

    public static function read(): PermissionInterface
    {
        if (self::$READ === null) {
            self::$READ = new UserOperationLogCategoryPermissions("READ", 2);
        }
        return self::$READ;
    }

    private static $UPDATE;

    public static function update(): PermissionInterface
    {
        if (self::$UPDATE === null) {
            self::$UPDATE = new UserOperationLogCategoryPermissions("UPDATE", 4);
        }
        return self::$UPDATE;
    }

    private static $DELETE;

    public static function delete(): PermissionInterface
    {
        if (self::$DELETE === null) {
            self::$DELETE = new UserOperationLogCategoryPermissions("DELETE", 16);
        }
        return self::$DELETE;
    }

    private static $RESOURCES;

    public static function resources(): array
    {
        if (self::$RESOURCES === null) {
            self::$RESOURCES = [ Resources::operationLogCategory() ];
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
