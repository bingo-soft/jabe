<?php

namespace Tests\Bpmn\Gateway;

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

class ExclusiveGatewayTest extends PluggableProcessEngineTest
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

    #[Deployment(resources: [ "tests/Resources/Bpmn/Gateway/ExclusiveGatewayTest.testDivergingExclusiveGateway.bpmn20.xml"])]
    public function testDivergingExclusiveGateway(): void
    {
        for ($i = 1; $i <= 3; $i++) {
            $pi = $this->runtimeService->startProcessInstanceByKey("exclusiveGwDiverging", null, ["input" => $i]);
            $this->assertEquals("Task " . $i, $this->taskService->createTaskQuery()->processInstanceId($pi->getId())->singleResult()->getName());
            $this->runtimeService->deleteProcessInstance($pi->getId(), "testing deletion");
        }
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Gateway/ExclusiveGatewayTest.testMergingExclusiveGateway.bpmn20.xml"])]
    public function testMergingExclusiveGateway(): void
    {
        $pi = $this->runtimeService->startProcessInstanceByKey("exclusiveGwMerging");
        $this->assertEquals(3, $this->taskService->createTaskQuery()->processInstanceId($pi->getId())->count());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Gateway/ExclusiveGatewayTest.testMultipleValidConditions.bpmn20.xml"])]
    public function testMultipleValidConditions(): void
    {
        $pi = $this->runtimeService->startProcessInstanceByKey("exclusiveGwMultipleValidConditions", null, ["input" => 5]);
        $this->assertEquals("Task 2", $this->taskService->createTaskQuery()->processInstanceId($pi->getId())->singleResult()->getName());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Gateway/ExclusiveGatewayTest.testNoSequenceFlowSelected.bpmn20.xml"])]
    public function testNoSequenceFlowSelected(): void
    {
        $pi = $this->runtimeService->startProcessInstanceByKey("exclusiveGwNoSeqFlowSelected", null, ["input" => 4]);
        $task = $this->taskService->createTaskQuery()->processInstanceId($pi->getId())->singleResult();
        $this->assertNull($task);
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Gateway/ExclusiveGatewayTest.testWhitespaceInExpression.bpmn20.xml"])]
    public function testWhitespaceInExpression(): void
    {
        $pi = $this->runtimeService->startProcessInstanceByKey("whiteSpaceInExpression", null, ["input" => 1]);
        $this->assertEquals("Task 1", $this->taskService->createTaskQuery()->processInstanceId($pi->getId())->singleResult()->getName());
    }

    #[Deployment(resources: [ "tests/Resources/Bpmn/Gateway/ExclusiveGatewayTest.testDecideBasedOnBeanProperty.bpmn20.xml"])]
    public function testDecideBasedOnBeanProperty(): void
    {
        $pi = $this->runtimeService->startProcessInstanceByKey("decisionBasedOnBeanProperty", null, ["order" => new ExclusiveGatewayTestOrder(150)]);

        $task = $this->taskService->createTaskQuery()->processInstanceId($pi->getId())->singleResult();
        $this->assertNotNull($task);
        $this->assertEquals("Standard service", $task->getName());
    }
}
