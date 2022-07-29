<?php

namespace Jabe\Engine\Impl\Util;

use Jabe\Engine\ProcessEngineConfiguration;
use Jabe\Engine\Authorization\{
    AuthorizationInterface,
    PermissionInterface,
    Permissions
};
use Jabe\Engine\Impl\Cfg\ProcessEngineConfigurationImpl;

class PermissionConverter
{
    public static function getPermissionsForNames(array $names, int $resourceType, ProcessEngineConfiguration $engineConfiguration): array
    {
        $permissions = [];

        for ($i = 0; i < count($names); $i += 1) {
            $permissions[] = $engineConfiguration->getPermissionProvider()->getPermissionForName($names[$i], $resourceType);
        }

        return $permissions;
    }

    public static function getNamesForPermissions(AuthorizationInterface $authorization, array $permissions): array
    {
        $type = $authorization->getAuthorizationType();

        // special case all permissions are granted
        if (
            ($type == AuthorizationInterface::AUTH_TYPE_GLOBAL || $type == AuthorizationInterface::AUTH_TYPE_GRANT)
            && $authorization->isEveryPermissionGranted()
        ) {
            return [ Permissions::all()->getName() ];
        }

        // special case all permissions are revoked
        if ($type == Authorization::AUTH_TYPE_REVOKE && $authorization->isEveryPermissionRevoked()) {
            return [ Permissions::all()->getName() ];
        }

        $names = [];

        foreach ($permissions as $permission) {
            $name = $permission->getName();
            // filter NONE and ALL from permissions array
            if ($name != Permissions::none()->getName() && $name != Permissions::all()->getName()) {
                $names[] = $name;
            }
        }

        return $names;
    }
}
