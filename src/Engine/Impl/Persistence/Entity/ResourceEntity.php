<?php

namespace BpmPlatform\Engine\Impl\Persistence\Entity;

use BpmPlatform\Engine\Impl\Db\DbEntityInterface;
use BpmPlatform\Engine\Repository\ResourceInterface;
use BpmPlatform\Engine\Impl\Util\ClassNameUtil;

class ResourceEntity implements \Serializable, DbEntityInterface, ResourceInterface
{
    protected $id;
    protected $name;
    protected $bytes;
    protected $deploymentId;
    protected $generated = false;
    protected $tenantId;
    protected $type;
    protected $createTime;

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

    public function getBytes(): ?string
    {
        return $this->bytes;
    }

    public function setBytes(string $bytes): void
    {
        $this->bytes = $bytes;
    }

    public function getDeploymentId(): ?string
    {
        return $this->deploymentId;
    }

    public function setDeploymentId(string $deploymentId): void
    {
        $this->deploymentId = $deploymentId;
    }

    public function getPersistentState()
    {
        return new \ReflectionClass($this);
    }

    public function setGenerated(bool $generated): void
    {
        $this->generated = $generated;
    }

    /**
     * Indicated whether or not the resource has been generated while deploying rather than
     * being actual part of the deployment.
     */
    public function isGenerated(): bool
    {
        return $this->generated;
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

    public function serialize()
    {
        return json_encode([
            'id' => $this->id,
            'name' => $this->name,
            'deploymentId' => $this->deploymentId,
            'generated' => $this->generated,
            'tenantId' => $this->tenantId,
            'type' => $this->type,
            'createTime' => $this->createTime
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->id = $json->id;
        $this->name = $json->name;
        $this->deploymentId = $json->deploymentId;
        $this->generated = $json->generated;
        $this->tenantId = $json->tenantId;
        $this->type = $json->type;
        $this->createTime = $json->createTime;
    }

    public function __toString()
    {
        $className = ClassNameUtil::getClassNameWithoutPackage(get_class($this));
        return $className
                . "[id=" . $this->id
                . ", name=" . $this->name
                . ", deploymentId=" . $this->deploymentId
                . ", generated=" . $this->generated
                . ", tenantId=" . $this->tenantId
                . ", type=" . $this->type
                . ", createTime=" . $this->createTime
                . "]";
    }
}
