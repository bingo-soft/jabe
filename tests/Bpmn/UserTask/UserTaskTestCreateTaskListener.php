<?php

namespace Tests\Bpmn\UserTask;

use Jabe\Delegate\{
    DelegateTaskInterface,
    ExpressionInterface,
    TaskListenerInterface
};

class UserTaskTestCreateTaskListener implements TaskListenerInterface
{
    private $expression;

    public function notify(DelegateTaskInterface $delegateTask): void
    {
        if ($this->expression != null && $this->expression->getValue($delegateTask) !== null) {
            // get the expression variable
            $expression = strval($this->expression->getValue($delegateTask));
            // this expression will be evaluated when completing the task
            $delegateTask->setVariableLocal("validationRule", $expression);
        }
    }
}
