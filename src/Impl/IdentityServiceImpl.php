<?php

namespace Jabe\Impl;

use Jabe\{
    BadUserRequestException,
    IdentityServiceInterface,
    ProcessEngineException
};
use Jabe\Identity\{
    PasswordPolicyResultInterface,
    GroupInterface,
    GroupQueryInterface,
    NativeUserQueryInterface,
    PasswordPolicyInterface,
    PasswordPolicyRuleInterface,
    Picture,
    TenantInterface,
    TenantQueryInterface,
    UserInterface,
    UserQueryInterface
};
use Jabe\Impl\Cmd\{
    CheckPassword,
    GetPasswordPolicyCmd,
    CreateGroupCmd,
    CreateGroupQueryCmd,
    CreateMembershipCmd,
    CreateNativeUserQueryCmd,
    CreateTenantCmd,
    CreateTenantGroupMembershipCmd,
    CreateTenantQueryCmd,
    CreateTenantUserMembershipCmd,
    CreateUserCmd,
    CreateUserQueryCmd,
    DeleteGroupCmd,
    DeleteMembershipCmd,
    DeleteTenantCmd,
    DeleteTenantGroupMembershipCmd,
    DeleteTenantUserMembershipCmd,
    DeleteUserCmd,
    DeleteUserInfoCmd,
    DeleteUserPictureCmd,
    GetUserAccountCmd,
    GetUserInfoCmd,
    GetUserInfoKeysCmd,
    GetUserPictureCmd,
    IsIdentityServiceReadOnlyCmd,
    SaveGroupCmd,
    SaveTenantCmd,
    SaveUserCmd,
    SetUserInfoCmd,
    SetUserPictureCmd,
    UnlockUserCmd
};
use Jabe\Impl\Identity\{
    AccountInterface,
    Authentication,
    PasswordPolicyResultImpl
};
use Jabe\Impl\Persistence\Entity\IdentityInfoEntity;
use Jabe\Impl\Util\{
    EnsureUtil,
    ExceptionUtil
};

class IdentityServiceImpl extends ServiceImpl implements IdentityServiceInterface
{
    /** thread local holding the current authentication */
    private $currentAuthentication;

    public function isReadOnly(): bool
    {
        return $this->commandExecutor->execute(new IsIdentityServiceReadOnlyCmd());
    }

    public function newGroup(?string $groupId): GroupInterface
    {
        return $this->commandExecutor->execute(new CreateGroupCmd($groupId));
    }

    public function newUser(?string $userId): UserInterface
    {
        return $this->commandExecutor->execute(new CreateUserCmd($userId));
    }

    public function newTenant(?string $tenantId): TenantInterface
    {
        return $this->commandExecutor->execute(new CreateTenantCmd($tenantId));
    }

    public function saveGroup(GroupInterface $group): void
    {
        try {
            $this->commandExecutor->execute(new SaveGroupCmd($group));
        } catch (\Throwable $ex) {
            if (ExceptionUtil::checkConstraintViolationException($ex)) {
                throw new BadUserRequestException("The group already exists", $ex);
            }
            throw $ex;
        }
    }

    public function saveUser(UserInterface $user, bool $skipPasswordPolicy = false): void
    {
        try {
            $this->commandExecutor->execute(new SaveUserCmd($user, $skipPasswordPolicy));
        } catch (\Throwable $ex) {
            if (ExceptionUtil::checkConstraintViolationException($ex)) {
                throw new BadUserRequestException("The user already exists", $ex);
            }
            throw $ex;
        }
    }

    public function saveTenant(TenantInterface $tenant): void
    {
        try {
            $this->commandExecutor->execute(new SaveTenantCmd($tenant));
        } catch (\Throwable $ex) {
            if (ExceptionUtil::checkConstraintViolationException($ex)) {
                throw new BadUserRequestException("The tenant already exists", $ex);
            }
            throw $ex;
        }
    }

    public function createUserQuery(): UserQueryInterface
    {
        return $this->commandExecutor->execute(new CreateUserQueryCmd());
    }

    public function createNativeUserQuery(): NativeUserQueryInterface
    {
        return $this->commandExecutor->execute(new CreateNativeUserQueryCmd());
    }

    public function createGroupQuery(): GroupQueryInterface
    {
        return $this->commandExecutor->execute(new CreateGroupQueryCmd());
    }

    public function createTenantQuery(): TenantQueryInterface
    {
        return $this->commandExecutor->execute(new CreateTenantQueryCmd());
    }

    public function createMembership(?string $userId, ?string $groupId): void
    {
        $this->commandExecutor->execute(new CreateMembershipCmd($userId, $groupId));
    }

    public function deleteGroup(?string $groupId): void
    {
        $this->commandExecutor->execute(new DeleteGroupCmd($groupId));
    }

    public function deleteMembership(?string $userId, ?string $groupId): void
    {
        $this->commandExecutor->execute(new DeleteMembershipCmd($userId, $groupId));
    }

    public function checkPassword(?string $userId, ?string $password): bool
    {
        return $this->commandExecutor->execute(new CheckPassword($userId, $password));
    }

    public function checkPasswordAgainstPolicy(
        ?PasswordPolicyResultInterface $policy,
        ?string $candidatePassword,
        ?UserInterface $user
    ): PasswordPolicyResultInterface {
        $policy = $policy ?? $this->getPasswordPolicy();
        EnsureUtil::ensureNotNull("policy", "policy", $policy);
        EnsureUtil::ensureNotNull("password", "candidatePassword", $candidatePassword);

        $violatedRules = [];
        $fulfilledRules = [];

        foreach ($policy->getRules() as $rule) {
            if ($rule->execute($candidatePassword, $user)) {
                $fulfilledRules[] = $rule;
            } else {
                $violatedRules[] = $rule;
            }
        }
        return new PasswordPolicyResultImpl($violatedRules, $fulfilledRules);
    }

    public function getPasswordPolicy(): PasswordPolicyInterface
    {
        return $this->commandExecutor->execute(new GetPasswordPolicyCmd());
    }

    public function unlockUser(?string $userId): void
    {
        $this->commandExecutor->execute(new UnlockUserCmd($userId));
    }

    public function deleteUser(?string $userId): void
    {
        $this->commandExecutor->execute(new DeleteUserCmd($userId));
    }

    public function deleteTenant(?string $tenantId): void
    {
        $this->commandExecutor->execute(new DeleteTenantCmd($tenantId));
    }

    public function setUserPicture(?string $userId, Picture $picture): void
    {
        $this->commandExecutor->execute(new SetUserPictureCmd($userId, $picture));
    }

    public function getUserPicture(?string $userId): Picture
    {
        return $this->commandExecutor->execute(new GetUserPictureCmd($userId));
    }

    public function deleteUserPicture(?string $userId): void
    {
        $this->commandExecutor->execute(new DeleteUserPictureCmd($userId));
    }

    public function setAuthenticatedUserId(?string $authenticatedUserId): void
    {
        $this->currentAuthentication = new Authentication($authenticatedUserId, null);
    }

    public function setAuthentication(?string $userId, array $groups, ?array $tenantIds = null): void
    {
        $this->currentAuthentication = new Authentication($userId, $groups, $tenantIds);
    }

    public function clearAuthentication(): void
    {
        $this->currentAuthentication = null;
    }

    public function getCurrentAuthentication(): ?Authentication
    {
        return $this->currentAuthentication;
    }

    public function getUserInfo(?string $userId, ?string $key): ?string
    {
        return $this->commandExecutor->execute(new GetUserInfoCmd($userId, $key));
    }

    public function getUserInfoKeys(?string $userId): array
    {
        return $this->commandExecutor->execute(new GetUserInfoKeysCmd($userId, IdentityInfoEntity::TYPE_USERINFO));
    }

    public function getUserAccountNames(?string $userId): array
    {
        return $this->commandExecutor->execute(new GetUserInfoKeysCmd($userId, IdentityInfoEntity::TYPE_USERACCOUNT));
    }

    public function setUserInfo(?string $userId, ?string $key, ?string $value): void
    {
        $this->commandExecutor->execute(new SetUserInfoCmd($userId, $key, $value));
    }

    public function deleteUserInfo(?string $userId, ?string $key): void
    {
        $this->commandExecutor->execute(new DeleteUserInfoCmd($userId, $key));
    }

    public function deleteUserAccount(?string $userId, ?string $accountName): void
    {
        $this->commandExecutor->execute(new DeleteUserInfoCmd($userId, $accountName));
    }

    public function getUserAccount(?string $userId, ?string $userPassword, ?string $accountName): ?AccountInterface
    {
        return $this->commandExecutor->execute(new GetUserAccountCmd($userId, $userPassword, $accountName));
    }

    public function setUserAccount(?string $userId, ?string $userPassword, ?string $accountName, ?string $accountUsername, ?string $accountPassword, array $accountDetails): void
    {
        $this->commandExecutor->execute(new SetUserInfoCmd($userId, $userPassword, $accountName, $accountUsername, $accountPassword, $accountDetails));
    }

    public function createTenantUserMembership(?string $tenantId, ?string $userId): void
    {
        $this->commandExecutor->execute(new CreateTenantUserMembershipCmd($tenantId, $userId));
    }

    public function createTenantGroupMembership(?string $tenantId, ?string $groupId): void
    {
        $this->commandExecutor->execute(new CreateTenantGroupMembershipCmd($tenantId, $groupId));
    }

    public function deleteTenantUserMembership(?string $tenantId, ?string $userId): void
    {
        $this->commandExecutor->execute(new DeleteTenantUserMembershipCmd($tenantId, $userId));
    }

    public function deleteTenantGroupMembership(?string $tenantId, ?string $groupId): void
    {
        $this->commandExecutor->execute(new DeleteTenantGroupMembershipCmd($tenantId, $groupId));
    }
}
