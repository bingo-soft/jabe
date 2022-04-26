<?php

namespace Jabe\Engine\Impl\Task\Listener;

use Jabe\Engine\ProcessEngineException;
use Jabe\Engine\Delegate\{
    DelegateTaskInterface,
    TaskListenerInterface
};
use Jabe\Engine\Impl\Bpmn\Parser\FieldDeclaration;
use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Delegate\ClassDelegate;
use Jabe\Engine\Impl\Task\Delegate\TaskListenerInvocation;

class ClassDelegateTaskListener extends ClassDelegate implements TaskListenerInterface
{
    public function __construct(string $className, array $fieldDeclarations)
    {
        parent::__construct($className, $fieldDeclarations);
    }

    public function notify(DelegateTaskInterface $delegateTask): void
    {
        $taskListenerInstance = $this->getTaskListenerInstance();
        try {
            Context::getProcessEngineConfiguration()
            ->getDelegateInterceptor()
            ->handleInvocation(new TaskListenerInvocation($taskListenerInstance, $delegateTask));
        } catch (\Exception $e) {
            throw new ProcessEngineException("Exception while invoking TaskListener: " . $e->getMessage(), $e);
        }
    }

    protected function getTaskListenerInstance(): TaskListenerInterface
    {
        $delegateInstance = $this->instantiateDelegate($this->className, $this->fieldDeclarations);

        if ($delegateInstance instanceof TaskListenerInterface) {
            return $delegateInstance;
        } else {
            throw new ProcessEngineException(get_class($delegateInstance) . " doesn't implement " . TaskListenerInterface::class);
        }
    }
}
