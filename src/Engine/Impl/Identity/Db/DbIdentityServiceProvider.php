<?php

namespace Jabe\Engine\Impl\Identity\Db;

use Jabe\Engine\Authorization\{
    Permissions,
    Resources
};
use Jabe\Engine\Identity\{
    GroupInterface,
    TenantInterface,
    UserInterface
};
use Jabe\Engine\Impl\ProcessEngineLogger;
use Jabe\Engine\Impl\Cfg\ProcessEngineConfigurationImpl;
use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Identity\{
    IdentityOperationResult,
    IndentityLogger,
    WritableIdentityProviderInterface
};
use Jabe\Engine\Impl\Persistence\Entity\{
    GroupEntity,
    MembershipEntity,
    TenantEntity,
    TenantMembershipEntity,
    UserEntity
};
use Jabe\Engine\Impl\Util\{
    ClockUtil,
    EnsureUtil
};

class DbIdentityServiceProvider extends DbReadOnlyIdentityServiceProvider implements WritableIdentityProviderInterface
{
    //protected static final IndentityLogger LOG = ProcessEngineLogger.INDENTITY_LOGGER;

    // users ////////////////////////////////////////////////////////

    public function createNewUser(string $userId): UserEntity
    {
        $this->checkAuthorization(Permissions::create(), Resources::user(), null);
        return new UserEntity($userId);
    }

    public function saveUser(UserInterface $user): IdentityOperationResult
    {
        $userEntity = $user;

        // encrypt password
        $userEntity->encryptPassword();

        $operation = null;
        if ($userEntity->getRevision() == 0) {
            $operation = IdentityOperationResult::OPERATION_CREATE;
            $this->checkAuthorization(Permissions::create(), Resources::user(), null);
            $this->getDbEntityManager()->insert($userEntity);
            $this->createDefaultAuthorizations($userEntity);
        } else {
            $operation = IdentityOperationResult::OPERATION_UPDATE;
            $this->checkAuthorization(Permissions::update(), Resources::user(), $user->getId());
            $this->getDbEntityManager()->merge($userEntity);
        }

        return new IdentityOperationResult($userEntity, $operation);
    }

    public function deleteUser(string $userId): IdentityOperationResult
    {
        $this->checkAuthorization(Permissions::delete(), Resources::user(), $userId);
        $user = $this->findUserById($userId);
        if ($user !== null) {
            $this->deleteMembershipsByUserId($userId);
            $this->deleteTenantMembershipsOfUser($userId);

            $this->deleteAuthorizations(Resources::user(), $userId);

            Context::getCommandContext()->runWithoutAuthorization(function () use ($scope, $userId) {
                $tenants = $scope->createTenantQuery()->userMember($userId)->list();
                if (!empty($tenants)) {
                    foreach ($tenants as $tenant) {
                        $scope->deleteAuthorizationsForUser(Resources::tenant(), $tenant->getId(), $userId);
                    }
                }
                return null;
            });

            $this->getDbEntityManager()->delete($user);
            return new IdentityOperationResult(null, IdentityOperationResult::OPERATION_DELETE);
        }
        return new IdentityOperationResult(null, IdentityOperationResult::OPERATION_NONE);
    }

    public function checkPassword(string $userId, string $password): bool
    {
        $user = $this->findUserById($userId);
        if ($user == null || empty($password)) {
            return false;
        }

        if ($this->isUserLocked($user)) {
            return false;
        }

        if ($this->matchPassword($password, $user)) {
            $this->unlockUser($user);
            return true;
        } else {
            $this->lockUser($user);
            return false;
        }
    }

    protected function isUserLocked(UserEntity $user): bool
    {
        $processEngineConfiguration = Context::getProcessEngineConfiguration();

        $maxAttempts = $processEngineConfiguration->getLoginMaxAttempts();
        $attempts = $user->getAttempts();

        if ($attempts >= $maxAttempts) {
            return true;
        }

        $lockExpirationTime = $user->getLockExpirationTime();
        $currentTime = ClockUtil::getCurrentTime();

        return $lockExpirationTime !== null && (new \DateTime($lockExpirationTime)) > $currentTime;
    }

    protected function lockUser(UserEntity $user): void
    {
        $processEngineConfiguration = Context::getProcessEngineConfiguration();

        $max = $processEngineConfiguration->getLoginDelayMaxTime();
        $baseTime = $processEngineConfiguration->getLoginDelayBase();
        $factor = $processEngineConfiguration->getLoginDelayFactor();
        $attempts = $user->getAttempts() + 1;

        $delay = $baseTime * pow($factor, $attempts - 1);
        $delay = min($delay, $max);

        $currentTime = ClockUtil::getCurrentTime()->getTimestamp();
        $lockExpirationTime = (new \DateTime())->setTimestamp($currentTime + $delay);

        if ($attempts >= $processEngineConfiguration->getLoginMaxAttempts()) {
            //LOG.infoUserPermanentlyLocked(user.getId());
        } else {
            //LOG.infoUserTemporarilyLocked(user.getId(), lockExpirationTime);
        }

        $this->getIdentityInfoManager()->updateUserLock($user, $attempts, $lockExpirationTime);
    }

    public function unlockUser($userOrUserId): IdentityOperationResult
    {
        if (is_string($userOrUserId)) {
            $user = $this->findUserById($userOrUserId);
            if ($user !== null) {
                return $this->unlockUser($user);
            }
            return new IdentityOperationResult(null, IdentityOperationResult::OPERATION_NONE);
        } elseif ($userOrUserId instanceof UserEntity) {
            if ($user->getAttempts() > 0 || $user->getLockExpirationTime() !== null) {
                $this->getIdentityInfoManager()->updateUserLock($user, 0, null);
                return new IdentityOperationResult($user, IdentityOperationResult::OPERATION_UNLOCK);
            }
            return new IdentityOperationResult($user, IdentityOperationResult::OPERATION_NONE);
        }
    }

    // groups ////////////////////////////////////////////////////////

    public function createNewGroup(string $groupId): GroupEntity
    {
        $this->checkAuthorization(Permissions::create(), Resources::group(), null);
        return new GroupEntity($groupId);
    }

    public function saveGroup(GroupInterface $group): IdentityOperationResult
    {
        $groupEntity = $group;
        $operation = null;
        if ($groupEntity->getRevision() == 0) {
            $operation = IdentityOperationResult::OPERATION_CREATE;
            $this->checkAuthorization(Permissions::create(), Resources::group(), null);
            $this->getDbEntityManager()->insert($groupEntity);
            $this->createDefaultAuthorizations($group);
        } else {
            $operation = IdentityOperationResult::OPERATION_UPDATE;
            $this->checkAuthorization(Permissions::update(), Resources::group(), $group->getId());
            $this->getDbEntityManager()->merge($groupEntity);
        }
        return new IdentityOperationResult($groupEntity, $operation);
    }

    public function deleteGroup(string $groupId): IdentityOperationResult
    {
        $this->checkAuthorization(Permissions::delete(), Resources::group(), $groupId);
        $group = $this->findGroupById($groupId);
        if ($group !== null) {
            $this->deleteMembershipsByGroupId($groupId);
            $this->deleteTenantMembershipsOfGroup($groupId);

            $this->deleteAuthorizations(Resources::group(), $groupId);

            $scope = $this;
            Context::getCommandContext()->runWithoutAuthorization(function () use ($scope, $groupId) {
                $tenants = $scope->createTenantQuery()->groupMember($groupId)->list();
                if (!empty($tenants)) {
                    foreach ($tenants as $tenant) {
                        $this->deleteAuthorizationsForGroup(Resources::tenant(), $tenant->getId(), $groupId);
                    }
                }
                return null;
            });
            $this->getDbEntityManager()->delete(group);
            return new IdentityOperationResult(null, IdentityOperationResult::OPERATION_DELETE);
        }
        return new IdentityOperationResult(null, IdentityOperationResult::OPERATION_NONE);
    }

    // tenants //////////////////////////////////////////////////////

    public function createNewTenant(string $tenantId): TenantInterface
    {
        $this->checkAuthorization(Permissions::create(), Resources::tenant(), null);
        return new TenantEntity($tenantId);
    }

    public function saveTenant(TenantInterface $tenant): IdentityOperationResult
    {
        $tenantEntity = $tenant;
        $operation = null;
        if ($tenantEntity->getRevision() == 0) {
            $operation = IdentityOperationResult::OPERATION_CREATE;
            $this->checkAuthorization(Permissions::create(), Resources::tenant(), null);
            $this->getDbEntityManager()->insert($tenantEntity);
            $this->createDefaultAuthorizations($tenant);
        } else {
            $operation = IdentityOperationResult::OPERATION_UPDATE;
            $this->checkAuthorization(Permissions::update(), Resources::tenant(), $tenant->getId());
            $this->getDbEntityManager()->merge($tenantEntity);
        }
        return new IdentityOperationResult($tenantEntity, $operation);
    }

    public function deleteTenant(string $tenantId): IdentityOperationResult
    {
        $this->checkAuthorization(Permissions::delete(), Resources::tenant(), $tenantId);
        $tenant = $this->findTenantById($tenantId);
        if ($tenant !== null) {
            $this->deleteTenantMembershipsOfTenant($tenantId);
            $this->deleteAuthorizations(Resources::tenant(), $tenantId);
            $this->getDbEntityManager()->delete($tenant);
            return new IdentityOperationResult(null, IdentityOperationResult::OPERATION_DELETE);
        }
        return new IdentityOperationResult(null, IdentityOperationResult::OPERATION_NONE);
    }

    // membership //////////////////////////////////////////////////////

    public function createMembership(string $userId, string $groupId): IdentityOperationResult
    {
        $this->checkAuthorization(Permissions::create(), Resources::groupMembership(), $groupId);
        $user = $this->findUserById($userId);
        EnsureUtil::ensureNotNull("No user found with id '" . $userId . "'.", "user", $user);
        $group = $this->findGroupById($groupId);
        EnsureUtil::ensureNotNull("No group found with id '" . $groupId . "'.", "group", $group);
        $membership = new MembershipEntity();
        $membership->setUser($user);
        $membership->setGroup($group);
        $this->getDbEntityManager()->insert($membership);
        $this->createDefaultMembershipAuthorizations($userId, $groupId);
        return new IdentityOperationResult(null, IdentityOperationResult::OPERATION_CREATE);
    }

    public function deleteMembership(string $userId, string $groupId): IdentityOperationResult
    {
        $this->checkAuthorization(Permissions::delete(), Resources::groupMembership(), $groupId);
        if ($this->existsMembership($userId, $groupId)) {
            $this->deleteAuthorizations(Resources::groupMembership(), $groupId);
            $parameters = [];
            $parameters["userId"] = $userId;
            $parameters["groupId"] = $groupId;
            $this->getDbEntityManager()->delete(MembershipEntity::class, "deleteMembership", $parameters);
            return new IdentityOperationResult(null, IdentityOperationResult::OPERATION_DELETE);
        }
        return new IdentityOperationResult(null, IdentityOperationResult::OPERATION_NONE);
    }

    protected function deleteMembershipsByUserId(string $userId): void
    {
        $this->getDbEntityManager()->delete(MembershipEntity::class, "deleteMembershipsByUserId", $userId);
    }

    protected function deleteMembershipsByGroupId(string $groupId): void
    {
        $this->getDbEntityManager()->delete(MembershipEntity::class, "deleteMembershipsByGroupId", $groupId);
    }

    public function createTenantUserMembership(string $tenantId, string $userId): IdentityOperationResult
    {
        $this->checkAuthorization(Permissions::create(), Resources::tenantMembership(), $tenantId);

        $tenant = $this->findTenantById($tenantId);
        $user = $this->findUserById($userId);

        EnsureUtil::ensureNotNull("No tenant found with id '" . $tenantId . "'.", "tenant", $tenant);
        EnsureUtil::ensureNotNull("No user found with id '" . $userId . "'.", "user", $user);

        $membership = new TenantMembershipEntity();
        $membership->setTenant($tenant);
        $membership->setUser($user);

        $this->getDbEntityManager()->insert($membership);

        $this->createDefaultTenantMembershipAuthorizations($tenant, $user);
        return new IdentityOperationResult(null, IdentityOperationResult::OPERATION_CREATE);
    }

    public function createTenantGroupMembership(string $tenantId, string $groupId): IdentityOperationResult
    {
        $this->checkAuthorization(Permissions::create(), Resources::tenantMembership(), $tenantId);

        $tenant = $this->findTenantById($tenantId);
        $group = $this->findGroupById($groupId);

        EnsureUtil::ensureNotNull("No tenant found with id '" . $tenantId . "'.", "tenant", $tenant);
        EnsureUtil::ensureNotNull("No group found with id '" . $groupId . "'.", "group", $group);

        $membership = new TenantMembershipEntity();
        $membership->setTenant($tenant);
        $membership->setGroup($group);

        $this->getDbEntityManager()->insert($membership);

        $this->createDefaultTenantMembershipAuthorizations($tenant, $group);
        return new IdentityOperationResult(null, IdentityOperationResult::OPERATION_CREATE);
    }

    public function deleteTenantUserMembership(string $tenantId, string $userId): IdentityOperationResult
    {
        $this->checkAuthorization(Permissions::delete(), Resources::tenantMembership(), $tenantId);
        if ($this->existsTenantMembership($tenantId, $userId, null)) {
            $this->deleteAuthorizations(Resources::tenantMembership(), $userId);

            $this->deleteAuthorizationsForUser(Resources::tenant(), $tenantId, $userId);

            $parameters = [];
            $parameters["tenantId"] = $tenantId;
            $parameters["userId"] = $userId;
            $this->getDbEntityManager()->delete(TenantMembershipEntity::class, "deleteTenantMembership", $parameters);
            return new IdentityOperationResult(null, IdentityOperationResult::OPERATION_DELETE);
        }
        return new IdentityOperationResult(null, IdentityOperationResult::OPERATION_NONE);
    }

    public function deleteTenantGroupMembership(string $tenantId, string $groupId): IdentityOperationResult
    {
        $this->checkAuthorization(Permissions::delete(), Resources::tenantMembership(), $tenantId);

        if ($this->existsTenantMembership($tenantId, null, $groupId)) {
            $this->deleteAuthorizations(Resources::tenantMembership(), $groupId);

            $this->deleteAuthorizationsForGroup(Resources::tenant(), $tenantId, $groupId);

            $parameters = [];
            $parameters["tenantId"] = $tenantId;
            $parameters["groupId"] = $groupId;
            $this->getDbEntityManager()->delete(TenantMembershipEntity::class, "deleteTenantMembership", $parameters);
            return new IdentityOperationResult(null, IdentityOperationResult::OPERATION_DELETE);
        }
        return new IdentityOperationResult(null, IdentityOperationResult::OPERATION_NONE);
    }

    protected function deleteTenantMembershipsOfUser(string $userId): void
    {
        $this->getDbEntityManager()->delete(TenantMembershipEntity::class, "deleteTenantMembershipsOfUser", $userId);
    }

    protected function deleteTenantMembershipsOfGroup(string $groupId): void
    {
        $this->getDbEntityManager()->delete(TenantMembershipEntity::class, "deleteTenantMembershipsOfGroup", $groupId);
    }

    protected function deleteTenantMembershipsOfTenant(string $tenant): void
    {
        $this->getDbEntityManager()->delete(TenantMembershipEntity::class, "deleteTenantMembershipsOfTenant", $tenant);
    }

    // authorizations ////////////////////////////////////////////////////////////

    protected function createDefaultAuthorizations(/*UserEntity|GroupInterface|TenantInterface*/$data): void
    {
        if ($data instanceof UserEntity) {
            if (Context::getProcessEngineConfiguration()->isAuthorizationEnabled()) {
                $this->saveDefaultAuthorizations($this->getResourceAuthorizationProvider()->newUser($data));
            }
        } elseif ($data instanceof GroupInterface) {
            if ($this->AuthorizationEnabled()) {
                $this->saveDefaultAuthorizations($this->getResourceAuthorizationProvider()->newGroup($data));
            }
        } elseif ($data instanceof TenantInterface) {
            if ($this->AuthorizationEnabled()) {
                $this->saveDefaultAuthorizations($this->getResourceAuthorizationProvider()->newTenant($data));
            }
        }
    }

    protected function createDefaultMembershipAuthorizations(string $userId, string $groupId): void
    {
        if ($this->AuthorizationEnabled()) {
            $this->saveDefaultAuthorizations($this->getResourceAuthorizationProvider()->groupMembershipCreated($groupId, $userId));
        }
    }

    protected function createDefaultTenantMembershipAuthorizations(TenantInterface $tenant, /*UserInterface|GroupInterface*/$data): void
    {
        if ($this->AuthorizationEnabled()) {
            $this->saveDefaultAuthorizations($this->getResourceAuthorizationProvider()->tenantMembershipCreated($tenant, $data));
        }
    }
}
