<?php

namespace Tests\Bpmn\UserTask;

use Jabe\Impl\Context\Context;
use Jabe\Impl\Bpmn\Parser\BpmnParse;
use Jabe\Impl\Interceptor\{
    CommandContext,
    CommandInterface,
    CommandInvocationContext
};
use Jabe\Test\{
    Deployment,
    RequiredHistoryLevel
};
use Tests\Util\PluggableProcessEngineTest;

class TaskPriorityExtensionsTest extends PluggableProcessEngineTest
{
    private const NAMESPACE = "xmlns='http://www.omg.org/spec/BPMN/20100524/MODEL'";
    private const TARGET_NAMESPACE = "targetNamespace='" . BpmnParse::BPMN_EXTENSIONS_NS_PREFIX . "'";

    #[Deployment(resources: [ "tests/Resources/Bpmn/UserTask/TaskPriorityExtensionsTest.testPriorityExtension.bpmn20.xml"])]
    public function testPriorityExtension(): void
    {
        $this->priorityExtension(25);
        $this->priorityExtension(75);
    }

    private function priorityExtension(int $priority): void
    {
        $variables = ["taskPriority" => $priority, "customVar" => "Тестовая переменная"];
        // Start process-instance, passing priority that should be used as task priority
        $processInstance = $this->runtimeService->startProcessInstanceByKey("taskPriorityExtension", null, $variables);
        $task = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->assertEquals($priority, $task->getPriority());
    }
}
