<?php

namespace Jabe\Impl;

use Jabe\HistoryServiceInterface;
use Jabe\Batch\BatchInterface;
use Jabe\Batch\History\HistoricBatchQueryInterface;
use Jabe\History\{
    CleanableHistoricBatchReportInterface,
    CleanableHistoricProcessInstanceReportInterface,
    HistoricActivityInstanceQueryInterface,
    HistoricActivityStatisticsQueryInterface,
    HistoricDetailQueryInterface,
    HistoricExternalTaskLogQueryInterface,
    HistoricIncidentQueryInterface,
    HistoricJobLogQueryInterface,
    HistoricProcessInstanceQueryInterface,
    HistoricProcessInstanceReportInterface,
    HistoricTaskInstanceQueryInterface,
    HistoricTaskInstanceReportInterface,
    HistoricVariableInstanceQueryInterface,
    NativeHistoricActivityInstanceQueryInterface,
    NativeHistoricProcessInstanceQueryInterface,
    NativeHistoricTaskInstanceQueryInterface,
    NativeHistoricVariableInstanceQueryInterface,
    SetRemovalTimeSelectModeForHistoricBatchesBuilderInterface,
    SetRemovalTimeSelectModeForHistoricProcessInstancesBuilderInterface,
    SetRemovalTimeToHistoricBatchesBuilderInterface,
    SetRemovalTimeToHistoricProcessInstancesBuilderInterface,
    UserOperationLogQueryInterface
};
use Jabe\Impl\Batch\History\{
    DeleteHistoricBatchCmd,
    HistoricBatchQueryImpl
};
use Jabe\Impl\Cmd\{
    DeleteHistoricProcessInstancesCmd,
    DeleteHistoricTaskInstanceCmd,
    DeleteHistoricVariableInstanceCmd,
    DeleteHistoricVariableInstancesByProcessInstanceIdCmd,
    DeleteUserOperationLogEntryCmd,
    FindHistoryCleanupJobsCmd,
    GetHistoricExternalTaskLogErrorDetailsCmd,
    GetHistoricJobLogExceptionStacktraceCmd,
    HistoryCleanupCmd
};
use Jabe\Impl\Cmd\Batch\DeleteHistoricProcessInstancesBatchCmd;
use Jabe\Impl\History\{
    SetRemovalTimeToHistoricBatchesBuilderImpl,
    //SetRemovalTimeToHistoricDecisionInstancesBuilderImpl,
    SetRemovalTimeToHistoricProcessInstancesBuilderImpl
};
use Jabe\Runtime\JobInterface;

class HistoryServiceImpl extends ServiceImpl implements HistoryServiceInterface
{
    public function createHistoricProcessInstanceQuery(): HistoricProcessInstanceQuery
    {
        return new HistoricProcessInstanceQueryImpl($this->commandExecutor);
    }

    public function createHistoricActivityInstanceQuery(): HistoricActivityInstanceQueryInterface
    {
        return new HistoricActivityInstanceQueryImpl($this->commandExecutor);
    }

    public function createHistoricActivityStatisticsQuery(string $processDefinitionId): HistoricActivityStatisticsQuery
    {
        return new HistoricActivityStatisticsQueryImpl($processDefinitionId, $this->commandExecutor);
    }

    /*public HistoricCaseActivityStatisticsQuery createHistoricCaseActivityStatisticsQuery(string $caseDefinitionId) {
        return new HistoricCaseActivityStatisticsQueryImpl(caseDefinitionId, $this->commandExecutor);
    }*/

    public function createHistoricTaskInstanceQuery(): HistoricTaskInstanceQueryInterface
    {
        return new HistoricTaskInstanceQueryImpl($this->commandExecutor);
    }

    public function createHistoricDetailQuery(): HistoricDetailQueryInterface
    {
        return new HistoricDetailQueryImpl($this->commandExecutor);
    }

    public function createUserOperationLogQuery(): UserOperationLogQueryInterface
    {
        return new UserOperationLogQueryImpl($this->commandExecutor);
    }

    public function createHistoricVariableInstanceQuery(): HistoricVariableInstanceQueryInterface
    {
        return new HistoricVariableInstanceQueryImpl($this->commandExecutor);
    }

    public function createHistoricIncidentQuery(): HistoricIncidentQueryInterface
    {
        return new HistoricIncidentQueryImpl($this->commandExecutor);
    }

    public function createHistoricIdentityLinkLogQuery(): HistoricIdentityLinkLogQueryImpl
    {
        return new HistoricIdentityLinkLogQueryImpl($this->commandExecutor);
    }

    /*public HistoricCaseInstanceQuery createHistoricCaseInstanceQuery() {
        return new HistoricCaseInstanceQueryImpl($this->commandExecutor);
    }

    public HistoricCaseActivityInstanceQuery createHistoricCaseActivityInstanceQuery() {
        return new HistoricCaseActivityInstanceQueryImpl($this->commandExecutor);
    }

    public HistoricDecisionInstanceQuery createHistoricDecisionInstanceQuery() {
        return new HistoricDecisionInstanceQueryImpl($this->commandExecutor);
    }*/

    public function deleteHistoricTaskInstance(string $taskId): void
    {
        $this->commandExecutor->execute(new DeleteHistoricTaskInstanceCmd($taskId));
    }

    public function deleteHistoricProcessInstance(string $processInstanceId): void
    {
        $this->deleteHistoricProcessInstances([$processInstanceId]);
    }

    public function deleteHistoricProcessInstanceIfExists(string $processInstanceId): void
    {
        $this->deleteHistoricProcessInstancesIfExists([$processInstanceId]);
    }

    public function deleteHistoricProcessInstances(array $processInstanceIds): void
    {
        $this->commandExecutor->execute(new DeleteHistoricProcessInstancesCmd($processInstanceIds, true));
    }

    public function deleteHistoricProcessInstancesIfExists(array $processInstanceIds): void
    {
        $this->commandExecutor->execute(new DeleteHistoricProcessInstancesCmd($processInstanceIds, false));
    }

    public function deleteHistoricProcessInstancesBulk(array $processInstanceIds): void
    {
        $this->deleteHistoricProcessInstances($processInstanceIds);
    }

    public function cleanUpHistoryAsync(bool $immediatelyDue = false): JobInterface
    {
        return $this->commandExecutor->execute(new HistoryCleanupCmd($immediatelyDue));
    }

    public function findHistoryCleanupJob(): ?JobInterface
    {
        $jobs = $this->commandExecutor->execute(new FindHistoryCleanupJobsCmd());
        if (count($jobs) > 0) {
            return $jobs[0];
        } else {
            return null;
        }
    }

    public function findHistoryCleanupJobs(): array
    {
        return $this->commandExecutor->execute(new FindHistoryCleanupJobsCmd());
    }

    public function deleteHistoricProcessInstancesAsync(array $processInstanceIds, ?HistoricProcessInstanceQueryInterface $query, string $deleteReason): BatchInterface
    {
        return $this->commandExecutor->execute(new DeleteHistoricProcessInstancesBatchCmd($processInstanceIds, $query, $deleteReason));
    }

    public function deleteUserOperationLogEntry(string $entryId): void
    {
        $this->commandExecutor->execute(new DeleteUserOperationLogEntryCmd($entryId));
    }

    /*public void deleteHistoricCaseInstance(string $caseInstanceId) {
        $this->commandExecutor->execute(new DeleteHistoricCaseInstanceCmd(caseInstanceId));
    }

    public void deleteHistoricCaseInstancesBulk(array $caseInstanceIds) {
        $this->commandExecutor->execute(new DeleteHistoricCaseInstancesBulkCmd(caseInstanceIds));
    }

    public void deleteHistoricDecisionInstance(string $decisionDefinitionId) {
        deleteHistoricDecisionInstanceByDefinitionId(decisionDefinitionId);
    }

    public void deleteHistoricDecisionInstancesBulk(array $decisionInstanceIds) {
        $this->commandExecutor->execute(new DeleteHistoricDecisionInstancesBulkCmd(decisionInstanceIds));
    }

    public void deleteHistoricDecisionInstanceByDefinitionId(string $decisionDefinitionId) {
        $this->commandExecutor->execute(new DeleteHistoricDecisionInstanceByDefinitionIdCmd(decisionDefinitionId));
    }

    public void deleteHistoricDecisionInstanceByInstanceId(string $historicDecisionInstanceId){
        $this->commandExecutor->execute(new DeleteHistoricDecisionInstanceByInstanceIdCmd(historicDecisionInstanceId));
    }

    public Batch deleteHistoricDecisionInstancesAsync(array $decisionInstanceIds, String deleteReason) {
        return deleteHistoricDecisionInstancesAsync(decisionInstanceIds, null, deleteReason);
    }

    public Batch deleteHistoricDecisionInstancesAsync(HistoricDecisionInstanceQuery query, String deleteReason) {
        return deleteHistoricDecisionInstancesAsync(null, query, deleteReason);
    }

    public Batch deleteHistoricDecisionInstancesAsync(array $decisionInstanceIds, HistoricDecisionInstanceQuery query, String deleteReason) {
        return $this->commandExecutor->execute(new DeleteHistoricDecisionInstancesBatchCmd(decisionInstanceIds, query, deleteReason));
    }*/

    public function deleteHistoricVariableInstance(string $variableInstanceId): void
    {
        $this->commandExecutor->execute(new DeleteHistoricVariableInstanceCmd($variableInstanceId));
    }

    public function deleteHistoricVariableInstancesByProcessInstanceId(string $processInstanceId): void
    {
        $this->commandExecutor->execute(new DeleteHistoricVariableInstancesByProcessInstanceIdCmd($processInstanceId));
    }

    public function createNativeHistoricProcessInstanceQuery(): NativeHistoricProcessInstanceQueryInterface
    {
        return new NativeHistoricProcessInstanceQueryImpl($this->commandExecutor);
    }

    public function createNativeHistoricTaskInstanceQuery(): NativeHistoricTaskInstanceQueryInterface
    {
        return new NativeHistoricTaskInstanceQueryImpl($this->commandExecutor);
    }

    public function createNativeHistoricActivityInstanceQuery(): NativeHistoricActivityInstanceQueryInterface
    {
        return new NativeHistoricActivityInstanceQueryImpl($this->commandExecutor);
    }

    /*public function createNativeHistoricCaseInstanceQuery(): NativeHistoricCaseInstanceQueryInterface
    {
        return new NativeHistoricCaseInstanceQueryImpl($this->commandExecutor);
    }

    public NativeHistoricCaseActivityInstanceQuery createNativeHistoricCaseActivityInstanceQuery() {
        return new NativeHistoricCaseActivityInstanceQueryImpl($this->commandExecutor);
    }

    public NativeHistoricDecisionInstanceQuery createNativeHistoricDecisionInstanceQuery() {
        return new NativeHistoryDecisionInstanceQueryImpl($this->commandExecutor);
    }*/

    public function createNativeHistoricVariableInstanceQuery(): NativeHistoricVariableInstanceQuery
    {
        return new NativeHistoricVariableInstanceQueryImpl($this->commandExecutor);
    }

    public function createHistoricJobLogQuery(): HistoricJobLogQueryInterface
    {
        return new HistoricJobLogQueryImpl($this->commandExecutor);
    }

    public function getHistoricJobLogExceptionStacktrace(string $historicJobLogId): string
    {
        return $this->commandExecutor->execute(new GetHistoricJobLogExceptionStacktraceCmd($historicJobLogId));
    }

    public function createHistoricProcessInstanceReport(): HistoricProcessInstanceReportInterface
    {
        return new HistoricProcessInstanceReportImpl($this->commandExecutor);
    }

    public function createHistoricTaskInstanceReport(): HistoricTaskInstanceReportInterface
    {
        return new HistoricTaskInstanceReportImpl($this->commandExecutor);
    }

    public function createCleanableHistoricProcessInstanceReport(): CleanableHistoricProcessInstanceReportInterface
    {
        return new CleanableHistoricProcessInstanceReportImpl($this->commandExecutor);
    }

    /*public CleanableHistoricDecisionInstanceReport createCleanableHistoricDecisionInstanceReport() {
        return new CleanableHistoricDecisionInstanceReportImpl($this->commandExecutor);
    }

    public CleanableHistoricCaseInstanceReport createCleanableHistoricCaseInstanceReport() {
        return new CleanableHistoricCaseInstanceReportImpl($this->commandExecutor);
    }*/

    public function createCleanableHistoricBatchReport(): CleanableHistoricBatchReportInterface
    {
        return new CleanableHistoricBatchReportImpl($this->commandExecutor);
    }

    public function createHistoricBatchQuery(): HistoricBatchQueryInterface
    {
        return new HistoricBatchQueryImpl($this->commandExecutor);
    }

    public function deleteHistoricBatch(string $batchId): void
    {
        $this->commandExecutor->execute(new DeleteHistoricBatchCmd($batchId));
    }

    /*public HistoricDecisionInstanceStatisticsQuery createHistoricDecisionInstanceStatisticsQuery(string $decisionRequirementsDefinitionId) {
        return new HistoricDecisionInstanceStatisticsQueryImpl(decisionRequirementsDefinitionId, $this->commandExecutor);
    }*/

    public function createHistoricExternalTaskLogQuery(): HistoricExternalTaskLogQueryInterface
    {
        return new HistoricExternalTaskLogQueryImpl($this->commandExecutor);
    }

    public function getHistoricExternalTaskLogErrorDetails(string $historicExternalTaskLogId): string
    {
        return $this->commandExecutor->execute(new GetHistoricExternalTaskLogErrorDetailsCmd($historicExternalTaskLogId));
    }

    public function setRemovalTimeToHistoricProcessInstances(): SetRemovalTimeSelectModeForHistoricProcessInstancesBuilderInterface
    {
        return new SetRemovalTimeToHistoricProcessInstancesBuilderImpl($this->commandExecutor);
    }

    /*public SetRemovalTimeSelectModeForHistoricDecisionInstancesBuilder setRemovalTimeToHistoricDecisionInstances() {
        return new SetRemovalTimeToHistoricDecisionInstancesBuilderImpl($this->commandExecutor);
    }*/

    public function setRemovalTimeToHistoricBatches(): SetRemovalTimeSelectModeForHistoricBatchesBuilderInterface
    {
        return new SetRemovalTimeToHistoricBatchesBuilderImpl($this->commandExecutor);
    }

    public function setAnnotationForOperationLogById(string $operationId, string $annotation): void
    {
        $this->commandExecutor->execute(new SetAnnotationForOperationLog($operationId, $annotation));
    }

    public function clearAnnotationForOperationLogById(string $operationId): void
    {
        $this->commandExecutor->execute(new SetAnnotationForOperationLog($operationId, null));
    }
}
