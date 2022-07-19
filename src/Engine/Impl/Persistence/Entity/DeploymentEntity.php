<?php

namespace Jabe\Engine\Impl\Persistence\Entity;

use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Db\DbEntityInterface;
use Jabe\Engine\Impl\Repository\ResourceDefinitionEntityInterface;
use Jabe\Engine\Repository\{
    DeploymentWithDefinitionsInterface
};
use Jabe\Engine\Impl\Util\ClassNameUtil;

class DeploymentEntity implements \Serializable, DeploymentWithDefinitionsInterface, DbEntityInterface
{
    protected $id;
    protected $name;
    protected $resources = [];
    protected $deploymentTime;
    protected $validatingSchema = true;
    protected $isNew;
    protected $source;
    protected $tenantId;

    public function serialize()
    {
        $resources = [];
        foreach ($this->resources as $resouce) {
            $resources[] = serialize($resouce);
        }
        return json_encode([
            'id' => $this->id,
            'name' => $this->name,
            'resources' => $resources,
            'deploymentTime' => $this->deploymentTime,
            'validatingSchema' => $this->validatingSchema,
            'isNew' => $this->isNew,
            'source' => $this->source,
            'tenantId' => $this->tenantId
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->id = $json->id;
        $this->name = $json->name;
        $resources = [];
        foreach ($json->resources as $resource) {
            $resources[] = unserialize($resource);
        }
        $this->resources = $resources;
        $this->deploymentTime = $json->deploymentTime;
        $this->validatingSchema = $json->validatingSchema;
        $this->isNew = $json->isNew;
        $this->source = $json->source;
        $this->tenantId = $json->tenantId;
    }

    /**
     * Will only be used during actual deployment to pass deployed artifacts (eg process definitions).
     * Will be null otherwise.
     */
    protected $deployedArtifacts = [];

    public function getResource(string $resourceName): ?ResourceEntity
    {
        $resources = $this->getResources();
        if (array_key_exists($resourceName, $resources)) {
            return $resources[$resourceName];
        }
        return null;
    }

    public function addResource(ResourceEntity $resource): void
    {
        $this->resources[$resource->getName()] = $resource;
    }

    public function clearResources(): void
    {
        $this->resources = [];
    }

    // lazy loading /////////////////////////////////////////////////////////////
    public function getResources(): array
    {
        if (empty($this->resources) && $this->id !== null) {
            $resourcesList = Context::getCommandContext()
            ->getResourceManager()
            ->findResourcesByDeploymentId($id);
            foreach ($resourcesList as $resource) {
                $this->resources[$resource->getName()] = $resource;
            }
        }
        return $this->resources;
    }

    public function getPersistentState()
    {
        // properties of this entity are immutable
        // so always the same value is returned
        // so never will an update be issued for a DeploymentEntity
        return DeploymentEntity::class;
    }

    // Deployed artifacts manipulation //////////////////////////////////////////

    public function addDeployedArtifact(ResourceDefinitionEntityInterface $deployedArtifact): void
    {
        $clazz = get_class($deployedArtifact);
        if (!array_key_exists($clazz, $this->deployedArtifacts)) {
            $this->deployedArtifacts[$clazz] = [];
        }
        $this->deployedArtifacts[$clazz][] = $deployedArtifact;
    }

    public function getDeployedArtifacts(?string $clazz = null): array
    {
        if ($clazz === null) {
            return $this->deployedArtifacts;
        } else {
            if (empty($this->deployedArtifacts)) {
                return [];
            } else {
                if (array_key_exists($clazz, $this->deployedArtifacts)) {
                    return $this->deployedArtifacts[$clazz];
                }
                return [];
            }
        }
    }

    public function removeArtifact(ResourceDefinitionEntityInterface $notDeployedArtifact): void
    {
        if (!empty($this->deployedArtifacts)) {
            $clazz = get_class($notDeployedArtifact);
            if (array_key_exists($clazz, $this->deployedArtifacts)) {
                $artifacts = $this->deployedArtifacts[$clazz];
                foreach ($artifacts as $key => $value) {
                    if ($value == $notDeployedArtifact) {
                        unset($artifacts[$key]);
                    }
                }
                if (empty($artifacts)) {
                    $this->deployedArtifacts[$clazz] = [];
                }
            }
        }
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

    public function setResources(array $resources): void
    {
        $this->resources = $resources;
    }

    public function getDeploymentTime(): string
    {
        return $this->deploymentTime;
    }

    public function setDeploymentTime(string $deploymentTime): void
    {
        $this->deploymentTime = $deploymentTime;
    }

    public function isValidatingSchema(): bool
    {
        return $this->validatingSchema;
    }

    public function setValidatingSchema(bool $validatingSchema): void
    {
        $this->validatingSchema = $validatingSchema;
    }

    public function isNew(): bool
    {
        return $this->isNew;
    }

    public function setNew(bool $isNew): void
    {
        $this->isNew = $isNew;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function setSource(string $source): void
    {
        $this->source = $source;
    }

    public function getTenantId(): ?string
    {
        return $this->tenantId;
    }

    public function setTenantId(?string $tenantId): void
    {
        $this->tenantId = $tenantId;
    }

    public function getDeployedProcessDefinitions(): array
    {
        $clazz = ProcessDefinitionEntity::class;
        if (array_key_exists($clazz, $this->deployedArtifacts)) {
            return $this->deployedArtifacts[$clazz];
        }
        return [];
    }

    public function __toString()
    {
        $className = ClassNameUtil::getClassNameWithoutPackage(get_class($this));
        return $className
                . "[id=" . $this->id
                . ", name=" . $this->name
                . ", resources=" . json_encode($this->resources)
                . ", deploymentTime=" . $this->deploymentTime
                . ", validatingSchema=" . $this->validatingSchema
                . ", isNew=" . $this->isNew
                . ", source=" . $this->source
                . ", tenantId=" . $this->tenantId
                . "]";
    }
}
