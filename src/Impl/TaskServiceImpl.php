<?php

namespace Jabe\Impl;

use Jabe\{
    BadUserRequestException,
    ProcessEngineException,
    TaskServiceInterface
};
use Jabe\Impl\Cmd\{
    AddCommentCmd,
    AddGroupIdentityLinkCmd,
    AddUserIdentityLinkCmd,
    AssignTaskCmd,
    ClaimTaskCmd,
    CompleteTaskCmd,
    CreateAttachmentCmd,
    CreateTaskCmd,
    DelegateTaskCmd,
    DeleteAttachmentCmd,
    DeleteGroupIdentityLinkCmd,
    DeleteTaskAttachmentCmd,
    DeleteTaskCmd,
    DeleteUserIdentityLinkCmd,
    GetAttachmentCmd,
    GetAttachmentContentCmd,
    GetIdentityLinksForTaskCmd,
    GetProcessInstanceAttachmentsCmd,
    GetProcessInstanceCommentsCmd,
    GetSubTasksCmd,
    GetTaskAttachmentCmd,
    GetTaskAttachmentContentCmd,
    GetTaskAttachmentsCmd,
    GetTaskCommentCmd,
    GetTaskCommentsCmd,
    GetTaskEventsCmd,
    GetTaskVariableCmd,
    GetTaskVariableCmdTyped,
    GetTaskVariablesCmd,
    HandleTaskBpmnErrorCmd,
    HandleTaskEscalationCmd,
    PatchTaskVariablesCmd,
    RemoveTaskVariablesCmd,
    ResolveTaskCmd,
    SaveAttachmentCmd,
    SaveTaskCmd,
    SetTaskOwnerCmd,
    SetTaskPriorityCmd,
    SetTaskVariablesCmd
};
use Jabe\Impl\Util\{
    EnsureUtil,
    ExceptionUtil
};
use Jabe\Task\{
    AttachmentInterface,
    CommentInterface,
    EventInterface,
    IdentityLinkInterface,
    IdentityLinkType,
    NativeTaskQueryInterface,
    TaskInterface,
    TaskQueryInterface,
    TaskReportInterface
};
use Jabe\Variable\VariableMapInterface;
use Jabe\Variable\Value\TypedValueInterface;

class TaskServiceImpl extends ServiceImpl implements TaskServiceInterface
{
    public function newTask(?string $taskId = null): TaskInterface
    {
        return $this->commandExecutor->execute(new CreateTaskCmd($taskId));
    }

    public function saveTask(TaskInterface $task): void
    {
        $this->commandExecutor->execute(new SaveTaskCmd($task));
    }

    public function deleteTask(?string $taskId, $cascadeOrReason = false): void
    {
        if (is_string($cascadeOrReason)) {
            $this->commandExecutor->execute(new DeleteTaskCmd($taskId, $cascadeOrReason, false));
        } else {
            $this->commandExecutor->execute(new DeleteTaskCmd($taskId, null, $cascadeOrReason));
        }
    }

    public function deleteTasks(array $taskIds, $cascadeOrReason = false): void
    {
        if (is_string($cascadeOrReason)) {
            $this->commandExecutor->execute(new DeleteTaskCmd($taskIds, $cascadeOrReason, false));
        } else {
            $this->commandExecutor->execute(new DeleteTaskCmd($taskIds, null, $cascadeOrReason));
        }
    }

    public function setAssignee(?string $taskId, ?string $userId): void
    {
        $this->commandExecutor->execute(new AssignTaskCmd($taskId, $userId));
    }

    public function setOwner(?string $taskId, ?string $userId): void
    {
        $this->commandExecutor->execute(new SetTaskOwnerCmd($taskId, $userId));
    }

    public function addCandidateUser(?string $taskId, ?string $userId): void
    {
        $this->commandExecutor->execute(new AddUserIdentityLinkCmd($taskId, $userId, IdentityLinkType::CANDIDATE));
    }

    public function addCandidateGroup(?string $taskId, ?string $groupId): void
    {
        $this->commandExecutor->execute(new AddGroupIdentityLinkCmd($taskId, $groupId, IdentityLinkType::CANDIDATE));
    }

    public function addUserIdentityLink(?string $taskId, ?string $userId, ?string $identityLinkType): void
    {
        $this->commandExecutor->execute(new AddUserIdentityLinkCmd($taskId, $userId, $identityLinkType));
    }

    public function addGroupIdentityLink(?string $taskId, ?string $groupId, ?string $identityLinkType): void
    {
        $this->commandExecutor->execute(new AddGroupIdentityLinkCmd($taskId, $groupId, $identityLinkType));
    }

    public function deleteCandidateGroup(?string $taskId, ?string $groupId): void
    {
        $this->commandExecutor->execute(new DeleteGroupIdentityLinkCmd($taskId, $groupId, IdentityLinkType::CANDIDATE));
    }

    public function deleteCandidateUser(?string $taskId, ?string $userId): void
    {
        $this->commandExecutor->execute(new DeleteUserIdentityLinkCmd($taskId, $userId, IdentityLinkType::CANDIDATE));
    }

    public function deleteGroupIdentityLink(?string $taskId, ?string $groupId, ?string $identityLinkType): void
    {
        $this->commandExecutor->execute(new DeleteGroupIdentityLinkCmd($taskId, $groupId, $identityLinkType));
    }

    public function deleteUserIdentityLink(?string $taskId, ?string $userId, ?string $identityLinkType): void
    {
        $this->commandExecutor->execute(new DeleteUserIdentityLinkCmd($taskId, $userId, $identityLinkType));
    }

    public function getIdentityLinksForTask(?string $taskId): array
    {
        return $this->commandExecutor->execute(new GetIdentityLinksForTaskCmd($taskId));
    }

    public function claim(?string $taskId, ?string $userId): void
    {
        $this->commandExecutor->execute(new ClaimTaskCmd($taskId, $userId));
    }

    public function complete(?string $taskId, array $variables = []): void
    {
        $this->commandExecutor->execute(new CompleteTaskCmd($taskId, $variables, false, false));
    }

    public function completeWithVariablesInReturn(?string $taskId, array $variables = [], bool $deserializeValues = true): VariableMapInterface
    {
        return $this->commandExecutor->execute(new CompleteTaskCmd($taskId, $variables, true, $deserializeValues));
    }

    public function delegateTask(?string $taskId, ?string $userId): void
    {
        $this->commandExecutor->execute(new DelegateTaskCmd($taskId, $userId));
    }

    public function resolveTask(?string $taskId, array $variables = []): void
    {
        $this->commandExecutor->execute(new ResolveTaskCmd($taskId, $variables));
    }

    public function setPriority(?string $taskId, int $priority): void
    {
        $this->commandExecutor->execute(new SetTaskPriorityCmd($taskId, $priority));
    }

    public function createTaskQuery(): TaskQueryInterface
    {
        return new TaskQueryImpl($this->commandExecutor);
    }

    public function createNativeTaskQuery(): NativeTaskQueryInterface
    {
        return new NativeTaskQueryImpl($this->commandExecutor);
    }

    public function getVariables(?string $taskId, array $variableNames = []): VariableMapInterface
    {
        return $this->getVariablesTyped($taskId, $variableNames, true);
    }

    public function getVariablesTyped(?string $taskId, array $variableNames = [], bool $deserializeValues = true): VariableMapInterface
    {
        return $this->commandExecutor->execute(new GetTaskVariablesCmd($taskId, $variableNames, false, $deserializeValues));
    }

    public function getVariablesLocal(?string $taskId, array $variableNames = []): VariableMapInterface
    {
        return $this->getVariablesLocalTyped($taskId, $variableNames, true);
    }

    public function getVariablesLocalTyped(?string $taskId, array $variableNames = [], bool $deserializeValues = true): VariableMapInterface
    {
        return $this->commandExecutor->execute(new GetTaskVariablesCmd($taskId, $variableNames, true, $deserializeValues));
    }

    public function getVariable(?string $taskId, ?string $variableName)
    {
        return $this->commandExecutor->execute(new GetTaskVariableCmd($taskId, $variableName, false));
    }

    public function getVariableLocal(?string $taskId, ?string $variableName)
    {
        return $this->commandExecutor->execute(new GetTaskVariableCmd($taskId, $variableName, true));
    }

    public function getVariableTyped(?string $taskId, ?string $variableName, bool $deserializeValue = true): ?TypedValueInterface
    {
        return $this->doGetVariableTyped($taskId, $variableName, false, $deserializeValue);
    }

    public function getVariableLocalTyped(?string $taskId, ?string $variableName, bool $deserializeValue = true): TypedValueInterface
    {
        return $this->doGetVariableTyped($taskId, $variableName, true, $deserializeValue);
    }

    private function doGetVariableTyped(?string $taskId, ?string $variableName, bool $isLocal, bool $deserializeValue): TypedValueInterface
    {
        return $this->commandExecutor->execute(new GetTaskVariableCmdTyped($taskId, $variableName, $isLocal, $deserializeValue));
    }

    public function setVariable(?string $taskId, ?string $variableName, $value): void
    {
        EnsureUtil::ensureNotNull("variableName", variableName);
        $variables = [];
        $variables[$variableName] = $value;
        $this->setVariables($taskId, $variables, false);
    }

    public function setVariableLocal(?string $taskId, ?string $variableName, $value): void
    {
        EnsureUtil::ensureNotNull("variableName", "variableName", $variableName);
        $variables = [];
        $variables[$variableName] = $value;
        $this->setVariables($taskId, $variables, true);
    }

    public function setVariablesLocal(?string $taskId, array $variables): void
    {
        $this->setVariables($taskId, $variables, true);
    }

    public function setVariables(?string $taskId, array $variables, bool $local = false): void
    {
        try {
            $this->commandExecutor->execute(new SetTaskVariablesCmd($taskId, $variables, $local));
        } catch (ProcessEngineException $ex) {
            if (ExceptionUtil::checkValueTooLongException($ex)) {
                throw new BadUserRequestException("Variable value is too long", $ex);
            }
            throw $ex;
        }
    }

    public function updateVariablesLocal(?string $taskId, array $modifications, array $deletions): void
    {
        $this->updateVariables($taskId, $modifications, $deletions, true);
    }

    protected function updateVariables(?string $taskId, array $modifications, array $deletions, bool $local = false): void
    {
        try {
            $this->commandExecutor->execute(new PatchTaskVariablesCmd($taskId, $modifications, $deletions, $local));
        } catch (ProcessEngineException $ex) {
            if (ExceptionUtil::checkValueTooLongException($ex)) {
                throw new BadUserRequestException("Variable value is too long", $ex);
            }
            throw $ex;
        }
    }

    public function removeVariable(?string $taskId, ?string $variableName): void
    {
        $variableNames = [];
        $variableNames[] = $variableName;
        $this->commandExecutor->execute(new RemoveTaskVariablesCmd($taskId, $variableNames, false));
    }

    public function removeVariableLocal(?string $taskId, ?string $variableName): void
    {
        $variableNames = [];
        $variableNames[] = $variableName;
        $this->commandExecutor->execute(new RemoveTaskVariablesCmd($taskId, $variableNames, true));
    }

    public function removeVariables(?string $taskId, ?array $variableNames = []): void
    {
        $this->commandExecutor->execute(new RemoveTaskVariablesCmd($taskId, $variableNames, false));
    }

    public function removeVariablesLocal(?string $taskId, ?array $variableNames = []): void
    {
        $this->commandExecutor->execute(new RemoveTaskVariablesCmd($taskId, $variableNames, true));
    }

    public function addComment(?string $taskId, ?string $processInstance, ?string $message): void
    {
        $this->createComment($taskId, $processInstance, $message);
    }

    public function createComment(?string $taskId, ?string $processInstance, ?string $message): CommentInterface
    {
        return $this->commandExecutor->execute(new AddCommentCmd($taskId, $processInstance, $message));
    }

    public function getTaskComments(?string $taskId): array
    {
        return $this->commandExecutor->execute(new GetTaskCommentsCmd($taskId));
    }

    public function getTaskComment(?string $taskId, ?string $commentId): CommentInterface
    {
        return $this->commandExecutor->execute(new GetTaskCommentCmd($taskId, $commentId));
    }

    public function getTaskEvents(?string $taskId): array
    {
        return $this->commandExecutor->execute(new GetTaskEventsCmd($taskId));
    }

    public function getProcessInstanceComments(?string $processInstanceId): array
    {
        return $this->commandExecutor->execute(new GetProcessInstanceCommentsCmd($processInstanceId));
    }

    public function createAttachment(?string $attachmentType, ?string $taskId, ?string $processInstanceId, ?string $attachmentName, ?string $attachmentDescription, $content = null, ?string $url = null): AttachmentInterface
    {
        return $this->commandExecutor->execute(new CreateAttachmentCmd($attachmentType, $taskId, $processInstanceId, $attachmentName, $attachmentDescription, $content, $url));
    }

    public function getAttachmentContent(?string $attachmentId)
    {
        return $this->commandExecutor->execute(new GetAttachmentContentCmd($attachmentId));
    }

    public function getTaskAttachmentContent(?string $taskId, ?string $attachmentId)
    {
        return $this->commandExecutor->execute(new GetTaskAttachmentContentCmd($taskId, $attachmentId));
    }

    public function deleteAttachment(?string $attachmentId): void
    {
        $this->commandExecutor->execute(new DeleteAttachmentCmd($attachmentId));
    }

    public function deleteTaskAttachment(?string $taskId, ?string $attachmentId): void
    {
        $this->commandExecutor->execute(new DeleteTaskAttachmentCmd($taskId, $attachmentId));
    }

    public function getAttachment(?string $attachmentId): AttachmentInterface
    {
        return $this->commandExecutor->execute(new GetAttachmentCmd($attachmentId));
    }

    public function getTaskAttachment(?string $taskId, ?string $attachmentId): AttachmentInterface
    {
        return $this->commandExecutor->execute(new GetTaskAttachmentCmd($taskId, $attachmentId));
    }

    public function getTaskAttachments(?string $taskId): array
    {
        return $this->commandExecutor->execute(new GetTaskAttachmentsCmd($taskId));
    }

    public function getProcessInstanceAttachments(?string $processInstanceId): array
    {
        return $this->commandExecutor->execute(new GetProcessInstanceAttachmentsCmd($processInstanceId));
    }

    public function saveAttachment(AttachmentInterface $attachment): void
    {
        $this->commandExecutor->execute(new SaveAttachmentCmd($attachment));
    }

    public function getSubTasks(?string $parentTaskId): array
    {
        return $this->commandExecutor->execute(new GetSubTasksCmd($parentTaskId));
    }

    public function createTaskReport(): TaskReportInterface
    {
        return new TaskReportImpl($this->commandExecutor);
    }

    public function handleBpmnError(?string $taskId, ?string $errorCode, ?string $errorMessage = null, array $variables = []): void
    {
        $this->commandExecutor->execute(new HandleTaskBpmnErrorCmd($taskId, $errorCode, $errorMessage, $variables));
    }

    public function handleEscalation(?string $taskId, ?string $escalationCode, array $variables = []): void
    {
        $this->commandExecutor->execute(new HandleTaskEscalationCmd($taskId, $escalationCode, $variables));
    }
}
