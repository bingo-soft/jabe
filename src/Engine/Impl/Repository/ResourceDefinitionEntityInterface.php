<?php

namespace Jabe\Engine\Impl\Repository;

use Jabe\Engine\Repository\ResourceDefinitionInterface;

interface ResourceDefinitionEntityInterface extends ResourceDefinitionInterface
{
    public function setId(string $id): void;

    public function setCategory(string $category): void;

    public function setName(string $name): void;

    public function setKey(string $key): void;

    public function setVersion(int $version): void;

    public function setResourceName(string $resourceName): void;

    public function setDeploymentId(string $deploymentId): void;

    public function setDiagramResourceName(string $diagramResourceName): void;

    public function setTenantId(?string $tenantId): void;

    public function getPreviousDefinition(): ResourceDefinitionEntityInterface;

    public function updateModifiableFieldsFromEntity(ResourceDefinitionInterface $updatingDefinition): void;

    public function setHistoryTimeToLive(int $historyTimeToLive): void;
}
