<?php

namespace Jabe\Engine\Authorization;

use Jabe\Engine\Authorization\Exception\PermissionNotFound;

class SystemPermissions implements PermissionInterface
{
    use PermissionTrait;

    private static $NONE;

    public static function none(): PermissionInterface
    {
        if (self::$NONE === null) {
            self::$NONE = new ProcessDefinitionPermissions("NONE", 0);
        }
        return self::$NONE;
    }

    private static $ALL;

    public static function all(): PermissionInterface
    {
        if (self::$ALL === null) {
            self::$ALL = new SystemPermissions("ALL", PHP_INT_MAX);
        }
        return self::$ALL;
    }

    private static $READ;

    public static function read(): PermissionInterface
    {
        if (self::$READ === null) {
            self::$READ = new SystemPermissions("READ", 2);
        }
        return self::$READ;
    }

    private static $SET;

    public static function set(): PermissionInterface
    {
        if (self::$SET === null) {
            self::$SET = new SystemPermissions("SET", 4);
        }
        return self::$SET;
    }

    private static $DELETE;

    public static function delete(): PermissionInterface
    {
        if (self::$DELETE === null) {
            self::$DELETE = new SystemPermissions("DELETE", 8);
        }
        return self::$DELETE;
    }

    public static function resources(): array
    {
        if (self::$RESOURCES === null) {
            self::$RESOURCES = [ Resources::system() ];
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
