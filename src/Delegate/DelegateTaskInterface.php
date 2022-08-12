<?php

namespace Jabe\Delegate;

use Bpmn\Instance\UserTaskInterface;

interface DelegateTaskInterface extends VariableScopeInterface, BpmnModelExecutionContextInterface, ProcessEngineServicesAwareInterface
{
    /** DB id of the task. */
    public function getId(): string;

    /** Name or title of the task. */
    public function getName(): string;

    /** Change the name of the task. */
    public function setName(string $name): void;

    /** Free text description of the task. */
    public function getDescription(): ?string;

    /** Change the description of the task */
    public function setDescription(string $description): void;

    /** indication of how important/urgent this task is with a number between
     * 0 and 100 where higher values mean a higher priority and lower values mean
     * lower priority: [0..19] lowest, [20..39] low, [40..59] normal, [60..79] high
     * [80..100] highest */
    public function getPriority(): int;

    /** indication of how important/urgent this task is with a number between
     * 0 and 100 where higher values mean a higher priority and lower values mean
     * lower priority: [0..19] lowest, [20..39] low, [40..59] normal, [60..79] high
     * [80..100] highest */
    public function setPriority(int $priority): void;

    /** Reference to the process instance or null if it is not related to a process instance. */
    public function getProcessInstanceId(): string;

    /** Reference to the path of execution or null if it is not related to a process instance. */
    public function getExecutionId(): string;

    /** Reference to the process definition or null if it is not related to a process. */
    public function getProcessDefinitionId(): string;

    /** The date/time when this task was created */
    public function getCreateTime(): string;

    /** The id of the activity in the process defining this task or null if this is not related to a process */
    public function getTaskDefinitionKey(): string;

    /** Returns the execution currently at the task. */
    public function getExecution(): ?DelegateExecutionInterface;

    /** Returns the event name which triggered the task listener to fire for this task. */
    public function getEventName(): string;

    /** Adds the given user as a candidate user to this task. */
    public function addCandidateUser(string $userId): void;

    /** Adds multiple users as candidate user to this task. */
    public function addCandidateUsers(array $candidateUsers): void;

    /** Adds the given group as candidate group to this task */
    public function addCandidateGroup(string $groupId): void;

    /** Adds multiple groups as candidate group to this task. */
    public function addCandidateGroups(array $candidateGroups): void;

    /** The {@link User.getId() userId} of the person responsible for this task. */
    public function getOwner(): string;

    /** The {@link User.getId() userId} of the person responsible for this task.*/
    public function setOwner(string $owner): void;

    /** The {@link User.getId() userId} of the person to which this task is delegated. */
    public function getAssignee(): string;

    /** The {@link User.getId() userId} of the person to which this task is delegated. */
    public function setAssignee(string $assignee): void;

    /** Due date of the task. */
    public function getDueDate(): string;

    /** Change due date of the task. */
    public function setDueDate(string $dueDate): void;

    /** Get delete reason of the task. */
    public function getDeleteReason(): string;

    /**
     * Involves a user with a task. The type of identity link is defined by the given identityLinkType.
     * @param userId id of the user involve, cannot be null.
     * @param identityLinkType type of identityLink, cannot be null (@see IdentityLinkType).
     * @throws ProcessEngineException when the task or user doesn't exist.
     */
    public function addUserIdentityLink(string $userId, string $identityLinkType): void;

    /**
     * Involves a group with group task. The type of identityLink is defined by the given identityLink.
     * @param groupId id of the group to involve, cannot be null.
     * @param identityLinkType type of identity, cannot be null (@see IdentityLinkType).
     * @throws ProcessEngineException when the task or group doesn't exist.
     */
    public function addGroupIdentityLink(string $groupId, string $identityLinkType): void;

    /**
     * Convenience shorthand for {@link #deleteUserIdentityLink(String, String)}; with type IdentityLinkType#CANDIDATE
     * @param userId id of the user to use as candidate, cannot be null.
     * @throws ProcessEngineException when the task or user doesn't exist.
     */
    public function deleteCandidateUser(string $userId): void;

    /**
     * Convenience shorthand for {@link #deleteGroupIdentityLink(String, String, String)}; with type IdentityLinkType#CANDIDATE
     * @param groupId id of the group to use as candidate, cannot be null.
     * @throws ProcessEngineException when the task or group doesn't exist.
     */
    public function deleteCandidateGroup(string $groupId): void;

    /**
     * Removes the association between a user and a task for the given identityLinkType.
     * @param userId id of the user involve, cannot be null.
     * @param identityLinkType type of identityLink, cannot be null (@see IdentityLinkType).
     * @throws ProcessEngineException when the task or user doesn't exist.
     */
    public function deleteUserIdentityLink(string $userId, string $identityLinkType): void;

    /**
     * Removes the association between a group and a task for the given identityLinkType.
     * @param groupId id of the group to involve, cannot be null.
     * @param identityLinkType type of identity, cannot be null (@see IdentityLinkType).
     * @throws ProcessEngineException when the task or group doesn't exist.
     */
    public function deleteGroupIdentityLink(string $groupId, string $identityLinkType): void;

    /**
     * Retrieves the candidate users and groups associated with the task.
     * @return set of IdentityLinks of type IdentityLinkType#CANDIDATE.
     */
    public function getCandidates(): array;

    /**
     * Provides access to the current UserTask Element from the Bpmn Model.
     * @return UserTaskInterface the current UserTask Element from the Bpmn Model.
     */
    public function getBpmnModelElementInstance(): UserTaskInterface;

    /**
     * Return the id of the tenant this task belongs to. Can be <code>null</code>
     * if the task belongs to no single tenant.
     */
    public function getTenantId(): ?string;

    /** Follow-up date of the task. */
    public function getFollowUpDate(): string;

    /** Change follow-up date of the task. */
    public function setFollowUpDate(string $followUpDate): void;

    /**
     * set status to complete.
     *
     * @throws IllegalStateException if performed on completion or deletion
     */
    public function complete(): void;
}
