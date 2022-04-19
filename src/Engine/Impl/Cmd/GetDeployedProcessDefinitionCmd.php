<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\Impl\ProcessInstantiationBuilderImpl;
use BpmPlatform\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use BpmPlatform\Engine\Impl\Persistence\Deploy\Cache\DeploymentCache;
use BpmPlatform\Engine\Impl\Persistence\Entity\ProcessDefinitionEntity;
use BpmPlatform\Engine\Impl\Util\EnsureUtil;

class GetDeployedProcessDefinitionCmd implements CommandInterface
{
    protected $processDefinitionId;
    protected $processDefinitionKey;
    protected $processDefinitionTenantId;
    protected $isTenantIdSet = false;
    protected $checkReadPermission;

    public function __construct($el, bool $checkReadPermission)
    {
        if (is_string($el)) {
            $this->processDefinitionId = $el;
        } elseif ($el instanceof ProcessInstantiationBuilderImpl) {
            $this->processDefinitionId = $el->getProcessDefinitionId();
            $this->processDefinitionKey = $el->getProcessDefinitionKey();
            $this->processDefinitionTenantId = $el->getProcessDefinitionTenantId();
            $this->isTenantIdSet = $el->isProcessDefinitionTenantIdSet();
        }
        $this->checkReadPermission = $checkReadPermission;
    }

    public function execute(CommandContext $commandContext)
    {
        EnsureUtil::ensureOnlyOneNotNull("either process definition id or key must be set", $this->processDefinitionId, $this->processDefinitionKey);

        $processDefinition = $this->find($commandContext);

        if ($this->checkReadPermission) {
            foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
                $checker->checkReadProcessDefinition($processDefinition);
            }
        }

        return $processDefinition;
    }

    protected function find(CommandContext $commandContext): ProcessDefinitionEntity
    {
        $deploymentCache = $commandContext->getProcessEngineConfiguration()->getDeploymentCache();

        if ($this->processDefinitionId != null) {
            return $this->findById($deploymentCache, $this->processDefinitionId);
        } else {
            return $this->findByKey($deploymentCache, $this->processDefinitionKey);
        }
    }

    protected function findById(DeploymentCache $deploymentCache, string $processDefinitionId): ?ProcessDefinitionEntity
    {
        return $deploymentCache->findDeployedProcessDefinitionById($processDefinitionId);
    }

    protected function findByKey(DeploymentCache $deploymentCache, string $processDefinitionKey): ?ProcessDefinitionEntity
    {
        if ($this->isTenantIdSet) {
            return $deploymentCache->findDeployedLatestProcessDefinitionByKeyAndTenantId($processDefinitionKey, $this->processDefinitionTenantId);
        } else {
            return $deploymentCache->findDeployedLatestProcessDefinitionByKey(processDefinitionKey);
        }
    }
}
