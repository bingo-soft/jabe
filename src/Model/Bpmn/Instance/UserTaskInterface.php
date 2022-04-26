<?php

namespace Jabe\Model\Bpmn\Instance;

use Jabe\Model\Bpmn\Builder\UserTaskBuilder;

interface UserTaskInterface extends TaskInterface
{
    public function builder(): UserTaskBuilder;

    public function getImplementation(): string;

    public function setImplementation(string $implementation): void;

    public function getRenderings(): array;

    public function getAssignee(): string;

    public function setAssignee(string $assignee): void;

    public function getCandidateGroups(): string;

    public function setCandidateGroups(string $candidateGroups): void;

    public function getCandidateGroupsList(): array;

    public function setCandidateGroupsList(array $candidateGroupsList): void;

    public function getCandidateUsers(): ?string;

    public function setCandidateUsers(?string $candidateUsers): void;

    public function getCandidateUsersList(): array;

    public function setCandidateUsersList(array $candidateUsersList): void;

    public function getDueDate(): string;

    public function setDueDate(string $dueDate): void;

    public function getFollowUpDate(): string;

    public function setFollowUpDate(string $followUpDate): void;

    public function getFormHandlerClass(): string;

    public function setFormHandlerClass(string $formHandlerClass): void;

    public function getFormKey(): string;

    public function setFormKey(string $formKey): void;

    public function getPriority(): string;

    public function setPriority(string $priority): void;
}
