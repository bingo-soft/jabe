<?php

namespace BpmPlatform\Engine\Impl\Persistence\Entity;

use BpmPlatform\Engine\Impl\Db\{
    DbEntityInterface,
    HasDbRevisionInterface
};
use BpmPlatform\Engine\Repository\ResourceTypeInterface;
use BpmPlatform\Engine\Impl\Util\ClassNameUtil;

class ByteArrayEntity implements \Serializable, DbEntityInterface, HasDbRevisionInterface
{
    private static $PERSISTENTSTATE_NULL;

    protected $id;
    protected $revision;
    protected $name;
    protected $bytes;
    protected $deploymentId;
    protected $tenantId;
    protected $type;
    protected $createTime;
    protected $rootProcessInstanceId;
    protected $removalTime;

    public function __construct(
        ?string $name = null,
        ?string $bytes = null,
        ?ResourceTypeInterface $type = null,
        ?string $rootProcessInstanceId = null,
        ?string $removalTime = null
    ) {
        if (self::$PERSISTENTSTATE_NULL == null) {
            self::$PERSISTENTSTATE_NULL = new \stdClass();
        }
        $this->name = $name;
        $this->bytes = $bytes;
        if ($type != null) {
            $this->type = $type->getValue();
        }
        $this->rootProcessInstanceId = $rootProcessInstanceId;
        $this->removalTime = $removalTime;
    }

    public function getBytes(): ?string
    {
        return $this->bytes;
    }

    public function getPersistentState()
    {
        return $this->bytes ?? self::$PERSISTENTSTATE_NULL;
    }

    public function getRevisionNext(): int
    {
        return $this->revision + 1;
    }

    // getters and setters //////////////////////////////////////////////////////

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDeploymentId(): ?string
    {
        return $this->deploymentId;
    }

    public function setDeploymentId(string $deploymentId): void
    {
        $this->deploymentId = $deploymentId;
    }

    public function setBytes(string $bytes): void
    {
        $this->bytes = $bytes;
    }

    public function getRevision(): int
    {
        return $this->revision;
    }

    public function setRevision(int $revision): void
    {
        $this->revision = $revision;
    }

    public function getTenantId(): ?string
    {
        return $this->tenantId;
    }

    public function setTenantId(string $tenantId): void
    {
        $this->tenantId = $tenantId;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type): void
    {
        $this->type = $type;
    }

    public function getCreateTime(): string
    {
        return $this->createTime;
    }

    public function setCreateTime(string $createTime): void
    {
        $this->createTime = $createTime;
    }

    public function getRootProcessInstanceId(): string
    {
        return $this->rootProcessInstanceId;
    }

    public function setRootProcessInstanceId(string $rootProcessInstanceId): void
    {
        $this->rootProcessInstanceId = $rootProcessInstanceId;
    }

    public function getRemovalTime(): string
    {
        return $this->removalTime;
    }

    public function setRemovalTime(string $removalTime): void
    {
        $this->removalTime = $removalTime;
    }

    public function serialize()
    {
        return json_encode([
            'id' => $this->id,
            'revision' => $this->revision,
            'name' => $this->name,
            'deploymentId' => $this->deploymentId,
            'tenantId' => $this->tenantId,
            'type' => $this->type,
            'createTime' => $this->createTime,
            'rootProcessInstanceId' => $this->rootProcessInstanceId,
            'removalTime' => $this->removalTime
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->id = $json->id;
        $this->revision = $json->revision;
        $this->name = $json->name;
        $this->deploymentId = $json->deploymentId;
        $this->tenantId = $json->tenantId;
        $this->type = $json->type;
        $this->createTime = $json->createTime;
        $this->rootProcessInstanceId = $json->rootProcessInstanceId;
        $this->removalTime = $json->removalTime;
    }

    public function __toString()
    {
        $className = ClassNameUtil::getClassNameWithoutPackage(get_class($this));
        return $className
                . "[id=" . $this->id
                . ", revision=" . $this->revision
                . ", name=" . $this->name
                . ", deploymentId=" . $this->deploymentId
                . ", tenantId=" . $this->tenantId
                . ", type=" . $this->type
                . ", createTime=" . $this->createTime
                . ", rootProcessInstanceId=" . $this->rootProcessInstanceId
                . ", removalTime=" . $this->removalTime
                . "]";
    }
}
