<?php

namespace Jabe\Engine\Impl\Persistence\Entity;

use Jabe\Engine\Impl\Db\DbEntityInterface;

class TenantMembershipEntity implements \Serializable, DbEntityInterface
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

    public function setId(string $id): void
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
        if ($this->user != null) {
            return $this->user->getId();
        } else {
            return null;
        }
    }

    public function getGroupId(): ?string
    {
        if ($this->group != null) {
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

    public function serialize()
    {
        return json_encode([
            'id' => $this->id,
            'tenant' => serialize($this->tenant),
            'user' => serialize($this->user),
            'group' => serialize($this->group),
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->id = $json->id;
        $this->tenant = unserialize($json->tenant);
        $this->user = unserialize($json->user);
        $this->group = unserialize($json->group);
    }

    public function __toString()
    {
        return "TenantMembershipEntity [id=" . $this->id . ", tenant=" . $this->tenant . ", user=" . $this->user . ", group=" . $this->group . "]";
    }
}
