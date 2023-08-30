<?php

namespace Jabe\Impl\Persistence\Entity;

use Jabe\Impl\Db\DbEntityInterface;

class TenantMembershipEntity implements DbEntityInterface
{
    protected $tenant;
    protected $user;
    protected $group;

    protected $id;

    public function getPersistentState()
    {
        // entity is not updatable
        return new \ReflectionClass($this);
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    public function getUser(): ?UserEntity
    {
        return $this->user;
    }

    public function setUser(UserEntity $user): void
    {
        $this->user = $user;
    }

    public function getGroup(): ?GroupEntity
    {
        return $this->group;
    }

    public function setGroup(GroupEntity $group): void
    {
        $this->group = $group;
    }

    public function getTenantId(): ?string
    {
        return $this->tenant->getId();
    }

    public function getUserId(): ?string
    {
        if ($this->user !== null) {
            return $this->user->getId();
        } else {
            return null;
        }
    }

    public function getGroupId(): ?string
    {
        if ($this->group !== null) {
            return $this->group->getId();
        } else {
            return null;
        }
    }

    public function getTenant(): TenantEntity
    {
        return $this->tenant;
    }

    public function setTenant(TenantEntity $tenant): void
    {
        $this->tenant = $tenant;
    }

    public function __serialize(): array
    {
        return [
            'id' => $this->id,
            'tenant' => serialize($this->tenant),
            'user' => serialize($this->user),
            'group' => serialize($this->group),
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->id = $data['id'];
        $this->tenant = unserialize($data['tenant']);
        $this->user = unserialize($data['user']);
        $this->group = unserialize($data['group']);
    }

    public function __toString()
    {
        return "TenantMembershipEntity [id=" . $this->id . ", tenant=" . $this->tenant . ", user=" . $this->user . ", group=" . $this->group . "]";
    }
}
