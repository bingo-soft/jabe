<?php

namespace Tests\Bpmn\UserTask;

use PHPUnit\Framework\TestCase;
use Jabe\Impl\Bpmn\Parser\BpmnParse;
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

        $tasks = $this->taskService->createTaskQuery()->list();
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
    }
}
