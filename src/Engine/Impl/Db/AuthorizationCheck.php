<?php

namespace BpmPlatform\Engine\Impl\Db;

use BpmPlatform\Engine\Authorization\Permissions;

class AuthorizationCheck implements \Serializable
{
    /**
     * If true authorization check is enabled. for This switch is
     * useful when implementing a query which may perform an authorization check
     * only under certain circumstances.
     */
    protected $isAuthorizationCheckEnabled = false;

    /**
     * If true authorization check is performed.
     */
    protected $shouldPerformAuthorizatioCheck = false;

    /**
     * Indicates if the revoke authorization checks are enabled or not.
     * The authorization checks without checking revoke permissions are much more faster.
     */
    protected $isRevokeAuthorizationCheckEnabled = false;

    /** the id of the user to check permissions for */
    protected $authUserId;

    /** the ids of the groups to check permissions for */
    protected $authGroupIds = [];

    /** the default permissions to use if no matching authorization
     * can be found.*/
    protected $authDefaultPerm = Permissions::ALL;

    protected $permissionChecks;

    protected $historicInstancePermissionsEnabled = false;

    protected $useLeftJoin = true;

    public function __construct(string $authUserId, array $authGroupIds, CompositePermissionCheck $permissionCheck, bool $isRevokeAuthorizationCheckEnabled)
    {
        $this->authUserId = $authUserId;
        $this->authGroupIds = $authGroupIds;
        $this->permissionChecks = $permissionCheck;
        $this->isRevokeAuthorizationCheckEnabled = $isRevokeAuthorizationCheckEnabled;
    }

    public function serialize()
    {
        return json_encode([
            'authUserId' => $this->authUserId,
            'authGroupIds' => $this->authGroupIds,
            'historicInstancePermissionsEnabled' => $this->historicInstancePermissionsEnabled,
            'useLeftJoin' => $this->useLeftJoin,
            'isRevokeAuthorizationCheckEnabled' => $this->isRevokeAuthorizationCheckEnabled
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->authUserId = $json->authUserId;
        $this->authGroupIds = $json->authGroupIds;
        $this->historicInstancePermissionsEnabled = $json->historicInstancePermissionsEnabled;
        $this->useLeftJoin = $json->useLeftJoin;
        $this->isRevokeAuthorizationCheckEnabled = $json->isRevokeAuthorizationCheckEnabled;
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

    public function getAuthUserId(): string
    {
        return $this->authUserId;
    }

    public function setAuthUserId(string $authUserId): void
    {
        $this->authUserId = $authUserId;
    }

    public function getAuthGroupIds(): array
    {
        return $this->authGroupIds;
    }

    public function setAuthGroupIds(array $authGroupIds): void
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
