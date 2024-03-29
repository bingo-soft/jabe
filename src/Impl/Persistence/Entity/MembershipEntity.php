<?php

namespace Jabe\Impl\Persistence\Entity;

use Jabe\Impl\Db\DbEntityInterface;
use Jabe\Impl\Util\ClassNameUtil;

class MembershipEntity implements DbEntityInterface
{
    protected $user;
    protected $group;
    protected ?string $userId;
    protected ?string $groupId;

    /**
     * To handle a MemberhipEntity in the cache, an id is necessary.
     * Even though it is not going to be persisted in the database.
     */
    protected $id;

    public function getPersistentState()
    {
        // membership is not updatable
        return (new \ReflectionClass($this));
    }

    public function getId(): ?string
    {
        // For the sake of Entity caching the id is necessary
        return $this->id;
    }

    public function setId(?string $id): void
    {
        // For the sake of Entity caching the id is necessary
        $this->id = $id;
    }

    public function getUser(): UserEntity
    {
        return $this->user;
    }

    public function setUser(UserEntity $user): void
    {
        $this->user = $user;
    }

    public function getGroup(): GroupEntity
    {
        return $this->group;
    }

    public function setGroup(GroupEntity $group): void
    {
        $this->group = $group;
    }

    public function getUserId(): ?string
    {
        return $this->user->getId();
    }

    public function getGroupId(): ?string
    {
        return $this->group->getId();
    }

    public function __serialize(): array
    {
        return [
            'id' => $this->id,
            'user' => serialize($this->user),
            'group' => serialize($this->group)
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->id = $data['id'];
        $this->user = unserialize($data['user']);
        $this->group = unserialize($data['group']);
    }

    public function __toString()
    {
        $className = ClassNameUtil::getClassNameWithoutPackage(get_class($this));
        return $className
             . "[user=" . $this->user
             . ", group=" . $this->group
             . "]";
    }
}
