<?php

namespace BpmPlatform\Engine\Impl\Identity;

use BpmPlatform\Engine\Identity\{
    GroupInterface,
    GroupQueryInterface,
    NativeUserQueryInterface,
    TenantInterface,
    TenantQueryInterface,
    UserInterface,
    UserQueryInterface
};
use BpmPlatform\Engine\Impl\Interceptor\{
    CommandContext,
    SessionInterface
};

interface ReadOnlyIdentityProviderInterface extends SessionInterface
{
    // users ////////////////////////////////////////

    /**
     * @return a {@link User} object for the given user id or null if no such user exists.
     * @throws IdentityProviderException in case an error occurs
     */
    public function findUserById(string $userId): ?UserInterface;

    /**
     * @return a {@link UserQuery} object which can be used for querying for users.
     * @throws IdentityProviderException in case an error occurs
     */
    public function createUserQuery(?CommandContext $commandContext = null): UserQueryInterface;

    /**
     * Creates a {@link NativeUserQuery} that allows to select users with native queries.
     * @return NativeUserQuery
     */
    public function createNativeUserQuery(): NativeUserQueryInterface;

    /**
     * @return 'true' if the password matches the
     * @throws IdentityProviderException in case an error occurs
     */
    public function checkPassword(string $userId, string $password): bool;

    // groups //////////////////////////////////////

    /**
     * @return a {@link Group} object for the given group id or null if no such group exists.
     * @throws IdentityProviderException in case an error occurs
     */
    public function findGroupById(string $groupId): ?GroupInterface;

    /**
     * @return a {@link GroupQuery} object which can be used for querying for groups.
     * @throws IdentityProviderException in case an error occurs
     */
    public function createGroupQuery(?CommandContext $commandContext = null): GroupQueryInterface;

    // tenants //////////////////////////////////////
    /**
     * @return a {@link Tenant} object for the given id or null if no such tenant
     *         exists.
     * @throws IdentityProviderException
     *           in case an error occurs
     */
    public function findTenantById(string $tenantId): TenantInterface;

    /**
     * @return a {@link TenantQuery} object which can be used for querying for
     *         tenants.
     * @throws IdentityProviderException
     *           in case an error occurs
     */
    public function createTenantQuery(?CommandContext $commandContext = null): TenantQueryInterface;
}
