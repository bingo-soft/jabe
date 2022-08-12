<?php

namespace Jabe\Authorization;

/**
 * The set of built-in Permissions for Resources::OPTIMIZE.
 */
class OptimizePermissions implements PermissionInterface
{
    use PermissionTrait;

    private static $NONE;

    public static function none(): PermissionInterface
    {
        if (self::$NONE === null) {
            self::$NONE = new OptimizePermissions("NONE", 0);
        }
        return self::$NONE;
    }

    private static $ALL;

    public static function all(): PermissionInterface
    {
        if (self::$ALL === null) {
            self::$ALL = new OptimizePermissions("ALL", PHP_INT_MAX);
        }
        return self::$ALL;
    }

    private static $EDIT;

    public static function edit(): PermissionInterface
    {
        if (self::$EDIT === null) {
            self::$EDIT = new OptimizePermissions("EDIT", 2);
        }
        return self::$EDIT;
    }

    private static $SHARE;

    public static function share(): PermissionInterface
    {
        if (self::$SHARE === null) {
            self::$SHARE = new OptimizePermissions("SHARE", 4);
        }
        return self::$SHARE;
    }

    private static $RESOURCES;

    public static function resources(): array
    {
        if (self::$RESOURCES === null) {
            self::$RESOURCES = [ Resources::optimize() ];
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
