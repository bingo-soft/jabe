<?php

namespace Jabe\Impl\Persistence\Entity;

use Jabe\Impl\Db\{
    HasDbRevisionInterface,
    DbEntityInterface
};
use Jabe\Impl\Identity\AccountInterface;
use Jabe\Impl\Util\ClassNameUtil;

class IdentityInfoEntity implements DbEntityInterface, HasDbRevisionInterface, AccountInterface
{
    public const TYPE_USERACCOUNT = "account";
    public const TYPE_USERINFO = "userinfo";

    protected $id;
    protected int $revision = 0;
    protected $type;
    protected $userId;
    protected $key;
    protected $value;
    protected $password;
    protected $passwordBytes;
    protected $parentId;
    protected $details = [];

    public function getPersistentState()
    {
        $persistentState = [];
        $persistentState["value"] = $this->value;
        $persistentState["password"] = $this->passwordBytes;
        return $persistentState;
    }

    public function getRevisionNext(): int
    {
        return $this->revision + 1;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    public function getRevision(): ?int
    {
        return $this->revision;
    }

    public function setRevision(int $revision): void
    {
        $this->revision = $revision;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function setUserId(?string $userId): void
    {
        $this->userId = $userId;
    }

    public function getKey(): ?string
    {
        return $this->key;
    }

    public function setKey(?string $key): void
    {
        $this->key = $key;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): void
    {
        $this->value = $value;
    }

    public function getPasswordBytes(): ?string
    {
        return $this->passwordBytes;
    }

    public function setPasswordBytes(?string $passwordBytes): void
    {
        $this->passwordBytes = $passwordBytes;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }

    public function getName(): ?string
    {
        return $this->key;
    }

    public function getUsername(): ?string
    {
        return $this->value;
    }

    public function getParentId(): ?string
    {
        return $this->parentId;
    }

    public function setParentId(?string $parentId): void
    {
        $this->parentId = $parentId;
    }

    public function getDetails(): array
    {
        return $this->details;
    }

    public function setDetails(array $details): void
    {
        $this->details = $details;
    }

    public function __serialize(): array
    {
        return [
            'id' => $this->id,
            'revision' => $this->revision,
            'type' => $this->type,
            'userId' => $this->userId,
            'key' => $this->key,
            'value' => $this->value,
            'password' => $this->password,
            'parentId' => $this->parentId,
            'details' => $this->details
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->id = $data['id'];
        $this->revision = $data['revision'];
        $this->type = $data['type'];
        $this->userId = $data['userId'];
        $this->key = $data['key'];
        $this->value = $data['value'];
        $this->password = $data['password'];
        $this->parentId = $data['parentId'];
        $this->details = $data['details'];
    }

    public function __toString()
    {
        $className = ClassNameUtil::getClassNameWithoutPackage(get_class($this));
        return $className
                . "[id=" . $this->id
                . ", revision=" . $this->revision
                . ", type=" . $this->type
                . ", userId=" . $this->userId
                . ", key=" . $this->key
                . ", value=" . $this->value
                . ", password=" . $this->password
                . ", parentId=" . $this->parentId
                . ", details=" . $this->details
                . "]";
    }
}
