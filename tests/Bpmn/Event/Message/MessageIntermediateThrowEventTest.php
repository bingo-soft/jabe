<?php

namespace Tests\Bpmn\Event\Message;

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

class MessageIntermediateThrowEventTest extends PluggableProcessEngineTest
{
    private const NAMESPACE = "xmlns='http://www.omg.org/spec/BPMN/20100524/MODEL'";
    private const TARGET_NAMESPACE = "targetNamespace='" . BpmnParse::BPMN_EXTENSIONS_NS_PREFIX . "'";

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        $deployments = $this->repositoryService->createDeploymentQuery()->list();
        foreach ($deployments as $deployment) {
            $this->repositoryService->deleteDeployment($deployment->getId(), true);
        }
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Message/MessageIntermediateThrowEventTest.testSingleIntermediateThrowMessageEvent.bpmn20.xml"])]
    public function testSingleIntermediateThrowMessageEvent(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("process");
        $this->testRule->assertProcessEnded($processInstance->getId());
        $this->assertTrue(true);
    }

    #[Deployment(resources: ["tests/Resources/Bpmn/Event/Message/MessageIntermediateThrowEventTest.testSingleIntermediateThrowMessageEventServiceTaskBehavior.bpmn20.xml"])]
    private function testSingleIntermediateThrowMessageEventServiceTaskBehavior(): void
    {
        $processInstance = $this->runtimeService->startProcessInstanceByKey("process");

        $this->testRule->assertProcessEnded($processInstance->getId());
        $this->assertTrue(DummyServiceTask::$wasExecuted);
    }
}
