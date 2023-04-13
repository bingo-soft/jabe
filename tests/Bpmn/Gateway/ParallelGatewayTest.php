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

class ParallelGatewayTest extends PluggableProcessEngineTest
{
    private const NAMESPACE = "xmlns='http://www.omg.org/spec/BPMN/20100524/MODEL'";
    private const TARGET_NAMESPACE = "targetNamespace='" . BpmnParse::BPMN_EXTENSIONS_NS_PREFIX . "'";

    protected function setUp(): void
    {
        //initialize all services
        parent::setUp();
    }

    protected function tearDown(): void
    {
        $deployments = $this->repositoryService->createDeploymentQuery()->list();
        foreach ($deployments as $deployment) {
            $this->repositoryService->deleteDeployment($deployment->getId(), true);
        }
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Gateway/ParallelGatewayTest.testSplitMergeNoWaitstates.bpmn20.xml"])]
    public function testSplitMergeNoWaitstates(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("forkJoinNoWaitStates");
        $this->assertTrue($processInstance->isEnded());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Gateway/ParallelGatewayTest.testUnstructuredConcurrencyTwoForks.bpmn20.xml"])]
    public function testUnstructuredConcurrencyTwoForks(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("unstructuredConcurrencyTwoForks");
        $this->assertTrue($processInstance->isEnded());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Gateway/ParallelGatewayTest.testUnstructuredConcurrencyTwoJoins.bpmn20.xml"])]
    public function testUnstructuredConcurrencyTwoJoins(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("unstructuredConcurrencyTwoJoins");
        $this->assertTrue($processInstance->isEnded());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Gateway/ParallelGatewayTest.testForkFollowedByOnlyEndEvents.bpmn20.xml"])]
    public function testForkFollowedByOnlyEndEvents(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("forkFollowedByEndEvents");
        $this->assertTrue($processInstance->isEnded());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Gateway/ParallelGatewayTest.testNestedForksFollowedByEndEvents.bpmn20.xml"])]
    public function testNestedForksFollowedByEndEvents(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("nestedForksFollowedByEndEvents");
        $this->assertTrue($processInstance->isEnded());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Gateway/ParallelGatewayTest.testNestedForkJoin.bpmn20.xml"])]
    public function testNestedForkJoin(): void
    {
        $pid = $this->runtimeService->startProcessInstanceByKey("nestedForkJoin")->getId();

        // After process startm, only task 0 should be active
        $query = $this->taskService->createTaskQuery()->processInstanceId($pid)->orderByTaskName()->asc();
        $tasks = $query->list();
        $this->assertEquals(1, count($tasks));
        $this->assertEquals("Task 0", $tasks[0]->getName());
        $this->assertEquals(1, count($this->runtimeService->getActivityInstance($pid)->getChildActivityInstances()));

        // Completing task 0 will create Task A and B
        $this->taskService->complete($tasks[0]->getId());
        $tasks = $query->list();
        $this->assertEquals(2, count($tasks));
        $this->assertEquals("Task A", $tasks[0]->getName());
        $this->assertEquals("Task B", $tasks[1]->getName());
        $this->assertEquals(2, count($this->runtimeService->getActivityInstance($pid)->getChildActivityInstances()));

        // Completing task A should not trigger any new tasks
        $this->taskService->complete($tasks[0]->getId());
        $tasks = $query->list();
        $this->assertEquals(1, count($tasks));
        $this->assertEquals("Task B", $tasks[0]->getName());
        $this->assertEquals(2, count($this->runtimeService->getActivityInstance($pid)->getChildActivityInstances()));

        // Completing task B creates tasks B1 and B2
        $this->taskService->complete($tasks[0]->getId());
        $tasks = $query->list();
        $this->assertEquals(2, count($tasks));
        $this->assertEquals("Task B1", $tasks[0]->getName());
        $this->assertEquals("Task B2", $tasks[1]->getName());
        $this->assertEquals(3, count($this->runtimeService->getActivityInstance($pid)->getChildActivityInstances()));

        // Completing B1 and B2 will activate both joins, and process reaches task C
        $this->taskService->complete($tasks[0]->getId());
        $this->taskService->complete($tasks[1]->getId());
        $tasks = $query->list();
        $this->assertEquals(1, count($tasks));
        $this->assertEquals("Task C", $tasks[0]->getName());
        $this->assertEquals(1, count($this->runtimeService->getActivityInstance($pid)->getChildActivityInstances()));
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Gateway/ParallelGatewayTest.testReceyclingExecutionWithCallActivity.bpmn20.xml"])]
    public function testReceyclingExecutionWithCallActivity(): void
    {
        $pi = $this->runtimeService->startProcessInstanceByKey("parent-process");

        // After process start we have two tasks, one from the parent and one from
        // the sub process
        $subprocesses = $this->runtimeService->createProcessInstanceQuery()->superProcessInstanceId($pi->getId())->list();
        $subprocess = $subprocesses[0];
        $query = $this->taskService->createTaskQuery()->processInstanceId($pi->getId())->orderByTaskName()->asc();
        $tasks = $query->list();
        $this->assertEquals(1, count($tasks));
        $this->assertEquals("Some Task", $tasks[0]->getName());


        $subprocessTasks = $this->taskService->createTaskQuery()->processInstanceId($subprocess->getId())->orderByTaskName()->asc()->list();
        $this->assertEquals(1, count($subprocessTasks));
        $this->assertEquals("Another task", $subprocessTasks[0]->getName());

        // we complete the task from the parent process, the root execution is
        // receycled, the task in the sub process is still there
        $this->taskService->complete($tasks[0]->getId());
        $subprocessTasks = $this->taskService->createTaskQuery()->processInstanceId($subprocess->getId())->orderByTaskName()->asc()->list();
        $this->assertEquals(1, count($tasks));
        $this->assertEquals("Another task", $subprocessTasks[0]->getName());

        // we end the task in the sub process and the sub process instance end is
        // propagated to the parent process
        $this->taskService->complete($subprocessTasks[0]->getId());
        $this->assertEquals(0, $this->taskService->createTaskQuery()->processInstanceId($subprocess->getId())->count());
        $this->assertEquals(0, $this->taskService->createTaskQuery()->processInstanceId($pi->getId())->count());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Gateway/ParallelGatewayTest.testCompletingJoin.bpmn"])]
    public function testCompletingJoin(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("process");
        $this->assertTrue($processInstance->isEnded());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Gateway/ParallelGatewayTest.testAsyncParallelGateway.bpmn"])]
    public function testAsyncParallelGateway(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("process-123");
        $this->assertFalse($processInstance->isEnded());

        // there are two jobs to continue the gateway:
        sleep(5);
        $list = $this->managementService->createJobQuery()->processInstanceId($processInstance->getId())->list();
        //Jobs could be executed by the job executor
        if (!empty($list)) {
            try {
                foreach ($list as $job) {
                    $this->managementService->executeJob($job->getId());
                }
            } catch (\Exception $e) {
                //ignore, because job could be run by the job executor
            }
        }

        $this->assertNull($this->runtimeService->createProcessInstanceQuery()->processInstanceId($processInstance->getId())->singleResult());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Gateway/ParallelGatewayTest.testAsyncParallelGatewayAfterScopeTask.bpmn20.xml"])]
    public function testAsyncParallelGatewayAfterScopeTask(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("process");
        $this->assertFalse($processInstance->isEnded());

        $task = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->taskService->complete($task->getId());

        sleep(5);
        $list = $this->managementService->createJobQuery()->processInstanceId($processInstance->getId())->list();
        if (!empty($list)) {
            try {
                foreach ($list as $job) {
                    $this->managementService->executeJob($job->getId());
                }
            } catch (\Exception $e) {
                //ignore, because job could be run by the job executor
            }
        }

        $this->assertNull($this->runtimeService->createProcessInstanceQuery()->processInstanceId($processInstance->getId())->singleResult());
        $this->assertEquals('COMPLETED', $this->historyService->createHistoricProcessInstanceQuery()->processInstanceId($processInstance->getId())->singleResult()->getState());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Gateway/ParallelGatewayTest.testCompletingJoinInSubProcess.bpmn"])]
    public function testCompletingJoinInSubProcess(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("process");
        $this->assertTrue($processInstance->isEnded());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Gateway/ParallelGatewayTest.testParallelGatewayBeforeAndInSubProcess.bpmn"])]
    public function testParallelGatewayBeforeAndInSubProcess(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("process");
        $tasks1 = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->list();
        $this->assertCount(3, $tasks1);

        $instance = $this->runtimeService->getActivityInstance($processInstance->getId());
        $this->assertEquals("Process1", $instance->getActivityName());
        $childActivityInstances = $instance->getChildActivityInstances();
        foreach ($childActivityInstances as $activityInstance) {
            if ($activityInstance->getActivityId() == "SubProcess_1") {
                $instances = $activityInstance->getChildActivityInstances();
                foreach ($instances as $activityInstance2) {
                    $this->assertTrue(strpos($activityInstance2->getActivityName(), "Inner User Task") !== false);
                }
            } else {
                $this->assertEquals("Outer User Task", $activityInstance->getActivityName());
            }
        }
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Gateway/ParallelGatewayTest.testForkJoin.bpmn20.xml"])]
    public function testForkJoin(): void
    {
        $pi = $this->runtimeService->startProcessInstanceByKey("forkJoin");
        $query = $this->taskService
                       ->createTaskQuery()
                       ->processInstanceId($pi->getId())
                       ->orderByTaskName()
                       ->asc();

        $tasks = $query->list();
        $this->assertCount(2, $tasks);
        // the tasks are ordered by name (see above)
        $task1 = $tasks[0];
        $this->assertEquals("Receive Payment", $task1->getName());
        $task2 = $tasks[1];
        $this->assertEquals("Ship Order", $task2->getName());

        // Completing both tasks will join the concurrent executions
        $this->taskService->complete($tasks[0]->getId());
        $this->taskService->complete($tasks[1]->getId());

        $tasks = $query->list();
        $this->assertCount(1, $tasks);
        $this->assertEquals("Archive Order", $tasks[0]->getName());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Gateway/ParallelGatewayTest.testUnbalancedForkJoin.bpmn20.xml"])]
    public function testUnbalancedForkJoin(): void
    {
        $pi = $this->runtimeService->startProcessInstanceByKey("UnbalancedForkJoin");
        $query = $this->taskService->createTaskQuery()
                                   ->processInstanceId($pi->getId())
                                   ->orderByTaskName()
                                   ->asc();

        $tasks = $query->list();
        $this->assertCount(3, $tasks);
        // the tasks are ordered by name (see above)
        $task1 = $tasks[0];
        $this->assertEquals("Task 1", $task1->getName());
        $task2 = $tasks[1];
        $this->assertEquals("Task 2", $task2->getName());

        // Completing the first task should *not* trigger the join
        $this->taskService->complete($task1->getId());

        // Completing the second task should trigger the first join
        $this->taskService->complete($task2->getId());

        $tasks = $query->list();
        $task3 = $tasks[0];
        $this->assertCount(2, $tasks);
        $this->assertEquals("Task 3", $task3->getName());
        $task4 = $tasks[1];
        $this->assertEquals("Task 4", $task4->getName());

        // Completing the remaing tasks should trigger the second join and end the process
        $this->taskService->complete($task3->getId());
        $this->taskService->complete($task4->getId());

        $this->testRule->assertProcessEnded($pi->getId());
    }

    public function testRemoveConcurrentExecutionLocalVariablesOnJoin(): void
    {
        $this->testRule->deploy(Bpmn::createExecutableProcess("process")
           ->startEvent()
           ->parallelGateway("fork")
           ->userTask("task1")
           ->parallelGateway("join")
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
        $this->assertEquals(0, $this->runtimeService->createVariableInstanceQuery()->processInstanceIdIn([ $pi->getId() ])->count());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Gateway/ParallelGatewayTest.testImplicitParallelGatewayAfterSignalBehavior.bpmn20.xml"])]
    public function testImplicitParallelGatewayAfterSignalBehavior(): void
    {
        // given
        $exceptionOccurred = null;
        $pi = $this->runtimeService->startProcessInstanceByKey("process");
        $execution = $this->runtimeService->createExecutionQuery()
          ->processInstanceId($pi->getId())
          ->activityId("service")
          ->singleResult();

        // when
        try {
            $this->runtimeService->signal($execution->getId());
        } catch (\Exception $e) {
            $exceptionOccurred = $e;
        }

        // then
        $this->assertNull($exceptionOccurred);
        $this->assertEquals(3, $this->taskService->createTaskQuery()->processInstanceId($pi->getId())->count());
    }
}
