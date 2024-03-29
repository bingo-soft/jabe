<?php

namespace Jabe\Impl\Db;

use Jabe\Authorization\Permissions;

class AuthorizationCheck
{
    /**
     * If true authorization check is enabled. for This switch is
     * useful when implementing a query which may perform an authorization check
     * only under certain circumstances.
     */
    protected bool $isAuthorizationCheckEnabled = false;

    /**
     * If true authorization check is performed.
     */
    protected bool $shouldPerformAuthorizatioCheck = false;

    /**
     * Indicates if the revoke authorization checks are enabled or not.
     * The authorization checks without checking revoke permissions are much more faster.
     */
    protected bool $isRevokeAuthorizationCheckEnabled = false;

    /** the id of the user to check permissions for */
    protected $authUserId;

    /** the ids of the groups to check permissions for */
    protected $authGroupIds = [];

    /** the default permissions to use if no matching authorization
     * can be found.*/
    protected $authDefaultPerm;

    protected $permissionChecks;

    protected bool $historicInstancePermissionsEnabled = false;

    protected bool $useLeftJoin = true;

    public function __construct(?string $authUserId = null, ?array $authGroupIds = [], ?CompositePermissionCheck $permissionCheck = null, ?bool $isRevokeAuthorizationCheckEnabled = false)
    {
        $this->authUserId = $authUserId;
        $this->authGroupIds = $authGroupIds;
        $this->authDefaultPerm = Permissions::all();
        $this->permissionChecks = $permissionCheck;
        $this->isRevokeAuthorizationCheckEnabled = $isRevokeAuthorizationCheckEnabled;
    }

    public function __serialize(): array
    {
        return [
            'authUserId' => $this->authUserId,
            'authGroupIds' => $this->authGroupIds,
            'historicInstancePermissionsEnabled' => $this->historicInstancePermissionsEnabled,
            'useLeftJoin' => $this->useLeftJoin,
            'isRevokeAuthorizationCheckEnabled' => $this->isRevokeAuthorizationCheckEnabled
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->authUserId = $data['authUserId'];
        $this->authGroupIds = $data['authGroupIds'];
        $this->historicInstancePermissionsEnabled = $data['historicInstancePermissionsEnabled'];
        $this->useLeftJoin = $data['useLeftJoin'];
        $this->isRevokeAuthorizationCheckEnabled = $data['isRevokeAuthorizationCheckEnabled'];
    }

    // getters / setters /////////////////////////////////////////

    public function isAuthorizationCheckEnabled(): bool
    {
        return $this->isAuthorizationCheckEnabled;
    }

    public function getIsAuthorizationCheckEnabled(): bool
    {
        return $this->isAuthorizationCheckEnabled;
    }

    public function setAuthorizationCheckEnabled(bool $isAuthorizationCheckPerformed): void
    {
        $this->isAuthorizationCheckEnabled = $isAuthorizationCheckPerformed;
    }

    public function shouldPerformAuthorizatioCheck(): bool
    {
        return $this->shouldPerformAuthorizatioCheck;
    }

    /** is used by myBatis */
    public function getShouldPerformAuthorizatioCheck(): bool
    {
        return $this->isAuthorizationCheckEnabled && !$this->isPermissionChecksEmpty();
    }

    public function setShouldPerformAuthorizatioCheck(bool $shouldPerformAuthorizatioCheck): void
    {
        $this->shouldPerformAuthorizatioCheck = $shouldPerformAuthorizatioCheck;
    }

    protected function isPermissionChecksEmpty(): bool
    {
        return empty($this->permissionChecks->getAtomicChecks()) && empty($this->permissionChecks->getCompositeChecks());
    }

    public function getAuthUserId(): ?string
    {
        return $this->authUserId;
    }

    public function setAuthUserId(?string $authUserId): void
    {
        $this->authUserId = $authUserId;
    }

    public function getAuthGroupIds(): array
    {
        return $this->authGroupIds;
    }

    public function setAuthGroupIds(?array $authGroupIds = []): void
    {
        $this->authGroupIds = $authGroupIds;
    }

    public function getAuthDefaultPerm(): int
    {
        return $this->authDefaultPerm;
    }

    public function setAuthDefaultPerm(int $authDefaultPerm): void
    {
        $this->authDefaultPerm = $authDefaultPerm;
    }

    // authorization check parameters

    public function getPermissionChecks(): CompositePermissionCheck
    {
        return $this->permissionChecks;
    }

    public function clearPermissionChecks(): void
    {
        //@TODO, will not be null after fix
        if ($this->permissionChecks !== null) {
            $this->permissionChecks->clear();
        }
    }

    public function setAtomicPermissionChecks(array $permissionChecks): void
    {
        $this->permissionChecks->setAtomicChecks($permissionChecks);
    }

    public function addAtomicPermissionCheck(PermissionCheck $permissionCheck): void
    {
        $this->permissionChecks->addAtomicCheck($permissionCheck);
    }

    public function setPermissionChecks(CompositePermissionCheck $permissionChecks): void
    {
        $this->permissionChecks = $permissionChecks;
    }

    public function isRevokeAuthorizationCheckEnabled(): bool
    {
        return $this->isRevokeAuthorizationCheckEnabled;
    }

    public function setRevokeAuthorizationCheckEnabled(bool $isRevokeAuthorizationCheckEnabled): void
    {
        $this->isRevokeAuthorizationCheckEnabled = $isRevokeAuthorizationCheckEnabled;
    }

    public function setHistoricInstancePermissionsEnabled(bool $historicInstancePermissionsEnabled): void
    {
        $this->historicInstancePermissionsEnabled = $historicInstancePermissionsEnabled;
    }

    /**
     * Used in SQL mapping
     */
    public function isHistoricInstancePermissionsEnabled(): bool
    {
        return $this->historicInstancePermissionsEnabled;
    }

    public function isUseLeftJoin(): bool
    {
        return $this->useLeftJoin;
    }

    public function setUseLeftJoin(bool $useLeftJoin): void
    {
        $this->useLeftJoin = $useLeftJoin;
    }
}
