<?php

namespace Jabe\Impl\Identity\Db;

use Jabe\Authorization\{
    PermissionInterface,
    Permissions,
    ResourceInterface,
    Resources
};
use Jabe\Identity\{
    GroupInterface,
    GroupQueryInterface,
    NativeUserQueryInterface,
    TenantInterface,
    TenantQueryInterface,
    UserInterface,
    UserQueryInterface
};
use Jabe\Impl\{
    AbstractQuery,
    NativeUserQueryImpl,
    UserQueryImp
};
use Jabe\Impl\Context\Context;
use Jabe\Impl\Identity\ReadOnlyIdentityProviderInterface;
use Jabe\Impl\Interceptor\CommandContext;
use Jabe\Impl\Persistence\AbstractManager;
use Jabe\Impl\Persistence\Entity\{
    GroupEntity,
    TenantEntity,
    UserEntity
};
use Jabe\Impl\Util\EncryptionUtil;

class DbReadOnlyIdentityServiceProvider extends AbstractManager implements ReadOnlyIdentityProviderInterface
{
    public function __construct(...$args)
    {
        parent::__construct(...$args);
    }

    // users /////////////////////////////////////////
    public function findUserById(?string $userId): ?UserEntity
    {
        $this->checkAuthorization(Permissions::read(), Resources::user(), $userId);
        return $this->getDbEntityManager()->selectById(UserEntity::class, $userId);
    }

    public function createUserQuery(CommandContext $commandContext = null): UserQueryInterface
    {
        if ($commandContext === null) {
            return new DbUserQueryImpl(Context::getProcessEngineConfiguration()->getCommandExecutorTxRequired());
        } else {
            return new DbUserQueryImpl();
        }
    }

    public function createNativeUserQuery(): NativeUserQueryInterface
    {
        return new NativeUserQueryImpl(Context::getProcessEngineConfiguration()->getCommandExecutorTxRequired());
    }

    public function findUserCountByQueryCriteria(DbUserQueryImpl $query): int
    {
        $this->configureQuery($query, Resources::user());
        return $this->getDbEntityManager()->selectOne("selectUserCountByQueryCriteria", $query);
    }

    public function findUserByQueryCriteria(DbUserQueryImpl $query): array
    {
        $this->configureQuery($query, Resources::user());
        return $this->getDbEntityManager()->selectList("selectUserByQueryCriteria", $query);
    }

    public function findUserByNativeQuery(array $parameterMap, int $firstResult, int $maxResults): array
    {
        return $this->getDbEntityManager()->selectListWithRawParameter("selectUserByNativeQuery", $parameterMap, $firstResult, $maxResults);
    }

    public function findUserCountByNativeQuery(array $parameterMap): int
    {
        return $this->getDbEntityManager()->selectOne("selectUserCountByNativeQuery", $parameterMap);
    }

    public function checkPassword(?string $userId, ?string $password): bool
    {
        $user = $this->findUserById($userId);
        if (($user !== null) && ($password !== null) && $this->matchPassword($password, $user)) {
            return true;
        } else {
            return false;
        }
    }

    protected function matchPassword(?string $password, UserEntity $user): bool
    {
        $saltedPassword = EncryptionUtil::saltPassword($password, $user->getSalt());
        return Context::getProcessEngineConfiguration()
            ->getPasswordManager()
            ->check($saltedPassword, $user->getPassword());
    }

    // groups //////////////////////////////////////////

    public function findGroupById(?string $groupId): ?GroupEntity
    {
        $this->checkAuthorization(Permissions::read(), Resources::group(), $groupId);
        return $this->getDbEntityManager()->selectById(GroupEntity::class, $groupId);
    }

    public function createGroupQuery(CommandContext $commandContext = null): GroupQueryInterface
    {
        if ($commandContext === null) {
            return new DbGroupQueryImpl(Context::getProcessEngineConfiguration()->getCommandExecutorTxRequired());
        }
        return new DbGroupQueryImpl();
    }

    public function findGroupCountByQueryCriteria(DbGroupQueryImpl $query): int
    {
        $this->configureQuery($query, Resources::group());
        return $this->getDbEntityManager()->selectOne("selectGroupCountByQueryCriteria", $query);
    }

    public function findGroupByQueryCriteria(DbGroupQueryImpl $query): array
    {
        $this->configureQuery($query, Resources::group());
        return $this->getDbEntityManager()->selectList("selectGroupByQueryCriteria", $query);
    }

    //tenants //////////////////////////////////////////

    public function findTenantById(?string $tenantId): TenantEntity
    {
        $this->checkAuthorization(Permissions::read(), Resources::tenant(), $tenantId);
        return $this->getDbEntityManager()->selectById(TenantEntity::class, $tenantId);
    }

    public function createTenantQuery(CommandContext $commandContext = null): TenantQueryInterface
    {
        if ($commandContext === null) {
            return new DbTenantQueryImpl(Context::getProcessEngineConfiguration()->getCommandExecutorTxRequired());
        }
        return new DbTenantQueryImpl();
    }

    public function findTenantCountByQueryCriteria(DbTenantQueryImpl $query): int
    {
        $this->configureQuery($query, Resources::tenant());
        return $this->getDbEntityManager()->selectOne("selectTenantCountByQueryCriteria", $query);
    }

    public function findTenantByQueryCriteria(DbTenantQueryImpl $query): array
    {
        $this->configureQuery($query, Resources::tenant());
        return $this->getDbEntityManager()->selectList("selectTenantByQueryCriteria", $query);
    }

    //memberships //////////////////////////////////////////
    protected function existsMembership(?string $userId, ?string $groupId): bool
    {
        $key = [];
        $key["userId"] = $userId;
        $key["groupId"] = $groupId;
        return $this->getDbEntityManager()->selectOne("selectMembershipCount", $key) > 0;
    }

    protected function existsTenantMembership(?string $tenantId, ?string $userId, ?string $groupId): bool
    {
        $key = [];
        $key["tenantId"] = $tenantId;
        if (!empty($userId)) {
            $key["userId"] = $userId;
        }
        if (!empty($groupId)) {
            $key["groupId"] = $groupId;
        }
        return $this->getDbEntityManager()->selectOne("selectTenantMembershipCount", $key) > 0;
    }

    public function configureQuery($query, ?ResourceInterface $resource = null, ?string $queryParam = "RES.ID_", ?PermissionInterface $permission = null)
    {
        Context::getCommandContext()
            ->getAuthorizationManager()
            ->configureQuery($query, $resource);
    }

    protected function checkAuthorization(PermissionInterface $permission, ResourceInterface $resource, ?string $resourceId): void
    {
        Context::getCommandContext()
                ->getAuthorizationManager()
                ->checkAuthorization($permission, $resource, $resourceId);
    }
}
