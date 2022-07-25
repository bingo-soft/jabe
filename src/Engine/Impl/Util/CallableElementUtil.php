<?php

namespace Jabe\Engine\Impl\Util;

use Jabe\Engine\ProcessEngineException;
use Jabe\Engine\Delegate\VariableScopeInterface;
use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Core\Model\BaseCallableElement;
use Jabe\Engine\Impl\El\StartProcessVariableScope;
use Jabe\Engine\Impl\Persistence\Deploy\Cache\DeploymentCache;
use Jabe\Engine\Impl\Persistence\Entity\ProcessDefinitionEntity;
use Jabe\Engine\Impl\Pvm\Process\ProcessDefinitionImpl;
use Jabe\Engine\Repository\ProcessDefinitionInterface;

class CallableElementUtil
{
    public static function getDeploymentCache(): DeploymentCache
    {
        return Context::getProcessEngineConfiguration()
            ->getDeploymentCache();
    }

    public static function getProcessDefinitionToCall(
        VariableScopeInterface $execution,
        string $defaultTenantId,
        BaseCallableElement $callableElement
    ): ProcessDefinitionImpl {
        $processDefinitionKey = $callableElement->getDefinitionKey($execution);
        $tenantId = $callableElement->getDefinitionTenantId($execution, $defaultTenantId);

        return self::getCalledProcessDefinition($execution, $callableElement, $processDefinitionKey, $tenantId);
    }

    public static function getStaticallyBoundProcessDefinition(
        string $callingProcessDefinitionId,
        string $activityId,
        BaseCallableElement $callableElement,
        ?string $tenantId
    ): ?ProcessDefinitionInterface {
        if ($callableElement->hasDynamicReferences()) {
            return null;
        }

        $emptyVariableScope = StartProcessVariableScope::getSharedInstance();

        $targetTenantId = $callableElement->getDefinitionTenantId($emptyVariableScope, $tenantId);

        try {
            $processDefinitionKey = $callableElement->getDefinitionKey($emptyVariableScope);
            return self::getCalledProcessDefinition($emptyVariableScope, $callableElement, $processDefinitionKey, $targetTenantId);
        } catch (\Exception $e) {
            //UTIL_LOGGER.debugCouldNotResolveCallableElement(callingProcessDefinitionId, activityId, e);
            return null;
        }
    }

    private static function getCalledProcessDefinition(
        VariableScopeInterface $execution,
        BaseCallableElement $callableElement,
        string $processDefinitionKey,
        string $tenantId
    ): ProcessDefinitionEntity {

        $deploymentCache = getDeploymentCache();

        $processDefinition = null;

        if ($callableElement->isLatestBinding()) {
            $processDefinition = $deploymentCache->findDeployedLatestProcessDefinitionByKeyAndTenantId($processDefinitionKey, $tenantId);
        } elseif ($callableElement->isDeploymentBinding()) {
            $deploymentId = $callableElement->getDeploymentId();
            $processDefinition = $deploymentCache->findDeployedProcessDefinitionByDeploymentAndKey($deploymentId, $processDefinitionKey);
        } elseif ($callableElement->isVersionBinding()) {
            $version = $callableElement->getVersion($execution);
            $processDefinition = $deploymentCache->findDeployedProcessDefinitionByKeyVersionAndTenantId($processDefinitionKey, $version, $tenantId);
        } elseif ($callableElement->isVersionTagBinding()) {
            $versionTag = $callableElement->getVersionTag($execution);
            $processDefinition = $deploymentCache->findDeployedProcessDefinitionByKeyVersionTagAndTenantId($processDefinitionKey, $versionTag, $tenantId);
        }

        return $processDefinition;
    }
}
