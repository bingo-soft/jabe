<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\Exception\NotFoundException;
use Jabe\Engine\History\UserOperationLogEntryInterface;
use Jabe\Engine\Impl\Bpmn\Deployer\BpmnDeployer;
use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Engine\Impl\Persistence\Entity\{
    ProcessDefinitionEntity,
    ProcessDefinitionManager,
    PropertyChange,
    UserOperationLogManager
};
use Jabe\Engine\Impl\Util\EnsureUtil;
use Jabe\Engine\Repository\ProcessDefinitionInterface;

class DeleteProcessDefinitionsByIdsCmd implements CommandInterface, \Serializable
{
    protected $processDefinitionIds = [];
    protected $cascadeToHistory;
    protected $cascadeToInstances;
    protected $skipCustomListeners;
    protected $writeUserOperationLog;
    protected $skipIoMappings;

    public function __construct(
        ?array $processDefinitionIds,
        bool $cascadeToHistory,
        bool $cascadeToInstances,
        bool $skipCustomListeners,
        ?bool $skipIoMappings = false,
        ?bool $writeUserOperationLog = true
    ) {
        $this->processDefinitionIds = $processDefinitionIds;
        $this->cascadeToHistory = $cascadeToHistory;
        $this->cascadeToInstances = $cascadeToInstances;
        $this->skipCustomListeners = $skipCustomListeners;
        $this->skipIoMappings = $skipIoMappings;
        $this->writeUserOperationLog = $writeUserOperationLog;
    }

    public function serialize()
    {
        return json_encode([
            'processDefinitionIds' => $this->processDefinitionIds,
            'cascadeToHistory' => $this->cascadeToHistory,
            'cascadeToInstances' => $this->cascadeToInstances,
            'skipCustomListeners' => $this->skipCustomListeners,
            'skipIoMappings' => $this->skipIoMappings,
            'writeUserOperationLog' => $this->writeUserOperationLog
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->processDefinitionIds = $json->processDefinitionIds;
        $this->cascadeToHistory = $json->cascadeToHistory;
        $this->cascadeToInstances = $json->cascadeToInstances;
        $this->skipCustomListeners = $json->skipCustomListeners;
        $this->skipIoMappings = $json->skipIoMappings;
        $this->writeUserOperationLog = $json->writeUserOperationLog;
    }

    public function execute(CommandContext $commandContext)
    {
        EnsureUtil::ensureNotNull("processDefinitionIds", "processDefinitionIds", $this->processDefinitionIds);

        $processDefinitions = [];
        if (count($this->processDefinitionIds) == 1) {
            $processDefinition = $this->getSingleProcessDefinition($commandContext);
            $processDefinitions[] = $processDefinition;
        } else {
            $processDefinitionManager = $commandContext->getProcessDefinitionManager();
            $processDefinitions = $processDefinitionManager->findDefinitionsByIds($this->processDefinitionIds);
            EnsureUtil::ensureNotEmpty(NotFoundException::class, "processDefinitions", $processDefinitions);
        }

        $groups = $this->groupByKeyAndTenant($processDefinitions);

        foreach ($groups as $group) {
            $this->checkAuthorization($group);
        }

        foreach ($groups as $group) {
            $this->deleteProcessDefinitions($group);
        }

        return null;
    }

    protected function getSingleProcessDefinition(CommandContext $commandContext): ?ProcessDefinitionInterface
    {
        $processDefinitionId = $processDefinitionIds[0];
        EnsureUtil::ensureNotNull("processDefinitionId", "processDefinitionId", $this->processDefinitionId);
        $processDefinition = $commandContext->getProcessDefinitionManager()->findLatestProcessDefinitionById($this->processDefinitionId);
        EnsureUtil::ensureNotNull("No process definition found with id '" . $this->processDefinitionId . "'", "processDefinition", $processDefinition);
        return $processDefinition;
    }

    protected function groupByKeyAndTenant(array $processDefinitions): array
    {
        $groups = [];
        $map = [];

        foreach ($processDefinitions as $current) {
            $processDefinition = $current;

            $group = new ProcessDefinitionGroup();
            $group->key = $processDefinition->getKey();
            $group->tenant = $processDefinition->getTenantId();

            $definitions = $group->processDefinitions;
            if (array_key_exists(strval($group), $map)) {
                $definitions = $map[$group];
            } else {
                $groups[] = $group;
                $map[strval($group)] = $definitions;
            }

            $definitions[] = $processDefinition;
        }

        return $groups;
    }

    protected function findNewLatestProcessDefinition(ProcessDefinitionGroup $group): ?ProcessDefinitionEntity
    {
        $newLatestProcessDefinition = null;

        $processDefinitions = $group->processDefinitions;
        $firstProcessDefinition = $processDefinitions[0];

        if ($this->isLatestProcessDefinition($firstProcessDefinition)) {
            foreach ($processDefinitions as $processDefinition) {
                $previousProcessDefinitionId = $processDefinition->getPreviousProcessDefinitionId();
                if ($previousProcessDefinitionId !== null && !in_array($previousProcessDefinitionId, $this->processDefinitionIds)) {
                    $commandContext = Context::getCommandContext();
                    $processDefinitionManager = $commandContext->getProcessDefinitionManager();
                    $newLatestProcessDefinition = $processDefinitionManager->findLatestDefinitionById($previousProcessDefinitionId);
                    break;
                }
            }
        }

        return $newLatestProcessDefinition;
    }

    protected function isLatestProcessDefinition(ProcessDefinitionEntity $processDefinition): bool
    {
        $processDefinitionManager = Context::getCommandContext()->getProcessDefinitionManager();
        $key = $processDefinition->getKey();
        $tenantId = $processDefinition->getTenantId();
        $latestProcessDefinition = $processDefinitionManager->findLatestDefinitionByKeyAndTenantId($key, $tenantId);
        return $processDefinition->getId() == $latestProcessDefinition->getId();
    }

    protected function checkAuthorization(ProcessDefinitionGroup $group): void
    {
        $commandCheckers = Context::getCommandContext()->getProcessEngineConfiguration()->getCommandCheckers();
        $processDefinitions = $group->processDefinitions;
        foreach ($processDefinitions as $processDefinition) {
            foreach ($commandCheckers as $commandChecker) {
                $commandChecker->checkDeleteProcessDefinitionById($processDefinition->getId());
            }
        }
    }

    protected function deleteProcessDefinitions(ProcessDefinitionGroup $group): void
    {
        $newLatestProcessDefinition = $this->findNewLatestProcessDefinition($group);

        $commandContext = Context::getCommandContext();
        $userOperationLogManager = $commandContext->getOperationLogManager();
        $definitionManager = $commandContext->getProcessDefinitionManager();

        $processDefinitions = $group->processDefinitions;
        foreach ($processDefinitions as $processDefinition) {
            $processDefinitionId = $processDefinition->getId();

            if ($this->writeUserOperationLog) {
                $userOperationLogManager->logProcessDefinitionOperation(
                    UserOperationLogEntryInterface::OPERATION_TYPE_DELETE,
                    $processDefinitionId,
                    $processDefinition->getKey(),
                    new PropertyChange("cascade", false, $this->cascadeToHistory)
                );
            }

            $definitionManager->deleteProcessDefinition(
                $processDefinition,
                $processDefinitionId,
                $this->cascadeToHistory,
                $this->cascadeToInstances,
                $this->skipCustomListeners,
                $this->skipIoMappings
            );
        }

        if ($newLatestProcessDefinition !== null) {
            $configuration = Context::getProcessEngineConfiguration();
            $deploymentCache = $configuration->getDeploymentCache();
            $newLatestProcessDefinition = $deploymentCache->resolveProcessDefinition($newLatestProcessDefinition);

            $deployers = $configuration->getDeployers();
            foreach ($deployers as $deployer) {
                if ($deployer instanceof BpmnDeployer) {
                    $deployer->addEventSubscriptions($newLatestProcessDefinition);
                }
            }
        }
    }
}
