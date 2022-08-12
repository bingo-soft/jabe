<?php

namespace Jabe\Authorization;

/**
 * The set of built-in Permission types.
 */
class HistoricTaskPermissions implements PermissionInterface
{
    use PermissionTrait;

    private static $NONE;

    public static function none(): PermissionInterface
    {
        if (self::$NONE === null) {
            self::$NONE = new HistoricTaskPermissions(
                "NONE",
                0
            );
        }
        return self::$NONE;
    }

    private static $ALL;

    public static function all(): PermissionInterface
    {
        if (self::$ALL === null) {
            self::$ALL = new HistoricTaskPermissions(
                "ALL",
                PHP_INT_MAX
            );
        }
        return self::$ALL;
    }

    private static $READ;

    public static function read(): PermissionInterface
    {
        if (self::$READ === null) {
            self::$READ = new HistoricTaskPermissions(
                "READ",
                2
            );
        }
        return self::$READ;
    }

    private static $READ_VARIABLE;

    public static function readVariable(): PermissionInterface
    {
        if (self::$READ_VARIABLE === null) {
            self::$READ_VARIABLE = new HistoricTaskPermissions(
                "READ_VARIABLE",
                64
            );
        }
        return self::$READ_VARIABLE;
    }

    private static $RESOURCES;

    public static function resources(): array
    {
        if (self::$RESOURCES === null) {
            self::$RESOURCES = [ Resources::historicTask() ];
        }
        return self::$RESOURCES;
    }

    private function __construct(string $name, int $id)
    {
        $this->name = $name;
        $this->id = $id;
    }

    public function __toString()
    {
        return $this->name;
    }

    public function getTypes(): array
    {
        return self::resources();
    }
}
