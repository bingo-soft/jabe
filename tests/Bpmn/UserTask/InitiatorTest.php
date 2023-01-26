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

class InitiatorTest extends PluggableProcessEngineTest
{
    private const NAMESPACE = "xmlns='http://www.omg.org/spec/BPMN/20100524/MODEL'";
    private const TARGET_NAMESPACE = "targetNamespace='" . BpmnParse::BPMN_EXTENSIONS_NS_PREFIX . "'";

    protected function setUp(): void
    {
        //initialize all services
        parent::setUp();
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/UserTask/InitiatorTest.testInitiator.bpmn20.xml"])]
    public function testInitiator(): void
    {
        try {
            $this->identityService->setAuthenticatedUserId("bono");
            $processInstance = $this->runtimeService->startProcessInstanceByKey("InitiatorProcess");
        } finally {
            $this->identityService->setAuthenticatedUserId(null);
        }

        $this->assertEquals(1, $this->taskService->createTaskQuery()->processInstanceId($processInstance->getId())->taskAssignee("bono")->count());
    }
}
