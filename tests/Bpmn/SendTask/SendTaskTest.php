<?php

namespace Tests\Bpmn\SendTask;

use Jabe\Impl\Bpmn\Parser\BpmnParse;
use Jabe\Test\Deployment;
use Tests\Util\PluggableProcessEngineTest;

class SendTaskTest extends PluggableProcessEngineTest
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

    #[Deployment(resources: [ "tests/Resources/Bpmn/SendTask/SendTaskTest.testPhpDelegate.bpmn20.xml"])]
    public function testPhpDelegate(): void
    {
        DummySendTask::$wasExecuted = false;
        $processInstance = $this->runtimeService->startProcessInstanceByKey("sendTaskPhpDelegate");
        $this->testRule->assertProcessEnded($processInstance->getId());
        $this->assertTrue(DummySendTask::$wasExecuted);
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/SendTask/SendTaskTest.testActivityName.bpmn20.xml"])]
    public function testActivityName(): void
    {
        DummyActivityBehavior::$wasExecuted = false;

        $processInstance = $this->runtimeService->startProcessInstanceByKey("process");

        $this->runtimeService->signal($processInstance->getId());

        $this->testRule->assertProcessEnded($processInstance->getId());

        $this->assertTrue(DummyActivityBehavior::$wasExecuted);

        $this->assertNotNull(DummyActivityBehavior::$currentActivityName);
        $this->assertEquals("Task", DummyActivityBehavior::$currentActivityName);

        $this->assertNotNull(DummyActivityBehavior::$currentActivityId);
        $this->assertEquals("task", DummyActivityBehavior::$currentActivityId);
    }
}
