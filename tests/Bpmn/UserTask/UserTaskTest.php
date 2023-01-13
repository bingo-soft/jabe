<?php

namespace Tests\Bpmn\UserTask;

use PHPUnit\Framework\TestCase;
use Jabe\Impl\Bpmn\Parser\BpmnParse;
use Jabe\Impl\Cfg\ProcessEngineConfigurationImpl;
use Jabe\Impl\Interceptor\{
    CommandContext,
    CommandInterface
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
        // start the process
        $processInstance = $this->runtimeService->startProcessInstanceByKey("ForkProcess");
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
}
