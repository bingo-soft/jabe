<?php

namespace Jabe\Engine\Impl\Form\Deployer;

use Jabe\Engine\Impl\{
    AbstractDefinitionDeployer,
    ProcessEngineLogger
};
use Jabe\Engine\Impl\Core\Model\Properties;
use Jabe\Engine\Impl\Persistence\Deploy\Cache\DeploymentCache;
use Jabe\Engine\Impl\Persistence\Entity\{
    FormDefinitionEntity,
    DeploymentEntity,
    ResourceEntity
};
use Jabe\Engine\Impl\Util\EngineUtilLogger;

class CamundaFormDefinitionDeployer extends AbstractDefinitionDeployer
{
    //protected static final EngineUtilLogger LOG = ProcessEngineLogger.UTIL_LOGGER;
    public static $FORM_RESOURCE_SUFFIXES = [ "form" ];

    protected function getResourcesSuffixes(): array
    {
        return self::$FORM_RESOURCE_SUFFIXES;
    }

    protected function transformDefinitions(
        DeploymentEntity $deployment,
        ResourceEntity $resource,
        Properties $properties
    ): array {
        $formContent = $resource->getBytes();

        try {
            $formJsonObject = json_decode($formContent);
            $formDefinitionKey = $formJsonObject->id;
            $definition = new FormDefinitionEntity($formDefinitionKey, $deployment->getId(), $resource->getName(), $deployment->getTenantId());
            return [$definition];
        } catch (\Exception $e) {
            // form could not be parsed, throw exception if strict parsing is not disabled
            if (!$this->getCommandContext()->getProcessEngineConfiguration()->isDisableStrictFormParsing()) {
                //throw LOG.exceptionDuringFormParsing(e.getMessage(), resource.getName());
                throw new \Exception("exceptionDuringFormParsing");
            }
            return [];
        }
    }

    protected function findDefinitionByDeploymentAndKey(string $deploymentId, string $definitionKey): ?FormDefinitionEntity
    {
        return $this->getCommandContext()->getFormDefinitionManager()->findDefinitionByDeploymentAndKey(
            $deploymentId,
            $definitionKey
        );
    }

    protected function findLatestDefinitionByKeyAndTenantId(string $definitionKey, ?string $tenantId): ?FormDefinitionEntity
    {
        return $this->getCommandContext()->getFormDefinitionManager()->findLatestDefinitionByKeyAndTenantId(
            $definitionKey,
            $tenantId
        );
    }

    protected function persistDefinition(FormDefinitionEntity $definition): void
    {
        $this->getCommandContext()->getFormDefinitionManager()->insert($definition);
    }

    protected function addDefinitionToDeploymentCache(DeploymentCache $deploymentCache, FormDefinitionEntity $definition): void
    {
        $deploymentCache->addCamundaFormDefinition($definition);
    }
}
