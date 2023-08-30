<?php

namespace Tests\Bpmn\Event\Timer;

use Bpmn\Bpmn;
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

class StartTimerEventTest extends PluggableProcessEngineTest
{
    private const NAMESPACE = "xmlns='http://www.omg.org/spec/BPMN/20100524/MODEL'";
    private const TARGET_NAMESPACE = "targetNamespace='" . BpmnParse::BPMN_EXTENSIONS_NS_PREFIX . "'";

    protected function setUp(): void
    {
        //initialize all services
        $startTimes = [
            'testFixedDateStartTimerEvent' => new \DateTime('2036-11-15 11:12:30'),
            'testExpressionStartTimerEvent' => new \DateTime('2036-11-15 11:12:30')
        ];
        if (array_key_exists($this->getName(), $startTimes)) {
            $this->currentTime = $startTimes[$this->getName()];
        } else {
            $this->currentTime = null;
        }
        parent::setUp();
    }

    protected function tearDown(): void
    {
        ClockUtil::resetClock(...$this->processEngineConfiguration->getJobExecutorState());
        /*$deployments = $this->repositoryService->createDeploymentQuery()->list();
        foreach ($deployments as $deployment) {
            $this->repositoryService->deleteDeployment($deployment->getId(), true);
        }*/
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testDurationStartTimerEvent.bpmn20.xml"])]
    public function testDurationStartTimerEvent(): void
    {
        // Set the clock fixed
        // After process start, there should be timer created
        $jobQuery = $this->managementService->createJobQuery()->processDefinitionKey("startTimerEventExample1");
        $this->assertEquals(1, $jobQuery->count());

        // After setting the clock to time '50minutes and 5 seconds', the second
        // timer should fire

        $this->moveBySeconds(120);
        sleep(35);

        $pi = $this->runtimeService->createProcessInstanceQuery()->processDefinitionKey("startTimerEventExample1")->list();
        $this->assertEquals(1, count($pi));

        $jobQuery = $this->managementService->createJobQuery()->processDefinitionKey("startTimerEventExample1");
        $this->assertEquals(0, $jobQuery->count());

        $processInstanceId = $pi[0]->getProcessInstanceId();
        $this->runtimeService->signal($processInstanceId);
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testFixedDateStartTimerEvent.bpmn20.xml"])]
    public function testFixedDateStartTimerEvent(): void
    {
        // After process start, there should be timer created
        $jobQuery = $this->managementService->createJobQuery()->processDefinitionKey("startTimerEventExample2");
        $this->assertEquals(1, $jobQuery->count());

        $this->moveBySeconds(90);
        sleep(35);

        $pi = $this->runtimeService->createProcessInstanceQuery()->processDefinitionKey("startTimerEventExample2")->list();
        $this->assertEquals(1, count($pi));

        $jobQuery = $this->managementService->createJobQuery()->processDefinitionKey("startTimerEventExample2");
        $this->assertEquals(0, $jobQuery->count());

        $processInstanceId = $pi[0]->getProcessInstanceId();
        $this->runtimeService->signal($processInstanceId);

        //ClockUtil::setCurrentTime(new \DateTime('now'));
    }

    // FIXME: This test likes to run in an endless loop when invoking the
    // waitForJobExecutorOnCondition method
    /*@Deployment
    @Ignore
    @Test
    public function testCycleDateStartTimerEvent(): void
    {
        ClockUtil::setCurrentTime(new \DateTime());

        // After process start, there should be timer created
        $jobQuery = $this->managementService->createJobQuery();
        $this->assertEquals(1, $jobQuery->count());

        final ProcessInstanceQuery piq = $this->runtimeService->createProcessInstanceQuery().processDefinitionKey("startTimerEventExample");

        $this->assertEquals(0, piq.count());

        moveByMinutes(5);
        executeAllJobs();
        $this->assertEquals(1, piq.count());
        $this->assertEquals(1, $jobQuery->count());

        moveByMinutes(5);
        executeAllJobs();
        $this->assertEquals(1, piq.count());

        $this->assertEquals(1, $jobQuery->count());
        // have to manually delete pending timer
        //    cleanDB();
    }*/

    private function moveBySeconds(int $seconds): void
    {
        ClockUtil::setCurrentTime((new \DateTime())->setTimestamp(ClockUtil::getCurrentTime()->getTimestamp() + ($seconds + 5)), ...$this->processEngineConfiguration->getJobExecutorState());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testCycleWithLimitStartTimerEvent.bpmn20.xml", "tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testCycleWithLimitStartTimerEvent2.bpmn20.xml", "tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testCycleWithLimitStartTimerEvent3.bpmn20.xml", "tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testCycleWithLimitStartTimerEvent4.bpmn20.xml", "tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testCycleWithLimitStartTimerEvent5.bpmn20.xml", "tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testCycleWithLimitStartTimerEvent6.bpmn20.xml", "tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testCycleWithLimitStartTimerEvent7.bpmn20.xml", "tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testCycleWithLimitStartTimerEvent8.bpmn20.xml", "tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testCycleWithLimitStartTimerEvent9.bpmn20.xml", "tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testCycleWithLimitStartTimerEvent10.bpmn20.xml", "tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testCycleWithLimitStartTimerEvent11.bpmn20.xml", "tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testCycleWithLimitStartTimerEvent12.bpmn20.xml", "tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testCycleWithLimitStartTimerEvent13.bpmn20.xml", "tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testCycleWithLimitStartTimerEvent14.bpmn20.xml", "tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testCycleWithLimitStartTimerEvent15.bpmn20.xml", "tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testCycleWithLimitStartTimerEvent16.bpmn20.xml", "tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testCycleWithLimitStartTimerEvent17.bpmn20.xml", "tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testCycleWithLimitStartTimerEvent18.bpmn20.xml", "tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testCycleWithLimitStartTimerEvent19.bpmn20.xml", "tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testCycleWithLimitStartTimerEvent20.bpmn20.xml", "tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testCycleWithLimitStartTimerEvent21.bpmn20.xml", "tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testCycleWithLimitStartTimerEvent22.bpmn20.xml", "tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testCycleWithLimitStartTimerEvent23.bpmn20.xml", "tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testCycleWithLimitStartTimerEvent24.bpmn20.xml", "tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testCycleWithLimitStartTimerEvent25.bpmn20.xml", "tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testCycleWithLimitStartTimerEvent26.bpmn20.xml", "tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testCycleWithLimitStartTimerEvent27.bpmn20.xml", "tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testCycleWithLimitStartTimerEvent28.bpmn20.xml", "tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testCycleWithLimitStartTimerEvent29.bpmn20.xml", "tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testCycleWithLimitStartTimerEvent30.bpmn20.xml", "tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testCycleWithLimitStartTimerEvent31.bpmn20.xml", "tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testCycleWithLimitStartTimerEvent32.bpmn20.xml", "tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testCycleWithLimitStartTimerEvent33.bpmn20.xml", "tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testCycleWithLimitStartTimerEvent34.bpmn20.xml", "tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testCycleWithLimitStartTimerEvent35.bpmn20.xml", "tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testCycleWithLimitStartTimerEvent36.bpmn20.xml", "tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testCycleWithLimitStartTimerEvent37.bpmn20.xml", "tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testCycleWithLimitStartTimerEvent38.bpmn20.xml", "tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testCycleWithLimitStartTimerEvent39.bpmn20.xml", "tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testCycleWithLimitStartTimerEvent40.bpmn20.xml", "tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testCycleWithLimitStartTimerEvent41.bpmn20.xml", "tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testCycleWithLimitStartTimerEvent42.bpmn20.xml", "tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testCycleWithLimitStartTimerEvent43.bpmn20.xml", "tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testCycleWithLimitStartTimerEvent44.bpmn20.xml", "tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testCycleWithLimitStartTimerEvent45.bpmn20.xml", "tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testCycleWithLimitStartTimerEvent46.bpmn20.xml", "tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testCycleWithLimitStartTimerEvent47.bpmn20.xml", "tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testCycleWithLimitStartTimerEvent48.bpmn20.xml", "tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testCycleWithLimitStartTimerEvent49.bpmn20.xml", "tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testCycleWithLimitStartTimerEvent50.bpmn20.xml"])]
    public function testCycleWithLimitStartTimerEvent(): void
    {
        sleep(180);

        for ($i = 1; $i <= 50; $i += 1) {
            $piqs = $this->runtimeService->createProcessInstanceQuery()->processDefinitionKey("startTimerEventExampleCycle$i")->list();
            $this->assertCount(2, $piqs);

            $execution = $piqs[0];
            //$this->runtimeService->signal($execution->getId());
            $this->runtimeService->deleteProcessInstance($execution->getId());

            $execution = $piqs[1];
            //$this->runtimeService->signal($execution->getId());
            $this->runtimeService->deleteProcessInstance($execution->getId());
        }

        /*sleep(60);

        for ($i = 1; $i <= 50; $i += 1) {
            $piqs = $this->runtimeService->createProcessInstanceQuery()->processDefinitionKey("startTimerEventExampleCycle$i")->list();
            $this->assertCount(0, $piqs);
        }*/
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testPriorityInTimerCycleEvent.bpmn20.xml"])]
    public function testPriorityInTimerCycleEvent(): void
    {
        // After process start, there should be timer created
        $jobQuery = $this->managementService->createJobQuery()->processDefinitionKey("startTimerEventExampleCycle801");
        $this->assertEquals(1, $jobQuery->count());

        // ensure that the deployment Id is set on the new job
        $job = $jobQuery->singleResult();
        //$this->assertNotNull($job->getDeploymentId());
        $this->assertEquals(9999, $job->getPriority());

        sleep(180);

        $piq = $this->runtimeService->createProcessInstanceQuery()->processDefinitionKey("startTimerEventExampleCycle801");
        $this->assertEquals(2, $piq->count());

        $piqs = $piq->list();

        $execution = $piqs[0];
        //$this->runtimeService->signal($execution->getId());
        $this->runtimeService->deleteProcessInstance($execution->getId());

        $execution = $piqs[1];
        //$this->runtimeService->signal($execution->getId());
        $this->runtimeService->deleteProcessInstance($execution->getId());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testExpressionStartTimerEvent.bpmn20.xml"])]
    public function testExpressionStartTimerEvent(): void
    {
        // ACT-1415: fixed start-date is an expression
        $jobQuery = $this->managementService->createJobQuery()->processDefinitionKey("startTimerEventExample802");
        //$this->assertEquals(1, $jobQuery->count());

        sleep(90);

        $pi = $this->runtimeService->createProcessInstanceQuery()->processDefinitionKey("startTimerEventExample802")->list();
        $this->assertEquals(1, count($pi));

        $execution = $pi[0];
        //$this->runtimeService->signal($execution->getId());
        $this->runtimeService->deleteProcessInstance($execution->getId());
    }


    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testRecalculateExpressionStartTimerEvent.bpmn20.xml"])]
    public function testRecalculateExpressionStartTimerEvent(): void
    {
        // given
        $jobQuery = $this->managementService->createJobQuery()->processDefinitionKey("startTimerEventExample803");
        $processInstanceQuery = $this->runtimeService->createProcessInstanceQuery()->processDefinitionKey("startTimerEventExample803");
        $this->assertEquals(1, $jobQuery->count());
        $this->assertEquals(0, $processInstanceQuery->count());

        $job = $jobQuery->singleResult();
        $oldDate = $job->getDuedate();

        // when
        $this->moveBySeconds(120);
        $currentTime = ClockUtil::getCurrentTime(...$this->processEngineConfiguration->getJobExecutorState());
        $this->managementService->recalculateJobDuedate($job->getId(), false);

        // then
        $this->assertEquals(1, $jobQuery->count());
        $this->assertEquals(0, $processInstanceQuery->count());

        $newDate = $jobQuery->singleResult()->getDuedate();
        $this->assertNotEquals($oldDate, $newDate);
        $this->assertTrue($oldDate < $newDate);
        $expectedDate = (new \DateTime())->setTimestamp(strtotime("+2 hours", strtotime($currentTime->format('Y-m-d H:i:s'))));
        $this->assertEquals($expectedDate, new \DateTime($newDate));

        // move the clock forward 2 hours and 2 min
        $this->moveBySeconds(7320);

        sleep(45);
        $pi = $processInstanceQuery->list();
        $this->assertCount(1, $pi);
        $this->assertEquals(0, $jobQuery->count());

        $execution = $pi[0];
        $this->runtimeService->deleteProcessInstance($execution->getId());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testRecalculateExpressionStartTimerEvent.bpmn20.xml"])]
    public function testRecalculateUnchangedExpressionStartTimerEventCreationDateBased(): void
    {
        // given
        $jobQuery = $this->managementService->createJobQuery()->processDefinitionKey("startTimerEventExample803");
        $processInstanceQuery = $this->runtimeService->createProcessInstanceQuery()->processDefinitionKey("startTimerEventExample803");
        $this->assertEquals(1, $jobQuery->count());
        $this->assertEquals(0, $processInstanceQuery->count());

        // when
        $this->moveBySeconds(60);
        $this->managementService->recalculateJobDuedate($jobQuery->singleResult()->getId(), true);

        // then due date should be based on the creation time
        $this->assertEquals(1, $jobQuery->count());
        $this->assertEquals(0, $processInstanceQuery->count());

        $jobUpdated = $jobQuery->singleResult();
        $expectedDate = (new \DateTime())->setTimestamp(strtotime("+2 hours", strtotime((new \DateTime($jobUpdated->getCreateTime()))->format('Y-m-d H:i:s'))));
        $this->assertEquals($expectedDate, new \DateTime($jobUpdated->getDuedate()));

        // move the clock forward 2 hours and 1 minute
        $this->moveBySeconds(7260);
        sleep(45);

        $pi = $processInstanceQuery->list();
        $this->assertCount(1, $pi);

        $this->assertEquals(0, $jobQuery->count());

        $execution = $pi[0];
        $this->runtimeService->deleteProcessInstance($execution->getId());
    }


    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testVersionUpgradeShouldCancelJobs.bpmn20.xml"])]
    public function testVersionUpgradeShouldCancelJobs(): void
    {
        ClockUtil::setCurrentTime(new \DateTime('now'), ...$this->processEngineConfiguration->getJobExecutorState());

        // After process start, there should be timer created
        $jobQuery = $this->managementService->createJobQuery();
        $this->assertEquals(1, $jobQuery->count());

        // we deploy new process version, with some small change
        $process = str_replace(
            "beforeChange",
            "changed",
            file_get_contents('tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testVersionUpgradeShouldCancelJobs.bpmn20.xml')
        );

        $path = tempnam(sys_get_temp_dir(), 'bpmn');
        file_put_contents($path, $process);
        $in = fopen($path, 'r+');

        $id = $this->repositoryService->createDeployment()->addInputStream("tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testVersionUpgradeShouldCancelJobs.bpmn20.xml", $in)->deploy()->getId();
        IoUtil::closeSilently($in);
        $this->assertEquals(1, $jobQuery->count());

        $this->moveBySeconds(300);
        sleep(40);
        $processInstance = $this->runtimeService->createProcessInstanceQuery()->processDefinitionKey("startTimerEventExample804")->singleResult();
        $pi = $processInstance->getProcessInstanceId();
        $this->assertEquals("changed", $this->runtimeService->getActiveActivityIds($pi)[0]);
        $this->assertEquals(1, $jobQuery->count());
        //    cleanDB();
        $this->repositoryService->deleteDeployment($id, true);
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testTimerShouldNotBeRecreatedOnDeploymentCacheReboot.bpmn20.xml"])]
    public function testTimerShouldNotBeRecreatedOnDeploymentCacheReboot(): void
    {
        // Just to be sure, I added this test. Sounds like something that could
        // easily happen
        // when the order of deploy/parsing is altered.

        // After process start, there should be timer created
        $jobQuery = $this->managementService->createJobQuery()->processDefinitionKey("startTimer1");
        $this->assertEquals(1, $jobQuery->count());

        // Reset deployment cache
        $this->processEngineConfiguration->getDeploymentCache()->discardProcessDefinitionCache();

        // Start one instance of the process definition, this will trigger a cache
        // reload
        $processInstance = $this->runtimeService->startProcessInstanceByKey("startTimer1");

        // No new jobs should have been created
        $this->assertEquals(1, $jobQuery->count());
        $this->runtimeService->deleteProcessInstance($processInstance->getId());

        $jobs = $this->managementService->createJobQuery()->processDefinitionKey("startTimer1")->list();
        foreach ($jobs as $job) {
            $this->managementService->deleteJob($job->getId());
        }
    }

    public function testTimerShouldNotBeRemovedWhenUndeployingOldVersion(): void
    {
        $process = file_get_contents('tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testTimerShouldNotBeRemovedWhenUndeployingOldVersion.bpmn20.xml');
        $path = tempnam(sys_get_temp_dir(), 'bpmn');
        file_put_contents($path, $process);
        $in = fopen($path, 'r+');

        $id = $this->repositoryService->createDeployment()->addInputStream("StartTimerEventTest.testTimerShouldNotBeRemovedWhenUndeployingOldVersion.bpmn20.xml", $in)->deploy()->getId();
        IoUtil::closeSilently($in);

        $path = tempnam(sys_get_temp_dir(), 'bpmn');
        file_put_contents($path, $process);
        $in = fopen($path, 'r+');
        $firstDeploymentId = $this->repositoryService->createDeployment()->addInputStream("StartTimerEventTest.testVersionUpgradeShouldCancelJobs.bpmn20.xml", $in)
            ->deploy()->getId();
        IoUtil::closeSilently($in);

        // After process start, there should be timer created
        $jobQuery = $this->managementService->createJobQuery()->processDefinitionKey("processWithTimer2");
        $this->assertEquals(1, $jobQuery->count());

        // we deploy new process version, with some small change
        $processChanged = str_replace("beforeChange", "changed", $process);
        $path = tempnam(sys_get_temp_dir(), 'bpmn');
        file_put_contents($path, $processChanged);
        $in = fopen($path, 'r+');
        $secondDeploymentId = $this->repositoryService->createDeployment()->addInputStream("StartTimerEventTest.testVersionUpgradeShouldCancelJobs.bpmn20.xml", $in)
            ->deploy()->getId();
        IoUtil::closeSilently($in);
        $this->assertEquals(1, $jobQuery->count());

        // Remove the first deployment
        $this->repositoryService->deleteDeployment($firstDeploymentId, true);

        // The removal of an old version should not affect timer deletion
        // ACT-1533: this was a bug, and the timer was deleted!
        $this->assertEquals(1, $jobQuery->count());

        // Cleanup
        //cleanDB();
        $this->repositoryService->deleteDeployment($secondDeploymentId, true);
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testStartTimerEventInEventSubProcess.bpmn20.xml"])]
    public function testStartTimerEventInEventSubProcess(): void
    {
        DummyServiceTask::$wasExecuted = false;

        // start process instance
        $processInstance = $this->runtimeService->startProcessInstanceByKey("startTimerEventInEventSubProcess");

        // check if execution exists
        $executionQuery = $this->runtimeService->createExecutionQuery()->processInstanceId($processInstance->getId());
        $this->assertEquals(1, $executionQuery->count());

        // check if user task exists
        $taskQuery = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId());
        $this->assertEquals(1, $taskQuery->count());

        $jobQuery = $this->managementService->createJobQuery()->processDefinitionKey("startTimerEventInEventSubProcess");
        $this->assertEquals(1, $jobQuery->count());
        // execute existing timer job
        $this->managementService->executeJob($jobQuery->list()[0]->getId());
        $this->assertEquals(0, $jobQuery->count());

        $this->assertEquals(true, DummyServiceTask::$wasExecuted);

        // check if user task doesn't exist because timer start event is
        // interrupting
        $this->assertEquals(0, $taskQuery->count());

        // check if execution doesn't exist because timer start event is
        // interrupting
        $this->assertEquals(0, $executionQuery->count());

        $processInstanceQuery = $this->runtimeService->createProcessInstanceQuery()->processInstanceId($processInstance->getId());
        $this->assertEquals(0, $processInstanceQuery->count());
    }


    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.shouldEvaluateExpressionStartTimerEventInEventSubprocess.bpmn20.xml"])]
    public function testShouldEvaluateExpressionStartTimerEventInEventSubprocess(): void
    {
        // given
        $builder = $this->runtimeService->createProcessInstanceByKey("shouldEvaluateExpressionStartTimerEventInEventSubprocess1")
            ->setVariable("duration", "PT1M");
        // when
        $processInstance = $builder->startBeforeActivity("processUserTask1")->execute();

        sleep(120);
        // then
        $tasks = $this->taskService->createTaskQuery()->taskDefinitionKey("subprocessUserTask1");
        $this->assertCount(1, $tasks->list());

        $this->runtimeService->deleteProcessInstance($processInstance->getId());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testNonInterruptingStartTimerEventInEventSubProcess.bpmn20.xml"])]
    public function testNonInterruptingStartTimerEventInEventSubProcess(): void
    {
        DummyServiceTask::$wasExecuted = false;

        // start process instance
        $processInstance = $this->runtimeService->startProcessInstanceByKey("nonInterruptingStartTimerEventInEventSubProcess");

        // check if execution exists
        $executionQuery = $this->runtimeService->createExecutionQuery()->processInstanceId($processInstance->getId());
        $this->assertEquals(1, $executionQuery->count());

        // check if user task exists
        $taskQuery = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId());
        $this->assertEquals(1, $taskQuery->count());

        $jobQuery = $this->managementService->createJobQuery()->processDefinitionKey("nonInterruptingStartTimerEventInEventSubProcess");
        $this->assertEquals(1, $jobQuery->count());
        // execute existing job timer
        $this->managementService->executeJob($this->managementService->createJobQuery()->list()[0]->getId());
        $this->assertEquals(0, $jobQuery->count());

        $this->assertEquals(true, DummyServiceTask::$wasExecuted);

        // check if user task still exists because timer start event is non
        // interrupting
        $this->assertEquals(1, $taskQuery->count());

        // check if execution still exists because timer start event is non
        // interrupting
        $this->assertEquals(1, $executionQuery->count());

        $processInstanceQuery = $this->runtimeService->createProcessInstanceQuery()->processInstanceId($processInstance->getId());
        $this->assertEquals(1, $processInstanceQuery->count());

        $this->runtimeService->deleteProcessInstance($processInstance->getId());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testStartTimerEventSubProcessInSubProcess.bpmn20.xml"])]
    public function testStartTimerEventSubProcessInSubProcess(): void
    {
        DummyServiceTask::$wasExecuted = false;

        // start process instance
        $processInstance = $this->runtimeService->startProcessInstanceByKey("startTimerEventSubProcessInSubProcess");

        // check if execution exists
        $executionQuery = $this->runtimeService->createExecutionQuery()->processInstanceId($processInstance->getId());
        $this->assertEquals(2, $executionQuery->count());

        // check if user task exists
        $taskQuery = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId());
        $this->assertEquals(1, $taskQuery->count());

        $jobQuery = $this->managementService->createJobQuery()->processDefinitionKey("startTimerEventSubProcessInSubProcess");
        $this->assertEquals(1, $jobQuery->count());
        // execute existing timer job
        $this->managementService->executeJob($this->managementService->createJobQuery()->list()[0]->getId());
        $this->assertEquals(0, $jobQuery->count());

        $this->assertEquals(true, DummyServiceTask::$wasExecuted);

        // check if user task doesn't exist because timer start event is
        // interrupting
        $this->assertEquals(0, $taskQuery->count());

        // check if execution doesn't exist because timer start event is
        // interrupting
        $this->assertEquals(0, $executionQuery->count());

        $processInstanceQuery = $this->runtimeService->createProcessInstanceQuery()->processInstanceId($processInstance->getId());
        $this->assertEquals(0, $processInstanceQuery->count());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testNonInterruptingStartTimerEventSubProcessInSubProcess.bpmn20.xml"])]
    public function testNonInterruptingStartTimerEventSubProcessInSubProcess(): void
    {
        DummyServiceTask::$wasExecuted = false;

        // start process instance
        $processInstance = $this->runtimeService->startProcessInstanceByKey("nonInterruptingStartTimerEventSubProcessInSubProcess");

        // check if execution exists
        $executionQuery = $this->runtimeService->createExecutionQuery()->processInstanceId($processInstance->getId());
        $this->assertEquals(2, $executionQuery->count());

        // check if user task exists
        $taskQuery = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId());
        $this->assertEquals(1, $taskQuery->count());

        $jobQuery = $this->managementService->createJobQuery()->processDefinitionKey("nonInterruptingStartTimerEventSubProcessInSubProcess");
        $this->assertEquals(1, $jobQuery->count());
        // execute existing timer job
        $this->managementService->executeJob($jobQuery->list()[0]->getId());
        $this->assertEquals(0, $jobQuery->count());

        $this->assertEquals(true, DummyServiceTask::$wasExecuted);

        // check if user task still exists because timer start event is non
        // interrupting
        $this->assertEquals(1, $taskQuery->count());

        // check if execution still exists because timer start event is non
        // interrupting
        $this->assertEquals(2, $executionQuery->count());

        $processInstanceQuery = $this->runtimeService->createProcessInstanceQuery()->processInstanceId($processInstance->getId());
        $this->assertEquals(1, $processInstanceQuery->count());

        $this->runtimeService->deleteProcessInstance($processInstance->getId());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testStartTimerEventWithTwoEventSubProcesses.bpmn20.xml"])]
    public function testStartTimerEventWithTwoEventSubProcesses(): void
    {
        // start process instance
        $processInstance = $this->runtimeService->startProcessInstanceByKey("startTimerEventWithTwoEventSubProcesses");

        // check if execution exists
        $executionQuery = $this->runtimeService->createExecutionQuery()->processInstanceId($processInstance->getId());
        $this->assertEquals(1, $executionQuery->count());

        // check if user task exists
        $taskQuery = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId());
        $this->assertEquals(1, $taskQuery->count());

        $jobQuery = $this->managementService->createJobQuery()->processDefinitionKey("startTimerEventWithTwoEventSubProcesses");
        $this->assertEquals(2, $jobQuery->count());
        // get all timer jobs ordered by dueDate
        $orderedJobList = $jobQuery->orderByJobDuedate()->asc()->list();
        // execute first timer job
        $this->managementService->executeJob($orderedJobList[0]->getId());
        $this->assertEquals(0, $jobQuery->count());

        // check if user task doesn't exist because timer start event is
        // interrupting
        $this->assertEquals(0, $taskQuery->count());

        // check if execution doesn't exist because timer start event is
        // interrupting
        $this->assertEquals(0, $executionQuery->count());

        // check if process instance doesn't exist because timer start event is
        // interrupting
        $processInstanceQuery = $this->runtimeService->createProcessInstanceQuery()->processInstanceId($processInstance->getId());
        $this->assertEquals(0, $processInstanceQuery->count());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testNonInterruptingStartTimerEventWithTwoEventSubProcesses.bpmn20.xml"])]
    public function testNonInterruptingStartTimerEventWithTwoEventSubProcesses(): void
    {
        DummyServiceTask::$wasExecuted = false;

        // start process instance
        $processInstance = $this->runtimeService->startProcessInstanceByKey("nonInterruptingStartTimerEventWithTwoEventSubProcesses");

        // check if execution exists
        $executionQuery = $this->runtimeService->createExecutionQuery()->processInstanceId($processInstance->getId());
        $this->assertEquals(1, $executionQuery->count());

        // check if user task exists
        $taskQuery = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId());
        $this->assertEquals(1, $taskQuery->count());

        $jobQuery = $this->managementService->createJobQuery()->processDefinitionKey("nonInterruptingStartTimerEventWithTwoEventSubProcesses");
        $this->assertEquals(2, $jobQuery->count());
        // get all timer jobs ordered by dueDate
        $orderedJobList = $jobQuery->orderByJobDuedate()->asc()->list();
        // execute first timer job
        $this->managementService->executeJob($orderedJobList[0]->getId());
        $this->assertEquals(1, $jobQuery->count());

        $this->assertEquals(true, DummyServiceTask::$wasExecuted);

        DummyServiceTask::$wasExecuted = false;

        // check if user task still exists because timer start event is non
        // interrupting
        $this->assertEquals(1, $taskQuery->count());

        // check if execution still exists because timer start event is non
        // interrupting
        $this->assertEquals(1, $executionQuery->count());

        // execute second timer job
        $this->managementService->executeJob($orderedJobList[1]->getId());
        $this->assertEquals(0, $jobQuery->count());

        $this->assertEquals(true, DummyServiceTask::$wasExecuted);

        // check if user task still exists because timer start event is non
        // interrupting
        $this->assertEquals(1, $taskQuery->count());

        // check if execution still exists because timer event is non interrupting
        $this->assertEquals(1, $executionQuery->count());

        $processInstanceQuery = $this->runtimeService->createProcessInstanceQuery()->processInstanceId($processInstance->getId());
        $this->assertEquals(1, $processInstanceQuery->count());

        $this->runtimeService->deleteProcessInstance($processInstance->getId());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testStartTimerEventSubProcessWithUserTask.bpmn20.xml"])]
    public function testStartTimerEventSubProcessWithUserTask(): void
    {
        // start process instance
        $processInstance = $this->runtimeService->startProcessInstanceByKey("startTimerEventSubProcessWithUserTask");

        // check if user task exists
        $taskQuery = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId());
        $this->assertEquals(1, $taskQuery->count());

        $jobQuery = $this->managementService->createJobQuery()->processDefinitionKey("startTimerEventSubProcessWithUserTask");
        $this->assertEquals(2, $jobQuery->count());
        // get all timer jobs ordered by dueDate
        $orderedJobList = $jobQuery->orderByJobDuedate()->asc()->list();
        // execute first timer job
        $this->managementService->executeJob($orderedJobList[0]->getId());
        $this->assertEquals(0, $jobQuery->count());

        // check if user task of event subprocess named "subProcess" exists
        $this->assertEquals(1, $taskQuery->count());
        $this->assertEquals("subprocessUserTask", $taskQuery->list()[0]->getTaskDefinitionKey());

        // check if process instance exists because subprocess named "subProcess" is
        // already running
        $processInstanceQuery = $this->runtimeService->createProcessInstanceQuery()->processInstanceId($processInstance->getId());
        $this->assertEquals(1, $processInstanceQuery->count());
        $this->runtimeService->deleteProcessInstance($processInstance->getId());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Timer/simpleProcessWithCallActivity.bpmn20.xml", "tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testStartTimerEventWithTwoEventSubProcesses.bpmn20.xml"])]
    public function testStartTimerEventSubProcessCalledFromCallActivity(): void
    {
        $variables = [];
        $variables["calledProcess"] = "startTimerEventWithTwoEventSubProcesses";
        // start process instance
        $processInstance = $this->runtimeService->startProcessInstanceByKey("simpleCallActivityProcess", $variables);

        // check if execution exists
        $executionQuery = $this->runtimeService->createExecutionQuery()->processInstanceId($processInstance->getId());
        $this->assertEquals(2, $executionQuery->count());

        // check if user task exists
        $taskQuery = $this->taskService->createTaskQuery()->processDefinitionKey("startTimerEventWithTwoEventSubProcesses");
        $this->assertEquals(1, $taskQuery->count());

        $jobQuery = $this->managementService->createJobQuery()->processDefinitionKey("startTimerEventWithTwoEventSubProcesses");
        $this->assertEquals(2, $jobQuery->count());
        // get all timer jobs ordered by dueDate
        $orderedJobList = $jobQuery->orderByJobDuedate()->asc()->list();
        // execute first timer job
        $this->managementService->executeJob($orderedJobList[0]->getId());
        $this->assertEquals(0, $jobQuery->count());

        // check if user task doesn't exist because timer start event is
        // interrupting
        $this->assertEquals(0, $taskQuery->count());

        // check if execution doesn't exist because timer start event is
        // interrupting
        $this->assertEquals(0, $executionQuery->count());

        // check if process instance doesn't exist because timer start event is
        // interrupting
        $processInstanceQuery = $this->runtimeService->createProcessInstanceQuery()->processInstanceId($processInstance->getId());
        $this->assertEquals(0, $processInstanceQuery->count());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Timer/simpleProcessWithCallActivity.bpmn20.xml", "tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testStartTimerEventWithTwoEventSubProcesses.bpmn20.xml"])]
    public function testNonInterruptingStartTimerEventSubProcessesCalledFromCallActivity(): void
    {
        DummyServiceTask::$wasExecuted = false;

        // start process instance
        $processInstance = $this->runtimeService->startProcessInstanceByKey("nonInterruptingStartTimerEventWithTwoEventSubProcesses");

        // check if execution exists
        $executionQuery = $this->runtimeService->createExecutionQuery()->processInstanceId($processInstance->getId());
        $this->assertEquals(1, $executionQuery->count());

        // check if user task exists
        $taskQuery = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId());
        $this->assertEquals(1, $taskQuery->count());

        $jobQuery = $this->managementService->createJobQuery()->processInstanceId($processInstance->getId());
        $this->assertEquals(2, $jobQuery->count());
        // get all timer jobs ordered by dueDate
        $orderedJobList = $jobQuery->orderByJobDuedate()->asc()->list();
        // execute first timer job
        $this->managementService->executeJob($orderedJobList[0]->getId());
        $this->assertEquals(1, $jobQuery->count());

        $this->assertEquals(true, DummyServiceTask::$wasExecuted);

        DummyServiceTask::$wasExecuted = false;

        // check if user task still exists because timer start event is non
        // interrupting
        $this->assertEquals(1, $taskQuery->count());

        // check if execution still exists because timer start event is non
        // interrupting
        $this->assertEquals(1, $executionQuery->count());

        // execute second timer job
        $this->managementService->executeJob($orderedJobList[1]->getId());
        $this->assertEquals(0, $jobQuery->count());

        $this->assertEquals(true, DummyServiceTask::$wasExecuted);

        // check if user task still exists because timer start event is non
        // interrupting
        $this->assertEquals(1, $taskQuery->count());

        // check if execution still exists because timer event is non interrupting
        $this->assertEquals(1, $executionQuery->count());

        $processInstanceQuery = $this->runtimeService->createProcessInstanceQuery()->processInstanceId($processInstance->getId());
        $this->assertEquals(1, $processInstanceQuery->count());
        $this->runtimeService->deleteProcessInstance($processInstance->getId());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testStartTimerEventSubProcessInMultiInstanceSubProcess.bpmn20.xml"])]
    public function testStartTimerEventSubProcessInMultiInstanceSubProcess(): void
    {
        DummyServiceTask::$wasExecuted = false;

        // start process instance
        $processInstance = $this->runtimeService->startProcessInstanceByKey("startTimerEventSubProcessInMultiInstanceSubProcess");

        // check if user task exists
        $taskQuery = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId());
        $this->assertEquals(1, $taskQuery->count());

        $jobQuery = $this->managementService->createJobQuery()->processDefinitionKey("startTimerEventSubProcessInMultiInstanceSubProcess");
        $this->assertEquals(1, $jobQuery->count());
        $jobIdFirstLoop = $jobQuery->list()[0]->getId();
        // execute timer job
        $this->managementService->executeJob($jobIdFirstLoop);

        $this->assertEquals(true, DummyServiceTask::$wasExecuted);
        DummyServiceTask::$wasExecuted = false;

        // execute multiInstance loop number 2
        $this->assertEquals(1, $taskQuery->count());
        $this->assertEquals(1, $jobQuery->count());
        $jobIdSecondLoop = $jobQuery->list()[0]->getId();
        $this->assertNotEquals($jobIdFirstLoop, $jobIdSecondLoop);
        // execute timer job
        $this->managementService->executeJob($jobIdSecondLoop);

        $this->assertEquals(true, DummyServiceTask::$wasExecuted);

        // multiInstance loop finished
        $this->assertEquals(0, $jobQuery->count());

        // check if user task doesn't exist because timer start event is
        // interrupting
        $this->assertEquals(0, $taskQuery->count());

        // check if process instance doesn't exist because timer start event is
        // interrupting
        $this->testRule->assertProcessEnded($processInstance->getId());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testNonInterruptingStartTimerEventInMultiInstanceEventSubProcess.bpmn20.xml"])]
    public function testNonInterruptingStartTimerEventInMultiInstanceEventSubProcess(): void
    {
        DummyServiceTask::$wasExecuted = false;

        // start process instance
        $processInstance = $this->runtimeService->startProcessInstanceByKey("nonInterruptingStartTimerEventInMultiInstanceEventSubProcess");

        // execute multiInstance loop number 1

        // check if user task exists
        $taskQuery = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId());
        $this->assertEquals(1, $taskQuery->count());

        $jobQuery = $this->managementService->createJobQuery()->processDefinitionKey("nonInterruptingStartTimerEventInMultiInstanceEventSubProcess");
        $this->assertEquals(1, $jobQuery->count());
        $jobIdFirstLoop = $jobQuery->list()[0]->getId();
        // execute timer job
        $this->managementService->executeJob($jobIdFirstLoop);

        $this->assertEquals(true, DummyServiceTask::$wasExecuted);
        DummyServiceTask::$wasExecuted = false;

        $this->assertEquals(1, $taskQuery->count());
        // complete existing task to start new execution for multi instance loop
        // number 2
        $this->taskService->complete($taskQuery->list()[0]->getId());

        // execute multiInstance loop number 2
        $this->assertEquals(1, $taskQuery->count());
        $this->assertEquals(1, $jobQuery->count());
        $jobIdSecondLoop = $jobQuery->list()[0]->getId();
        $this->assertNotEquals($jobIdFirstLoop, $jobIdSecondLoop);
        // execute timer job
        $this->managementService->executeJob($jobIdSecondLoop);

        $this->assertEquals(true, DummyServiceTask::$wasExecuted);

        // multiInstance loop finished
        $this->assertEquals(0, $jobQuery->count());

        // check if user task doesn't exist because timer start event is
        // interrupting
        $this->assertEquals(1, $taskQuery->count());

        // check if process instance doesn't exist because timer start event is
        // interrupting
        $processInstanceQuery = $this->runtimeService->createProcessInstanceQuery()->processInstanceId($processInstance->getId());
        $this->assertEquals(1, $processInstanceQuery->count());
        $this->runtimeService->deleteProcessInstance($processInstance->getId());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testStartTimerEventSubProcessInParallelMultiInstanceSubProcess.bpmn20.xml"])]
    public function testStartTimerEventSubProcessInParallelMultiInstanceSubProcess(): void
    {
        DummyServiceTask::$wasExecuted = false;

        // start process instance
        $processInstance = $this->runtimeService->startProcessInstanceByKey("startTimerEventSubProcessInParallelMultiInstanceSubProcess");

        // check if execution exists
        $executionQuery = $this->runtimeService->createExecutionQuery()->processInstanceId($processInstance->getId());
        $this->assertEquals(6, $executionQuery->count());

        // check if user task exists
        $taskQuery = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId());
        $this->assertEquals(2, $taskQuery->count());

        $jobQuery = $this->managementService->createJobQuery()->processDefinitionKey("startTimerEventSubProcessInParallelMultiInstanceSubProcess");
        $this->assertEquals(2, $jobQuery->count());
        // execute timer job
        foreach ($jobQuery->list() as $job) {
            $this->managementService->executeJob($job->getId());

            $this->assertEquals(true, DummyServiceTask::$wasExecuted);
            DummyServiceTask::$wasExecuted = false;
        }

        // check if user task doesn't exist because timer start event is
        // interrupting
        $this->assertEquals(0, $taskQuery->count());

        // check if execution doesn't exist because timer start event is
        // interrupting
        $this->assertEquals(0, $executionQuery->count());

        // check if process instance doesn't exist because timer start event is
        // interrupting
        $processInstanceQuery = $this->runtimeService->createProcessInstanceQuery()->processInstanceId($processInstance->getId());
        $this->assertEquals(0, $processInstanceQuery->count());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testNonInterruptingStartTimerEventSubProcessWithParallelMultiInstance.bpmn20.xml"])]
    public function testNonInterruptingStartTimerEventSubProcessWithParallelMultiInstance(): void
    {
        DummyServiceTask::$wasExecuted = false;

        // start process instance
        $processInstance = $this->runtimeService->startProcessInstanceByKey("nonInterruptingParallelMultiInstance");

        // check if execution exists
        $executionQuery = $this->runtimeService->createExecutionQuery()->processInstanceId($processInstance->getId());
        $this->assertEquals(6, $executionQuery->count());

        // check if user task exists
        $taskQuery = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId());
        $this->assertEquals(2, $taskQuery->count());

        $jobQuery = $this->managementService->createJobQuery()->processDefinitionKey("nonInterruptingParallelMultiInstance");
        $this->assertEquals(2, $jobQuery->count());
        // execute all timer jobs
        foreach ($jobQuery->list() as $job) {
            $this->managementService->executeJob($job->getId());

            $this->assertEquals(true, DummyServiceTask::$wasExecuted);
            DummyServiceTask::$wasExecuted = false;
        }

        $this->assertEquals(0, $jobQuery->count());

        // check if user task doesn't exist because timer start event is
        // interrupting
        $this->assertEquals(2, $taskQuery->count());

        // check if execution doesn't exist because timer start event is
        // interrupting
        $this->assertEquals(6, $executionQuery->count());

        // check if process instance doesn't exist because timer start event is
        // interrupting
        $processInstanceQuery = $this->runtimeService->createProcessInstanceQuery()->processInstanceId($processInstance->getId());
        $this->assertEquals(1, $processInstanceQuery->count());
        $this->runtimeService->deleteProcessInstance($processInstance->getId());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testStartTimerEventSubProcessInMultiInstanceSubProcessWithNonInterruptingBoundaryTimerEvent.bpmn20.xml"])]
    public function testStartTimerEventSubProcessInMultiInstanceSubProcessWithNonInterruptingBoundaryTimerEvent(): void
    {
        DummyServiceTask::$wasExecuted = false;

        // start process instance
        $processInstance = $this->runtimeService->startProcessInstanceByKey("process");

        // check if user task exists
        $taskQuery = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId());
        $this->assertEquals(1, $taskQuery->count());

        $jobQuery = $this->managementService->createJobQuery()->processDefinitionKey("process");
        // 1 start timer job and 1 boundary timer job
        $this->assertEquals(2, $jobQuery->count());
        // execute interrupting start timer event subprocess job
        $this->managementService->executeJob($jobQuery->orderByJobDuedate()->asc()->list()[1]->getId());

        $this->assertEquals(true, DummyServiceTask::$wasExecuted);

        // after first interrupting start timer event sub process execution
        // multiInstance loop number 2
        $this->assertEquals(1, $taskQuery->count());
        $this->assertEquals(2, $jobQuery->count());

        // execute non interrupting boundary timer job
        $this->managementService->executeJob($jobQuery->orderByJobDuedate()->asc()->list()[0]->getId());

        // after non interrupting boundary timer job execution
        $this->assertEquals(1, $jobQuery->count());
        $this->assertEquals(1, $taskQuery->count());
        $processInstanceQuery = $this->runtimeService->createProcessInstanceQuery()->processInstanceId($processInstance->getId());
        $this->assertEquals(1, $processInstanceQuery->count());
        $this->runtimeService->deleteProcessInstance($processInstance->getId());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testStartTimerEventSubProcessInMultiInstanceSubProcessWithInterruptingBoundaryTimerEvent.bpmn20.xml"])]
    public function testStartTimerEventSubProcessInMultiInstanceSubProcessWithInterruptingBoundaryTimerEvent(): void
    {
        DummyServiceTask::$wasExecuted = false;

        // start process instance
        $processInstance = $this->runtimeService->startProcessInstanceByKey("process");

        // execute multiInstance loop number 1
        // check if execution exists

        // check if user task exists
        $taskQuery = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId());
        $this->assertEquals(1, $taskQuery->count());

        $jobQuery = $this->managementService->createJobQuery()->processDefinitionKey("process");
        // 1 start timer job and 1 boundary timer job
        $this->assertEquals(2, $jobQuery->count());
        // execute interrupting start timer event subprocess job
        $this->managementService->executeJob($jobQuery->orderByJobDuedate()->asc()->list()[1]->getId());

        $this->assertEquals(true, DummyServiceTask::$wasExecuted);

        // after first interrupting start timer event sub process execution
        // multiInstance loop number 2
        $this->assertEquals(1, $taskQuery->count());
        $this->assertEquals(2, $jobQuery->count());

        // execute interrupting boundary timer job
        $this->managementService->executeJob($jobQuery->orderByJobDuedate()->asc()->list()[0]->getId());

        // after interrupting boundary timer job execution
        $this->assertEquals(0, $jobQuery->count());
        $this->assertEquals(0, $taskQuery->count());

        $this->testRule->assertProcessEnded($processInstance->getId());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testNonInterruptingStartTimerEventSubProcessInMultiInstanceSubProcessWithInterruptingBoundaryTimerEvent.bpmn20.xml"])]
    public function testNonInterruptingStartTimerEventSubProcessInMultiInstanceSubProcessWithInterruptingBoundaryTimerEvent(): void
    {
        DummyServiceTask::$wasExecuted = false;

        // start process instance
        $processInstance = $this->runtimeService->startProcessInstanceByKey("process");

        // execute multiInstance loop number 1
        // check if execution exists
        $executionQuery = $this->runtimeService->createExecutionQuery()->processInstanceId($processInstance->getId());
        $this->assertEquals(3, $executionQuery->count());

        // check if user task exists
        $taskQuery = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId());
        $this->assertEquals(1, $taskQuery->count());

        $jobQuery = $this->managementService->createJobQuery()->processDefinitionKey("process");
        // 1 start timer job and 1 boundary timer job
        $this->assertEquals(2, $jobQuery->count());
        // execute non interrupting start timer event subprocess job
        $this->managementService->executeJob($jobQuery->orderByJobDuedate()->asc()->list()[1]->getId());

        $this->assertEquals(true, DummyServiceTask::$wasExecuted);

        // complete user task to finish execution of first multiInstance loop
        $this->assertEquals(1, $taskQuery->count());
        $this->taskService->complete($taskQuery->list()[0]->getId());

        // after first non interrupting start timer event sub process execution
        // multiInstance loop number 2
        $this->assertEquals(1, $taskQuery->count());
        $this->assertEquals(2, $jobQuery->count());

        // execute interrupting boundary timer job
        $this->managementService->executeJob($jobQuery->orderByJobDuedate()->asc()->list()[0]->getId());

        // after interrupting boundary timer job execution
        $this->assertEquals(0, $jobQuery->count());
        $this->assertEquals(0, $taskQuery->count());
        $this->assertEquals(0, $executionQuery->count());
        $processInstanceQuery = $this->runtimeService->createProcessInstanceQuery()->processInstanceId($processInstance->getId());
        $this->assertEquals(0, $processInstanceQuery->count());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testStartTimerEventSubProcessInParallelMultiInstanceSubProcessWithNonInterruptingBoundaryTimerEvent.bpmn20.xml"])]
    public function testStartTimerEventSubProcessInParallelMultiInstanceSubProcessWithNonInterruptingBoundaryTimerEvent(): void
    {
        DummyServiceTask::$wasExecuted = false;

        // start process instance
        $processInstance = $this->runtimeService->startProcessInstanceByKey("process");

        // execute multiInstance loop number 1
        // check if execution exists
        $executionQuery = $this->runtimeService->createExecutionQuery()->processInstanceId($processInstance->getId());
        $this->assertEquals(6, $executionQuery->count());

        // check if user task exists
        $taskQuery = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId());
        $this->assertEquals(2, $taskQuery->count());

        $jobQuery = $this->managementService->createJobQuery()->processDefinitionKey("process");
        $this->assertEquals(3, $jobQuery->count());

        // execute interrupting timer job
        $this->managementService->executeJob($jobQuery->orderByJobDuedate()->asc()->list()[1]->getId());

        $this->assertEquals(true, DummyServiceTask::$wasExecuted);

        // after interrupting timer job execution
        $this->assertEquals(2, $jobQuery->count());
        $this->assertEquals(1, $taskQuery->count());
        $this->assertEquals(5, $executionQuery->count());

        // execute non interrupting boundary timer job
        $this->managementService->executeJob($jobQuery->orderByJobDuedate()->asc()->list()[0]->getId());

        // after non interrupting boundary timer job execution
        $this->assertEquals(1, $jobQuery->count());
        $this->assertEquals(1, $taskQuery->count());
        $this->assertEquals(5, $executionQuery->count());

        $processInstanceQuery = $this->runtimeService->createProcessInstanceQuery()->processInstanceId($processInstance->getId());
        $this->assertEquals(1, $processInstanceQuery->count());
        $this->runtimeService->deleteProcessInstance($processInstance->getId());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testStartTimerEventSubProcessInParallelMultiInstanceSubProcessWithInterruptingBoundaryTimerEvent.bpmn20.xml"])]
    public function testStartTimerEventSubProcessInParallelMultiInstanceSubProcessWithInterruptingBoundaryTimerEvent(): void
    {
        // start process instance
        $processInstance = $this->runtimeService->startProcessInstanceByKey("process");

        // execute multiInstance loop number 1
        // check if execution exists
        $executionQuery = $this->runtimeService->createExecutionQuery()->processInstanceId($processInstance->getId());
        $this->assertEquals(6, $executionQuery->count());

        // check if user task exists
        $taskQuery = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId());
        $this->assertEquals(2, $taskQuery->count());

        $jobQuery = $this->managementService->createJobQuery()->processDefinitionKey("process");
        $this->assertEquals(3, $jobQuery->count());

        // execute interrupting timer job
        $this->managementService->executeJob($jobQuery->orderByJobDuedate()->asc()->list()[1]->getId());

        // after interrupting timer job execution
        $this->assertEquals(2, $jobQuery->count());
        $this->assertEquals(1, $taskQuery->count());
        $this->assertEquals(5, $executionQuery->count());

        // execute interrupting boundary timer job
        $this->managementService->executeJob($jobQuery->orderByJobDuedate()->asc()->list()[0]->getId());

        // after interrupting boundary timer job execution
        $this->assertEquals(0, $jobQuery->count());
        $this->assertEquals(0, $taskQuery->count());
        $this->assertEquals(0, $executionQuery->count());

        $this->testRule->assertProcessEnded($processInstance->getId());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testNonInterruptingStartTimerEventSubProcessInParallelMiSubProcessWithInterruptingBoundaryTimerEvent.bpmn20.xml"])]
    public function testNonInterruptingStartTimerEventSubProcessInParallelMiSubProcessWithInterruptingBoundaryTimerEvent(): void
    {
        DummyServiceTask::$wasExecuted = false;

        // start process instance
        $processInstance = $this->runtimeService->startProcessInstanceByKey("process");

        // execute multiInstance loop number 1
        // check if execution exists
        $executionQuery = $this->runtimeService->createExecutionQuery()->processInstanceId($processInstance->getId());
        $this->assertEquals(6, $executionQuery->count());

        // check if user task exists
        $taskQuery = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId());
        $this->assertEquals(2, $taskQuery->count());

        $jobQuery = $this->managementService->createJobQuery()->processDefinitionKey("process");
        $this->assertEquals(3, $jobQuery->count());

        // execute non interrupting timer job
        $this->managementService->executeJob($jobQuery->orderByJobDuedate()->asc()->list()[1]->getId());

        $this->assertEquals(true, DummyServiceTask::$wasExecuted);

        // after non interrupting timer job execution
        $this->assertEquals(2, $jobQuery->count());
        $this->assertEquals(2, $taskQuery->count());
        $this->assertEquals(6, $executionQuery->count());

        // execute interrupting boundary timer job
        $this->managementService->executeJob($jobQuery->orderByJobDuedate()->asc()->list()[0]->getId());

        // after interrupting boundary timer job execution
        $this->assertEquals(0, $jobQuery->count());
        $this->assertEquals(0, $taskQuery->count());
        $this->assertEquals(0, $executionQuery->count());

        $this->testRule->assertProcessEnded($processInstance->getId());

        // start process instance again and
        // test if boundary events deleted after all tasks are completed
        $processInstance = $this->runtimeService->startProcessInstanceByKey("process");
        $jobQuery = $this->managementService->createJobQuery()->processDefinitionKey("process");
        $this->assertEquals(3, $jobQuery->count());

        $taskQuery = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId());
        $this->assertEquals(2, $taskQuery->count());
        // complete all existing tasks
        foreach ($taskQuery->list() as $task) {
            $this->taskService->complete($task->getId());
        }

        $this->assertEquals(0, $jobQuery->count());
        $this->assertEquals(0, $taskQuery->count());
        $this->assertEquals(0, $executionQuery->count());

        $this->testRule->assertProcessEnded($processInstance->getId());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testTimeCycle.bpmn20.xml"])]
    public function testTimeCycle(): void
    {
        // given
        $jobQuery = $this->managementService->createJobQuery()->processDefinitionKey("process20");
        $this->assertEquals(1, $jobQuery->count());

        $jobId = $jobQuery->singleResult()->getId();

        // when
        $this->managementService->executeJob($jobId);

        // then
        $this->assertEquals(1, $jobQuery->count());

        $anotherJob = $jobQuery->singleResult();
        $anotherJobId = $anotherJob->getId();
        $this->assertNotEquals($jobId, $anotherJobId);

        $processDefinitionId = $anotherJob->getProcessDefinitionId();
        $executionQuery = $this->runtimeService->createExecutionQuery()->processDefinitionId($processDefinitionId);
        $execution = $executionQuery->singleResult();

        $jobs = $this->managementService->createJobQuery()->processDefinitionKey("process20")->list();
        foreach ($jobs as $job) {
            $this->managementService->deleteJob($job->getId());
        }

        $this->runtimeService->deleteProcessInstance($execution->getId());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testFailingTimeCycle.bpmn20.xml"])]
    public function testFailingTimeCycle(): void
    {
        // given
        $query = $this->managementService->createJobQuery()->processDefinitionKey("process30");
        $failedJobQuery = $this->managementService->createJobQuery()->processDefinitionKey("process30");

        // a job to start a process instance
        $this->assertEquals(1, $query->count());

        $jobId = $query->singleResult()->getId();
        $failedJobQuery->jobId($jobId);

        $this->moveBySeconds(600);

        // when (1)
        try {
            $this->managementService->executeJob($jobId);
        } catch (\Exception $e) {
            // expected
        }

        // then (1)
        $failedJob = $failedJobQuery->singleResult();
        $this->assertEquals(2, $failedJob->getRetries());

        // a new timer job has been created
        $this->assertEquals(2, $query->count());

        $this->assertEquals(1, $this->managementService->createJobQuery()->withException()->count());
        $this->assertEquals(0, $this->managementService->createJobQuery()->noRetriesLeft()->count());
        $this->assertEquals(2, $this->managementService->createJobQuery()->withRetriesLeft()->count());

        // when (2)
        try {
            $this->managementService->executeJob($jobId);
        } catch (\Exception $e) {
        }

        // then (2)
        $failedJob = $failedJobQuery->singleResult();
        $this->assertEquals(1, $failedJob->getRetries());

        // there are still two jobs
        $this->assertEquals(2, $query->count());

        $this->assertEquals(1, $this->managementService->createJobQuery()->withException()->count());
        $this->assertEquals(0, $this->managementService->createJobQuery()->noRetriesLeft()->count());
        $this->assertEquals(2, $this->managementService->createJobQuery()->withRetriesLeft()->count());

        $jobs = $this->managementService->createJobQuery()->processDefinitionKey("process30")->list();
        foreach ($jobs as $job) {
            $this->managementService->deleteJob($job->getId());
        }
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testNonInterruptingTimeCycleInEventSubProcess.bpmn20.xml"])]
    public function testNonInterruptingTimeCycleInEventSubProcess(): void
    {
        // given
        $this->runtimeService->startProcessInstanceByKey("process40");

        $jobQuery = $this->managementService->createJobQuery()->processDefinitionKey("process40");
        $this->assertEquals(1, $jobQuery->count());

        $jobId = $jobQuery->singleResult()->getId();

        // when
        $this->managementService->executeJob($jobId);

        // then
        $this->assertEquals(1, $jobQuery->count());

        $anotherJobId = $jobQuery->singleResult()->getId();
        $this->assertNotEquals($jobId, $anotherJobId);

        $piqs = $this->runtimeService->createProcessInstanceQuery()->processDefinitionKey("process40")->list();
        foreach ($piqs as $execution) {
            $this->runtimeService->deleteProcessInstance($execution->getId());
        }
    }

    public function testInterruptingWithDurationExpressionInEventSubprocess(): void
    {
        // given
        $processBuilder = Bpmn::createExecutableProcess("process50");

        $modelInstance = $processBuilder
            ->startEvent()
            ->userTask()
            ->endEvent()
            ->done();

        $processBuilder->eventSubProcess()
            ->startEvent()->timerWithDuration('${duration}')
            ->userTask("taskInSubprocess")
            ->endEvent();

        $this->testRule->deploy(
            $this->repositoryService->createDeployment()->addModelInstance("process50.bpmn", $modelInstance)
        );

        // when
        $processInstance = $this->runtimeService->startProcessInstanceByKey("process50", ["duration" => "PT60S"]);

        $jobId = $this->managementService->createJobQuery()->processDefinitionKey("process50")->singleResult()->getId();

        $this->managementService->executeJob($jobId);

        // then
        $this->assertEquals(1, count($this->taskService->createTaskQuery()->processDefinitionKey("process50")->taskName("taskInSubprocess")->list()));

        $this->runtimeService->deleteProcessInstance($processInstance->getId());
    }

    public function testNonInterruptingWithDurationExpressionInEventSubprocess(): void
    {
        // given
        $processBuilder = Bpmn::createExecutableProcess("process60");

        $modelInstance = $processBuilder
            ->startEvent()
            ->userTask()
            ->endEvent()->done();

        $processBuilder->eventSubProcess()
            ->startEvent()->interrupting(false)->timerWithDuration('${duration}')
            ->userTask("taskInSubprocess")
            ->endEvent();

        $this->testRule->deploy($this->repositoryService->createDeployment()->addModelInstance("process60.bpmn", $modelInstance));

        // when
        $processInstance = $this->runtimeService->startProcessInstanceByKey("process60", ["duration" => "PT60S"]);

        $jobId = $this->managementService->createJobQuery()->processDefinitionKey("process60")->singleResult()->getId();

        $this->managementService->executeJob($jobId);

        // then
        $this->assertEquals(1, count($this->taskService->createTaskQuery()->processDefinitionKey("process60")->taskName("taskInSubprocess")->list()));

        $this->runtimeService->deleteProcessInstance($processInstance->getId());
    }

    public function testRecalculateNonInterruptingWithUnchangedDurationExpressionInEventSubprocessCurrentDateBased(): void
    {
        // given
        $processBuilder = Bpmn::createExecutableProcess("process70");

        $modelInstance = $processBuilder
            ->startEvent()
            ->userTask()
            ->endEvent()->done();

        $processBuilder->eventSubProcess()
            ->startEvent()->interrupting(false)->timerWithDuration('${duration}')
            ->userTask("taskInSubprocess")
            ->endEvent();

        $this->testRule->deploy($this->repositoryService->createDeployment()
            ->addModelInstance("process70.bpmn", $modelInstance));

        $processInstance = $this->runtimeService->startProcessInstanceByKey("process70", ["duration" => "PT70S"]);

        $jobQuery = $this->managementService->createJobQuery()->processDefinitionKey("process70");
        $job = $jobQuery->singleResult();
        $jobId = $job->getId();
        $oldDueDate = $job->getDuedate();

        // when
        $this->moveBySeconds(120);
        $currentTime = ClockUtil::getCurrentTime(...$this->processEngineConfiguration->getJobExecutorState());
        $this->managementService->recalculateJobDuedate($jobId, false);

        // then
        $this->assertEquals(1, $jobQuery->count());
        $newDuedate = $jobQuery->singleResult()->getDuedate();
        $this->assertNotEquals($oldDueDate, $newDuedate);
        $this->assertTrue($oldDueDate < $newDuedate);
        $expectedDate = (new \DateTime())->setTimestamp(strtotime("+70 seconds", strtotime($currentTime->format('Y-m-d H:i:s'))));
        $this->assertEquals($expectedDate, new \DateTime($newDuedate));
        $this->managementService->executeJob($jobId);
        $this->assertEquals(1, count($this->taskService->createTaskQuery()->processDefinitionKey("process70")->taskName("taskInSubprocess")->list()));

        $this->runtimeService->deleteProcessInstance($processInstance->getId());
    }

    public function testRecalculateNonInterruptingWithChangedDurationExpressionInEventSubprocessCreationDateBased(): void
    {
        // given
        $processBuilder = Bpmn::createExecutableProcess("process80");

        $modelInstance = $processBuilder
            ->startEvent()
            ->userTask()
            ->endEvent()->done();

        $processBuilder->eventSubProcess()
            ->startEvent()->interrupting(false)->timerWithDuration('${duration}')
            ->userTask("taskInSubprocess")
            ->endEvent();

        $this->testRule->deploy($this->repositoryService->createDeployment()->addModelInstance("process80.bpmn", $modelInstance));

        $pi = $this->runtimeService->startProcessInstanceByKey("process80", ["duration" => "PT60S"]);

        $jobQuery = $this->managementService->createJobQuery()->processDefinitionKey("process80");
        $job = $jobQuery->singleResult();
        $jobId = $job->getId();
        $oldDueDate = $job->getDuedate();

        // when
        $this->runtimeService->setVariable($pi->getId(), "duration", "PT2M");
        $this->managementService->recalculateJobDuedate($jobId, true);

        // then
        $this->assertEquals(1, $jobQuery->count());
        $newDuedate = $jobQuery->singleResult()->getDuedate();
        $expectedDate = (new \DateTime())->setTimestamp(strtotime("+2 minutes", strtotime($jobQuery->singleResult()->getCreateTime())));
        $this->assertTrue($oldDueDate < $newDuedate);
        $this->assertEquals($expectedDate, new \DateTime($newDuedate));

        $this->managementService->executeJob($jobId);
        $this->assertEquals(1, count($this->taskService->createTaskQuery()->processDefinitionKey("process80")->taskName("taskInSubprocess")->list()));

        $this->runtimeService->deleteProcessInstance($pi->getId());
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Timer/StartTimerEventTest.testNonInterruptingFailingTimeCycleInEventSubProcess.bpmn20.xml"])]
    public function testNonInterruptingFailingTimeCycleInEventSubProcess(): void
    {
        // given
        $this->runtimeService->startProcessInstanceByKey("process90");

        $failedJobQuery = $this->managementService->createJobQuery()->processDefinitionKey("process90");
        $jobQuery = $this->managementService->createJobQuery()->processDefinitionKey("process90");

        $this->assertEquals(1, $jobQuery->count());
        $jobId = $jobQuery->singleResult()->getId();

        $failedJobQuery->jobId($jobId);

        // when (1)
        try {
            $this->managementService->executeJob($jobId);
        } catch (\Exception $e) {
            // expected
        }

        // then (1)
        $failedJob = $failedJobQuery->singleResult();
        $this->assertEquals(2, $failedJob->getRetries());

        // a new timer job has been created
        $this->assertEquals(2, $jobQuery->count());

        $this->assertEquals(1, $this->managementService->createJobQuery()->processDefinitionKey("process90")->withException()->count());
        $this->assertEquals(0, $this->managementService->createJobQuery()->processDefinitionKey("process90")->noRetriesLeft()->count());
        $this->assertEquals(2, $this->managementService->createJobQuery()->processDefinitionKey("process90")->withRetriesLeft()->count());

        // when (2)
        try {
            $this->managementService->executeJob($jobId);
        } catch (\Exception $e) {
            // expected
        }

        // then (2)
        $failedJob = $failedJobQuery->singleResult();
        $this->assertEquals(1, $failedJob->getRetries());

        // there are still two jobs
        $this->assertEquals(2, $jobQuery->count());

        $this->assertEquals(1, $this->managementService->createJobQuery()->processDefinitionKey("process90")->withException()->count());
        $this->assertEquals(0, $this->managementService->createJobQuery()->processDefinitionKey("process90")->noRetriesLeft()->count());
        $this->assertEquals(2, $this->managementService->createJobQuery()->processDefinitionKey("process90")->withRetriesLeft()->count());

        $piqs = $this->runtimeService->createProcessInstanceQuery()->processDefinitionKey("process90")->list();
        $execution = $piqs[0];
        $this->runtimeService->deleteProcessInstance($execution->getId());
    }
    // util methods ////////////////////////////////////////

    protected function executeAllJobs(string $processDefKey): void
    {
        $nextJobId = $this->getNextExecutableJobId($processDefKey);
        while ($nextJobId != null) {
            try {
                $this->managementService->executeJob($nextJobId);
            } catch (\Throwable $t) {
            }
            $nextJobId = $this->getNextExecutableJobId($processDefKey);
        }
    }

    protected function getNextExecutableJobId(string $processDefKey): ?string
    {
        $jobs = $this->managementService->createJobQuery()/*->executable()*/->processDefinitionKey($processDefKey)->listPage(0, 1);
        if (count($jobs) == 1) {
            return $jobs[0]->getId();
        } else {
            return null;
        }
    }
}
