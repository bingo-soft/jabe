<?php

namespace Jabe\Impl\Persistence\Entity;

use Jabe\Authorization\{
    PermissionInterface,
    ResourceInterface,
    Resources
};
use Jabe\Impl\{
    DeploymentQueryImpl,
    Page
};
use Jabe\Impl\Cfg\ProcessEngineConfigurationImpl;
use Jabe\Impl\Cmd\DeleteProcessDefinitionsByIdsCmd;
use Jabe\Impl\Context\Context;
use Jabe\Impl\Persistence\AbstractManager;
use Jabe\Impl\Persistence\Deploy\Cache\DeploymentCache;
use Jabe\Impl\Util\ClockUtil;
use Jabe\Repository\{
    DeploymentInterface,
    ProcessDefinitionInterface,
    ResourceTypes
};

class DeploymentManager extends AbstractManager
{
    public function __construct(...$args)
    {
        parent::__construct(...$args);
    }

    public function insertDeployment(DeploymentEntity $deployment): void
    {
        $this->getDbEntityManager()->insert($deployment, ...$this->jobExecutorState);
        $this->createDefaultAuthorizations($deployment);

        foreach ($deployment->getResources() as $resource) {
            $resource->setDeploymentId($deployment->getId());
            $resource->setType(ResourceTypes::repository()->getValue());
            $resource->setCreateTime(ClockUtil::getCurrentTime()->format('Y-m-d H:i:s'));
            $this->getResourceManager()->insertResource($resource);
        }

        Context::getProcessEngineConfiguration()
            ->getDeploymentCache()
            ->deploy($deployment);
    }

    public function deleteDeployment(?string $deploymentId, bool $cascade, ?bool $skipCustomListeners = false, ?bool $skipIoMappings = false): void
    {
        $processDefinitions = $this->getProcessDefinitionManager()->findProcessDefinitionsByDeploymentId($deploymentId);
        if ($cascade) {
            // *NOTE*:
            // The process instances of ALL process definitions must be
            // deleted, before every process definition can be deleted!
            //
            // On deletion of all process instances, the task listeners will
            // be deleted as well. Deletion of tasks and listeners needs
            // the redeployment of deployments, which can cause to problems if
            // is done sequential with deletion of process definition.
            //
            // For example:
            // Deployment contains two process definiton. First process definition
            // and instances will be removed, also cleared from the cache.
            // Second process definition will be removed and his instances.
            // Deletion of instances will cause redeployment this deploys again
            // first into the cache. Only the second will be removed from cache and
            // first remains in the cache after the deletion process.
            //
            // Thats why we have to clear up all instances at first, after that
            // we can cleanly remove the process definitions.
            foreach ($processDefinitions as $processDefinition) {
                $processDefinitionId = $processDefinition->getId();
                $this->getProcessInstanceManager()
                    ->deleteProcessInstancesByProcessDefinition(
                        $processDefinitionId,
                        "deleted deployment",
                        true,
                        $skipCustomListeners,
                        $skipIoMappings
                    );
            }
            // delete historic job logs (for example for timer start event jobs)
            $this->getHistoricJobLogManager()->deleteHistoricJobLogsByDeploymentId($deploymentId);
        }

        foreach ($processDefinitions as $processDefinition) {
            $processDefinitionId = $processDefinition->getId();
            // Process definition cascade true deletes the history and
            // process instances if instances flag is set as well to true.
            // Problem as described above, redeployes the deployment.
            // Represents no problem if only one process definition is deleted
            // in a transaction! We have to set the instances flag to false.
            $commandContext = Context::getCommandContext();
            $commandContext->runWithoutAuthorization(function () use ($commandContext, $processDefinitionId, $cascade, $skipCustomListeners) {
                $cmd = new DeleteProcessDefinitionsByIdsCmd(
                    [$processDefinitionId],
                    $cascade,
                    false,
                    $skipCustomListeners,
                    false
                );
                $cmd->execute($commandContext);
            });
        }

        //deleteCaseDeployment(deploymentId, cascade);

        //deleteDecisionDeployment(deploymentId, cascade);
        //deleteDecisionRequirementDeployment(deploymentId);

        $this->deleteFormDefinitionDeployment($deploymentId);

        $this->getResourceManager()->deleteResourcesByDeploymentId($deploymentId);

        $this->deleteAuthorizations(Resources::deployment(), $deploymentId);
        $this->getDbEntityManager()->delete(DeploymentEntity::class, "deleteDeployment", $deploymentId);
    }

    /*protected void deleteCaseDeployment(String deploymentId, boolean cascade) {
        ProcessEngineConfigurationImpl processEngineConfiguration = Context->getProcessEngineConfiguration();
        if (processEngineConfiguration.isCmmnEnabled()) {
            List<CaseDefinition> caseDefinitions = getCaseDefinitionManager().findCaseDefinitionByDeploymentId(deploymentId);

            if (cascade) {

                // delete case instances
                for (CaseDefinition caseDefinition: caseDefinitions) {
                    String caseDefinitionId = caseDefinition->getId();

                    getCaseInstanceManager()
                    .deleteCaseInstancesByCaseDefinition(caseDefinitionId, "deleted deployment", true);

                }
            }

            // delete case definitions from db
            getCaseDefinitionManager()
            .deleteCaseDefinitionsByDeploymentId(deploymentId);

            for (CaseDefinition caseDefinition : caseDefinitions) {
                String processDefinitionId = caseDefinition->getId();

                // remove case definitions from cache:
                Context
                    ->getProcessEngineConfiguration()
                    ->getDeploymentCache()
                    .removeCaseDefinition(processDefinitionId);
            }
        }
    }

    protected void deleteDecisionDeployment(String deploymentId, boolean cascade) {
        ProcessEngineConfigurationImpl processEngineConfiguration = Context->getProcessEngineConfiguration();
        if (processEngineConfiguration.isDmnEnabled()) {
            DecisionDefinitionManager decisionDefinitionManager = getDecisionDefinitionManager();
            List<DecisionDefinition> decisionDefinitions = decisionDefinitionManager.findDecisionDefinitionByDeploymentId(deploymentId);

            if(cascade) {
                // delete historic decision instances
                for(DecisionDefinition decisionDefinition : decisionDefinitions) {
                    getHistoricDecisionInstanceManager().deleteHistoricDecisionInstancesByDecisionDefinitionId(decisionDefinition->getId());
                }
            }

            // delete decision definitions from db
            decisionDefinitionManager
            .deleteDecisionDefinitionsByDeploymentId(deploymentId);

            DeploymentCache deploymentCache = processEngineConfiguration->getDeploymentCache();

            for (DecisionDefinition decisionDefinition : decisionDefinitions) {
                String decisionDefinitionId = decisionDefinition->getId();

                // remove decision definitions from cache:
                deploymentCache
                    .removeDecisionDefinition(decisionDefinitionId);
            }
        }
    }

    protected void deleteDecisionRequirementDeployment(String deploymentId) {
        ProcessEngineConfigurationImpl processEngineConfiguration = Context->getProcessEngineConfiguration();
        if (processEngineConfiguration.isDmnEnabled()) {
            DecisionRequirementsDefinitionManager manager = getDecisionRequirementsDefinitionManager();
            List<DecisionRequirementsDefinition> decisionRequirementsDefinitions =
                manager.findDecisionRequirementsDefinitionByDeploymentId(deploymentId);

            // delete decision requirements definitions from db
            manager.deleteDecisionRequirementsDefinitionsByDeploymentId(deploymentId);

            DeploymentCache deploymentCache = processEngineConfiguration->getDeploymentCache();

            for (DecisionRequirementsDefinition decisionRequirementsDefinition : decisionRequirementsDefinitions) {
                String decisionDefinitionId = decisionRequirementsDefinition->getId();

                // remove decision requirements definitions from cache:
                deploymentCache.removeDecisionRequirementsDefinition(decisionDefinitionId);
            }
        }
    }*/

    protected function deleteFormDefinitionDeployment(?string $deploymentId): void
    {
        $manager = $this->getFormDefinitionManager();

        $formDefinitions = $manager->findDefinitionsByDeploymentId($deploymentId);

        // delete definitions from db
        $manager->deleteFormDefinitionsByDeploymentId($deploymentId);

        // delete definitions from deployment cache
        $processEngineConfiguration = Context::getProcessEngineConfiguration();
        $deploymentCache = $processEngineConfiguration->getDeploymentCache();
        foreach ($formDefinitions as $formDefinition) {
            $deploymentCache->removeFormDefinition($formDefinition->getId());
        }
    }

    public function findLatestDeploymentByName(?string $deploymentName): ?DeploymentEntity
    {
        $list = $this->getDbEntityManager()->selectList("selectDeploymentsByName", $deploymentName, 0, 1);
        if (!empty($list)) {
            return $list[0];
        }
        return null;
    }

    public function findDeploymentById(?string $deploymentId): ?DeploymentEntity
    {
        return $this->getDbEntityManager()->selectById(DeploymentEntity::class, $deploymentId);
    }

    public function findDeploymentsByIds(array $deploymentsIds): array
    {
        return $this->getDbEntityManager()->selectList("selectDeploymentsByIds", $deploymentsIds);
    }

    public function findDeploymentCountByQueryCriteria(DeploymentQueryImpl $deploymentQuery): int
    {
        $this->configureQuery($deploymentQuery);
        return $this->getDbEntityManager()->selectOne("selectDeploymentCountByQueryCriteria", $deploymentQuery);
    }

    public function findDeploymentsByQueryCriteria(DeploymentQueryImpl $deploymentQuery, ?Page $page): array
    {
        $this->configureQuery($deploymentQuery);
        return $this->getDbEntityManager()->selectList("selectDeploymentsByQueryCriteria", $deploymentQuery, $page);
    }

    public function getDeploymentResourceNames(?string $deploymentId): array
    {
        return $this->getDbEntityManager()->selectList("selectResourceNamesByDeploymentId", $deploymentId);
    }

    public function findDeploymentIdsByProcessInstances(array $processInstanceIds): array
    {
        return $this->getDbEntityManager()->selectList("selectDeploymentIdsByProcessInstances", $processInstanceIds);
    }

    public function close(): void
    {
    }

    public function flush(): void
    {
    }

    // helper /////////////////////////////////////////////////

    protected function createDefaultAuthorizations(DeploymentEntity $deployment): void
    {
        if ($this->isAuthorizationEnabled()) {
            $provider = $this->getResourceAuthorizationProvider();
            $authorizations = $provider->newDeployment($deployment);
            $this->saveDefaultAuthorizations($authorizations);
        }
    }

    public function configureQuery($query, ?ResourceInterface $resource = null, ?string $queryParam = "RES.ID_", ?PermissionInterface $permission = null)
    {
        $this->getAuthorizationManager()->configureDeploymentQuery($query);
        $this->getTenantManager()->configureQuery($query);
    }
}
