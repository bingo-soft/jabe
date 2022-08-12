<?php

namespace Jabe\Impl\Repository;

use Jabe\Repository\{
    ProcessDefinitionInterface,
    CalledProcessDefinitionInterface
};

class CalledProcessDefinitionImpl implements CalledProcessDefinitionInterface
{
    protected $id;
    protected $key;
    protected $category;
    protected $description;
    protected $name;
    protected $version;
    protected $deploymentId;
    protected $suspended;
    protected $tenantId;
    protected $versionTag;
    protected $historyTimeToLive;
    protected $isStartableInTasklist;
    protected $hasStartFormKey;
    protected $diagramResourceName;
    protected $resourceName;

    protected $calledFromActivityIds = [];
    protected $callingProcessDefinitionId;

    public function __construct(ProcessDefinitionInterface $definition, string $callingProcessDefinitionId)
    {
        $this->calledFromActivityIds = [];
        $this->callingProcessDefinitionId = $callingProcessDefinitionId;
        $this->id = $definition->getId();
        $this->key = $definition->getKey();
        $this->category = $definition->getCategory();
        $this->description = $definition->getDescription();
        $this->name = $definition->getName();
        $this->version = $definition->getVersion();
        $this->deploymentId = $definition->getDeploymentId();
        $this->suspended = $definition->isSuspended();
        $this->tenantId = $definition->getTenantId();
        $this->versionTag = $definition->getVersionTag();
        $this->historyTimeToLive = $definition->getHistoryTimeToLive();
        $this->isStartableInTasklist = $definition->isStartableInTasklist();
        $this->hasStartFormKey = $definition->hasStartFormKey();
        $this->diagramResourceName = $definition->getDiagramResourceName();
        $this->resourceName = $definition->getResourceName();
    }

    public function getCallingProcessDefinitionId(): string
    {
        return $this->callingProcessDefinitionId;
    }

    public function getCalledFromActivityIds(): array
    {
        return $this->calledFromActivityIds;
    }

    public function addCallingCallActivity(string $activityId): void
    {
        $this->calledFromActivityIds->add($activityId);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function hasStartFormKey(): bool
    {
        return $this->hasStartFormKey;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function getResourceName(): string
    {
        return $this->resourceName;
    }

    public function getDeploymentId(): string
    {
        return $this->deploymentId;
    }

    public function getDiagramResourceName(): string
    {
        return $this->diagramResourceName;
    }

    public function isSuspended(): bool
    {
        return $this->suspended;
    }

    public function getTenantId(): string
    {
        return $this->tenantId;
    }

    public function getVersionTag(): string
    {
        return $this->versionTag;
    }

    public function getHistoryTimeToLive(): int
    {
        return $this->historyTimeToLive;
    }

    public function isStartableInTasklist(): bool
    {
        return $this->isStartableInTasklist;
    }
}
