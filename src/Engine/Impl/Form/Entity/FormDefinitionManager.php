<?php

namespace Jabe\Engine\Impl\Form\Entity;

use Jabe\Engine\Impl\ProcessEngineLogger;
use Jabe\Engine\Impl\Db\{
    EnginePersistenceLogger,
    ListQueryParameterObject
};
use Jabe\Engine\Impl\Persistence\{
    AbstractManager,
    AbstractResourceDefinitionManagerInterface
};
use Jabe\Engine\Impl\Persistence\Entity\FormDefinitionEntity;

class FormDefinitionManager extends AbstractManager implements AbstractResourceDefinitionManagerInterface
{
    //protected static final EnginePersistenceLogger LOG = ProcessEngineLogger.PERSISTENCE_LOGGER;

    public function findLatestDefinitionByKey(string $key): ?FormDefinitionEntity
    {
        $formDefinitions = $this->getDbEntityManager()->selectList("selectLatestFormDefinitionByKey", $this->configureParameterizedQuery($key));

        if (empty($formDefinitions)) {
            return null;
        } else if (count($formDefinitions) == 1) {
            return $camundaFormDefinitions[0];
        } else {
            //throw LOG.multipleTenantsForCamundaFormDefinitionKeyException(key);
            throw new \Exception("multipleTenantsForFormDefinitionKeyException");
        }
    }

    public function findLatestDefinitionById(string $id): ?FormDefinitionEntity
    {
        return $this->getDbEntityManager()->selectById(FormDefinitionEntity::class, $id);
    }

    public function findLatestDefinitionByKeyAndTenantId(string $definitionKey, ?string $tenantId): ?FormDefinitionEntity
    {
        $arameters = [];
        $parameters["formDefinitionKey"] = $definitionKey;
        $parameters["tenantId"] = $tenantId;

        if ($tenantId === null) {
            return $this->getDbEntityManager()
                ->selectOne("selectLatestFormDefinitionByKeyWithoutTenantId", $parameters);
        } else {
            return $this->getDbEntityManager()
                ->selectOne("selectLatestDefinitionByKeyAndTenantId", $parameters);
        }
    }

    public function findDefinitionByKeyVersionAndTenantId(string $definitionKey, int $definitionVersion, ?string $tenantId): ?FormDefinitionEntity
    {
        $parameters = [];
        $parameters["formDefinitionVersion"] = $definitionVersion;
        $parameters["formDefinitionKey"] = $definitionKey;
        $parameters["tenantId"] = $tenantId;
        if ($tenantId === null) {
            return $this->getDbEntityManager()
                ->selectOne("selectFormDefinitionByKeyVersionWithoutTenantId", $parameters);
        } else {
            return $this->getDbEntityManager()
                ->selectOne("selectFormDefinitionByKeyVersionAndTenantId", $parameters);
        }
    }

    public function findDefinitionByDeploymentAndKey(string $deploymentId, string $definitionKey): ?FormDefinitionEntity
    {
        $parameters = [];
        $parameters["deploymentId"] = $deploymentId;
        $parameters["formDefinitionKey"] = $definitionKey;
        return $this->getDbEntityManager()->selectOne(
            "selectFormDefinitionByDeploymentAndKey",
            $parameters
        );
    }

    public function findDefinitionsByDeploymentId(string $deploymentId): array
    {
        return $this->getDbEntityManager()->selectList("selectCamundaFormDefinitionByDeploymentId", $deploymentId);
    }

    public function getCachedResourceDefinitionEntity(string $definitionId): ?FormDefinitionEntity
    {
        return $this->getDbEntityManager()->getCachedEntity(FormDefinitionEntity::class, $definitionId);
    }

    public function findDefinitionByKeyVersionTagAndTenantId(
        string $definitionKey,
        string $definitionVersionTag,
        ?string $tenantId
    ): ?FormDefinitionEntity {
        throw new Exception(
            "Currently finding Form definition by version tag and tenant is not implemented."
        );
    }

    public function deleteFormDefinitionsByDeploymentId(string $deploymentId): void
    {
        $this->getDbEntityManager()->delete(
            FormDefinitionEntity::class,
            "deleteFormDefinitionsByDeploymentId",
            $deploymentId
        );
    }

    protected function configureParameterizedQuery($parameter): ListQueryParameterObject
    {
        return $this->getTenantManager()->configureQuery($parameter);
    }
}
