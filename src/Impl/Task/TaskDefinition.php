<?php

namespace Jabe\Impl\Task;

use Jabe\Delegate\{
    ExpressionInterface,
    TaskListenerInterface
};
use Jabe\Impl\Form\FormDefinition;
use Jabe\Impl\Form\Handler\TaskFormHandlerInterface;
use Jabe\Impl\Util\CollectionUtil;

class TaskDefinition
{
    protected $key;

    // assignment fields
    protected $nameExpression;
    protected $descriptionExpression;
    protected $assigneeExpression;
    protected $candidateUserIdExpressions = [];
    protected $candidateGroupIdExpressions = [];
    protected $dueDateExpression;
    protected $followUpDateExpression;
    protected $priorityExpression;

    // form fields
    protected $taskFormHandler;
    protected $formDefinition;

    // task listeners
    protected $taskListeners = [];
    protected $builtinTaskListeners = [];
    protected $timeoutTaskListeners = [];

    public function __construct(TaskFormHandlerInterface $taskFormHandler)
    {
        $this->taskFormHandler = $taskFormHandler;
        $this->formDefinition = new FormDefinition();
    }

    // getters and setters //////////////////////////////////////////////////////

    public function getNameExpression(): ?ExpressionInterface
    {
        return $this->nameExpression;
    }

    public function setNameExpression(ExpressionInterface $nameExpression): void
    {
        $this->nameExpression = $nameExpression;
    }

    public function getDescriptionExpression(): ?ExpressionInterface
    {
        return $this->descriptionExpression;
    }

    public function setDescriptionExpression(ExpressionInterface $descriptionExpression): void
    {
        $this->descriptionExpression = $descriptionExpression;
    }

    public function getAssigneeExpression(): ?ExpressionInterface
    {
        return $this->assigneeExpression;
    }

    public function setAssigneeExpression(ExpressionInterface $assigneeExpression): void
    {
        $this->assigneeExpression = $assigneeExpression;
    }

    public function getCandidateUserIdExpressions(): array
    {
        return $this->candidateUserIdExpressions;
    }

    public function addCandidateUserIdExpression(ExpressionInterface $userId): void
    {
        $this->candidateUserIdExpressions[] = $userId;
    }

    public function getCandidateGroupIdExpressions(): array
    {
        return $this->candidateGroupIdExpressions;
    }

    public function addCandidateGroupIdExpression(ExpressionInterface $groupId): void
    {
        $this->candidateGroupIdExpressions[] = $groupId;
    }

    public function getPriorityExpression(): ?ExpressionInterface
    {
        return $this->priorityExpression;
    }

    public function setPriorityExpression(ExpressionInterface $priorityExpression): void
    {
        $this->priorityExpression = $priorityExpression;
    }

    public function getTaskFormHandler(): TaskFormHandlerInterface
    {
        return $this->taskFormHandler;
    }

    public function setTaskFormHandler(TaskFormHandlerInterface $taskFormHandler): void
    {
        $this->taskFormHandler = $taskFormHandler;
    }

    public function getKey(): ?string
    {
        return $this->key;
    }

    public function setKey(?string $key): void
    {
        $this->key = $key;
    }

    public function getDueDateExpression(): ?ExpressionInterface
    {
        return $this->dueDateExpression;
    }

    public function setDueDateExpression(ExpressionInterface $dueDateExpression): void
    {
        $this->dueDateExpression = $dueDateExpression;
    }

    public function getFollowUpDateExpression(): ?ExpressionInterface
    {
        return $this->followUpDateExpression;
    }

    public function setFollowUpDateExpression(ExpressionInterface $followUpDateExpression): void
    {
        $this->followUpDateExpression = $followUpDateExpression;
    }

    public function setTaskListeners(array $taskListeners): void
    {
        $this->taskListeners = $taskListeners;
    }

    public function getTaskListeners(?string $eventName = null): array
    {
        if ($eventName !== null) {
            if (array_key_exists($eventName, $this->taskListeners)) {
                return $this->taskListeners[$eventName];
            }
            return [];
        }
        return $this->taskListeners;
    }

    public function getBuiltinTaskListeners(?string $eventName = null): array
    {
        if ($eventName !== null) {
            if (array_key_exists($eventName, $this->builtinTaskListeners)) {
                return $this->builtinTaskListeners[$eventName];
            }
            return [];
        }
        return $this->builtinTaskListeners;
    }

    public function getTimeoutTaskListener(?string $timeoutId): ?TaskListenerInterface
    {
        if (array_key_exists($timeoutId, $this->timeoutTaskListeners)) {
            return $this->timeoutTaskListeners[$timeoutId];
        }
        return null;
    }

    public function addTaskListener(?string $eventName, TaskListenerInterface $taskListener): void
    {
        CollectionUtil::addToMapOfLists($this->taskListeners, $eventName, $taskListener);
    }

    public function addBuiltInTaskListener(?string $eventName, TaskListenerInterface $taskListener): void
    {
        $listeners = [];
        if (array_key_exists($eventName, $this->taskListeners)) {
            $listeners = $this->taskListeners[$eventName];
        }
        $init = false;
        if (empty($listeners)) {
            //$this->taskListeners[$eventName] = $listeners;
            $init = true;
        }

        array_unshift($listeners, $taskListener);
        if ($init) {
            $this->taskListeners[$eventName] = $listeners;
        }

        CollectionUtil::addToMapOfLists($this->builtinTaskListeners, $eventName, $taskListener);
    }

    public function addTimeoutTaskListener(?string $timeoutId, TaskListenerInterface $taskListener): void
    {
        $this->timeoutTaskListeners[$timeoutId] = $taskListener;
    }

    public function getFormDefinition(): FormDefinition
    {
        return $this->formDefinition;
    }

    public function setFormDefinition(FormDefinition $formDefinition): void
    {
        $this->formDefinition = $formDefinition;
    }

    public function getFormKey(): ?ExpressionInterface
    {
        return $this->formDefinition->getFormKey();
    }

    public function setFormKey(ExpressionInterface $formKey): void
    {
        $this->formDefinition->setFormKey($formKey);
    }

    public function getFormDefinitionKey(): ExpressionInterface
    {
        return $this->formDefinition->getFormDefinitionKey();
    }

    public function getFormDefinitionBinding(): ?string
    {
        return $this->formDefinition->getFormDefinitionBinding();
    }

    public function getFormDefinitionVersion(): ExpressionInterface
    {
        return $this->formDefinition->getFormDefinitionVersion();
    }
}
