<?php

namespace Jabe\Impl\Task\Listener;

use Jabe\ProcessEngineException;
use Jabe\Delegate\{
    DelegateTaskInterface,
    TaskListenerInterface
};
use Jabe\Impl\Context\Context;
use Jabe\Impl\Delegate\ClassDelegate;
use Jabe\Impl\Task\Delegate\TaskListenerInvocation;
use Jabe\Impl\Util\ClassDelegateUtil;

class ClassDelegateTaskListener extends ClassDelegate implements TaskListenerInterface
{
    public function __construct(?string $className, array $fieldDeclarations)
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
        } catch (\Throwable $e) {
            throw new ProcessEngineException("Exception while invoking TaskListener: " . $e->getMessage(), $e);
        }
    }

    protected function getTaskListenerInstance(): TaskListenerInterface
    {
        $delegateInstance = ClassDelegateUtil::instantiateDelegate($this->className, $this->fieldDeclarations);
        if ($delegateInstance instanceof TaskListenerInterface) {
            return $delegateInstance;
        } else {
            throw new ProcessEngineException(get_class($delegateInstance) . " doesn't implement " . TaskListenerInterface::class);
        }
    }
}
