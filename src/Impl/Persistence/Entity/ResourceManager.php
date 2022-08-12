<?php

namespace Jabe\Impl\Persistence\Entity;

use Jabe\Impl\Cmd\LicenseCmd;
use Jabe\Impl\Persistence\AbstractManager;

class ResourceManager extends AbstractManager
{
    public function insertResource(ResourceEntity $resource): void
    {
        $this->getDbEntityManager()->insert($resource);
    }

    public function deleteResourcesByDeploymentId(string $deploymentId): void
    {
        $this->getDbEntityManager()->delete(ResourceEntity::class, "deleteResourcesByDeploymentId", $deploymentId);
    }

    public function findResourceByDeploymentIdAndResourceName(string $deploymentId, string $resourceName): ?ResourceEntity
    {
        $params = [];
        $params["deploymentId"] = $deploymentId;
        $params["resourceName"] = $resourceName;
        return $this->getDbEntityManager()->selectOne("selectResourceByDeploymentIdAndResourceName", $params);
    }

    public function findResourceByDeploymentIdAndResourceNames(string $deploymentId, array $resourceNames): array
    {
        $params = [];
        $params["deploymentId"] = $deploymentId;
        $params["resourceNames"] = $resourceNames;
        return $this->getDbEntityManager()->selectList("selectResourceByDeploymentIdAndResourceNames", $params);
    }

    public function findResourceByDeploymentIdAndResourceId(string $deploymentId, string $resourceId): ?ResourceEntity
    {
        $params = [];
        $params["deploymentId"] = $deploymentId;
        $params["resourceId"] = $resourceId;
        return $this->getDbEntityManager()->selectOne("selectResourceByDeploymentIdAndResourceId", $params);
    }

    public function findResourceByDeploymentIdAndResourceIds(string $deploymentId, array $resourceIds): array
    {
        $params = [];
        $params["deploymentId"] = $deploymentId;
        $params["resourceIds"] = $resourceIds;
        return $this->getDbEntityManager()->selectList("selectResourceByDeploymentIdAndResourceIds", $params);
    }

    public function findResourcesByDeploymentId(string $deploymentId): array
    {
        return $this->getDbEntityManager()->selectList("selectResourcesByDeploymentId", $deploymentId);
    }

    public function findLatestResourcesByDeploymentName(string $deploymentName, array $resourcesToFind, string $source, string $tenantId): array
    {
        $params = [];
        $params["deploymentName"] = $deploymentName;
        $params["resourcesToFind"] = $resourcesToFind;
        $params["source"] = $source;
        $params["tenantId"] = $tenantId;

        $resources = $this->getDbEntityManager()->selectList("selectLatestResourcesByDeploymentName", $params);

        $existingResourcesByName = [];
        foreach ($resources as $existingResource) {
            $existingResourcesByName[$existingResource->getName()] = $existingResource;
        }

        return $existingResourcesByName;
    }

    public function findLicenseKeyResource(): ?ResourceEntity
    {
        $licenseProperty = $this->getDbEntityManager()->selectOne("selectProperty", LicenseCmd::LICENSE_KEY_BYTE_ARRAY_ID);
        return $licenseProperty === null ? null : $this->getDbEntityManager()->selectOne("selectResourceById", $licenseProperty->value);
    }
}
