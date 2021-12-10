<?php

namespace BpmPlatform\Engine\Authorization;

use BpmPlatform\Engine\Authorization\Exception\PermissionNotFound;

/**
 * The set of built-in Permissions for Resources::HISTORIC_PROCESS_INSTANCE.
 */
class HistoricProcessInstancePermissions implements PermissionInterface
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

    private const RESOURCES = [ Resources::HISTORIC_PROCESS_INSTANCE ];

    private function __construct(string $name, int $id)
    {
        $this->name = $name;
        $this->id = $id;
    }

    public function getTypes(): array
    {
        return self::RESOURCES;
    }
}
