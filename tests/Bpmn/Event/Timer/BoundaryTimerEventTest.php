<?php

namespace Tests\Bpmn\Event\Timer;

use Jabe\ProcessEngineConfiguration;
use Jabe\Impl\Bpmn\Parser\BpmnParse;
use Jabe\Impl\Cfg\ProcessEngineConfigurationImpl;
use Jabe\Impl\Cmd\DeleteJobsCmd;
use Jabe\Impl\Interceptor\{
    CommandContext,
    CommandInterface,
    CommandInvocationContext
};
use Jabe\Impl\Util\{
    ClockUtil,
    IoUtil
};
use Jabe\Test\Deployment;
use Jabe\Variable\Variables;
use Tests\Util\PluggableProcessEngineTest;

class BoundaryTimerEventTest extends PluggableProcessEngineTest
{
    private const NAMESPACE = "xmlns='http://www.omg.org/spec/BPMN/20100524/MODEL'";
    private const TARGET_NAMESPACE = "targetNamespace='" . BpmnParse::BPMN_EXTENSIONS_NS_PREFIX . "'";

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        ClockUtil::resetClock(...$this->processEngineConfiguration->getJobExecutorState());
        $deployments = $this->repositoryService->createDeploymentQuery()->list();
        foreach ($deployments as $deployment) {
            $this->repositoryService->deleteDeployment($deployment->getId(), true);
        }
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Timer/BoundaryTimerEventTest.testMultipleTimersOnUserTask.bpmn20.xml"])]
    public function testMultipleTimersOnUserTask(): void
    {
        // Set the clock fixed
        $startTime = new \DateTime('now');

        // After process start, there should be 3 timers created
        $pi = $this->runtimeService->startProcessInstanceByKey("multipleTimersOnUserTask");
        $jobQuery = $this->managementService->createJobQuery()->processInstanceId($pi->getId());
        $jobs = $jobQuery->list();
        $this->assertEquals(3, count($jobs));

        // After setting the clock to time '1 hour and 5 seconds', the second timer should fire
        ClockUtil::setCurrentTime((new \DateTime())->setTimestamp($startTime->getTimestamp() + ((60 * 60) + 5)), ...$this->processEngineConfiguration->getJobExecutorState());
        sleep(50);
        $this->assertEquals(0, $jobQuery->count());

        // which means that the third task is reached
        $task = $this->taskService->createTaskQuery()->processInstanceId($pi->getId())->singleResult();
        $this->assertEquals("Third Task", $task->getName());

        $this->runtimeService->deleteProcessInstance($pi->getId());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Timer/BoundaryTimerEventTest.testTimerOnNestingOfSubprocesses.bpmn20.xml"])]
    public function testTimerOnNestingOfSubprocesses(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("timerOnNestedSubprocesses");
        $tasks = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->orderByTaskName()->asc()->list();
        $this->assertEquals(2, count($tasks));
        $this->assertEquals("Inner subprocess task 1", $tasks[0]->getName());
        $this->assertEquals("Inner subprocess task 2", $tasks[1]->getName());

        $timer = $this->managementService->createJobQuery()->processInstanceId($processInstance->getId())->timers()->singleResult();
        $this->managementService->executeJob($timer->getId());

        $task = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->singleResult();
        $this->assertEquals("task outside subprocess", $task->getName());

        $this->runtimeService->deleteProcessInstance($processInstance->getId());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Timer/BoundaryTimerEventTest.testExpressionOnTimer1.bpmn20.xml"])]
    public function testExpressionOnTimer(): void
    {
        // Set the clock fixed
        $startTime = new \DateTime('now');

        $variables = ["duration" => "PT1H"];

        // After process start, there should be a timer created
        $pi = $this->runtimeService->startProcessInstanceByKey("testExpressionOnTimer1", $variables);

        $jobQuery = $this->managementService->createJobQuery()->processInstanceId($pi->getId());
        $jobs = $jobQuery->list();
        $this->assertEquals(1, count($jobs));

        // After setting the clock to time '1 hour and 5 seconds', the second timer should fire
        ClockUtil::setCurrentTime((new \DateTime())->setTimestamp($startTime->getTimestamp() + ((60 * 60) + 5)), ...$this->processEngineConfiguration->getJobExecutorState());

        sleep(50);

        $this->assertEquals(0, $jobQuery->count());

        // which means the process has ended
        $this->testRule->assertProcessEnded($pi->getId());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Timer/BoundaryTimerEventTest.testRecalculate2.bpmn20.xml"])]
    public function testRecalculateUnchangedExpressionOnTimerCurrentDateBased(): void
    {
        // Set the clock fixed
        $startTime = new \DateTime('now');

        $variables = ["duedate" => "PT1H"];

        // After process start, there should be a timer created
        $pi = $this->runtimeService->startProcessInstanceByKey("testExpressionOnTimer2", $variables);

        $jobQuery = $this->managementService->createJobQuery()->processInstanceId($pi->getId());
        $jobs = $jobQuery->list();
        $this->assertEquals(1, count($jobs));
        $job = $jobs[0];
        $oldDate = $job->getDuedate();

        // After recalculation of the timer, the job's duedate should be changed
        $currentTime = (new \DateTime())->setTimestamp($startTime->getTimestamp() + 300);
        ClockUtil::setCurrentTime($currentTime, ...$this->processEngineConfiguration->getJobExecutorState());
        $this->managementService->recalculateJobDuedate($job->getId(), false);
        $jobUpdated = $jobQuery->singleResult();
        $this->assertEquals($job->getId(), $jobUpdated->getId());
        $this->assertNotEquals($oldDate, $jobUpdated->getDuedate());
        $this->assertTrue($oldDate < $jobUpdated->getDuedate());

        $expectedDate = (new \DateTime())->setTimestamp(strtotime("+1 hours", strtotime($currentTime->format('Y-m-d H:i:s'))));
        $this->assertEquals($expectedDate, new \DateTime($jobUpdated->getDuedate()));

        // After setting the clock to time '1 hour and 6 min', the second timer should fire
        ClockUtil::setCurrentTime((new \DateTime())->setTimestamp(strtotime("+1 hours +6 minutes", strtotime($currentTime->format('Y-m-d H:i:s')))), ...$this->processEngineConfiguration->getJobExecutorState());
        sleep(50);
        $this->assertEquals(0, $jobQuery->count());

        // which means the process has ended
        $this->testRule->assertProcessEnded($pi->getId());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Timer/BoundaryTimerEventTest.testRecalculate3.bpmn20.xml"])]
    public function testRecalculateUnchangedExpressionOnTimerCreationDateBased(): void
    {
        // Set the clock fixed
        $startTime = new \DateTime('now');

        $variables = ["duedate" => "PT1H"];

        // After process start, there should be a timer created
        $pi = $this->runtimeService->startProcessInstanceByKey("testExpressionOnTimer3", $variables);

        $jobQuery = $this->managementService->createJobQuery()->processInstanceId($pi->getId());
        $jobs = $jobQuery->list();
        $this->assertEquals(1, count($jobs));
        $job = $jobs[0];

        // After recalculation of the timer, the job's duedate should be based on the creation date
        $currentTime = (new \DateTime())->setTimestamp($startTime->getTimestamp() + 5);
        ClockUtil::setCurrentTime($currentTime, ...$this->processEngineConfiguration->getJobExecutorState());
        $this->managementService->recalculateJobDuedate($job->getId(), true);
        $jobUpdated = $jobQuery->singleResult();
        $this->assertEquals($job->getId(), $jobUpdated->getId());
        $expectedDate = (new \DateTime())->setTimestamp(strtotime("+1 hours", strtotime($jobUpdated->getCreateTime())));
        $this->assertEquals($expectedDate, new \DateTime($jobUpdated->getDuedate()));

        // After setting the clock to time '1 hour and 15 seconds', the second timer should fire
        ClockUtil::setCurrentTime((new \DateTime())->setTimestamp($startTime->getTimestamp() + 3615), ...$this->processEngineConfiguration->getJobExecutorState());

        sleep(50);
        $this->assertEquals(0, $jobQuery->count());

        // which means the process has ended
        $this->testRule->assertProcessEnded($pi->getId());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Timer/BoundaryTimerEventTest.testRecalculate4.bpmn20.xml"])]
    public function testRecalculateChangedExpressionOnTimerCurrentDateBased(): void
    {
        // Set the clock fixed
        $startTime = new \DateTime('now');
        $variables = ["duedate" => "PT1H"];

        // After process start, there should be a timer created
        $pi = $this->runtimeService->startProcessInstanceByKey("testExpressionOnTimer4", $variables);

        $jobQuery = $this->managementService->createJobQuery()->processInstanceId($pi->getId());
        $jobs = $jobQuery->list();
        $this->assertEquals(1, count($jobs));
        $job = $jobs[0];
        $oldDate = $job->getDuedate();
        ClockUtil::offset(2000);

        // After recalculation of the timer, the job's duedate should be changed
        $this->managementService->recalculateJobDuedate($job->getId(), false);
        $jobUpdated = $jobQuery->singleResult();
        $this->assertEquals($job->getId(), $jobUpdated->getId());
        $this->assertTrue($oldDate < $jobUpdated->getDuedate());

        // After setting the clock to time '16 minutes', the timer should fire
        ClockUtil::setCurrentTime((new \DateTime())->setTimestamp($startTime->getTimestamp() + 7200), ...$this->processEngineConfiguration->getJobExecutorState());
        sleep(50);
        $this->assertEquals(0, $jobQuery->count());

        // which means the process has ended
        $this->testRule->assertProcessEnded($pi->getId());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Timer/BoundaryTimerEventTest.testRecalculate5.bpmn20.xml"])]
    public function testRecalculateChangedExpressionOnTimerCreationDateBased(): void
    {
        // Set the clock fixed
        $startTime = new \DateTime('now');
        $variables = ["duedate" => "PT1H"];

        // After process start, there should be a timer created
        $pi = $this->runtimeService->startProcessInstanceByKey("testExpressionOnTimer5", $variables);

        $jobQuery = $this->managementService->createJobQuery()->processInstanceId($pi->getId());
        $jobs = $jobQuery->list();
        $this->assertEquals(1, count($jobs));
        $job = $jobs[0];
        $oldDate = $job->getDuedate();

        // After recalculation of the timer, the job's duedate should be the same
        $this->runtimeService->setVariable($pi->getId(), "duedate", "PT15M");
        $this->managementService->recalculateJobDuedate($job->getId(), true);
        $jobUpdated = $jobQuery->singleResult();
        $this->assertEquals($job->getId(), $jobUpdated->getId());
        $this->assertEquals((new \DateTime())->setTimestamp((new \DateTime($jobUpdated->getCreateTime()))->getTimestamp() + 900), new \DateTime($jobUpdated->getDuedate()));

        // After setting the clock to time '16 minutes', the timer should fire
        ClockUtil::setCurrentTime((new \DateTime())->setTimestamp($startTime->getTimestamp() + 960), ...$this->processEngineConfiguration->getJobExecutorState());
        sleep(50);
        $this->assertEquals(0, $jobQuery->count());

        // which means the process has ended
        $this->testRule->assertProcessEnded($pi->getId());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Timer/BoundaryTimerEventTest.testTimerInSingleTransactionProcess.bpmn20.xml"])]
    public function testTimerInSingleTransactionProcess(): void
    {
        // make sure that if a PI completes in single transaction, JobEntities associated with the execution are deleted.
        // broken before 5.10, see ACT-1133
        $this->runtimeService->startProcessInstanceByKey("timerOnSubprocesses");
        $jobs = $this->managementService->createJobQuery()->processDefinitionKey("timerOnSubprocesses")->list();
        $this->assertEquals(0, count($jobs));
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Timer/BoundaryTimerEventTest.testRepeatingTimerWithCancelActivity.bpmn20.xml"])]
    public function testRepeatingTimerWithCancelActivity(): void
    {
        $pi = $this->runtimeService->startProcessInstanceByKey("repeatingTimerAndCallActivity");
        $this->assertEquals(1, $this->managementService->createJobQuery()->processInstanceId($pi->getId())->count());
        $this->assertEquals(1, $this->taskService->createTaskQuery()->processInstanceId($pi->getId())->count());

        // Firing job should cancel the user task, destroy the scope,
        // re-enter the task and recreate the task. A new timer should also be created.
        // This didn't happen before 5.11 (new jobs kept being created). See ACT-1427
        $job = $this->managementService->createJobQuery()->processInstanceId($pi->getId())->singleResult();
        $this->managementService->executeJob($job->getId());
        $this->assertEquals(1, $this->managementService->createJobQuery()->processInstanceId($pi->getId())->count());
        $this->assertEquals(1, $this->taskService->createTaskQuery()->processInstanceId($pi->getId())->count());

        $this->runtimeService->deleteProcessInstance($pi->getId());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Timer/BoundaryTimerEventTest.testMultipleOutgoingSequenceFlows.bpmn20.xml"])]
    public function testMultipleOutgoingSequenceFlows(): void
    {
        $pi = $this->runtimeService->startProcessInstanceByKey("interruptingTimer");

        $job = $this->managementService->createJobQuery()->processInstanceId($pi->getId())->singleResult();
        $this->assertNotNull($job);

        $this->managementService->executeJob($job->getId());

        $taskQuery = $this->taskService->createTaskQuery()->processInstanceId($pi->getId());
        $this->assertEquals(2, $taskQuery->count());

        $tasks = $taskQuery->list();

        foreach ($tasks as $task) {
            $this->taskService->complete($task->getId());
        }

        $this->testRule->assertProcessEnded($pi->getId());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Timer/BoundaryTimerEventTest.testMultipleOutgoingSequenceFlowsOnSubprocess.bpmn20.xml"])]
    public function testMultipleOutgoingSequenceFlowsOnSubprocess(): void
    {
        $pi = $this->runtimeService->startProcessInstanceByKey("interruptingTimer");

        $job = $this->managementService->createJobQuery()->processInstanceId($pi->getId())->singleResult();
        $this->assertNotNull($job);

        $this->managementService->executeJob($job->getId());

        $taskQuery = $this->taskService->createTaskQuery()->processInstanceId($pi->getId());
        $this->assertEquals(2, $taskQuery->count());

        $tasks = $taskQuery->list();

        foreach ($tasks as $task) {
            $this->taskService->complete($task->getId());
        }

        $this->testRule->assertProcessEnded($pi->getId());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Timer/BoundaryTimerEventTest.testMultipleOutgoingSequenceFlowsOnSubprocess.bpmn20.xml"])]
    public function testMultipleOutgoingSequenceFlowsOnSubprocessMi(): void
    {
        $pi = $this->runtimeService->startProcessInstanceByKey("interruptingTimer");

        $job = $this->managementService->createJobQuery()->processInstanceId($pi->getId())->singleResult();
        $this->assertNotNull($job);

        $this->managementService->executeJob($job->getId());

        $taskQuery = $this->taskService->createTaskQuery()->processInstanceId($pi->getId());
        $this->assertEquals(2, $taskQuery->count());

        $tasks = $taskQuery->list();

        foreach ($tasks as $task) {
            $this->taskService->complete($task->getId());
        }

        $this->testRule->assertProcessEnded($pi->getId());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Timer/BoundaryTimerEventTest.testInterruptingTimerDuration.bpmn20.xml"])]
    public function testInterruptingTimerDuration(): void
    {
        // Start process instance
        $pi = $this->runtimeService->startProcessInstanceByKey("escalationExample");

        // There should be one task, with a timer : first line support
        $task = $this->taskService->createTaskQuery()->processInstanceId($pi->getId())->singleResult();
        $this->assertEquals("First line support", $task->getName());

        // Manually execute the job
        $timer = $this->managementService->createJobQuery()->processInstanceId($pi->getId())->singleResult();
        $this->managementService->executeJob($timer->getId());

        // The timer has fired, and the second task (secondlinesupport) now exists
        $task = $this->taskService->createTaskQuery()->processInstanceId($pi->getId())->singleResult();
        $this->assertEquals("Handle escalated issue", $task->getName());

        $this->runtimeService->deleteProcessInstance($pi->getId());
    }
}
