<?php

namespace Jabe\Engine\Impl\Persistence\Entity;

use Jabe\Engine\Impl\Db\{
    DbEntityInterface,
    HasDbRevisionInterface
};
use Jabe\Engine\Impl\Repository\ResourceDefinitionEntityInterface;
use Jabe\Engine\Repository\FormDefinitionInterface;

class FormDefinitionEntity implements FormDefinitionInterface, ResourceDefinitionEntityInterface, DbEntity, HasDbRevision, \Serializable
{
    protected $id;
    protected $revision = 1;
    protected $key;
    protected $version;
    protected $deploymentId;
    protected $resourceName;
    protected $tenantId;

    public function __construct(string $key, string $deploymentId, string $resourceName, string $tenantId)
    {
        $this->key = $key;
        $this->deploymentId = $deploymentId;
        $this->resourceName = $resourceName;
        $this->tenantId = $tenantId;
    }

    public function serialize()
    {
        return json_encode([
            'id' => $this->id,
            'revision' => $this->revision,
            'key' => $this->key,
            'version' => $this->version,
            'deploymentId' => $this->deploymentId,
            'resourceName' => $this->resourceName,
            'tenantId' => $this->tenantId
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->id = $json->id;
        $this->revision = $json->revision;
        $this->key = $json->key;
        $this->version = $json->version;
        $this->deploymentId = $json->deploymentId;
        $this->resourceName = $json->resourceName;
        $this->tenantId = $json->tenantId;
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

    public function getRevisionNext(): int
    {
        return $this->revision + 1;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function setKey(string $key): void
    {
        $this->key = $key;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function setVersion(int $version): void
    {
        $this->version = $version;
    }

    public function getDeploymentId(): ?string
    {
        return $this->deploymentId;
    }

    public function setDeploymentId(string $deploymentId): void
    {
        $this->deploymentId = $deploymentId;
    }

    public function getResourceName(): string
    {
        return $this->resourceName;
    }

    public function setResourceName(string $resourceName): void
    {
        $this->resourceName = $resourceName;
    }

    public function getTenantId(): ?string
    {
        return $this->tenantId;
    }

    public function setTenantId(?string $tenantId): void
    {
        $this->tenantId = $tenantId;
    }

    public function getCategory(): string
    {
        throw new \Exception("Unsupported operation");
    }

    public function getPreviousDefinition(): ?FormDefinitionEntity
    {
        throw new \Exception("Unsupported operation");
    }

    public function setCategory(string $category): void
    {
        throw new \Exception("Unsupported operation");
    }

    public function getDiagramResourceName(): string
    {
        throw new \Exception("Unsupported operation");
    }

    public function setDiagramResourceName(string $diagramResourceName): void
    {
        throw new \Exception("Unsupported operation");
    }

    public function getHistoryTimeToLive(): int
    {
        throw new \Exception("Unsupported operation");
    }

    public function setHistoryTimeToLive(int $historyTimeToLive): void
    {
        throw new \Exception("Unsupported operation");
    }

    public function getPersistentState()
    {
        // properties of this entity are immutable
        return FormDefinitionEntity::class;
    }

    public function updateModifiableFieldsFromEntity(FormDefinitionEntity $updatingDefinition): void
    {
        throw new \Exception("Unsupported operation");
    }

    public function getName(): string
    {
        throw new \Exception("Unsupported operation");
    }

    public function setName(string $name): void
    {
        throw new \Exception("Unsupported operation");
    }
}
