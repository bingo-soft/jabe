<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\Extension\TaskListenerInterface;
use BpmPlatform\Model\Bpmn\Instance\Extension\{
    FormDataInterface,
    FormFieldInterface,
};
use BpmPlatform\Model\Bpmn\Instance\{
    TimerEventDefinitionInterface,
    UserTaskInterface
};

abstract class AbstractUserTaskBuilder extends AbstractTaskBuilder
{
    protected function __construct(
        BpmnModelInstanceInterface $modelInstance,
        UserTaskInterface $element,
        string $selfType
    ) {
        parent::__construct($modelInstance, $element, $selfType);
    }

    public function implementation(string $implementation): AbstractUserTaskBuilder
    {
        $this->element->setImplementation($implementation);
        return $this;
    }

    public function assignee(string $assignee): AbstractUserTaskBuilder
    {
        $this->element->setAssignee($assignee);
        return $this;
    }

    /**
     * @param mixed $candidateGroups
     */
    public function candidateGroups($candidateGroups): AbstractUserTaskBuilder
    {
        if (is_array($candidateGroups)) {
            $this->element->setCandidateGroupsList($candidateGroups);
        } elseif (is_string($candidateGroups)) {
            $this->element->setCandidateGroups($candidateGroups);
        }
        return $this;
    }

    /**
     * @param mixed $candidateUsers
     */
    public function candidateUsers($candidateUsers): AbstractUserTaskBuilder
    {
        if (is_array($candidateUsers)) {
            $this->element->setCandidateUsersList($candidateUsers);
        } elseif (is_string($candidateUsers)) {
            $this->element->setCandidateUsers($candidateUsers);
        }
        return $this;
    }

    public function dueDate(string $dueDate): AbstractUserTaskBuilder
    {
        $this->element->setDueDate($dueDate);
        return $this;
    }

    public function followUpDate(string $followUpDate): AbstractUserTaskBuilder
    {
        $this->element->setFollowUpDate($followUpDate);
        return $this;
    }

    public function formHandlerClass(string $className): AbstractUserTaskBuilder
    {
        $this->element->setFormHandlerClass($className);
        return $this;
    }

    public function formKey(string $formKey): AbstractUserTaskBuilder
    {
        $this->element->setFormKey($formKey);
        return $this;
    }

    public function priority(string $priority): AbstractUserTaskBuilder
    {
        $this->element->setPriority($priority);
        return $this;
    }

    public function formField(): UserTaskFormFieldBuilder
    {
        $formData = $this->getCreateSingleExtensionElement(FormDataInterface::class);
        $formField = $this->createChild($formData, FormFieldInterface::class);
        return new UserTaskFormFieldBuilder($this->modelInstance, $this->element, $formField);
    }

    public function taskListenerClass(string $eventName, string $className): AbstractUserTaskBuilder
    {
        $executionListener = $this->createInstance(TaskListenerInterface::class);
        $executionListener->setEvent($eventName);
        $executionListener->setClass($className);

        $this->addExtensionElement($executionListener);
        return $this;
    }

    public function taskListenerExpression(string $eventName, string $expression): AbstractUserTaskBuilder
    {
        $executionListener = $this->createInstance(TaskListenerInterface::class);
        $executionListener->setEvent($eventName);
        $executionListener->setExpression($expression);

        $this->addExtensionElement($executionListener);
        return $this;
    }

    public function taskListenerDelegateExpression(
        string $eventName,
        string $delegateExpression
    ): AbstractUserTaskBuilder {
        $executionListener = $this->createInstance(TaskListenerInterface::class);
        $executionListener->setEvent($eventName);
        $executionListener->setDelegateExpression($delegateExpression);

        $this->addExtensionElement($executionListener);
        return $this;
    }

    public function taskListenerClassTimeoutWithCycle(
        string $id,
        string $className,
        string $timerCycle
    ): AbstractUserTaskBuilder {
        return $this->createTaskListenerClassTimeout($id, $className, $this->createTimeCycle($timerCycle));
    }

    public function taskListenerClassTimeoutWithDate(
        string $id,
        string $className,
        string $timerDate
    ): AbstractUserTaskBuilder {
        return $this->createTaskListenerClassTimeout($id, $className, $this->createTimeDate($timerDate));
    }

    public function taskListenerClassTimeoutWithDuration(
        string $id,
        string $className,
        string $timerDuration
    ): AbstractUserTaskBuilder {
        return $this->createTaskListenerClassTimeout($id, $className, $this->createTimeDuration($timerDuration));
    }

    public function taskListenerExpressionTimeoutWithCycle(
        string $id,
        string $expression,
        string $timerCycle
    ): AbstractUserTaskBuilder {
        return $this->createTaskListenerExpressionTimeout($id, $expression, $this->createTimeCycle($timerCycle));
    }

    public function taskListenerExpressionTimeoutWithDate(
        string $id,
        string $expression,
        string $timerDate
    ): AbstractUserTaskBuilder {
        return $this->createTaskListenerExpressionTimeout($id, $expression, $this->createTimeDate($timerDate));
    }

    public function taskListenerExpressionTimeoutWithDuration(
        string $id,
        string $expression,
        string $timerDuration
    ): AbstractUserTaskBuilder {
        return $this->createTaskListenerExpressionTimeout($id, $expression, $this->createTimeDuration($timerDuration));
    }

    public function taskListenerDelegateExpressionTimeoutWithCycle(
        string $id,
        string $delegateExpression,
        string $timerCycle
    ): AbstractUserTaskBuilder {
        return $this->createTaskListenerDelegateExpressionTimeout(
            $id,
            $delegateExpression,
            $this->createTimeCycle($timerCycle)
        );
    }

    public function taskListenerDelegateExpressionTimeoutWithDate(
        string $id,
        string $delegateExpression,
        string $timerDate
    ): AbstractUserTaskBuilder {
        return $this->createTaskListenerDelegateExpressionTimeout(
            $id,
            $delegateExpression,
            $this->createTimeDate($timerDate)
        );
    }

    public function taskListenerDelegateExpressionTimeoutWithDuration(
        string $id,
        string $delegateExpression,
        string $timerDuration
    ): AbstractUserTaskBuilder {
        return $this->createTaskListenerDelegateExpressionTimeout(
            $id,
            $delegateExpression,
            $this->createTimeDuration($timerDuration)
        );
    }

    protected function createTaskListenerClassTimeout(
        string $id,
        string $className,
        TimerEventDefinitionInterface $timerDefinition
    ): AbstractUserTaskBuilder {
        $executionListener  = $this->createTaskListenerTimeout($id, $timerDefinition);
        $executionListener->setClass($className);
        return $this;
    }

    protected function createTaskListenerExpressionTimeout(
        string $id,
        string $expression,
        TimerEventDefinitionInterface $timerDefinition
    ): AbstractUserTaskBuilder {
        $executionListener  = $this->createTaskListenerTimeout($id, $timerDefinition);
        $executionListener->setExpression($expression);
        return $this;
    }

    protected function createTaskListenerDelegateExpressionTimeout(
        string $id,
        string $delegateExpression,
        TimerEventDefinitionInterface $timerDefinition
    ): AbstractUserTaskBuilder {
        $executionListener  = $this->createTaskListenerTimeout($id, $timerDefinition);
        $executionListener->setDelegateExpression($delegateExpression);
        return $this;
    }

    protected function createTaskListenerTimeout(
        string $id,
        TimerEventDefinitionInterface $timerDefinition
    ): TaskListenerInterface {
        $executionListener = $this->createInstance(TaskListenerInterface::class);
        $executionListener->setAttributeValue(BpmnModelConstants::BPMN_ATTRIBUTE_ID, $id, true);
        $executionListener->setEvent("timeout");
        $executionListener->addChildElement($timerDefinition);
        $this->addExtensionElement($executionListener);
        return $executionListener;
    }
}
