<?php

namespace Jabe\Authorization;

/**
 * The set of built-in Permissions for Resources::HISTORIC_PROCESS_INSTANCE.
 */
class HistoricProcessInstancePermissions implements PermissionInterface
{
    use PermissionTrait;

    private static $NONE;

    public static function none(): PermissionInterface
    {
        if (self::$NONE === null) {
            self::$NONE = new HistoricProcessInstancePermissions("NONE", 0);
        }
        return self::$NONE;
    }

    private static $ALL;

    public static function all(): PermissionInterface
    {
        if (self::$ALL === null) {
            self::$ALL = new HistoricProcessInstancePermissions("ALL", PHP_INT_MAX);
        }
        return self::$ALL;
    }

    private static $READ;

    public static function read(): PermissionInterface
    {
        if (self::$READ === null) {
            self::$READ = new HistoricProcessInstancePermissions("READ", 2);
        }
        return self::$READ;
    }

    private static $RESOURCES;

    public static function resources(): array
    {
        if (self::$RESOURCES === null) {
            self::$RESOURCES = [ Resources::historicProcessInstance() ];
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
