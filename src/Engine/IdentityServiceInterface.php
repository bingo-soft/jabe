<?php

namespace Jabe\Engine;

use Jabe\Engine\Authorization\{
    Permissions,
    Resources
};
use Jabe\Engine\Identity\{
    GroupInterface,
    GroupQueryInterface,
    NativeUserQueryInterface,
    PasswordPolicyInterface,
    PasswordPolicyResultInterface,
    TenantInterface,
    TenantQueryInterface,
    UserInterface,
    UserQueryInterface
};
use Jabe\Engine\Impl\Identity\Authentication;

interface IdentityServiceInterface
{
    public function isReadOnly(): bool;

    public function newUser(string $userId): UserInterface;

    public function saveUser(UserInterface $user, bool $skipPasswordPolicy = false): void;

    public function createUserQuery(): UserQueryInterface;

    public function deleteUser(string $userId): void;

    public function unlockUser(string $userId): void;

    public function newGroup(string $groupId): GroupInterface;

    public function createNativeUserQuery(): NativeUserQueryInterface;

    public function createGroupQuery(): GroupQueryInterface;

    public function saveGroup(GroupInterface $group): void;

    public function deleteGroup(string $groupId): void;

    public function createMembership(string $userId, string $groupId): void;

    public function deleteMembership(string $userId, string $groupId): void;

    public function newTenant(string $tenantId): TenantInterface;

    public function createTenantQuery(): TenantQueryInterface;

    public function saveTenant(TenantInterface $tenant): void;

    public function deleteTenant(string $tenantId): void;

    public function createTenantUserMembership(string $tenantId, string $userId): void;

    public function createTenantGroupMembership(string $tenantId, string $groupId): void;

    public function deleteTenantUserMembership(string $tenantId, string $userId): void;

    public function deleteTenantGroupMembership(string $tenantId, string $groupId): void;

    public function checkPassword(string $userId, string $password): bool;

    public function checkPasswordAgainstPolicy(
        ?PasswordPolicyResultInterface $policy,
        string $candidatePassword,
        ?UserInterface $user
    ): PasswordPolicyResultInterface;

    public function getPasswordPolicy(): PasswordPolicyInterface;

    public function setAuthentication(string $userId, array $groups, ?array $tenantIds = null): void;

    public function getCurrentAuthentication(): ?Authentication;

    public function clearAuthentication(): void;

    public function setUserInfo(string $userId, string $key, string $value): void;

    public function getUserInfo(string $userId, string $key): ?string;

    public function getUserInfoKeys(string $userId): array;

    public function deleteUserInfo(string $userId, string $key): void;
}
