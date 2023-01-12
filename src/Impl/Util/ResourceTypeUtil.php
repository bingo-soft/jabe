<?php

namespace Jabe\Impl\Util;

use Jabe\Authorization\{
    BatchPermissions,
    HistoricProcessInstancePermissions,
    HistoricTaskPermissions,
    OptimizePermissions,
    PermissionInterface,
    Permissions,
    ProcessDefinitionPermissions,
    ProcessInstancePermissions,
    ResourceInterface,
    Resources,
    SystemPermissions,
    UserOperationLogCategoryPermissions
};

class ResourceTypeUtil
{
    /**
     * A map containing all Resources as a key and
     * the respective Permission Enum class for this resource.<p>
     * NOTE: In case of new Permission Enum class, please adjust the map accordingly
     */
    protected static $PERMISSION_ENUMS = [];

    /**
     * @return bool true in case the resource with the provided resourceTypeId is contained by the specified list
     */
    public static function resourceIsContainedInArray(int $resourceTypeId, array $resources): bool
    {
        foreach ($resources as $resource) {
            if ($resourceTypeId == $resource->resourceType()) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return See ResourceTypeUtil#PERMISSION_ENUMS
     */
    public static function getPermissionEnums(): array
    {
        if (empty(self::$PERMISSION_ENUMS)) {
            self::$PERMISSION_ENUMS = [
                Resources::batch()->resourceType() => BatchPermissions::class,
                Resources::processDefinition()->resourceType() => ProcessDefinitionPermissions::class,
                Resources::processInstance()->resourceType() => ProcessInstancePermissions::class,
                Resources::task()->resourceType() => TaskPermissions::class,
                Resources::historicTask()->resourceType() => HistoricTaskPermissions::class,
                Resources::historicProcessInstance()->resourceType() => HistoricProcessInstancePermissions::class,
                Resources::operationLogCategory()->resourceType() => UserOperationLogCategoryPermissions::class,
                Resources::optimize()->resourceType() => OptimizePermissions::class,
                Resources::system()->resourceType() => SystemPermissions::class
            ];

            // the rest
            foreach (Permissions::values() as $permission) {
                if ($permission == Permissions::all() || $permission == Permissions::none()) {
                    continue;
                }
                foreach ($permission->getTypes() as $resource) {
                    $resourceType = $resource->resourceType();
                    if (!array_key_exists($resourceType, self::$PERMISSION_ENUMS)) {
                        self::$PERMISSION_ENUMS[$resourceType] = Permissions::class;
                    }
                }
            }
        }
        return self::$PERMISSION_ENUMS;
    }

    /**
     * Retrieves the Permission array based on the predifined {@link ResourceTypeUtil#PERMISSION_ENUMS PERMISSION_ENUMS}
     */
    public static function getPermissionsByResourceType(int $givenResourceType): array
    {
        $clazz = null;
        if (array_key_exists($givenResourceType, self::getPermissionEnums())) {
            $clazz = self::$PERMISSION_ENUMS[$givenResourceType];
        }
        if ($clazz === null) {
            return Permissions::values();
        }
        return $clazz::values();
    }

    /**
     * Currently used only in the Rest API
     * Returns a Permission based on the specified <code>permissionName</code> and <code>resourceType</code>
     * @throws BadUserRequestException in case the permission is not valid for the specified resource type
     */
    public static function getPermissionByNameAndResourceType(?string $permissionName, int $resourceType): PermissionInterface
    {
        foreach (self::getPermissionsByResourceType($resourceType) as $permission) {
            if ($permission->getName() == $permissionName) {
                return $permission;
            }
        }
        throw new BadUserRequestException(
            sprintf("The permission '%s' is not valid for '%s' resource type.", $permissionName, self::getResourceByType($resourceType))
        );
    }

    /**
     * Iterates over the Resources and
     * returns either the resource with specified <code>resourceType</code> or <code>null</code>.
     */
    public static function getResourceByType(int $resourceType): ?ResourceInterface
    {
        foreach (Resources::values() as $resource) {
            if ($resource->resourceType() == $resourceType) {
                return $resource;
            }
        }
        return null;
    }
}
