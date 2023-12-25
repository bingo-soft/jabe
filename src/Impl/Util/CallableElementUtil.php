<?php

namespace Jabe\Impl\Util;

use Jabe\Delegate\VariableScopeInterface;
use Jabe\Impl\Context\Context;
use Jabe\Impl\Core\Model\BaseCallableElement;
use Jabe\Impl\El\StartProcessVariableScope;
use Jabe\Impl\Persistence\Deploy\Cache\DeploymentCache;
use Jabe\Impl\Persistence\Entity\ProcessDefinitionEntity;
use Jabe\Impl\Pvm\Process\ProcessDefinitionImpl;
use Jabe\Repository\ProcessDefinitionInterface;

class CallableElementUtil
{
    public static function getDeploymentCache(): DeploymentCache
    {
        return Context::getProcessEngineConfiguration()
            ->getDeploymentCache();
    }

    public static function getProcessDefinitionToCall(
        VariableScopeInterface $execution,
        ?string $defaultTenantId,
        BaseCallableElement $callableElement
    ): ProcessDefinitionImpl {
        $processDefinitionKey = $callableElement->getDefinitionKey($execution);
        $tenantId = $callableElement->getDefinitionTenantId($execution, $defaultTenantId);

        return self::getCalledProcessDefinition($execution, $callableElement, $processDefinitionKey, $tenantId);
    }

    public static function getStaticallyBoundProcessDefinition(
        ?string $callingProcessDefinitionId,
        ?string $activityId,
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
        } catch (\Throwable $e) {
            //UTIL_LOGGER.debugCouldNotResolveCallableElement(callingProcessDefinitionId, activityId, e);
            return null;
        }
    }

    private static function getCalledProcessDefinition(
        VariableScopeInterface $execution,
        BaseCallableElement $callableElement,
        ?string $processDefinitionKey,
        ?string $tenantId
    ): ProcessDefinitionEntity {

        $deploymentCache = self::getDeploymentCache();

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
