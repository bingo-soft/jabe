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

class TaskAssignmentExtensionsTest extends PluggableProcessEngineTest
{
    private const NAMESPACE = "xmlns='http://www.omg.org/spec/BPMN/20100524/MODEL'";
    private const TARGET_NAMESPACE = "targetNamespace='" . BpmnParse::BPMN_EXTENSIONS_NS_PREFIX . "'";

    protected function setUp(): void
    {
        //initialize all services
        parent::setUp();
        $this->identityService->saveUser($this->identityService->newUser("fozzie"));
        $this->identityService->saveUser($this->identityService->newUser("kermit"));
        $this->identityService->saveUser($this->identityService->newUser("gonzo"));

        $this->identityService->saveGroup($this->identityService->newGroup("accountancy"));
        $this->identityService->saveGroup($this->identityService->newGroup("management"));

        $this->identityService->createMembership("fozzie", "accountancy");
        $this->identityService->createMembership("kermit", "management");
        $this->identityService->createMembership("kermit", "accountancy");
    }

    protected function tearDown(): void
    {
        $this->identityService->deleteUser("fozzie");
        $this->identityService->deleteUser("kermit");
        $this->identityService->deleteUser("gonzo");
        $this->identityService->deleteGroup("accountancy");
        $this->identityService->deleteGroup("management");
        $deployments = $this->repositoryService->createDeploymentQuery()->list();
        foreach ($deployments as $deployment) {
            $this->repositoryService->deleteDeployment($deployment->getId(), true);
        }
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/UserTask/TaskAssignmentExtensionsTest.testAssigneeExtension.bpmn20.xml"])]
    public function testAssigneeExtension(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("assigneeExtension");
        $tasks = $this->taskService
          ->createTaskQuery()
          ->processInstanceId($processInstance->getId())
          ->taskAssignee("kermit")
          ->list();
        $this->assertCount(1, $tasks);
        $this->assertEquals("my task", $tasks[0]->getName());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/UserTask/TaskAssignmentExtensionsTest.testCandidateUsersExtension.bpmn20.xml"])]
    public function testCandidateUsersExtension(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("candidateUsersExtension");
        $tasks = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->taskCandidateUser("kermit")->list();
        $this->assertCount(1, $tasks);
        $tasks = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->taskCandidateUser("gonzo")->list();
        $this->assertCount(1, $tasks);

        $this->taskService->claim($tasks[0]->getId(), "kermit");
        $this->taskService->complete($tasks[0]->getId());
        $this->testRule->assertProcessEnded($processInstance->getId());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/UserTask/TaskAssignmentExtensionsTest.testCandidateGroupsExtension.bpmn20.xml"])]
    public function testCandidateGroupsExtension(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("candidateGroupsExtension");

        // Bugfix check: potentially the query could return 2 tasks since
        // kermit is a member of the two candidate groups
        $tasks = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->taskCandidateUser("kermit")->list();
        $this->assertCount(1, $tasks);
        $this->assertEquals("make profit", $tasks[0]->getName());

        $tasks = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->taskCandidateUser("fozzie")->list();
        $this->assertCount(1, $tasks);
        $this->assertEquals("make profit", $tasks[0]->getName());

        // Test the task query find-by-candidate-group operation
        $query = $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId());
        $this->assertEquals(1, $query->taskCandidateGroup("management")->count());
        $this->assertEquals(1, $query->taskCandidateGroup("accountancy")->count());
    }
}
