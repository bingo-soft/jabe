<?php

namespace Jabe\Engine\Impl;

use Jabe\Engine\Application\ProcessApplicationReferenceInterface;
use Jabe\Engine\RepositoryServiceInterface;
use Jabe\Engine\Exception\{
    DeploymentResourceNotFoundException,
    NotFoundException,
    NotValidException,
    NullValueException
};
use Jabe\Engine\Impl\Cmd\{
    DeleteDeploymentCmd,
    DeleteIdentityLinkForProcessDefinitionCmd,
    DeployCmd,
    GetDeployedProcessDefinitionCmd,
    GetDeploymentBpmnModelInstanceCmd,
    GetDeploymentProcessDiagramCmd,
    GetDeploymentProcessDiagramLayoutCmd,
    GetDeploymentProcessModelCmd,
    GetDeploymentResourceCmd,
    GetDeploymentResourceForIdCmd,
    GetDeploymentResourceNamesCmd,
    GetDeploymentResourcesCmd,
    GetIdentityLinksForProcessDefinitionCmd,
    GetStaticCalledProcessDefinitionCmd,
    UpdateDecisionDefinitionHistoryTimeToLiveCmd,
    UpdateProcessDefinitionHistoryTimeToLiveCmd
};
use Jabe\Engine\Impl\Pvm\ReadOnlyProcessDefinitionInterface;
use Jabe\Engine\Impl\Repository\{
    DeleteProcessDefinitionsBuilderImpl,
    DeploymentBuilderImpl,
    ProcessApplicationDeploymentBuilderImpl,
    UpdateProcessDefinitionSuspensionStateBuilderImpl
};
use Jabe\Engine\Repository\{
    DeleteProcessDefinitionsSelectBuilderInterface,
    DeploymentBuilderInterface,
    DeploymentQueryInterface,
    DeploymentWithDefinitionsInterface,
    DiagramLayout,
    ProcessApplicationDeploymentBuilderInterface,
    ProcessDefinitionInterface,
    ProcessDefinitionQueryInterface,
    UpdateProcessDefinitionSuspensionStateSelectBuilderInterface
};
use Jabe\Model\Bpmn\BpmnModelInstanceInterface;

class RepositoryServiceImpl extends ServiceImpl implements RepositoryServiceInterface
{
    protected $deploymentCharset;

    public function getDeploymentCharset(): string
    {
        return $this->deploymentCharset;
    }

    public function setDeploymentCharset(string $deploymentCharset): void
    {
        $this->deploymentCharset = $deploymentCharset;
    }

    public function createDeployment(ProcessApplicationReferenceInterface $processApplication = null): ProcessApplicationDeploymentBuilderInterface
    {
        if ($processApplication === null) {
            return new DeploymentBuilderImpl($this);
        } else {
            return new ProcessApplicationDeploymentBuilderImpl($this, $processApplication);
        }
    }

    public function deployWithResult(DeploymentBuilderImpl $deploymentBuilder): DeploymentWithDefinitionsInterface
    {
        return $this->commandExecutor->execute(new DeployCmd($deploymentBuilder));
    }

    public function deleteDeploymentCascade(string $deploymentId): void
    {
        $this->commandExecutor->execute(new DeleteDeploymentCmd($deploymentId, true, false, false));
    }

    public function deleteDeployment(string $deploymentId, bool $cascade = false, bool $skipCustomListeners = false, bool $skipIoMappings = false): void
    {
        $this->commandExecutor->execute(new DeleteDeploymentCmd($deploymentId, $cascade, $skipCustomListeners, $skipIoMappings));
    }

    public function deleteProcessDefinition(string $processDefinitionId, bool $cascade = false, bool $skipCustomListeners = false, bool $skipIoMappings = false): void
    {
        $builder = $this->deleteProcessDefinitions()->byIds($processDefinitionId);

        if ($cascade) {
            $builder->cascade();
        }

        if ($skipCustomListeners) {
            $builder->skipCustomListeners();
        }

        if ($skipIoMappings) {
            $builder->skipIoMappings();
        }

        $builder->delete();
    }

    public function deleteProcessDefinitions(): DeleteProcessDefinitionsSelectBuilderInterface
    {
        return new DeleteProcessDefinitionsBuilderImpl($this->commandExecutor);
    }

    public function createProcessDefinitionQuery(): ProcessDefinitionQueryInterface
    {
        return new ProcessDefinitionQueryImpl($this->commandExecutor);
    }

    /*public CaseDefinitionQuery createCaseDefinitionQuery() {
        return new CaseDefinitionQueryImpl(commandExecutor);
    }

    public DecisionDefinitionQuery createDecisionDefinitionQuery() {
        return new DecisionDefinitionQueryImpl(commandExecutor);
    }

    public DecisionRequirementsDefinitionQuery createDecisionRequirementsDefinitionQuery() {
        return new DecisionRequirementsDefinitionQueryImpl(commandExecutor);
    }*/

    public function getDeploymentResourceNames(string $deploymentId): array
    {
        return $this->commandExecutor->execute(new GetDeploymentResourceNamesCmd($deploymentId));
    }

    public function getDeploymentResources(string $deploymentId): array
    {
        return $this->commandExecutor->execute(new GetDeploymentResourcesCmd($deploymentId));
    }

    public function getResourceAsStream(string $deploymentId, string $resourceName)
    {
        return $this->commandExecutor->execute(new GetDeploymentResourceCmd($deploymentId, $resourceName));
    }

    public function getResourceAsStreamById(string $deploymentId, string $resourceId)
    {
        return $this->commandExecutor->execute(new GetDeploymentResourceForIdCmd($deploymentId, $resourceId));
    }

    public function createDeploymentQuery(): DeploymentQueryInterface
    {
        return new DeploymentQueryImpl(commandExecutor);
    }

    public function getProcessDefinition(string $processDefinitionId): ProcessDefinitionInterface
    {
        return $this->commandExecutor->execute(new GetDeployedProcessDefinitionCmd($processDefinitionId, true));
    }

    public function getDeployedProcessDefinition(string $processDefinitionId): ReadOnlyProcessDefinitionInterface
    {
        return $this->commandExecutor->execute(new GetDeployedProcessDefinitionCmd($processDefinitionId, true));
    }

    public function suspendProcessDefinitionById(string $processDefinitionId, bool $suspendProcessInstances = null, string $suspensionDate = null): void
    {
        if ($suspendProcessInstances === null) {
            $this->updateProcessDefinitionSuspensionState()
            ->byProcessDefinitionId($processDefinitionId)
            ->suspend();
        } else {
            $this->updateProcessDefinitionSuspensionState()
                ->byProcessDefinitionId($processDefinitionId)
                ->includeProcessInstances($suspendProcessInstances)
                ->executionDate($suspensionDate)
                ->suspend();
        }
    }

    public function suspendProcessDefinitionByKey(string $processDefinitionKey, bool $suspendProcessInstances = null, string $suspensionDate = null): void
    {
        if ($suspendProcessInstances === null) {
            $this->updateProcessDefinitionSuspensionState()
            ->byProcessDefinitionKey($processDefinitionKey)
            ->suspend();
        } else {
            $this->updateProcessDefinitionSuspensionState()
                ->byProcessDefinitionKey($processDefinitionKey)
                ->includeProcessInstances($suspendProcessInstances)
                ->executionDate($suspensionDate)
                ->suspend();
        }
    }

    public function activateProcessDefinitionById(string $processDefinitionId, bool $activateProcessInstances = null, string $activationDate = null): void
    {
        if ($activateProcessInstances === null) {
            $this->updateProcessDefinitionSuspensionState()
            ->byProcessDefinitionId($processDefinitionId)
            ->activate();
        } else {
            $this->updateProcessDefinitionSuspensionState()
                ->byProcessDefinitionId($processDefinitionId)
                ->includeProcessInstances($activateProcessInstances)
                ->executionDate($activationDate)
                ->activate();
        }
    }

    public function activateProcessDefinitionByKey(string $processDefinitionKey, bool $activateProcessInstances = null, string $activationDate = null): void
    {
        if ($activateProcessInstances === null) {
            $this->updateProcessDefinitionSuspensionState()
            ->byProcessDefinitionKey($processDefinitionKey)
            ->activate();
        } else {
            $this->updateProcessDefinitionSuspensionState()
                ->byProcessDefinitionKey($processDefinitionKey)
                ->includeProcessInstances($activateProcessInstances)
                ->executionDate($activationDate)
                ->activate();
        }
    }

    public function updateProcessDefinitionSuspensionState(): UpdateProcessDefinitionSuspensionStateSelectBuilderInterface
    {
        return new UpdateProcessDefinitionSuspensionStateBuilderImpl($this->commandExecutor);
    }

    public function updateProcessDefinitionHistoryTimeToLive(string $processDefinitionId, int $historyTimeToLive): void
    {
        $this->commandExecutor->execute(new UpdateProcessDefinitionHistoryTimeToLiveCmd($processDefinitionId, $historyTimeToLive));
    }

    /*public void updateDecisionDefinitionHistoryTimeToLive(string $decisionDefinitionId, Integer historyTimeToLive){
        $this->commandExecutor->execute(new UpdateDecisionDefinitionHistoryTimeToLiveCmd(decisionDefinitionId, historyTimeToLive));
    }

    public function updateCaseDefinitionHistoryTimeToLive(string $caseDefinitionId, Integer historyTimeToLive): void
    {
        $this->commandExecutor->execute(new UpdateCaseDefinitionHistoryTimeToLiveCmd(caseDefinitionId, historyTimeToLive));
    }*/

    public function getProcessModel(string $processDefinitionId)
    {
        return $this->commandExecutor->execute(new GetDeploymentProcessModelCmd($processDefinitionId));
    }

    public function getProcessDiagram(string $processDefinitionId)
    {
        return $this->commandExecutor->execute(new GetDeploymentProcessDiagramCmd($processDefinitionId));
    }

    /*public InputStream getCaseDiagram(string $caseDefinitionId) {
        return $this->commandExecutor->execute(new GetDeploymentCaseDiagramCmd(caseDefinitionId));
    }*/

    public function getProcessDiagramLayout(string $processDefinitionId): ?DiagramLayout
    {
        return $this->commandExecutor->execute(new GetDeploymentProcessDiagramLayoutCmd($processDefinitionId));
    }

    public function getBpmnModelInstance(string $processDefinitionId): BpmnModelInstanceInterface
    {
        return $this->commandExecutor->execute(new GetDeploymentBpmnModelInstanceCmd($processDefinitionId));
    }

    /*public CmmnModelInstance getCmmnModelInstance(string $caseDefinitionId) {
        try {
            return $this->commandExecutor->execute(new GetDeploymentCmmnModelInstanceCmd(caseDefinitionId));
        } catch (NullValueException e) {
            throw new NotValidException(e.getMessage(), e);
        } catch (CmmnModelInstanceNotFoundException e) {
            throw new NotFoundException(e.getMessage(), e);
        } catch (DeploymentResourceNotFoundException e) {
            throw new NotFoundException(e.getMessage(), e);
        }
    }

    public DmnModelInstance getDmnModelInstance(string $decisionDefinitionId) {
        try {
            return $this->commandExecutor->execute(new GetDeploymentDmnModelInstanceCmd(decisionDefinitionId));
        } catch (NullValueException e) {
            throw new NotValidException(e.getMessage(), e);
        } catch (DmnModelInstanceNotFoundException e) {
            throw new NotFoundException(e.getMessage(), e);
        } catch (DeploymentResourceNotFoundException e) {
            throw new NotFoundException(e.getMessage(), e);
        }
    }*/

    public function addCandidateStarterUser(string $processDefinitionId, string $userId): void
    {
        $this->commandExecutor->execute(new AddIdentityLinkForProcessDefinitionCmd($processDefinitionId, $userId, null));
    }

    public function addCandidateStarterGroup(string $processDefinitionId, string $groupId): void
    {
        $this->commandExecutor->execute(new AddIdentityLinkForProcessDefinitionCmd($processDefinitionId, null, $groupId));
    }

    public function deleteCandidateStarterGroup(string $processDefinitionId, string $groupId): void
    {
        $this->commandExecutor->execute(new DeleteIdentityLinkForProcessDefinitionCmd($processDefinitionId, null, $groupId));
    }

    public function deleteCandidateStarterUser(string $processDefinitionId, string $userId): void
    {
        $this->commandExecutor->execute(new DeleteIdentityLinkForProcessDefinitionCmd($processDefinitionId, $userId, null));
    }

    public function getIdentityLinksForProcessDefinition(string $processDefinitionId): array
    {
        return $this->commandExecutor->execute(new GetIdentityLinksForProcessDefinitionCmd($processDefinitionId));
    }

    /*public CaseDefinition getCaseDefinition(string $caseDefinitionId) {
        try {
            return $this->commandExecutor->execute(new GetDeploymentCaseDefinitionCmd(caseDefinitionId));
        } catch (NullValueException e) {
            throw new NotValidException(e.getMessage(), e);
        } catch (CaseDefinitionNotFoundException e) {
            throw new NotFoundException(e.getMessage(), e);
        }
    }

    public InputStream getCaseModel(string $caseDefinitionId) {
        try {
            return $this->commandExecutor->execute(new GetDeploymentCaseModelCmd(caseDefinitionId));
        } catch (NullValueException e) {
            throw new NotValidException(e.getMessage(), e);
        } catch (CaseDefinitionNotFoundException e) {
            throw new NotFoundException(e.getMessage(), e);
        } catch (DeploymentResourceNotFoundException e) {
            throw new NotFoundException(e.getMessage(), e);
        }
    }

    public DecisionDefinition getDecisionDefinition(string $decisionDefinitionId) {
        try {
            return $this->commandExecutor->execute(new GetDeploymentDecisionDefinitionCmd(decisionDefinitionId));
        } catch (NullValueException e) {
            throw new NotValidException(e.getMessage(), e);
        } catch (DecisionDefinitionNotFoundException e) {
            throw new NotFoundException(e.getMessage(), e);
        }
    }

    public DecisionRequirementsDefinition getDecisionRequirementsDefinition(string $decisionRequirementsDefinitionId) {
        try {
            return $this->commandExecutor->execute(new GetDeploymentDecisionRequirementsDefinitionCmd(decisionRequirementsDefinitionId));
        } catch (NullValueException e) {
            throw new NotValidException(e.getMessage(), e);
        } catch (DecisionDefinitionNotFoundException e) {
            throw new NotFoundException(e.getMessage(), e);
        }
    }

    public InputStream getDecisionModel(string $decisionDefinitionId) {
        try {
            return $this->commandExecutor->execute(new GetDeploymentDecisionModelCmd(decisionDefinitionId));
        } catch (NullValueException e) {
            throw new NotValidException(e.getMessage(), e);
        } catch (DecisionDefinitionNotFoundException e) {
            throw new NotFoundException(e.getMessage(), e);
        } catch (DeploymentResourceNotFoundException e) {
            throw new NotFoundException(e.getMessage(), e);
        }
    }

    public InputStream getDecisionRequirementsModel(string $decisionRequirementsDefinitionId) {
        try {
            return $this->commandExecutor->execute(new GetDeploymentDecisionRequirementsModelCmd(decisionRequirementsDefinitionId));
        } catch (NullValueException e) {
            throw new NotValidException(e.getMessage(), e);
        } catch (DecisionDefinitionNotFoundException e) {
            throw new NotFoundException(e.getMessage(), e);
        } catch (DeploymentResourceNotFoundException e) {
            throw new NotFoundException(e.getMessage(), e);
        }
    }

    public InputStream getDecisionDiagram(string $decisionDefinitionId) {
        return $this->commandExecutor->execute(new GetDeploymentDecisionDiagramCmd(decisionDefinitionId));
    }

    public InputStream getDecisionRequirementsDiagram(string $decisionRequirementsDefinitionId) {
        return $this->commandExecutor->execute(new GetDeploymentDecisionRequirementsDiagramCmd(decisionRequirementsDefinitionId));
    }*/

    public function getStaticCalledProcessDefinitions(string $processDefinitionId): array
    {
        return $this->commandExecutor->execute(new GetStaticCalledProcessDefinitionCmd($processDefinitionId));
    }
}
