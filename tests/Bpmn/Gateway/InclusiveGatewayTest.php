<?php

namespace Tests\Bpmn\Gateway;

use Bpmn\Bpmn;
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

class InclusiveGatewayTest extends PluggableProcessEngineTest
{
    private const NAMESPACE = "xmlns='http://www.omg.org/spec/BPMN/20100524/MODEL'";
    private const TARGET_NAMESPACE = "targetNamespace='" . BpmnParse::BPMN_EXTENSIONS_NS_PREFIX . "'";

    private const TASK1_NAME = "Task 1";
    private const TASK2_NAME = "Task 2";
    private const TASK3_NAME = "Task 3";

    private const BEAN_TASK1_NAME = "Basic service";
    private const BEAN_TASK2_NAME = "Standard service";
    private const BEAN_TASK3_NAME = "Gold Member service";

    protected function setUp(): void
    {
        //initialize all services
        parent::setUp();
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Gateway/InclusiveGatewayTest.testDivergingInclusiveGateway.bpmn20.xml"])]
    public function testDivergingInclusiveGateway(): void
    {
        for ($i = 1; $i <= 3; $i += 1) {
            $pi = $this->runtimeService->startProcessInstanceByKey("inclusiveGwDiverging", null, ["input" => $i]);
            $tasks = $this->taskService->createTaskQuery()->processInstanceId($pi->getId())->list();
            $expectedNames = [];
            if ($i == 1) {
                $expectedNames[] = self::TASK1_NAME;
            }
            if ($i <= 2) {
                $expectedNames[] = self::TASK2_NAME;
            }
            $expectedNames[] = self::TASK3_NAME;
            $this->assertEquals(4 - $i, count($tasks));
            foreach ($tasks as $task) {
                unset($expectedNames[array_search($task->getName(), $expectedNames)]);
            }
            $this->assertEquals(0, count($expectedNames));
            $this->runtimeService->deleteProcessInstance($pi->getId(), "testing deletion");
        }
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Gateway/InclusiveGatewayTest.testMergingInclusiveGateway.bpmn20.xml"])]
    public function testMergingInclusiveGateway(): void
    {
        $pi = $this->runtimeService->startProcessInstanceByKey("inclusiveGwMerging", null, ["input" => 2]);
        $this->assertEquals(1, $this->taskService->createTaskQuery()->processInstanceId($pi->getId())->count());
        $this->runtimeService->deleteProcessInstance($pi->getId(), "testing deletion");
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Gateway/InclusiveGatewayTest.testMergingInclusiveGatewayAsync.bpmn20.xml"])]
    public function testMergingInclusiveGatewayAsync(): void
    {
        $pi = $this->runtimeService->startProcessInstanceByKey("inclusiveGwMerging", ["input" => 2]);
        //sleep(5);
        $list = $this->managementService->createJobQuery()->processInstanceId($pi->getId())->list();
        foreach ($list as $job) {
            $this->managementService->executeJob($job->getId());
        }
        $this->assertEquals(1, $this->taskService->createTaskQuery()->processInstanceId($pi->getId())->count());
        $this->runtimeService->deleteProcessInstance($pi->getId(), "testing deletion");
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Gateway/InclusiveGatewayTest.testPartialMergingInclusiveGateway.bpmn20.xml"])]
    public function testPartialMergingInclusiveGateway(): void
    {
        $pi = $this->runtimeService->startProcessInstanceByKey("partialInclusiveGwMerging", ["input" => 2]);
        $partialTask = $this->taskService->createTaskQuery()->processInstanceId($pi->getId())->singleResult();
        $this->assertEquals("partialTask", $partialTask->getTaskDefinitionKey());

        $this->taskService->complete($partialTask->getId());

        $fullTask = $this->taskService->createTaskQuery()->processInstanceId($pi->getId())->singleResult();
        $this->assertEquals("theTask", $fullTask->getTaskDefinitionKey());

        $this->runtimeService->deleteProcessInstance($pi->getId(), "testing deletion");
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Gateway/InclusiveGatewayTest.testNoSequenceFlowSelected.bpmn20.xml"])]
    public function testNoSequenceFlowSelected(): void
    {
        $pi = $this->runtimeService->startProcessInstanceByKey("inclusiveGwNoSeqFlowSelected", ["input" => 4]);
        $tasks = $this->taskService->createTaskQuery()->processInstanceId($pi->getId())->count();
        $this->assertEquals(0, $tasks);
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Gateway/InclusiveGatewayTest.testParentActivationOnNonJoiningEnd.bpmn20.xml"])]
    public function testParentActivationOnNonJoiningEnd(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("parentActivationOnNonJoiningEnd");

        $executionsBefore = $this->runtimeService->createExecutionQuery()->processInstanceId($processInstance->getId())->list();
        $this->assertEquals(3, count($executionsBefore));

        // start first round of tasks
        $firstTasks = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->list();

        $this->assertEquals(2, count($firstTasks));

        foreach ($firstTasks as $t) {
            $this->taskService->complete($t->getId());
        }

        // start second round of tasks
        $secondTasks = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->list();

        $this->assertEquals(2, count($secondTasks));

        // complete one task
        $task = $secondTasks[0];
        $this->taskService->complete($task->getId());

        // should have merged last child execution into parent
        $executionsAfter = $this->runtimeService->createExecutionQuery()->processInstanceId($processInstance->getId())->list();
        $this->assertEquals(1, count($executionsAfter));

        $execution = $executionsAfter[0];

        // and should have one active activity
        $activeActivityIds = $this->runtimeService->getActiveActivityIds($execution->getId());
        $this->assertEquals(1, count($activeActivityIds));

        // Completing last task should finish the process instance

        $lastTask = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->taskService->complete($lastTask->getId());

        $this->assertEquals(0, $this->runtimeService->createProcessInstanceQuery()->processInstanceId($processInstance->getId())->active()->count());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Gateway/InclusiveGatewayTest.testDivergingInclusiveGateway.bpmn20.xml"])]
    public function testUnknownVariableInExpression(): void
    {
        // Instead of 'input' we're starting a process instance with the name
        // 'iinput' (ie. a typo)
        $this->expectException(\Jabe\ProcessEngineException::class);
        $this->runtimeService->startProcessInstanceByKey("inclusiveGwDiverging", ["iinput" => 1]);
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Gateway/InclusiveGatewayTest.testDecideBasedOnBeanProperty.bpmn20.xml"])]
    public function testDecideBasedOnBeanProperty(): void
    {
        $pi = $this->runtimeService->startProcessInstanceByKey("inclusiveDecisionBasedOnBeanProperty", ["order" => new InclusiveGatewayTestOrder(150)]);
        $tasks = $this->taskService->createTaskQuery()->processInstanceId($pi->getId())->list();
        $this->assertEquals(2, count($tasks));
        $expectedNames = [];
        $expectedNames[self::BEAN_TASK2_NAME] = self::BEAN_TASK2_NAME;
        $expectedNames[self::BEAN_TASK3_NAME] = self::BEAN_TASK3_NAME;
        foreach ($tasks as $task) {
            unset($expectedNames[$task->getName()]);
        }
        $this->assertEquals(0, count($expectedNames));
    }

    /*public function testDecideBasedOnListOrArrayOfBeans(): void
    {
        $orders = [];
        $orders[] = new InclusiveGatewayTestOrder(50);
        $orders[] = new InclusiveGatewayTestOrder(300);
        $orders[] = new InclusiveGatewayTestOrder(175);

        $pi = $this->runtimeService->startProcessInstanceByKey("inclusiveDecisionBasedOnListOrArrayOfBeans", ["orders" => $orders]);
    }*/

    #[Deployment(resources: [ "tests/Resources/Bpmn/Gateway/InclusiveGatewayTest.testDefaultSequenceFlow.bpmn20.xml"])]
    public function testDefaultSequenceFlow(): void
    {
        // Input == 1 -> default is not selected, other 2 tasks are selected
        $pi = $this->runtimeService->startProcessInstanceByKey("inclusiveGwDefaultSequenceFlow", ["input" => 1]);
        $tasks = $this->taskService->createTaskQuery()->processInstanceId($pi->getId())->list();
        $this->assertEquals(2, count($tasks));
        $expectedNames = [];
        $expectedNames["Input is one"] = "Input is one";
        $expectedNames["Input is three or one"] = "Input is three or one";
        foreach ($tasks as $t) {
            unset($expectedNames[$t->getName()]);
        }
        $this->assertEquals(0, count($expectedNames));
        $this->runtimeService->deleteProcessInstance($pi->getId(), null);

        // Input == 3 -> default is not selected, "one or three" is selected
        $pi = $this->runtimeService->startProcessInstanceByKey("inclusiveGwDefaultSequenceFlow", ["input" => 3]);
        $task = $this->taskService->createTaskQuery()->processInstanceId($pi->getId())->singleResult();
        $this->assertEquals("Input is three or one", $task->getName());

        // Default input
        $pi = $this->runtimeService->startProcessInstanceByKey("inclusiveGwDefaultSequenceFlow", ["input" => 5]);
        $task = $this->taskService->createTaskQuery()->processInstanceId($pi->getId())->singleResult();
        $this->assertEquals("Default input", $task->getName());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Gateway/InclusiveGatewayTest.testDefaultSequenceFlow.bpmn20.xml"])]
    public function testDefaultSequenceFlowExecutionIsActive(): void
    {
        // given a triggered inclusive gateway default flow
        $pi = $this->runtimeService->startProcessInstanceByKey("inclusiveGwDefaultSequenceFlow", ["input" => 5]);

        // then the process instance execution is not deactivated
        $execution = $this->runtimeService->createExecutionQuery()->processInstanceId($pi->getId())->singleResult();
        $this->assertEquals("theTask2", $execution->getActivityId());
        $this->assertTrue($execution->isActive());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Gateway/InclusiveGatewayTest.testSplitMergeSplit.bpmn20.xml"])]
    public function testSplitMergeSplit(): void
    {
        // given a process instance with two concurrent tasks
        $processInstance =
            $this->runtimeService->startProcessInstanceByKey("inclusiveGwSplitAndMerge", ["input" => 1]);

        $tasks = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->list();
        $this->assertEquals(2, count($tasks));

        // when the executions are joined at an inclusive gateway and the gateway itself has an outgoing default flow
        $this->taskService->complete($tasks[0]->getId());
        $this->taskService->complete($tasks[1]->getId());

        // then the task after the inclusive gateway is reached by the process instance execution (i.e. concurrent root)
        $task = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->assertNotNull($task);

        $this->assertEquals($processInstance->getId(), $task->getExecutionId());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Gateway/InclusiveGatewayTest.testNoIdOnSequenceFlow.bpmn20.xml"])]
    public function testNoIdOnSequenceFlow(): void
    {
        $pi = $this->runtimeService->startProcessInstanceByKey("inclusiveNoIdOnSequenceFlow", ["input" => 3]);
        $task = $this->taskService->createTaskQuery()->processInstanceId($pi->getId())->singleResult();
        $this->assertEquals("Input is more than one", $task->getName());
        // Both should be enabled on 1
        $pi = $this->runtimeService->startProcessInstanceByKey("inclusiveNoIdOnSequenceFlow", ["input" => 1]);
        $tasks = $this->taskService->createTaskQuery()->processInstanceId($pi->getId())->list();
        $this->assertEquals(2, count($tasks));
        $expectedNames = [];
        $expectedNames["Input is one"] = "Input is one";
        $expectedNames["Input is more than one"] = "Input is more than one";
        foreach ($tasks as $t) {
            unset($expectedNames[$t->getName()]);
        }
        $this->assertEquals(0, count($expectedNames));
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Gateway/InclusiveGatewayTest.testJoinAfterSubprocesses.bpmn20.xml"])]
    public function testJoinAfterSubprocesses(): void
    {
        // Test case to test act-1204
        $variableMap = [];
        $variableMap["a"] = 1;
        $variableMap["b"] = 1;
        $processInstance = $this->runtimeService->startProcessInstanceByKey("InclusiveGateway", $variableMap);
        $this->assertNotNull($processInstance->getId());

        $tasks = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->list();
        $this->assertEquals(2, $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->count());

        $this->taskService->complete($tasks[0]->getId());
        $this->assertEquals(1, $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->count());
        $this->taskService->complete($tasks[1]->getId());
        $task = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->taskAssignee("c")->singleResult();
        $this->assertNotNull($task);
        $this->taskService->complete($task->getId());

        $processInstance = $this->runtimeService->createProcessInstanceQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->assertNull($processInstance);

        $variableMap = [];
        $variableMap["a"] = 1;
        $variableMap["b"] = 2;
        $processInstance = $this->runtimeService->startProcessInstanceByKey("InclusiveGateway", $variableMap);
        $this->assertNotNull($processInstance->getId());

        $tasks = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->list();
        $this->assertEquals(1, $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->count());

        $task = $tasks[0];
        $this->assertEquals("a", $task->getAssignee());
        $this->taskService->complete($task->getId());

        $task = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->taskAssignee("c")->singleResult();
        $this->assertNotNull($task);
        $this->taskService->complete($task->getId());

        $processInstance = $this->runtimeService->createProcessInstanceQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->assertNull($processInstance);

        $variableMap = [];
        $variableMap["a"] = 2;
        $variableMap["b"] = 2;
        $processInstance = $this->runtimeService->startProcessInstanceByKey("InclusiveGateway", $variableMap);
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Gateway/InclusiveGatewayTest.testJoinAfterCall.bpmn20.xml", "tests/Resources/Bpmn/Gateway/InclusiveGatewayTest.testJoinAfterCallSubProcess.bpmn20.xml"])]
    public function testJoinAfterCall(): void
    {
        // Test case to test act-1026
        $processInstance = $this->runtimeService->startProcessInstanceByKey("InclusiveGatewayAfterCall");
        $this->assertNotNull($processInstance->getId());

        // now complete task A and check number of remaining tasks.
        // inclusive gateway should wait for the "Task B" and "Task C"
        $taskA = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->taskName("Task A")->singleResult();
        $this->assertNotNull($taskA);
        $this->taskService->complete($taskA->getId());

        // now complete task B and check number of remaining tasks
        // inclusive gateway should wait for "Task C"
        $taskB = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->taskName("Task B")->singleResult();
        $this->assertNotNull($taskB);
        $this->taskService->complete($taskB->getId());

        // now complete task C. Gateway activates and "Task C" remains
        $taskC = $this->taskService->createTaskQuery()->taskName("Task C 1")->singleResult();
        $this->assertNotNull($taskC);
        $this->taskService->complete($taskC->getId());

        // check that remaining task is in fact task D
        $taskD = $this->taskService->createTaskQuery()->taskName("Task D 1")->singleResult();
        $this->assertNotNull($taskD);
        $this->assertEquals("Task D 1", $taskD->getName());
        $this->taskService->complete($taskD->getId());

        $processInstance = $this->runtimeService->createProcessInstanceQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->assertNull($processInstance);
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Gateway/InclusiveGatewayTest.testJoinAfterSequentialMultiInstanceSubProcess.bpmn20.xml"])]
    public function testJoinAfterSequentialMultiInstanceSubProcess(): void
    {
        // given
        $pi = $this->runtimeService->startProcessInstanceByKey("process");

        $query = $this->taskService->createTaskQuery()->processInstanceId($pi->getId());

        // when
        $task = $query
            ->taskDefinitionKey("task")
            ->singleResult();
        $this->taskService->complete($task->getId());

        // then
        $this->assertNull($query->taskDefinitionKey("taskAfterJoin")->singleResult());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Gateway/InclusiveGatewayTest.testJoinAfterParallelMultiInstanceSubProcess.bpmn20.xml"])]
    public function testJoinAfterParallelMultiInstanceSubProcess(): void
    {
        // given
        $pi = $this->runtimeService->startProcessInstanceByKey("process");

        $query = $this->taskService->createTaskQuery()->processInstanceId($pi->getId());

        // when
        $task = $query
            ->taskDefinitionKey("task")
            ->singleResult();
        $this->taskService->complete($task->getId());

        // then
        $this->assertNull($query->taskDefinitionKey("taskAfterJoin")->singleResult());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Gateway/InclusiveGatewayTest.testJoinAfterNestedScopes.bpmn20.xml"])]
    public function testJoinAfterNestedScopes(): void
    {
        // given
        $pi = $this->runtimeService->startProcessInstanceByKey("process");

        $query = $this->taskService->createTaskQuery()->processInstanceId($pi->getId());

        // when
        $task = $query
            ->taskDefinitionKey("task")
            ->singleResult();
        $this->taskService->complete($task->getId());

        // then
        $this->assertNull($query->taskDefinitionKey("taskAfterJoin")->singleResult());
    }

    public function testTriggerGatewayWithEnoughArrivedTokens(): void
    {
        $this->testRule->deploy(Bpmn::createExecutableProcess("process")
           ->startEvent()
           ->userTask("beforeTask")
           ->inclusiveGateway("gw")
           ->userTask("afterTask")
           ->endEvent()
           ->done());

        // given
        $processInstance = $this->runtimeService->createProcessInstanceByKey("process")
           ->startBeforeActivity("beforeTask")
           ->startBeforeActivity("beforeTask")
           ->execute();

        $task = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->list()[0];

        // when
        $this->taskService->complete($task->getId());

        // then
        $activityInstance = $this->runtimeService->getActivityInstance($processInstance->getId());

        $this->assertTrue(strpos(strval($activityInstance), '├── afterTask=>afterTask') !== false);
        $this->assertTrue(strpos(strval($activityInstance), '└── beforeTask=>beforeTask') !== false);
    }

    public function testRemoveConcurrentExecutionLocalVariablesOnJoin(): void
    {
        $this->testRule->deploy(Bpmn::createExecutableProcess("process")
           ->startEvent()
           ->inclusiveGateway("fork")
           ->userTask("task1")
           ->inclusiveGateway("join")
           ->userTask("afterTask")
           ->endEvent()
           ->moveToNode("fork")
           ->userTask("task2")
           ->connectTo("join")
           ->done());

        // given
        $pi = $this->runtimeService->startProcessInstanceByKey("process");

        $tasks = $this->taskService->createTaskQuery()->processInstanceId($pi->getId())->list();
        foreach ($tasks as $task) {
            $this->runtimeService->setVariableLocal($task->getExecutionId(), "var", "value");
        }

        // when
        $this->taskService->complete($tasks[0]->getId());
        $this->taskService->complete($tasks[1]->getId());

        // then
        $this->assertEquals(0, $this->runtimeService->createVariableInstanceQuery()->processInstanceIdIn([$pi->getId()])->count());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Gateway/InclusiveGatewayTest.testJoinAfterEventBasedGateway.bpmn20.xml"])]
    public function testJoinAfterEventBasedGateway(): void
    {
        // given
        $id = $this->processEngineConfiguration->getIdGenerator()->getNextId();
        $pi = $this->runtimeService->startProcessInstanceByKey("process", "businessKey-" . $id);
        $taskQuery = $this->taskService->createTaskQuery()->processInstanceId($pi->getId());

        $task = $taskQuery->singleResult();
        $this->taskService->complete($task->getId());

        // assume
        $this->assertNull($taskQuery->singleResult());

        // when
        $this->runtimeService->correlateMessage("foo", "businessKey-" . $id);

        // then
        $task = $taskQuery->singleResult();
        $this->assertNotNull($task);
        $this->assertEquals("taskAfterJoin", $task->getTaskDefinitionKey());

        $this->taskService->complete($task->getId());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Gateway/InclusiveGatewayTest.testJoinAfterEventBasedGatewayInSubProcess.bpmn20.xml"])]
    public function testJoinAfterEventBasedGatewayInSubProcess(): void
    {
        // given
        $id = $this->processEngineConfiguration->getIdGenerator()->getNextId();
        $pi = $this->runtimeService->startProcessInstanceByKey("process", "businessKey-" . $id);
        $taskQuery = $this->taskService->createTaskQuery()->processInstanceId($pi->getId());

        $task = $taskQuery->singleResult();
        $this->taskService->complete($task->getId());

        // assume
        $this->assertNull($taskQuery->singleResult());

        // when
        $this->runtimeService->correlateMessage("foo", "businessKey-" . $id);

        // then
        $task = $taskQuery->singleResult();
        $this->assertNotNull($task);
        $this->assertEquals("taskAfterJoin", $task->getTaskDefinitionKey());

        $this->taskService->complete($task->getId());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Gateway/InclusiveGatewayTest.testJoinAfterEventBasedGatewayContainedInSubProcess.bpmn20.xml"])]
    public function testJoinAfterEventBasedGatewayContainedInSubProcess(): void
    {
        // given
        $id = $this->processEngineConfiguration->getIdGenerator()->getNextId();
        $pi = $this->runtimeService->startProcessInstanceByKey("process", "businessKey-" . $id);
        $taskQuery = $this->taskService->createTaskQuery()->processInstanceId($pi->getId());

        $task = $taskQuery->singleResult();
        $this->taskService->complete($task->getId());

        // assume
        $this->assertNull($taskQuery->singleResult());

        // when
        $this->runtimeService->correlateMessage("foo", "businessKey-" . $id);

        // then
        $task = $taskQuery->singleResult();
        $this->assertNotNull($task);
        $this->assertEquals("taskAfterJoin", $task->getTaskDefinitionKey());

        $this->taskService->complete($task->getId());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Gateway/InclusiveGatewayTest.AsyncConcurrentExecutions.ParallelGateway.bpmn"])]
    public function testShouldCompleteWithConcurrentExecutionParallelGateway(): void
    {
        // given
        $pi = $this->runtimeService->startProcessInstanceByKey("process");

        // when
        $this->testRule->executeAvailableJobs($pi->getId(), 1);

        // then
        $this->assertEquals(0, $this->managementService->createJobQuery()->processInstanceId($pi->getId())->count());
        $this->assertEquals("COMPLETED", $this->historyService->createHistoricProcessInstanceQuery()->processInstanceId($pi->getId())->singleResult()->getState());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Gateway/InclusiveGatewayTest.AsyncConcurrentExecutions.ParallelInclusiveGateway.bpmn"])]
    public function testShouldCompleteWithConcurrentExecutionInclusiveGateway(): void
    {
        // given
        $pi = $this->runtimeService->startProcessInstanceByKey("process");

        // when
        $this->testRule->executeAvailableJobs($pi->getId(), 1);

        // then
        $this->assertEquals(0, $this->managementService->createJobQuery()->processInstanceId($pi->getId())->count());
        $this->assertEquals("COMPLETED", $this->historyService->createHistoricProcessInstanceQuery()->processInstanceId($pi->getId())->singleResult()->getState());
    }
}
