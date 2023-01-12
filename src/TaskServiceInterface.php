<?php

namespace Jabe;

use Jabe\Authorization\{
    Permissions,
    ProcessDefinitionPermissions,
    ProcessInstancePermissions,
    Resources,
    TaskPermissions
};
use Jabe\Exception\{
    NullValueException,
    NotFoundException
};
use Jabe\History\{
    UserOperationLogEntryInterface,
    UserOperationLogQueryInterface
};
use Jabe\Task\{
    AttachmentInterface,
    CommentInterface,
    EventInterface,
    IdentityLinkInterface,
    NativeTaskQueryInterface,
    TaskInterface,
    TaskQueryInterface,
    TaskReportInterface
};
use Jabe\Variable\VariableMapInterface;
use Jabe\Variable\Value\{
    SerializableValueInterface,
    TypedValueInterface
};

interface TaskServiceInterface
{
    /** create a new task with a user defined task id */
    public function newTask(?string $taskId = null): TaskInterface;

    /**
     * Saves the given task to the persistent data store. If the task is already
     * present in the persistent store, it is updated.
     * After a new task has been saved, the task instance passed into this method
     * is updated with the id of the newly created task.
     *
     * @param task the task, cannot be null.
     *
    * @throws AuthorizationException
    *          If the task is already present and the user has no Permissions#UPDATE permission
    *          on Resources#TASK or no Permissions#UPDATE_TASK permission on
    *          Resources#PROCESS_DEFINITION.
    *          Or if the task is not present and the user has no Permissions#CREATE permission
    *          on Resources#TASK.
    */
    public function saveTask(TaskInterface $task): void;

    /**
    * Deletes the given task.
    *
    * @param taskId The id of the task that will be deleted, cannot be null. If no task
    * exists with the given taskId, the operation is ignored.
    *
    * @param cascade If cascade is true, also the historic information related to this task is deleted.
    *
    * @throws ProcessEngineException
    *          when an error occurs while deleting the task or in case the task is part
    *          of a running process or case instance.
    * @throws AuthorizationException
    *          If the user has no Permissions#DELETE permission on Resources#TASK.
    */
    public function deleteTask(?string $taskId, $cascadeOrReason = false): void;

    /**
    * Deletes all tasks of the given collection.
    *
    * @param taskIds The id's of the tasks that will be deleted, cannot be null. All
    * id's in the list that don't have an existing task will be ignored.
    * @param cascade If cascade is true, also the historic information related to this task is deleted.
    *
    * @throws ProcessEngineException
    *          when an error occurs while deleting the tasks or in case one of the tasks
    *          is part of a running process or case instance.
    * @throws AuthorizationException
    *          If the user has no Permissions#DELETE permission on Resources#TASK.
    */
    public function deleteTasks(array $taskIds, $cascadeOrReason = false): void;

    /**
    * Claim responsibility for a task:
    * the given user is made {@link Task#getAssignee() assignee} for the task.
    * The difference with {@link #setAssignee(String, String)} is that here
    * a check is done if the task already has a user assigned to it.
    * No check is done whether the user is known by the identity component.
    *
    * @param taskId task to claim, cannot be null.
    * @param userId user that claims the task. When userId is null the task is unclaimed,
    * assigned to no one.
    *
    * @throws ProcessEngineException
    *          when the task doesn't exist or when the task is already claimed by another user.
    * @throws AuthorizationException
    *          If the user has no Permissions#UPDATE permission on Resources#TASK
    *          or no Permissions#UPDATE_TASK permission on Resources#PROCESS_DEFINITION
    *          (if the task is part of a running process instance).
    */
    public function claim(?string $taskId, ?string $userId): void;

    /**
    * Delegates the task to another user.
    *
    * This means that the {@link Task#getAssignee() assignee} is set
    * and the {@link Task#getDelegationState() delegation state} is set to
    * DelegationState#PENDING.
    * If no owner is set on the task, the owner is set to the current
    * {@link Task#getAssignee() assignee} of the task.
    * The new assignee must use TaskService#resolveTask(String)
    * to report back to the owner.
    * Only the owner can {@link TaskService#complete(String) complete} the task.
    *
    * @param taskId The id of the task that will be delegated.
    * @param userId The id of the user that will be set as assignee.
    *
    * @throws ProcessEngineException
    *          when no task exists with the given id.
    * @throws AuthorizationException
    *          If the user has no Permissions#UPDATE permission on Resources#TASK
    *          or no Permissions#UPDATE_TASK permission on Resources#PROCESS_DEFINITION
    *          (if the task is part of a running process instance).
    */
    public function delegateTask(?string $taskId, ?string $userId): void;

    /**
    * Marks that the {@link Task#getAssignee() assignee} is done with the task
    * {@link TaskService#delegateTask(String, String) delegated}
    * to her and that it can be sent back to the {@link Task#getOwner() owner}
    * with the provided variables.
    * Can only be called when this task is DelegationState#PENDING delegation.
    * After this method returns, the {@link Task#getDelegationState() delegation state}
    * is set to DelegationState#RESOLVED and the task can be
    * {@link TaskService#complete(String) completed}.
    *
    * @param taskId
    * @param variables
    *
    * @throws ProcessEngineException
    *          when no task exists with the given id.
    * @throws AuthorizationException
    *          If the user has no Permissions#UPDATE permission on Resources#TASK
    *          or no Permissions#UPDATE_TASK permission on Resources#PROCESS_DEFINITION
    *          (if the task is part of a running process instance).
    */
    public function resolveTask(?string $taskId, array $variables = []): void;

    /**
    * Marks a task as done and continues process execution.
    *
    * This method is typically called by a task list user interface
    * after a task form has been submitted by the
    * {@link Task#getAssignee() assignee}
    * and the required task parameters have been provided.
    *
    * @param taskId the id of the task to complete, cannot be null.
    * @param variables task parameters. May be null or empty.
    *
    * @throws ProcessEngineException
    *          when no task exists with the given id.
    * @throws AuthorizationException
    *          If the user has no Permissions#UPDATE permission on Resources#TASK
    *          or no Permissions#UPDATE_TASK permission on Resources#PROCESS_DEFINITION
    *          (if the task is part of a running process instance).
    */
    public function complete(?string $taskId, array $variables = []): void;

    /**
    * Marks a task as done and continues process execution.
    *
    * This method is typically called by a task list user interface
    * after a task form has been submitted by the
    * {@link Task#getAssignee() assignee}
    * and the required task parameters have been provided.
    *
    * @param taskId the id of the task to complete, cannot be null.
    * @param variables task parameters. May be null or empty.
    * @param deserializeValues if false, returned SerializableValues
    *   will not be deserialized (unless they are passed into this method as a
    *   deserialized value or if the BPMN process triggers deserialization)
    *
    * @return All task variables with their current value
    *
    * @throws ProcessEngineException
    *          when no task exists with the given id.
    * @throws AuthorizationException
    *          If the user has no Permissions#UPDATE permission on Resources#TASK
    *          or no Permissions#UPDATE_TASK permission on Resources#PROCESS_DEFINITION
    *          (if the task is part of a running process instance).
    */
    public function completeWithVariablesInReturn(?string $taskId, array $variables = [], bool $deserializeValues = true): VariableMapInterface;

    /**
    * Changes the assignee of the given task to the given userId.
    * No check is done whether the user is known by the identity component.
    *
    * @param taskId id of the task, cannot be null.
    * @param userId id of the user to use as assignee.
    *
    * @throws ProcessEngineException
    *          when the task or user doesn't exist.
    * @throws AuthorizationException
    *          If the user has no Permissions#UPDATE permission on Resources#TASK
    *          or no Permissions#UPDATE_TASK permission on Resources#PROCESS_DEFINITION
    *          (if the task is part of a running process instance).
    */
    public function setAssignee(?string $taskId, ?string $userId): void;

    /**
    * Transfers ownership of this task to another user.
    * No check is done whether the user is known by the identity component.
    *
    * @param taskId id of the task, cannot be null.
    * @param userId of the person that is receiving ownership.
    *
    * @throws ProcessEngineException
    *          when the task or user doesn't exist.
    * @throws AuthorizationException
    *          If the user has no Permissions#UPDATE permission on Resources#TASK
    *          or no Permissions#UPDATE_TASK permission on Resources#PROCESS_DEFINITION
    *          (if the task is part of a running process instance).
    */
    public function setOwner(?string $taskId, ?string $userId): void;

    /**
    * Retrieves the IdentityLinks associated with the given task.
    * Such an IdentityLink informs how a certain identity (eg. group or user)
    * is associated with a certain task (eg. as candidate, assignee, etc.)
    *
    * @throws ProcessEngineException
    *          when the task doesn't exist.
    * @throws AuthorizationException
    *          If the user has no Permissions#READ permission on Resources#TASK
    *          or no Permissions#READ_TASK permission on Resources#PROCESS_DEFINITION
    *          (if the task is part of a running process instance).
    */
    public function getIdentityLinksForTask(?string $taskId): array;

    /**
    * Convenience shorthand for {@link #addUserIdentityLink(String, String, String)}; with type IdentityLinkType#CANDIDATE
    *
    * @param taskId id of the task, cannot be null.
    * @param userId id of the user to use as candidate, cannot be null.
    *
    * @throws ProcessEngineException
    *          when the task or user doesn't exist.
    * @throws AuthorizationException
    *          If the user has no Permissions#UPDATE permission on Resources#TASK
    *          or no Permissions#UPDATE_TASK permission on Resources#PROCESS_DEFINITION
    *          (if the task is part of a running process instance).
    */
    public function addCandidateUser(?string $taskId, ?string $userId): void;

    /**
    * Convenience shorthand for {@link #addGroupIdentityLink(String, String, String)}; with type IdentityLinkType#CANDIDATE
    *
    * @param taskId id of the task, cannot be null.
    * @param groupId id of the group to use as candidate, cannot be null.
    *
    * @throws ProcessEngineException
    *          when the task or group doesn't exist.
    * @throws AuthorizationException
    *          If the user has no Permissions#UPDATE permission on Resources#TASK
    *          or no Permissions#UPDATE_TASK permission on Resources#PROCESS_DEFINITION
    *          (if the task is part of a running process instance).
    */
    public function addCandidateGroup(?string $taskId, ?string $groupId): void;

    /**
    * Involves a user with a task. The type of identity link is defined by the
    * given identityLinkType.
    *
    * @param taskId id of the task, cannot be null.
    * @param userId id of the user involve, cannot be null.
    * @param identityLinkType type of identityLink, cannot be null (@see IdentityLinkType).
    *
    * @throws ProcessEngineException
    *          when the task or user doesn't exist.
    * @throws AuthorizationException
    *          If the user has no Permissions#UPDATE permission on Resources#TASK
    *          or no Permissions#UPDATE_TASK permission on Resources#PROCESS_DEFINITION
    *          (if the task is part of a running process instance).
    */
    public function addUserIdentityLink(?string $taskId, ?string $userId, ?string $identityLinkType): void;

    /**
    * Involves a group with a task. The type of identityLink is defined by the
    * given identityLink.
    *
    * @param taskId id of the task, cannot be null.
    * @param groupId id of the group to involve, cannot be null.
    * @param identityLinkType type of identity, cannot be null (@see IdentityLinkType).
    *
    * @throws ProcessEngineException
    *          when the task or group doesn't exist.
    * @throws AuthorizationException
    *          If the user has no Permissions#UPDATE permission on Resources#TASK
    *          or no Permissions#UPDATE_TASK permission on Resources#PROCESS_DEFINITION
    *          (if the task is part of a running process instance).
    */
    public function addGroupIdentityLink(?string $taskId, ?string $groupId, ?string $identityLinkType): void;

    /**
    * Convenience shorthand for {@link #deleteUserIdentityLink(String, String, String)}; with type IdentityLinkType#CANDIDATE
    *
    * @param taskId id of the task, cannot be null.
    * @param userId id of the user to use as candidate, cannot be null.
    *
    * @throws ProcessEngineException
    *          when the task or user doesn't exist.
    * @throws AuthorizationException
    *          If the user has no Permissions#UPDATE permission on Resources#TASK
    *          or no Permissions#UPDATE_TASK permission on Resources#PROCESS_DEFINITION
    *          (if the task is part of a running process instance).
    */
    public function deleteCandidateUser(?string $taskId, ?string $userId): void;

    /**
    * Convenience shorthand for {@link #deleteGroupIdentityLink(String, String, String)}; with type IdentityLinkType#CANDIDATE
    *
    * @param taskId id of the task, cannot be null.
    * @param groupId id of the group to use as candidate, cannot be null.
    *
    * @throws ProcessEngineException
    *          when the task or group doesn't exist.
    * @throws AuthorizationException
    *          If the user has no Permissions#UPDATE permission on Resources#TASK
    *          or no Permissions#UPDATE_TASK permission on Resources#PROCESS_DEFINITION
    *          (if the task is part of a running process instance).
    */
    public function deleteCandidateGroup(?string $taskId, ?string $groupId): void;

    /**
    * Removes the association between a user and a task for the given identityLinkType.
    *
    * @param taskId id of the task, cannot be null.
    * @param userId id of the user involve, cannot be null.
    * @param identityLinkType type of identityLink, cannot be null (@see IdentityLinkType).
    *
    * @throws ProcessEngineException
    *          when the task or user doesn't exist.
    * @throws AuthorizationException
    *          If the user has no Permissions#UPDATE permission on Resources#TASK
    *          or no Permissions#UPDATE_TASK permission on Resources#PROCESS_DEFINITION
    *          (if the task is part of a running process instance).
    */
    public function deleteUserIdentityLink(?string $taskId, ?string $userId, ?string $identityLinkType): void;

    /**
    * Removes the association between a group and a task for the given identityLinkType.
    *
    * @param taskId id of the task, cannot be null.
    * @param groupId id of the group to involve, cannot be null.
    * @param identityLinkType type of identity, cannot be null (@see IdentityLinkType).
    *
    * @throws ProcessEngineException
    *          when the task or group doesn't exist.
    * @throws AuthorizationException
    *          If the user has no Permissions#UPDATE permission on Resources#TASK
    *          or no Permissions#UPDATE_TASK permission on Resources#PROCESS_DEFINITION
    *          (if the task is part of a running process instance).
    */
    public function deleteGroupIdentityLink(?string $taskId, ?string $groupId, ?string $identityLinkType): void;

    /**
    * Changes the priority of the task.
    *
    * Authorization: actual owner / business admin
    *
    * @param taskId id of the task, cannot be null.
    * @param priority the new priority for the task.
    *
    * @throws ProcessEngineException
    *          when the task doesn't exist.
    * @throws AuthorizationException
    *          If the user has no Permissions#UPDATE permission on Resources#TASK
    *          or no Permissions#UPDATE_TASK permission on Resources#PROCESS_DEFINITION
    *          (if the task is part of a running process instance).
    */
    public function setPriority(?string $taskId, int $priority): void;

    /**
    * Returns a new TaskQuery that can be used to dynamically query tasks.
    */
    public function createTaskQuery(): TaskQueryInterface;

    /**
    * Returns a new
    */
    public function createNativeTaskQuery(): NativeTaskQueryInterface;

    /**
    * Set variable on a task. If the variable is not already existing, it will be created in the
    * most outer scope.  This means the process instance in case this task is related to an
    * execution.
    *
    * @throws ProcessEngineException
    *          when the task doesn't exist.
    * @throws AuthorizationException
    *           If the user has none of the following:
    *           <li>TaskPermissions#UPDATE_VARIABLE permission on Resources#TASK</li>
    *           <li>Permissions#UPDATE permission on Resources#TASK</li>
    *           <li>or if the task is part of a running process instance:</li>
    *           <ul>
    *           <li>ProcessDefinitionPermissions#UPDATE_TASK_VARIABLE permission on Resources#PROCESS_DEFINITION</li>
    *           <li>Permissions#UPDATE_TASK permission on Resources#PROCESS_DEFINITION</li>
    *           </ul>
    */
    public function setVariable(?string $taskId, ?string $variableName, $value): void;

    /**
    * Set variables on a task. If the variable is not already existing, it will be created in the
    * most outer scope.  This means the process instance in case this task is related to an
    * execution.
    *
    * @throws ProcessEngineException
    *          when the task doesn't exist.
    * @throws AuthorizationException
    *           If the user has none of the following:
    *           <li>TaskPermissions#UPDATE_VARIABLE permission on Resources#TASK</li>
    *           <li>Permissions#UPDATE permission on Resources#TASK</li>
    *           <li>or if the task is part of a running process instance:</li>
    *           <ul>
    *           <li>ProcessDefinitionPermissions#UPDATE_TASK_VARIABLE permission on Resources#PROCESS_DEFINITION</li>
    *           <li>Permissions#UPDATE_TASK permission on Resources#PROCESS_DEFINITION</li>
    *           </ul>
    */
    public function setVariables(?string $taskId, array $variables, bool $local = false): void;

    /**
    * Set variable on a task. If the variable is not already existing, it will be created in the
    * task.
    *
    * @throws ProcessEngineException
    *          when the task doesn't exist.
    * @throws AuthorizationException
    *           If the user has none of the following:
    *           <li>TaskPermissions#UPDATE_VARIABLE permission on Resources#TASK</li>
    *           <li>Permissions#UPDATE permission on Resources#TASK</li>
    *           <li>or if the task is part of a running process instance:</li>
    *           <ul>
    *           <li>ProcessDefinitionPermissions#UPDATE_TASK_VARIABLE permission on Resources#PROCESS_DEFINITION</li>
    *           <li>Permissions#UPDATE_TASK permission on Resources#PROCESS_DEFINITION</li>
    *           </ul>
    */
    public function setVariableLocal(?string $taskId, ?string $variableName, $value): void;

    /**
    * Set variables on a task. If the variable is not already existing, it will be created in the
    * task.
    *
    * @throws ProcessEngineException
    *          when the task doesn't exist.
    * @throws AuthorizationException
    *           If the user has none of the following:
    *           <li>TaskPermissions#UPDATE_VARIABLE permission on Resources#TASK</li>
    *           <li>Permissions#UPDATE permission on Resources#TASK</li>
    *           <li>or if the task is part of a running process instance:</li>
    *           <ul>
    *           <li>ProcessDefinitionPermissions#UPDATE_TASK_VARIABLE permission on Resources#PROCESS_DEFINITION</li>
    *           <li>Permissions#UPDATE_TASK permission on Resources#PROCESS_DEFINITION</li>
    *           </ul>
    */
    public function setVariablesLocal(?string $taskId, array $variables): void;

    /**
    * Get a variables and search in the task scope and if available also the execution scopes.
    *
    * @throws ProcessEngineException
    *          when the task doesn't exist.
    * @throws AuthorizationException
    *          <p>In case of standalone tasks:
    *          <li>if the user has no Permissions#READ permission on Resources#TASK or</li>
    *          <li>if {@link ProcessEngineConfiguration#enforceSpecificVariablePermission this} configuration is enabled and
    *          the user has no TaskPermissions#READ_VARIABLE permission on Resources#TASK</li></p>
    *          <p>In case the task is part of a running process instance:</li>
    *          <li>if the user has no Permissions#READ permission on Resources#TASK and
    *           no Permissions#READ_TASK permission on Resources#PROCESS_DEFINITION </li>
    *          <li>in case {@link ProcessEngineConfiguration#enforceSpecificVariablePermission this} configuration is enabled and
    *          the user has no TaskPermissions#READ_VARIABLE permission on Resources#TASK and
    *          no ProcessDefinitionPermissions#READ_TASK_VARIABLE permission on Resources#PROCESS_DEFINITION</li></p>
    */
    public function getVariable(?string $taskId, ?string $variableName);

    /**
    * Get a variables and search in the task scope and if available also the execution scopes.
    *
    * @param taskId the id of the task
    * @param variableName the name of the variable to fetch
    * @param deserializeValue if false a, SerializableValue will not be deserialized.
    *
    * @return TypedValueInterface the TypedValue for the variable or 'null' in case no such variable exists.
    *
    * @throws ClassCastException
    *          in case the value is not of the requested type
    * @throws ProcessEngineException
    *          when the task doesn't exist.
    * @throws AuthorizationException
    *          <p>In case of standalone tasks:
    *          <li>if the user has no Permissions#READ permission on Resources#TASK or</li>
    *          <li>if {@link ProcessEngineConfiguration#enforceSpecificVariablePermission this} configuration is enabled and
    *          the user has no TaskPermissions#READ_VARIABLE permission on Resources#TASK</li></p>
    *          <p>In case the task is part of a running process instance:</li>
    *          <li>if the user has no Permissions#READ permission on Resources#TASK and
    *           no Permissions#READ_TASK permission on Resources#PROCESS_DEFINITION </li>
    *          <li>in case {@link ProcessEngineConfiguration#enforceSpecificVariablePermission this} configuration is enabled and
    *          the user has no TaskPermissions#READ_VARIABLE permission on Resources#TASK and
    *          no ProcessDefinitionPermissions#READ_TASK_VARIABLE permission on Resources#PROCESS_DEFINITION</li></p>
    *
    * @since 7.2
    */
    public function getVariableTyped(?string $taskId, ?string $variableName, bool $deserializeValue = true): ?TypedValueInterface;

    /**
    * Get a variables and only search in the task scope.
    *
    * @throws ProcessEngineException
    *          when the task doesn't exist.
    * @throws AuthorizationException
    *          <p>In case of standalone tasks:
    *          <li>if the user has no Permissions#READ permission on Resources#TASK or</li>
    *          <li>if {@link ProcessEngineConfiguration#enforceSpecificVariablePermission this} configuration is enabled and
    *          the user has no TaskPermissions#READ_VARIABLE permission on Resources#TASK</li></p>
    *          <p>In case the task is part of a running process instance:</li>
    *          <li>if the user has no Permissions#READ permission on Resources#TASK and
    *           no Permissions#READ_TASK permission on Resources#PROCESS_DEFINITION </li>
    *          <li>in case {@link ProcessEngineConfiguration#enforceSpecificVariablePermission this} configuration is enabled and
    *          the user has no TaskPermissions#READ_VARIABLE permission on Resources#TASK and
    *          no ProcessDefinitionPermissions#READ_TASK_VARIABLE permission on Resources#PROCESS_DEFINITION</li></p>
    */
    public function getVariableLocal(?string $taskId, ?string $variableName);

    /**
    * Get a variables and only search in the task scope.
    *
    * @param taskId the id of the task
    * @param variableName the name of the variable to fetch
    * @param deserializeValue if false a, SerializableValue will not be deserialized.
    *
    * @return TypedValueInterface the TypedValue for the variable or 'null' in case no such variable exists.
    *
    * @throws ClassCastException
    *          in case the value is not of the requested type
    * @throws ProcessEngineException
    *          when the task doesn't exist.
    * @throws AuthorizationException
    *          <p>In case of standalone tasks:
    *          <li>if the user has no Permissions#READ permission on Resources#TASK or</li>
    *          <li>if {@link ProcessEngineConfiguration#enforceSpecificVariablePermission this} configuration is enabled and
    *          the user has no TaskPermissions#READ_VARIABLE permission on Resources#TASK</li></p>
    *          <p>In case the task is part of a running process instance:</li>
    *          <li>if the user has no Permissions#READ permission on Resources#TASK and
    *           no Permissions#READ_TASK permission on Resources#PROCESS_DEFINITION </li>
    *          <li>in case {@link ProcessEngineConfiguration#enforceSpecificVariablePermission this} configuration is enabled and
    *          the user has no TaskPermissions#READ_VARIABLE permission on Resources#TASK and
    *          no ProcessDefinitionPermissions#READ_TASK_VARIABLE permission on Resources#PROCESS_DEFINITION</li></p>
    *
    * @since 7.2
    */
    public function getVariableLocalTyped(?string $taskId, ?string $variableName, bool $deserializeValue = true): TypedValueInterface;

    /**
    * Get values for all given variableNames
    *
    * @throws ProcessEngineException
    *          when the task doesn't exist.
    * @throws AuthorizationException
    *          <p>In case of standalone tasks:
    *          <li>if the user has no Permissions#READ permission on Resources#TASK or</li>
    *          <li>if {@link ProcessEngineConfiguration#enforceSpecificVariablePermission this} configuration is enabled and
    *          the user has no TaskPermissions#READ_VARIABLE permission on Resources#TASK</li></p>
    *          <p>In case the task is part of a running process instance:</li>
    *          <li>if the user has no Permissions#READ permission on Resources#TASK and
    *           no Permissions#READ_TASK permission on Resources#PROCESS_DEFINITION </li>
    *          <li>in case {@link ProcessEngineConfiguration#enforceSpecificVariablePermission this} configuration is enabled and
    *          the user has no TaskPermissions#READ_VARIABLE permission on Resources#TASK and
    *          no ProcessDefinitionPermissions#READ_TASK_VARIABLE permission on Resources#PROCESS_DEFINITION</li></p>
    *
    */
    public function getVariables(?string $taskId, array $variableNames = []): VariableMapInterface;

    /**
    * Get values for all given variableName
    *
    * @param taskId the id of the task
    * @param variableNames only fetch variables whose names are in the collection.
    * @param deserializeValues if false, {@link SerializableValue SerializableValues} will not be deserialized.
    *
    * @throws ProcessEngineException
    *          when the task doesn't exist.
    * @throws AuthorizationException
    *          <p>In case of standalone tasks:
    *          <li>if the user has no Permissions#READ permission on Resources#TASK or</li>
    *          <li>if {@link ProcessEngineConfiguration#enforceSpecificVariablePermission this} configuration is enabled and
    *          the user has no TaskPermissions#READ_VARIABLE permission on Resources#TASK</li></p>
    *          <p>In case the task is part of a running process instance:</li>
    *          <li>if the user has no Permissions#READ permission on Resources#TASK and
    *           no Permissions#READ_TASK permission on Resources#PROCESS_DEFINITION </li>
    *          <li>in case {@link ProcessEngineConfiguration#enforceSpecificVariablePermission this} configuration is enabled and
    *          the user has no TaskPermissions#READ_VARIABLE permission on Resources#TASK and
    *          no ProcessDefinitionPermissions#READ_TASK_VARIABLE permission on Resources#PROCESS_DEFINITION</li></p>
    *
    * @since 7.2
    * */
    public function getVariablesTyped(?string $taskId, array $variableNames = [], bool $deserializeValues = true): VariableMapInterface;

    /**
    * Get a variable on a task
    *
    * @throws ProcessEngineException
    *          when the task doesn't exist.
    * @throws AuthorizationException
    *          <p>In case of standalone tasks:
    *          <li>if the user has no Permissions#READ permission on Resources#TASK or</li>
    *          <li>if {@link ProcessEngineConfiguration#enforceSpecificVariablePermission this} configuration is enabled and
    *          the user has no TaskPermissions#READ_VARIABLE permission on Resources#TASK</li></p>
    *          <p>In case the task is part of a running process instance:</li>
    *          <li>if the user has no Permissions#READ permission on Resources#TASK and
    *           no Permissions#READ_TASK permission on Resources#PROCESS_DEFINITION </li>
    *          <li>in case {@link ProcessEngineConfiguration#enforceSpecificVariablePermission this} configuration is enabled and
    *          the user has no TaskPermissions#READ_VARIABLE permission on Resources#TASK and
    *          no ProcessDefinitionPermissions#READ_TASK_VARIABLE permission on Resources#PROCESS_DEFINITION</li></p>
    *
    */
    public function getVariablesLocal(?string $taskId, array $variableNames = []): VariableMapInterface;

    /**
    * Get values for all given variableName. Only search in the local task scope.
    *
    * @param taskId the id of the task
    * @param variableNames only fetch variables whose names are in the collection.
    * @param deserializeValues if false, {@link SerializableValue SerializableValues} will not be deserialized.
    *
    * @throws ProcessEngineException
    *          when the task doesn't exist.
    * @throws AuthorizationException
    *          <p>In case of standalone tasks:
    *          <li>if the user has no Permissions#READ permission on Resources#TASK or</li>
    *          <li>if {@link ProcessEngineConfiguration#enforceSpecificVariablePermission this} configuration is enabled and
    *          the user has no TaskPermissions#READ_VARIABLE permission on Resources#TASK</li></p>
    *          <p>In case the task is part of a running process instance:</li>
    *          <li>if the user has no Permissions#READ permission on Resources#TASK and
    *           no Permissions#READ_TASK permission on Resources#PROCESS_DEFINITION </li>
    *          <li>in case {@link ProcessEngineConfiguration#enforceSpecificVariablePermission this} configuration is enabled and
    *          the user has no TaskPermissions#READ_VARIABLE permission on Resources#TASK and
    *          no ProcessDefinitionPermissions#READ_TASK_VARIABLE permission on Resources#PROCESS_DEFINITION</li></p>
    *
    * @since 7.2
    */
    public function getVariablesLocalTyped(?string $taskId, array $variableNames = [], bool $deserializeValues = true): VariableMapInterface;

    /**
    * Removes the variable from the task.
    * When the variable does not exist, nothing happens.
    *
    * @throws ProcessEngineException
    *          when the task doesn't exist.
    * @throws AuthorizationException
    *           If the user has none of the following:
    *           <li>TaskPermissions#UPDATE_VARIABLE permission on Resources#TASK</li>
    *           <li>Permissions#UPDATE permission on Resources#TASK</li>
    *           <li>or if the task is part of a running process instance:</li>
    *           <ul>
    *           <li>ProcessDefinitionPermissions#UPDATE_TASK_VARIABLE permission on Resources#PROCESS_DEFINITION</li>
    *           <li>Permissions#UPDATE_TASK permission on Resources#PROCESS_DEFINITION</li>
    *           </ul>
    */
    public function removeVariable(?string $taskId, ?string $variableName): void;

    /**
    * Removes the variable from the task (not considering parent scopes).
    * When the variable does not exist, nothing happens.
    *
    * @throws ProcessEngineException
    *          when the task doesn't exist.
    * @throws AuthorizationException
    *           If the user has none of the following:
    *           <li>TaskPermissions#UPDATE_VARIABLE permission on Resources#TASK</li>
    *           <li>Permissions#UPDATE permission on Resources#TASK</li>
    *           <li>or if the task is part of a running process instance:</li>
    *           <ul>
    *           <li>ProcessDefinitionPermissions#UPDATE_TASK_VARIABLE permission on Resources#PROCESS_DEFINITION</li>
    *           <li>Permissions#UPDATE_TASK permission on Resources#PROCESS_DEFINITION</li>
    *           </ul>
    */
    public function removeVariableLocal(?string $taskId, ?string $variableName): void;

    /**
    * Removes all variables in the given collection from the task.
    * Non existing variable names are simply ignored.
    *
    * @throws ProcessEngineException
    *          when the task doesn't exist.
    * @throws AuthorizationException
    *           If the user has none of the following:
    *           <li>TaskPermissions#UPDATE_VARIABLE permission on Resources#TASK</li>
    *           <li>Permissions#UPDATE permission on Resources#TASK</li>
    *           <li>or if the task is part of a running process instance:</li>
    *           <ul>
    *           <li>ProcessDefinitionPermissions#UPDATE_TASK_VARIABLE permission on Resources#PROCESS_DEFINITION</li>
    *           <li>Permissions#UPDATE_TASK permission on Resources#PROCESS_DEFINITION</li>
    *           </ul>
    */
    public function removeVariables(?string $taskId, ?array $variableNames = []): void;

    /**
    * Removes all variables in the given collection from the task (not considering parent scopes).
    * Non existing variable names are simply ignored.
    *
    * @throws ProcessEngineException
    *          when the task doesn't exist.
    * @throws AuthorizationException
    *           If the user has none of the following:
    *           <li>TaskPermissions#UPDATE_VARIABLE permission on Resources#TASK</li>
    *           <li>Permissions#UPDATE permission on Resources#TASK</li>
    *           <li>or if the task is part of a running process instance:</li>
    *           <ul>
    *           <li>ProcessDefinitionPermissions#UPDATE_TASK_VARIABLE permission on Resources#PROCESS_DEFINITION</li>
    *           <li>Permissions#UPDATE_TASK permission on Resources#PROCESS_DEFINITION</li>
    *           </ul>
    */
    public function removeVariablesLocal(?string $taskId, ?array $variableNames = []): void;

    /** Creates a comment to a task and/or process instance and returns the comment. */
    public function createComment(?string $taskId, ?string $processInstanceId, ?string $message): CommentInterface;

    /** The comments related to the given task. */
    public function getTaskComments(?string $taskId): array;

    /** Retrieve a particular task comment */
    public function getTaskComment(?string $taskId, ?string $commentId): CommentInterface;

    /** The comments related to the given process instance. */
    public function getProcessInstanceComments(?string $processInstanceId): array;

    /**
    * Add a new attachment to a task and/or a process instance and use an input stream to provide the content
    * please use method in runtime service to operate on process instance.
    *
    * Either taskId or processInstanceId has to be provided
    *
    * @param taskId task that should have an attachment
    * @param processInstanceId id of a process to use if task id is null
    * @param attachmentType name of the attachment, can be null
    * @param attachmentName name of the attachment, can be null
    * @param attachmentDescription  full text description, can be null
    * @param content byte array with content of attachment
    *
    */
    public function createAttachment(?string $attachmentType, ?string $taskId, ?string $processInstanceId, ?string $attachmentName, ?string $attachmentDescription, $content = null, ?string $url = null): AttachmentInterface;

    /** Update the name and decription of an attachment */
    public function saveAttachment(AttachmentInterface $attachment): void;

    /** Retrieve a particular attachment */
    public function getAttachment(?string $attachmentId): AttachmentInterface;

    /** Retrieve a particular attachment to the given task id and attachment id*/
    public function getTaskAttachment(?string $taskId, ?string $attachmentId): AttachmentInterface;

    /** Retrieve stream content of a particular attachment */
    public function getAttachmentContent(?string $attachmentId);

    /** Retrieve stream content of a particular attachment to the given task id and attachment id*/
    public function getTaskAttachmentContent(?string $taskId, ?string $attachmentId);

    /** The list of attachments associated to a task */
    public function getTaskAttachments(?string $taskId): array;

    /** The list of attachments associated to a process instance */
    public function getProcessInstanceAttachments(?string $processInstanceId): array;

    /** Delete an attachment */
    public function deleteAttachment(?string $attachmentId): void;

    /** Delete an attachment to the given task id and attachment id */
    public function deleteTaskAttachment(?string $taskId, ?string $attachmentId): void;

    /** The list of subtasks for this parent task */
    public function getSubTasks(?string $parentTaskId): array;

    /** Instantiate a task report */
    public function createTaskReport(): TaskReportInterface;

    /**
    * @see #handleBpmnError(String, String)
    *
    * @param taskId the id of an existing active task
    * @param errorCode the error code of the corresponding bmpn error
    * @param errorMessage the error message of the corresponding bmpn error
    * @param variables the variables to pass to the execution
    */
    public function handleBpmnError(?string $taskId, ?string $errorCode, ?string $errorMessage = null, array $variables = []): void;

    /**
    * Signals that an escalation appears, which should be handled by the process engine.
    *
    * @param taskId the id of an existing active task
    * @param escalationCode the escalation code of the corresponding escalation
    * @param variables the variables to pass to the execution
    *
    * @throws NotFoundException if no task with the given id exists
    * @throws BadUserRequestException if task id or escalation code were null or empty
    * @throws SuspendedEntityInteractionException if the task is suspended
    * @throws AuthorizationException if the user has none of the following permissions:
    * <li>Permissions#TASK_WORK permission on Resources#TASK or
    *                                                    Resources#PROCESS_DEFINITION resource</li>
    * <li>Permissions#UPDATE permission on Resources#TASK resource</li>
    * <li>Permissions#UPDATE_TASK permission on Resources#PROCESS_DEFINITION resource</li>
    */
    public function handleEscalation(?string $taskId, ?string $escalationCode, array $variables = []): void;
}
