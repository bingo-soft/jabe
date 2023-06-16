<?php

namespace Jabe\Impl\Task;

use Jabe\ProcessEngineException;
use Jabe\Delegate\{
    ExpressionInterface,
    VariableScopeInterface
};
use Jabe\Impl\Calendar\{
    BusinessCalendarInterface,
    DueDateBusinessCalendar
};
use Jabe\Impl\Context\Context;
use Jabe\Impl\El\ExpressionManagerInterface;
use Jabe\Impl\Persistence\Entity\TaskEntity;

class TaskDecorator
{
    protected $taskDefinition;
    protected $expressionManager;

    public function __construct(TaskDefinition $taskDefinition, ExpressionManagerInterface $expressionManager)
    {
        $this->taskDefinition = $taskDefinition;
        $this->expressionManager = $expressionManager;
    }

    public function decorate(TaskEntity $task, VariableScopeInterface $variableScope): void
    {
        // set the taskDefinition
        $task->setTaskDefinition($this->taskDefinition);
        // name
        $this->initializeTaskName($task, $variableScope);
        // description
        $this->initializeTaskDescription($task, $variableScope);
        // dueDate
        $this->initializeTaskDueDate($task, $variableScope);
        // followUpDate
        $this->initializeTaskFollowUpDate($task, $variableScope);
        // priority
        $this->initializeTaskPriority($task, $variableScope);
        // assignments
        $this->initializeTaskAssignments($task, $variableScope);
    }

    protected function initializeTaskName(TaskEntity $task, VariableScopeInterface $variableScope): void
    {
        $nameExpression = $this->taskDefinition->getNameExpression();
        if ($nameExpression !== null) {
            $name = $nameExpression->getValue($variableScope);
            $task->setName($name);
        }
    }

    protected function initializeTaskDescription(TaskEntity $task, VariableScopeInterface $variableScope): void
    {
        $descriptionExpression = $this->taskDefinition->getDescriptionExpression();
        if ($descriptionExpression !== null) {
            $description = $descriptionExpression->getValue($variableScope);
            $task->setDescription($description);
        }
    }

    protected function initializeTaskDueDate(TaskEntity $task, VariableScopeInterface $variableScope): void
    {
        $dueDateExpression = $this->taskDefinition->getDueDateExpression();
        if ($dueDateExpression !== null) {
            $dueDate = $dueDateExpression->getValue($variableScope);
            if ($dueDate !== null) {
                if ($dueDate instanceof \DateTime) {
                    $task->setDueDate($dueDate->format('Y-m-d H:i:s'));
                } elseif (is_string($dueDate)) {
                    $businessCalendar = $this->getBusinessCalender();
                    $task->setDueDate($businessCalendar->resolveDuedate($dueDate/*, $task*/)->format('Y-m-d H:i:s'));
                } else {
                    throw new ProcessEngineException("Due date expression does not resolve to a Date or Date string: " .
                        $dueDateExpression->getExpressionText());
                }
            }
        }
    }

    protected function initializeTaskFollowUpDate(TaskEntity $task, VariableScopeInterface $variableScope): void
    {
        $followUpDateExpression = $this->taskDefinition->getFollowUpDateExpression();
        if ($followUpDateExpression !== null) {
            $followUpDate = $followUpDateExpression->getValue($variableScope);
            if ($followUpDate !== null) {
                if ($followUpDate instanceof \DateTime) {
                    $task->setFollowUpDate($followUpDate->format('Y-m-d H:i:s'));
                } elseif (is_string($followUpDate)) {
                    $businessCalendar = $this->getBusinessCalender();
                    $task->setFollowUpDate($businessCalendar->resolveDuedate($followUpDate/*, $task*/)->format('Y-m-d H:i:s'));
                } else {
                    throw new ProcessEngineException("Follow up date expression does not resolve to a Date or Date string: " .
                        $followUpDateExpression->getExpressionText());
                }
            }
        }
    }

    protected function initializeTaskPriority(TaskEntity $task, VariableScopeInterface $variableScope): void
    {
        $priorityExpression = $this->taskDefinition->getPriorityExpression();
        if ($priorityExpression !== null) {
            $priority = $priorityExpression->getValue($variableScope);
            if ($priority !== null) {
                if (is_string($priority)) {
                    try {
                        $task->setPriority(intval($priority));
                    } catch (\Exception $e) {
                        throw new ProcessEngineException("Priority does not resolve to a number: " . $priority, $e);
                    }
                } elseif (is_numeric($priority)) {
                    $task->setPriority(intval($priority));
                } else {
                    throw new ProcessEngineException("Priority expression does not resolve to a number: " .
                            $priorityExpression->getExpressionText());
                }
            }
        }
    }

    protected function initializeTaskAssignments(TaskEntity $task, VariableScopeInterface $variableScope): void
    {
        // assignee
        $this->initializeTaskAssignee($task, $variableScope);
        // candidateUsers
        $this->initializeTaskCandidateUsers($task, $variableScope);
        // candidateGroups
        $this->initializeTaskCandidateGroups($task, $variableScope);
    }

    protected function initializeTaskAssignee(TaskEntity $task, VariableScopeInterface $variableScope): void
    {
        $assigneeExpression = $this->taskDefinition->getAssigneeExpression();
        if ($assigneeExpression !== null) {
            $task->setAssignee($assigneeExpression->getValue($variableScope));
        }
    }

    protected function initializeTaskCandidateGroups(TaskEntity $task, VariableScopeInterface $variableScope): void
    {
        $candidateGroupIdExpressions = $this->taskDefinition->getCandidateGroupIdExpressions();
        foreach ($candidateGroupIdExpressions as $groupIdExpr) {
            $value = $groupIdExpr->getValue($variableScope);
            if (is_string($value)) {
                $candiates = $this->extractCandidates($value);
                $task->addCandidateGroups($candiates);
            } elseif (is_array($value)) {
                $task->addCandidateGroups($value);
            } else {
                throw new ProcessEngineException("Expression did not resolve to a string or collection of strings");
            }
        }
    }

    protected function initializeTaskCandidateUsers(TaskEntity $task, VariableScopeInterface $variableScope): void
    {
        $candidateUserIdExpressions = $this->taskDefinition->getCandidateUserIdExpressions();
        foreach ($candidateUserIdExpressions as $userIdExpr) {
            $value = $userIdExpr->getValue($variableScope);
            if (is_string($value)) {
                $candiates = $this->extractCandidates($value);
                $task->addCandidateUsers($candiates);
            } elseif (is_array($value)) {
                $task->addCandidateUsers($value);
            } else {
                throw new ProcessEngineException("Expression did not resolve to a string or collection of strings");
            }
        }
    }

    /**
     * Extract a candidate list from a string.
     */
    protected function extractCandidates(?string $str): array
    {
        return preg_split("/[\s]*,[\s]*/", $str);
    }

    // getters ///////////////////////////////////////////////////////////////

    public function getTaskDefinition(): TaskDefinition
    {
        return $this->taskDefinition;
    }

    public function getExpressionManager(): ExpressionManagerInterface
    {
        return $this->expressionManager;
    }

    protected function getBusinessCalender(): BusinessCalendarInterface
    {
        return Context::getProcessEngineConfiguration()
            ->getBusinessCalendarManager()
            ->getBusinessCalendar(DueDateBusinessCalendar::NAME);
    }
}
