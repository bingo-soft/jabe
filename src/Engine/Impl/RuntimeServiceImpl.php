<?php

namespace Jabe\Engine\Impl;

use Jabe\Engine\{
    BadUserRequestException,
    ProcessEngineException,
    RuntimeServiceInterface
};
use Jabe\Engine\Batch\BatchInterface;
use Jabe\Engine\Form\FormDataInterface;
use Jabe\Engine\History\HistoricProcessInstanceQueryInterface;
use Jabe\Engine\Impl\Cmd\{
    CreateIncidentCmd,
    DeleteProcessInstanceCmd,
    DeleteProcessInstancesCmd,
    FindActiveActivityIdsCmd,
    GetActivityInstanceCmd,
    GetExecutionVariableCmd,
    GetExecutionVariableTypedCmd,
    GetExecutionVariablesCmd,
    GetStartFormCmd,
    MessageEventReceivedCmd,
    PatchExecutionVariablesCmd,
    RemoveExecutionVariablesCmd,
    ResolveIncidentCmd,
    SetAnnotationForIncidentCmd,
    SetExecutionVariablesCmd,
    SignalCmd
};
use Jabe\Engine\Impl\Cmd\Batch\{
    CorrelateAllMessageBatchCmd,
    DeleteHistoricProcessInstancesBatchCmd
};
use Jabe\Engine\Impl\Cmd\Batch\Variables\SetVariablesToProcessInstancesBatchCmd;
use Jabe\Engine\Impl\Migration\{
    MigrationPlanBuilderImpl,
    MigrationPlanExecutionBuilderImpl
};
use Jabe\Engine\Impl\Runtime\{
    UpdateProcessInstanceSuspensionStateBuilderImpl
};
use Jabe\Engine\Impl\Util\{
    EnsureUtil,
    ExceptionUtil
};
use Jabe\Engine\Migration\{
    MigrationPlanInterface,
    MigrationPlanBuilderInterface,
    MigrationPlanExecutionBuilderInterface
};
use Jabe\Engine\Runtime\{
    ActivityInstanceInterface,
    ConditionEvaluationBuilderInterface,
    EventSubscriptionQueryInterface,
    ExecutionQueryInterface,
    IncidentInterface,
    IncidentQueryInterface,
    MessageCorrelationAsyncBuilderInterface,
    MessageCorrelationBuilderInterface,
    ModificationBuilderInterface,
    NativeExecutionQueryInterface,
    NativeProcessInstanceQueryInterface,
    ProcessInstanceInterface,
    ProcessInstanceModificationBuilderInterface,
    ProcessInstanceQueryInterface,
    ProcessInstantiationBuilderInterface,
    RestartProcessInstanceBuilderInterface,
    SignalEventReceivedBuilderInterface,
    UpdateProcessInstanceSuspensionStateSelectBuilderInterface,
    VariableInstanceQueryInterface
};
use Jabe\Engine\Variable\VariableMapInterface;
use Jabe\Engine\Variable\Value\TypedValueInterface;

class RuntimeServiceImpl extends ServiceImpl implements RuntimeServiceInterface
{
    public function startProcessInstanceByKey(string $processDefinitionKey, string $businessKey = null, array $variables = []): ProcessInstance
    {
        $res = $this->createProcessInstanceByKey($processDefinitionKey);
        if ($businessKey !== null) {
            $res->businessKey($businessKey);
        }
        if (!empty($variables)) {
            $res->setVariables($variables);
        }
        return $res->execute();
    }

    public function startProcessInstanceById(string $processDefinitionId, string $businessKey = null, array $variables = []): ProcessInstanceInterface
    {
        $res = $this->createProcessInstanceById($processDefinitionId);
        if ($businessKey !== null) {
            $res->businessKey($businessKey);
        }
        if (!empty($variables)) {
            $res->setVariables($variables);
        }
        return $res->execute();
    }

    public function deleteProcessInstancesAsync(
        array $processInstanceIds,
        ProcessInstanceQueryInterface $processInstanceQuery = null,
        HistoricProcessInstanceQueryInterface $historicProcessInstanceQuery = null,
        string $deleteReason = null,
        bool $skipCustomListeners = false,
        bool $skipSubprocesses = false
    ): BatchInterface {
        return $this->commandExecutor->execute(
            new DeleteProcessInstanceBatchCmd(
                $processInstanceIds,
                $processInstanceQuery,
                $historicProcessInstanceQuery,
                $deleteReason,
                $skipCustomListeners,
                $skipSubprocesses
            )
        );
    }

    public function deleteProcessInstance(
        string $processInstanceId,
        string $deleteReason = null,
        bool $skipCustomListeners = false,
        bool $skipIoMappings = false,
        bool $externallyTerminated = false,
        bool $skipSubprocesses = false
    ): void {
        $this->commandExecutor->execute(new DeleteProcessInstanceCmd($processInstanceId, $deleteReason, $skipCustomListeners, $externallyTerminated, $skipIoMappings, $skipSubprocesses, true));
    }

    public function deleteProcessInstanceIfExists(string $processInstanceId, string $deleteReason = null, bool $skipCustomListeners = false, bool $externallyTerminated = false, bool $skipIoMappings = false, bool $skipSubprocesses = false): void
    {
        $this->commandExecutor->execute(new DeleteProcessInstanceCmd($processInstanceId, $deleteReason, $skipCustomListeners, $externallyTerminated, $skipIoMappings, $skipSubprocesses, false));
    }

    public function deleteProcessInstances(
        array $processInstanceIds,
        string $deleteReason = null,
        bool $skipCustomListeners = false,
        bool $externallyTerminated = false,
        bool $skipSubprocesses = false
    ): void {
        $this->commandExecutor->execute(new DeleteProcessInstancesCmd($processInstanceIds, $deleteReason, $skipCustomListeners, $externallyTerminated, $skipSubprocesses, true));
    }

    public function deleteProcessInstancesIfExists(array $processInstanceIds, string $deleteReason = null, bool $skipCustomListeners = false, bool $externallyTerminated = false, bool $skipSubprocesses = false): void
    {
        $this->commandExecutor->execute(new DeleteProcessInstancesCmd($processInstanceIds, $deleteReason, $skipCustomListeners, $externallyTerminated, $skipSubprocesses, false));
    }

    public function createExecutionQuery(): ExecutionQueryInterface
    {
        return new ExecutionQueryImpl($this->commandExecutor);
    }

    public function createNativeExecutionQuery(): NativeExecutionQueryInterface
    {
        return new NativeExecutionQueryImpl($this->commandExecutor);
    }

    public function createNativeProcessInstanceQuery(): NativeProcessInstanceQueryInterface
    {
        return new NativeProcessInstanceQueryImpl($this->commandExecutor);
    }

    public function createIncidentQuery(): IncidentQueryInterface
    {
        return new IncidentQueryImpl($this->commandExecutor);
    }

    public function createEventSubscriptionQuery(): EventSubscriptionQueryInterface
    {
        return new EventSubscriptionQueryImpl($this->commandExecutor);
    }

    public function createVariableInstanceQuery(): VariableInstanceQueryInterface
    {
        return new VariableInstanceQueryImpl($this->commandExecutor);
    }

    public function getVariables(string $executionId, array $variableNames = []): VariableMapInterface
    {
        return $this->getVariablesTyped($executionId, $variableNames);
    }

    public function getVariablesLocal(string $executionId, array $variableNames = []): VariableMapInterface
    {
        return $this->getVariablesLocalTyped($executionId, $variableNames);
    }

    public function getVariablesLocalTyped(string $executionId, array $variableNames = [], bool $deserializeValues = true): VariableMapInterface
    {
        return $this->commandExecutor->execute(new GetExecutionVariablesCmd($executionId, $variableNames, true, $deserializeValues));
    }

    public function getVariablesTyped(string $executionId, array $variableNames = [], bool $deserializeValues = true): VariableMapInterface
    {
        return $this->commandExecutor->execute(new GetExecutionVariablesCmd($executionId, $variableNames, false, $deserializeValues));
    }

    public function getVariable(string $executionId, string $variableName)
    {
        return $this->commandExecutor->execute(new GetExecutionVariableCmd($executionId, $variableName, false));
    }

    public function getVariableTyped(string $executionId, string $variableName, bool $deserializeValue = true): ?TypedValueInterface
    {
        return $this->commandExecutor->execute(new GetExecutionVariableTypedCmd($executionId, $variableName, false, $deserializeValue));
    }

    public function getVariableLocalTyped(string $executionId, string $variableName, bool $deserializeValue = true): ?TypedValueInterface
    {
        return $this->commandExecutor->execute(new GetExecutionVariableTypedCmd($executionId, $variableName, true, $deserializeValue));
    }

    public function getVariableLocal(string $executionId, string $variableName)
    {
        return $this->commandExecutor->execute(new GetExecutionVariableCmd($executionId, $variableName, true));
    }

    public function setVariable(string $executionId, string $variableName, $value): void
    {
        EnsureUtil::ensureNotNull("variableName", "variableName", $variableName);
        $variables = [];
        $variables[$variableName] = $value;
        $this->setVariables($executionId, $variables);
    }

    public function setVariableLocal(string $executionId, string $variableName, $value): void
    {
        EnsureUtil::ensureNotNull("variableName", "variableName", $variableName);
        $variables = [];
        $variables[$variableName] = $value;
        $this->setVariablesLocal($executionId, $variables);
    }

    public function setVariables(string $executionId, array $variables = []): void
    {
        $this->doSetVariables($executionId, $variables, false);
    }

    public function setVariablesLocal(string $executionId, array $variables = []): void
    {
        $this->doSetVariables($executionId, $variables, true);
    }

    private function doSetVariables(string $executionId, array $variables, bool $local): void
    {
        try {
            $this->commandExecutor->execute(new SetExecutionVariablesCmd($executionId, $variables, $local));
        } catch (ProcessEngineException $ex) {
            if (ExceptionUtil::checkValueTooLongException($ex)) {
                throw new BadUserRequestException("Variable value is too long", $ex);
            }
            throw $ex;
        }
    }

    public function setVariablesAsync(
        array $processInstanceIds,
        ProcessInstanceQueryInterface $processInstanceQuery = null,
        HistoricProcessInstanceQuery $historicProcessInstanceQuery = null,
        array $variables = []
    ): BatchInterface {
        return $this->commandExecutor->execute(new SetVariablesToProcessInstancesBatchCmd(
            $processInstanceIds,
            $processInstanceQuery,
            $historicProcessInstanceQuery,
            $variables
        ));
    }

    public function removeVariable(string $executionId, string $variableName): void
    {
        $variableNames = [];
        $variableNames[] = $variableName;
        $this->commandExecutor->execute(new RemoveExecutionVariablesCmd($executionId, $variableNames, false));
    }

    public function removeVariableLocal(string $executionId, string $variableName): void
    {
        $variableNames = [];
        $variableNames[] = $variableName;
        $this->commandExecutor->execute(new RemoveExecutionVariablesCmd($executionId, $variableNames, true));
    }

    public function removeVariables(string $executionId, array $variableNames = []): void
    {
        $this->commandExecutor->execute(new RemoveExecutionVariablesCmd($executionId, $variableNames, false));
    }

    public function removeVariablesLocal(string $executionId, array $variableNames): void
    {
        $this->commandExecutor->execute(new RemoveExecutionVariablesCmd($executionId, $variableNames, true));
    }

    public function updateVariables(string $executionId, array $modifications = [], array $deletions = []): void
    {
        $this->doUpdateVariables($executionId, $modifications, $deletions, false);
    }

    public function updateVariablesLocal(string $executionId, array $modifications = [], array $deletions = []): void
    {
        $this->doUpdateVariables($executionId, $modifications, $deletions, true);
    }

    private function doUpdateVariables(string $executionId, array $modifications, array $deletions, bool $local): void
    {
        try {
            $this->commandExecutor->execute(new PatchExecutionVariablesCmd($executionId, $modifications, $deletions, $local));
        } catch (ProcessEngineException $ex) {
            if (ExceptionUtil::checkValueTooLongException($ex)) {
                throw new BadUserRequestException("Variable value is too long", $ex);
            }
            throw $ex;
        }
    }

    public function signal(string $executionId, string $signalName = null, $signalData = null, array $processVariables = []): void
    {
        $this->commandExecutor->execute(new SignalCmd($executionId, $signalName, $signalData, $processVariables));
    }

    public function createProcessInstanceQuery(): ProcessInstanceQueryInterface
    {
        return new ProcessInstanceQueryImpl($this->commandExecutor);
    }

    public function getActiveActivityIds(string $executionId): array
    {
        return $this->commandExecutor->execute(new FindActiveActivityIdsCmd($executionId));
    }

    public function getActivityInstance(string $processInstanceId): ActivityInstanceInterface
    {
        return $this->commandExecutor->execute(new GetActivityInstanceCmd($processInstanceId));
    }

    public function getFormInstanceById(string $processDefinitionId): FormDataInterface
    {
        return $this->commandExecutor->execute(new GetStartFormCmd($processDefinitionId));
    }

    public function suspendProcessInstanceById(string $processInstanceId): void
    {
        $this->updateProcessInstanceSuspensionState()
            ->byProcessInstanceId($processInstanceId)
            ->suspend();
    }

    public function suspendProcessInstanceByProcessDefinitionId(string $processDefinitionId): void
    {
        $this->updateProcessInstanceSuspensionState()
            ->byProcessDefinitionId($processDefinitionId)
            ->suspend();
    }

    public function suspendProcessInstanceByProcessDefinitionKey(string $processDefinitionKey): void
    {
        $this->updateProcessInstanceSuspensionState()
            ->byProcessDefinitionKey($processDefinitionKey)
            ->suspend();
    }

    public function activateProcessInstanceById(string $processInstanceId): void
    {
        $this->updateProcessInstanceSuspensionState()
            ->byProcessInstanceId($processInstanceId)
            ->activate();
    }

    public function activateProcessInstanceByProcessDefinitionId(string $processDefinitionId): void
    {
        $this->updateProcessInstanceSuspensionState()
            ->byProcessDefinitionId($processDefinitionId)
            ->activate();
    }

    public function activateProcessInstanceByProcessDefinitionKey(string $processDefinitionKey): void
    {
        $this->updateProcessInstanceSuspensionState()
            ->byProcessDefinitionKey($processDefinitionKey)
            ->activate();
    }

    public function updateProcessInstanceSuspensionState(): UpdateProcessInstanceSuspensionStateSelectBuilderInterface
    {
        return new UpdateProcessInstanceSuspensionStateBuilderImpl($this->commandExecutor);
    }

    public function startProcessInstanceByMessage(string $messageName, string $businessKey = null, array $processVariables = []): ProcessInstanceInterface
    {
        $res = $this->createMessageCorrelation($messageName);
        if ($businessKey !== null) {
            $res->processInstanceBusinessKey($businessKey);
        }
        if (!empty($processVariables)) {
            $res->setVariables($processVariables);
        }
        $res->correlateStartMessage();
    }

    public function startProcessInstanceByMessageAndProcessDefinitionId(string $messageName, string $processDefinitionId, string $businessKey = null, array $processVariables = []): ProcessInstanceInterface
    {
        $res = $this->createMessageCorrelation($messageName)->processDefinitionId($processDefinitionId);
        if ($businessKey !== null) {
            $res->processInstanceBusinessKey($businessKey);
        }
        if (!empty($processVariables)) {
            $res->setVariables($processVariables);
        }
        $res->correlateStartMessage();
    }

    public function signalEventReceived(string $signalName, string $executionId = null, array $processVariables = []): void
    {
        $res = $this->createSignalEvent($signalName);
        if ($executionId !== null) {
            $res->executionId($executionId);
        }
        if (!empty($processVariables)) {
            $res->setVariables($processVariables);
        }
        $res->send();
    }

    public function createSignalEvent(string $signalName): SignalEventReceivedBuilderInterface
    {
        return new SignalEventReceivedBuilderImpl($this->commandExecutor, $signalName);
    }

    public function messageEventReceived(string $messageName, string $executionId, array $processVariables = []): void
    {
        EnsureUtil::ensureNotNull("messageName", "messageName", $messageName);
        $this->commandExecutor->execute(new MessageEventReceivedCmd($messageName, $executionId, $processVariables));
    }

    public function createMessageCorrelation(string $messageName): MessageCorrelationBuilderInterface
    {
        return new MessageCorrelationBuilderImpl($this->commandExecutor, $messageName);
    }

    public function correlateMessage(string $messageName, string $businessKey = null, array $correlationKeys = null, array $processVariables = null): void
    {
        $res = $this->createMessageCorrelation($messageName);
        if ($businessKey !== null) {
            $res->processInstanceBusinessKey($businessKey);
        }
        if (!empty($correlationKeys)) {
            $res->processInstanceVariablesEqual($correlationKeys);
        }
        if (!empty($processVariables)) {
            $res->setVariables($processVariables);
        }
        $res->correlate();
    }

    public function createMessageCorrelationAsync(string $messageName): MessageCorrelationAsyncBuilder
    {
        return new MessageCorrelationAsyncBuilderImpl($this->commandExecutor, $messageName);
    }

    public function createProcessInstanceModification(string $processInstanceId): ProcessInstanceModificationBuilderInterface
    {
        return new ProcessInstanceModificationBuilderImpl($this->commandExecutor, $processInstanceId);
    }

    public function createProcessInstanceById(string $processDefinitionId): ProcessInstantiationBuilderInterface
    {
        return ProcessInstantiationBuilderImpl::createProcessInstanceById($this->commandExecutor, $processDefinitionId);
    }

    public function createProcessInstanceByKey(string $processDefinitionKey): ProcessInstantiationBuilderInterface
    {
        return ProcessInstantiationBuilderImpl::createProcessInstanceByKey($this->commandExecutor, $processDefinitionKey);
    }

    public function createMigrationPlan(string $sourceProcessDefinitionId, string $targetProcessDefinitionId): MigrationPlanBuilderInterface
    {
        return new MigrationPlanBuilderImpl($this->commandExecutor, $sourceProcessDefinitionId, $targetProcessDefinitionId);
    }

    public function newMigration(MigrationPlanInterface $migrationPlan): MigrationPlanExecutionBuilderInterface
    {
        return new MigrationPlanExecutionBuilderImpl($this->commandExecutor, $migrationPlan);
    }

    public function createModification(string $processDefinitionId): ModificationBuilderInterface
    {
        return new ModificationBuilderImpl($this->commandExecutor, $processDefinitionId);
    }

    public function restartProcessInstances(string $processDefinitionId): RestartProcessInstanceBuilderInterface
    {
        return new RestartProcessInstanceBuilderImpl($this->commandExecutor, $processDefinitionId);
    }

    public function createIncident(string $incidentType, string $executionId, string $configuration, string $message = null): IncidentInterface
    {
        return $this->commandExecutor->execute(new CreateIncidentCmd($incidentType, $executionId, $configuration, $message));
    }

    public function resolveIncident(string $incidentId): void
    {
        $this->commandExecutor->execute(new ResolveIncidentCmd($incidentId));
    }

    public function setAnnotationForIncidentById(string $incidentId, string $annotation): void
    {
        $this->commandExecutor->execute(new SetAnnotationForIncidentCmd($incidentId, $annotation));
    }

    public function clearAnnotationForIncidentById(string $incidentId): void
    {
        $this->commandExecutor->execute(new SetAnnotationForIncidentCmd($incidentId, null));
    }

    public function createConditionEvaluation(): ConditionEvaluationBuilderInterface
    {
        return new ConditionEvaluationBuilderImpl($this->commandExecutor);
    }
}
