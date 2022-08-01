<?php

namespace Jabe\Engine\Impl;

use Jabe\Engine\Identity\{
    GroupInterface,
    GroupQueryInterface
};
use Jabe\Engine\Impl\Util\EnsureUtil;

abstract class GroupQueryImpl extends AbstractQuery implements GroupQueryInterface
{
    protected $id;
    protected $ids = [];
    protected $name;
    protected $nameLike;
    protected $type;
    protected $userId;
    protected $procDefId;
    protected $tenantId;

    public function groupId(string $id): GroupQueryInterface
    {
        EnsureUtil::ensureNotNull("Provided id", "id", $id);
        $this->id = $id;
        return $this;
    }

    public function groupIdIn(array $ids): GroupQueryInterface
    {
        EnsureUtil::ensureNotNull("Provided ids", "ids", $ids);
        $this->ids = $ids;
        return $this;
    }

    public function groupName(string $name): GroupQueryInterface
    {
        EnsureUtil::ensureNotNull("Provided name", "name", $name);
        $this->name = $name;
        return $this;
    }

    public function groupNameLike(string $nameLike): GroupQueryInterface
    {
        EnsureUtil::ensureNotNull("Provided nameLike", "nameLike", $nameLike);
        $this->nameLike = $nameLike;
        return $this;
    }

    public function groupType(string $type): GroupQueryInterface
    {
        EnsureUtil::ensureNotNull("Provided type", "type", $type);
        $this->type = $type;
        return $this;
    }

    public function groupMember(string $userId): GroupQueryInterface
    {
        EnsureUtil::ensureNotNull("Provided userId", "userId", $userId);
        $this->userId = $userId;
        return $this;
    }

    public function potentialStarter(string $procDefId): GroupQueryInterface
    {
        EnsureUtil::ensureNotNull("Provided processDefinitionId", "procDefId", $procDefId);
        $this->procDefId = $procDefId;
        return $this;
    }

    public function memberOfTenant(string $tenantId): GroupQueryInterface
    {
        EnsureUtil::ensureNotNull("Provided tenantId", "tenantId", $tenantId);
        $this->tenantId = $tenantId;
        return $this;
    }

    public function orderByGroupId(): GroupQueryInterface
    {
        return $this->orderBy(GroupQueryProperty::groupId());
    }

    public function orderByGroupName(): GroupQueryInterface
    {
        return $this->orderBy(GroupQueryProperty::name());
    }

    public function orderByGroupType(): GroupQueryInterface
    {
        return $this->orderBy(GroupQueryProperty::type());
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getNameLike(): string
    {
        return $this->nameLike;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getTenantId(): string
    {
        return $this->tenantId;
    }

    public function getIds(): array
    {
        return $this->ids;
    }
}
