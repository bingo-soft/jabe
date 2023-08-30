<?php

namespace Jabe\Impl;

use Jabe\Identity\{
    TenantInterface,
    TenantQueryInterface
};
use Jabe\Impl\Interceptor\CommandExecutorInterface;
use Jabe\Impl\Util\EnsureUtil;

abstract class TenantQueryImpl extends AbstractQuery implements TenantQueryInterface
{
    protected $id;
    protected $ids = [];
    protected $name;
    protected $nameLike;
    protected $userId;
    protected $groupId;
    protected bool $includingGroups = false;

    public function __construct(?CommandExecutorInterface $commandExecutor = null)
    {
        parent::__construct($commandExecutor);
    }

    public function tenantId(?string $id): TenantQueryInterface
    {
        EnsureUtil::ensureNotNull("tenant ud", "id", $id);
        $this->id = $id;
        return $this;
    }

    public function tenantIdIn(array $ids): TenantQueryInterface
    {
        EnsureUtil::ensureNotNull("tenant ids", "ids", $ids);
        $this->ids = $ids;
        return $this;
    }

    public function tenantName(?string $name): TenantQueryInterface
    {
        EnsureUtil::ensureNotNull("tenant name", "name", $name);
        $this->name = $name;
        return $this;
    }

    public function tenantNameLike(?string $nameLike): TenantQueryInterface
    {
        EnsureUtil::ensureNotNull("tenant name like", "nameLike", $nameLike);
        $this->nameLike = $nameLike;
        return $this;
    }

    public function userMember(?string $userId): TenantQueryInterface
    {
        EnsureUtil::ensureNotNull("user id", "userId", $userId);
        $this->userId = $userId;
        return $this;
    }

    public function groupMember(?string $groupId): TenantQueryInterface
    {
        EnsureUtil::ensureNotNull("group id", "groupId", $groupId);
        $this->groupId = $groupId;
        return $this;
    }

    public function includingGroupsOfUser(bool $includingGroups): TenantQueryInterface
    {
        $this->includingGroups = $includingGroups;
        return $this;
    }

    //sorting ////////////////////////////////////////////////////////

    public function orderByTenantId(): TenantQueryInterface
    {
        return $this->orderBy(TenantQueryProperty::groupId());
    }

    public function orderByTenantName(): TenantQueryInterface
    {
        return $this->orderBy(TenantQueryProperty::name());
    }

    //getters ////////////////////////////////////////////////////////

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getNameLike(): ?string
    {
        return $this->nameLike;
    }

    public function getIds(): array
    {
        return $this->ids;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function getGroupId(): ?string
    {
        return $this->groupId;
    }

    public function isIncludingGroups(): bool
    {
        return $this->includingGroups;
    }
}
