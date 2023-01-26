<?php

namespace Tests\Bpmn\UserTask;

use Jabe\Impl\Context\Context;
use Jabe\Impl\Bpmn\Parser\BpmnParse;
use Jabe\Impl\Cfg\ProcessEngineConfigurationImpl;
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

class UserTaskTest extends PluggableProcessEngineTest
{
    private const NAMESPACE = "xmlns='http://www.omg.org/spec/BPMN/20100524/MODEL'";
    private const TARGET_NAMESPACE = "targetNamespace='" . BpmnParse::BPMN_EXTENSIONS_NS_PREFIX . "'";

    protected function setUp(): void
    {
        //initialize all services
        parent::setUp();
        $this->identityService->saveUser($this->identityService->newUser("fozzie"));
        $this->identityService->saveUser($this->identityService->newUser("kermit"));

        $this->identityService->saveGroup($this->identityService->newGroup("accountancy"));
        $this->identityService->saveGroup($this->identityService->newGroup("management"));

        $this->identityService->createMembership("fozzie", "accountancy");
        $this->identityService->createMembership("kermit", "management");
    }

    protected function tearDown(): void
    {
        $this->identityService->deleteUser("fozzie");
        $this->identityService->deleteUser("kermit");
        $this->identityService->deleteGroup("accountancy");
        $this->identityService->deleteGroup("management");
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/UserTask/UserTaskTest.testTaskPropertiesNotNull.bpmn20.xml"])]
    public function testTaskPropertiesNotNull(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("oneTaskProcess");

        $activeActivityIds = $this->runtimeService->getActiveActivityIds($processInstance->getId());

        $tasks = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->list();
        $task = $tasks[count($tasks) - 1];
        $this->assertNotNull($task->getId());
        $this->assertEquals("my task", $task->getName());
        $this->assertEquals("Very important", $task->getDescription());
        $this->assertTrue($task->getPriority() > 0);
        $this->assertEquals("kermit", $task->getAssignee());
        $this->assertEquals($processInstance->getId(), $task->getProcessInstanceId());
        $this->assertEquals($processInstance->getId(), $task->getExecutionId());
        $this->assertNotNull($task->getProcessDefinitionId());
        $this->assertNotNull($task->getTaskDefinitionKey());
        $this->assertNotNull($task->getCreateTime());

        if ($this->processEngineConfiguration->getHistoryLevel()->getId() >= ProcessEngineConfigurationImpl::HISTORYLEVEL_ACTIVITY) {
            $this->assertCount(0, $this->taskService->getTaskEvents($task->getId()));
        }
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/UserTask/UserTaskTest.testTaskPropertiesNotNull.bpmn20.xml"])]
    public function testQuerySortingWithParameter(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("oneTaskProcess");
        $this->assertCount(1, $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->list());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/UserTask/UserTaskTest.testCompleteAfterParallelGateway.bpmn20.xml"])]
    public function testCompleteAfterParallelGateway(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("ForkProcess");
        // start the process
        $taskList = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->list();
        $this->assertCount(2, $taskList);

        // make sure user task exists
        //$task = $this->taskService->createTaskQuery()->taskDefinitionKey("SimpleUser")->singleResult();
        $task = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->taskDefinitionKey("SimpleUser")->singleResult();

        $this->assertNotNull($task);

        // attempt to complete the task and get PersistenceException pointing to "referential integrity constraint violation"
        $this->taskService->complete($task->getId());

        $this->testRule->assertProcessEnded($processInstance->getId());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/UserTask/UserTaskTest.testComplexScenarioWithSubprocessesAndParallelGateways.bpmn"])]
    public function testComplexScenarioWithSubprocessesAndParallelGateways(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("processWithSubProcessesAndParallelGateways");
        $taskList = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->list();
        $this->assertCount(13, $taskList);
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/UserTask/UserTaskTest.testSimpleProcess.bpmn20.xml"])]
    public function testSimpleProcess(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("financialReport");
        $tasks = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->taskCandidateUser("fozzie")->list();
        $this->assertCount(1, $tasks);
        $task = $tasks[0];
        $this->assertEquals("Write monthly financial report", $task->getName());

        $this->taskService->claim($task->getId(), "fozzie");
        $tasks = $this->taskService
          ->createTaskQuery()
          ->processInstanceId($processInstance->getId())
          ->taskAssignee("fozzie")
          ->list();

        $this->assertCount(1, $tasks);
        $this->taskService->complete($task->getId());

        $this->testRule->assertProcessNotEnded($processInstance->getId());

        $tasks = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->taskCandidateUser("fozzie")->list();
        $this->assertCount(0, $tasks);
        $tasks = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->taskCandidateUser("kermit")->list();
        $this->assertCount(1, $tasks);
        $this->assertEquals("Verify monthly financial report", $tasks[0]->getName());
        $this->taskService->complete($tasks[0]->getId());

        $this->testRule->assertProcessEnded($processInstance->getId());
    }
}
