<?php

namespace Jabe\Impl\Persistence\Entity;

use Jabe\Impl\Db\DbEntityInterface;
use Jabe\Repository\ResourceInterface;
use Jabe\Impl\Util\ClassNameUtil;

class ResourceEntity implements DbEntityInterface, ResourceInterface
{
    protected $id;
    protected $name;
    protected ?string $bytes;
    protected $deploymentId;
    protected bool $generated = false;
    protected $tenantId;
    protected $type;
    protected $createTime;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getBytes(): ?string
    {
        return $this->bytes;
    }

    public function setBytes(/*string|resource*/$bytes): void
    {
        $this->bytes = is_resource($bytes) ? stream_get_contents($bytes) : $bytes;
    }

    public function getDeploymentId(): ?string
    {
        return $this->deploymentId;
    }

    public function setDeploymentId(?string $deploymentId): void
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

    public function setTenantId(?string $tenantId): void
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

    public function getCreateTime(): ?string
    {
        return $this->createTime;
    }

    public function setCreateTime(?string $createTime): void
    {
        $this->createTime = $createTime;
    }

    public function __serialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'deploymentId' => $this->deploymentId,
            'generated' => $this->generated,
            'tenantId' => $this->tenantId,
            'type' => $this->type,
            'createTime' => $this->createTime
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->id = $data['id'];
        $this->name = $data['name'];
        $this->deploymentId = $data['deploymentId'];
        $this->generated = $data['generated'];
        $this->tenantId = $data['tenantId'];
        $this->type = $data['type'];
        $this->createTime = $data['createTime'];
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
