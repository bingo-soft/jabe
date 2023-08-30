<?php

namespace Jabe\Impl\Persistence\Entity;

use Jabe\Impl\Util\{
    EnsureUtil,
    ResourceTypeUtil
};
use Jabe\Authorization\{
    AuthorizationInterface,
    PermissionInterface,
    Permissions,
    ResourceInterface
};
use Jabe\Impl\ProcessEngineLogger;
use Jabe\Impl\Db\{
    DbEntityInterface,
    EnginePersistenceLogger,
    HasDbReferencesInterface,
    HasDbRevisionInterface
};
use Jabe\Impl\Util\ClassNameUtil;

class AuthorizationEntity implements AuthorizationInterface, DbEntityInterface, HasDbReferencesInterface, HasDbRevisionInterface
{
    //protected static final EnginePersistenceLogger LOG = ProcessEngineLogger.PERSISTENCE_LOGGER;
    //private static final long serialVersionUID = 1L;

    protected $id;
    protected int $revision = 0;

    protected int $authorizationType = 0;
    protected int $permissions = 0;
    protected $userId;
    protected $groupId;
    protected $resourceType;
    protected $resourceId;
    protected $removalTime;
    protected $rootProcessInstanceId;

    private $cachedPermissions = [];

    public function __construct(?int $type = null)
    {
        $this->authorizationType = $type;

        if ($this->authorizationType == self::AUTH_TYPE_GLOBAL) {
            $this->userId = self::ANY;
        }

        $this->resetPermissions();
    }

    protected function resetPermissions(): void
    {
        $this->cachedPermissions = [];

        if ($this->authorizationType == self::AUTH_TYPE_GLOBAL) {
            $this->permissions = Permissions::none()->getValue();
        } elseif ($this->authorizationType == self::AUTH_TYPE_GRANT) {
            $this->permissions = Permissions::none()->getValue();
        } elseif ($this->authorizationType == self::AUTH_TYPE_REVOKE) {
            $this->permissions = Permissions::all()->getValue();
        } else {
            //throw LOG.engineAuthorizationTypeException(authorizationType, AUTH_TYPE_GLOBAL, AUTH_TYPE_GRANT, AUTH_TYPE_REVOKE);
        }
    }

    // grant / revoke methods ////////////////////////////

    public function addPermission(PermissionInterface $p): void
    {
        $this->cachedPermissions[] = $p;
        $this->permissions |= $p->getValue();
    }

    public function removePermission(PermissionInterface $p): void
    {
        $this->cachedPermissions[] = $p;
        $this->permissions &= ~($p->getValue());
    }

    public function isPermissionGranted(PermissionInterface $p): bool
    {
        if (self::AUTH_TYPE_REVOKE == $this->authorizationType) {
            //throw LOG.permissionStateException("isPermissionGranted", "REVOKE");
        }

        EnsureUtil::ensureNotNull("Authorization 'resourceType' cannot be null", "authorization.getResource()", $this->resourceType);

        if (!ResourceTypeUtil::resourceIsContainedInArray($this->resourceType, $p->getTypes())) {
            return false;
        }
        return ($this->permissions & $p->getValue()) == $p->getValue();
    }

    public function isPermissionRevoked(PermissionInterface $p): bool
    {
        if (self::AUTH_TYPE_GRANT == $this->authorizationType) {
            //throw LOG.permissionStateException("isPermissionRevoked", "GRANT");
        }

        EnsureUtil::ensureNotNull("Authorization 'resourceType' cannot be null", "authorization.getResource()", $this->resourceType);

        if (!ResourceTypeUtil::resourceIsContainedInArray($this->resourceType, $p->getTypes())) {
            return false;
        }
        return ($this->permissions & $p->getValue()) != $p->getValue();
    }

    public function isEveryPermissionGranted(): bool
    {
        if (self::AUTH_TYPE_REVOKE == $this->authorizationType) {
            //throw LOG.permissionStateException("isEveryPermissionGranted", "REVOKE");
        }
        return $this->permissions == Permissions::all()->getValue();
    }

    public function isEveryPermissionRevoked(): bool
    {
        if ($this->authorizationType == self::AUTH_TYPE_GRANT) {
            //throw LOG.permissionStateException("isEveryPermissionRevoked", "GRANT");
        }
        return $this->permissions == 0;
    }

    public function getPermissions(): int
    {
        return $this->permissions;
    }

    public function getPermissionsFromSupplied(array $permissions = []): array
    {
        $result = [];

        foreach ($permissions as $permission) {
            if (
                (self::AUTH_TYPE_GLOBAL == $this->authorizationType || self::AUTH_TYPE_GRANT == $this->authorizationType)
                && $this->isPermissionGranted($permission)
            ) {
                $result[] = $permission;
            } elseif (
                self::AUTH_TYPE_REVOKE == $this->authorizationType
                && $this->isPermissionRevoked($permission)
            ) {
                $result[] = $permission;
            }
        }
        return $result;
    }

    public function setPermissions(int $permissions): void
    {
        $this->permissions = $permissions;
    }

    public function setPermissionsFromSupplied(array $permissions): void
    {
        $this->resetPermissions();
        foreach ($permissions as $permission) {
            if (self::AUTH_TYPE_REVOKE == $this->authorizationType) {
                $this->removePermission($permission);
            } else {
                $this->addPermission($permission);
            }
        }
    }

    // getters setters ///////////////////////////////

    public function getAuthorizationType(): int
    {
        return $this->authorizationType;
    }

    public function setAuthorizationType(int $authorizationType): void
    {
        $this->authorizationType = $authorizationType;
    }

    public function getGroupId(): ?string
    {
        return $this->groupId;
    }

    public function setGroupId(?string $groupId): void
    {
        if ($groupId !== null && $this->authorizationType == self::AUTH_TYPE_GLOBAL) {
            //throw LOG.notUsableGroupIdForGlobalAuthorizationException();
        }
        $this->groupId = $groupId;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function setUserId(?string $userId): void
    {
        if ($userId !== null && $this->authorizationType == self::AUTH_TYPE_GLOBAL && self::ANY != $userId) {
            //throw LOG.illegalValueForUserIdException(userId, ANY);
        }
        $this->userId = $userId;
    }

    public function getResourceType(): int
    {
        return $this->resourceType;
    }

    public function setResourceType(int $type): void
    {
        $this->resourceType = $type;
    }

    public function getResource(): int
    {
        return $this->resourceType;
    }

    public function setResource(ResourceInterface $resource): void
    {
        $this->resourceType = $resource->resourceType();
    }

    public function getResourceId(): ?string
    {
        return $this->resourceId;
    }

    public function setResourceId(?string $resourceId): void
    {
        $this->resourceId = $resourceId;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    public function getRevision(): ?int
    {
        return $this->revision;
    }

    public function setRevision(int $revision): void
    {
        $this->revision = $revision;
    }

    public function getCachedPermissions(): array
    {
        return $this->cachedPermissions;
    }

    public function getRevisionNext(): int
    {
        return $this->revision + 1;
    }

    public function getPersistentState()
    {
        $state = [];
        $state["userId"] = $this->userId;
        $state["groupId"] = $this->groupId;
        $state["resourceType"] = $this->resourceType;
        $state["resourceId"] = $this->resourceId;
        $state["permissions"] = $this->permissions;
        $state["removalTime"] = $this->removalTime;
        $state["rootProcessInstanceId"] = $this->rootProcessInstanceId;
        return $state;
    }

    public function getRemovalTime(): ?string
    {
        return $this->removalTime;
    }

    public function setRemovalTime(?string $removalTime): void
    {
        $this->removalTime = $removalTime;
    }

    public function getRootProcessInstanceId(): ?string
    {
        return $this->rootProcessInstanceId;
    }

    public function setRootProcessInstanceId(?string $rootProcessInstanceId): void
    {
        $this->rootProcessInstanceId = $rootProcessInstanceId;
    }

    public function getReferencedEntityIds(): array
    {
        $referencedEntityIds = [];
        return $referencedEntityIds;
    }

    public function getReferencedEntitiesIdAndClass(): array
    {
        $referenceIdAndClass = [];
        return $referenceIdAndClass;
    }

    public function __serialize(): array
    {
        return [
            'id' => $this->id,
            'revision' => $this->revision,
            'authorizationType' => $this->authorizationType,
            'permissions' => $this->permissions,
            'userId' => $this->userId,
            'groupId' => $this->groupId,
            'resourceType' => $this->resourceType,
            'resourceId' => $this->resourceId
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->id = $data['id'];
        $this->revision = $data['revision'];
        $this->authorizationType = $data['authorizationType'];
        $this->permissions = $data['permissions'];
        $this->userId = $data['userId'];
        $this->groupId = $data['groupId'];
        $this->resourceType = $data['resourceType'];
        $this->resourceId = $data['resourceId'];
    }

    public function __toString()
    {
        $className = ClassNameUtil::getClassNameWithoutPackage(get_class($this));
        return $className
                . "[id=" . $this->id
                . ", revision=" . $this->revision
                . ", authorizationType=" . $this->authorizationType
                . ", permissions=" . $this->permissions
                . ", userId=" . $this->userId
                . ", groupId=" . $this->groupId
                . ", resourceType=" . $this->resourceType
                . ", resourceId=" . $this->resourceId
                . "]";
    }

    public function getDependentEntities(): array
    {
        return [];
    }
}
