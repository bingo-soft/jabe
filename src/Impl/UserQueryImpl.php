<?php

namespace Jabe\Impl;

use Jabe\Identity\{
    UserInterface,
    UserQueryInterface
};
use Jabe\Impl\Interceptor\CommandExecutorInterface;
use Jabe\Impl\Util\EnsureUtil;

abstract class UserQueryImpl extends AbstractQuery implements UserQueryInterface
{
    protected $id;
    protected $ids = [];
    protected $firstName;
    protected $firstNameLike;
    protected $lastName;
    protected $lastNameLike;
    protected $email;
    protected $emailLike;
    protected $groupId;
    protected $procDefId;
    protected $tenantId;

    public function __construct(CommandExecutorInterface $commandExecutor = null)
    {
        parent::__construct($commandExecutor);
    }

    public function userId(?string $id): UserQueryInterface
    {
        EnsureUtil::ensureNotNull("Provided id", "id", $id);
        $this->id = $id;
        return $this;
    }

    public function userIdIn(array $ids): UserQueryInterface
    {
        EnsureUtil::ensureNotNull("Provided ids", "ids", $ids);
        $this->ids = $ids;
        return $this;
    }

    public function userFirstName(?string $firstName): UserQueryInterface
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function userFirstNameLike(?string $firstNameLike): UserQueryInterface
    {
        EnsureUtil::ensureNotNull("Provided firstNameLike", "firstNameLike", $firstNameLike);
        $this->firstNameLike = $firstNameLike;
        return $this;
    }

    public function userLastName(?string $lastName): UserQueryInterface
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function userLastNameLike(?string $lastNameLike): UserQueryInterface
    {
        EnsureUtil::ensureNotNull("Provided lastNameLike", "lastNameLike", $lastNameLike);
        $this->lastNameLike = $lastNameLike;
        return $this;
    }

    public function userEmail(?string $email): UserQueryInterface
    {
        $this->email = $email;
        return $this;
    }

    public function userEmailLike(?string $emailLike): UserQueryInterface
    {
        EnsureUtil::ensureNotNull("Provided emailLike", "emailLike", $emailLike);
        $this->emailLike = $emailLike;
        return $this;
    }

    public function memberOfGroup(?string $groupId): UserQueryInterface
    {
        EnsureUtil::ensureNotNull("Provided groupId", "groupId", $groupId);
        $this->groupId = $groupId;
        return $this;
    }

    public function potentialStarter(?string $procDefId): UserQueryInterface
    {
        EnsureUtil::ensureNotNull("Provided processDefinitionId", "procDefId", $procDefId);
        $this->procDefId = $procDefId;
        return $this;
    }

    public function memberOfTenant(?string $tenantId): UserQueryInterface
    {
        EnsureUtil::ensureNotNull("Provided tenantId", "tenantId", $tenantId);
        $this->tenantId = $tenantId;
        return $this;
    }

    //sorting //////////////////////////////////////////////////////////

    public function orderByUserId(): UserQueryInterface
    {
        return $this->orderBy(UserQueryProperty::userId());
    }

    public function orderByUserEmail(): UserQueryInterface
    {
        return $this->orderBy(UserQueryProperty::email());
    }

    public function orderByUserFirstName(): UserQueryInterface
    {
        return $this->orderBy(UserQueryProperty::firstName());
    }

    public function orderByUserLastName(): UserQueryInterface
    {
        return $this->orderBy(UserQueryProperty::lastName());
    }

    //getters //////////////////////////////////////////////////////////

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getIds(): array
    {
        return $this->ids;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function getFirstNameLike(): ?string
    {
        return $this->firstNameLike;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function getLastNameLike(): ?string
    {
        return $this->lastNameLike;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getEmailLike(): ?string
    {
        return $this->emailLike;
    }

    public function getGroupId(): ?string
    {
        return $this->groupId;
    }

    public function getTenantId(): ?string
    {
        return $this->tenantId;
    }
}
