<?php

namespace Jabe\Engine\Impl\Persistence\Entity;

use Jabe\Engine\Impl\Db\{
    HasDbRevisionInterface,
    DbEntityInterface
};
use Jabe\Engine\Impl\Identity\AccountInterface;
use Jabe\Engine\Impl\Util\ClassNameUtil;

class IdentityInfoEntity implements DbEntityInterface, HasDbRevisionInterface, AccountInterface, \Serializable
{
    public const TYPE_USERACCOUNT = "account";
    public const TYPE_USERINFO = "userinfo";

    protected $id;
    protected $revision;
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
        $persistentState["value"] = $value;
        $persistentState["password"] = $passwordBytes;
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

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getRevision(): int
    {
        return $this->revision;
    }

    public function setRevision(int $revision): void
    {
        $this->revision = $revision;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function setUserId(string $userId): void
    {
        $this->userId = $userId;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function setKey(string $key): void
    {
        $this->key = $key;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    public function getPasswordBytes(): string
    {
        return $this->passwordBytes;
    }

    public function setPasswordBytes(string $passwordBytes): void
    {
        $this->passwordBytes = $passwordBytes;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getName(): string
    {
        return $this->key;
    }

    public function getUsername(): string
    {
        return $this->value;
    }

    public function getParentId(): ?string
    {
        return $this->parentId;
    }

    public function setParentId(string $parentId): void
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

    public function serialize()
    {
        return json_encode([
            'id' => $this->id,
            'revision' => $this->revision,
            'type' => $this->type,
            'userId' => $this->userId,
            'key' => $this->key,
            'value' => $this->value,
            'password' => $this->password,
            'parentId' => $this->parentId,
            'details' => $this->details
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->id = $json->id;
        $this->revision = $json->revision;
        $this->type = $json->type;
        $this->userId = $json->userId;
        $this->key = $json->key;
        $this->value = $json->value;
        $this->password = $json->password;
        $this->parentId = $json->parentId;
        $this->details = $json->details;
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
