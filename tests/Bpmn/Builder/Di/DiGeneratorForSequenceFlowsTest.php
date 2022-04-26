<?php

namespace Tests\Bpmn\Builder;

use PHPUnit\Framework\TestCase;
use Tests\Bpmn\BpmnTestConstants;
use Jabe\Model\Bpmn\Bpmn;
use Jabe\Model\Bpmn\Instance\Bpmndi\{
    BpmnEdgeInterface
};

class DiGeneratorForSequenceFlowsTest extends TestCase
{
    private $instance;

    protected function tearDown(): void
    {
        if ($this->instance != null) {
            Bpmn::getInstance()->validateModel($this->instance);
        }
    }

    public function testShouldGenerateEdgeForSequenceFlow(): void
    {
        $builder = Bpmn::getInstance()->createExecutableProcess();

        $this->instance = $builder
                      ->startEvent(BpmnTestConstants::START_EVENT_ID)
                      ->sequenceFlowId(BpmnTestConstants::SEQUENCE_FLOW_ID)
                      ->endEvent(BpmnTestConstants::END_EVENT_ID)
                      ->done();

        $allEdges = $this->instance->getModelElementsByType(BpmnEdgeInterface::class);
        $this->assertCount(1, $allEdges);

        $this->assertBpmnEdgeExists(BpmnTestConstants::SEQUENCE_FLOW_ID);
    }

    public function testShouldGenerateEdgesForSequenceFlowsUsingGateway(): void
    {
        $builder = Bpmn::getInstance()->createExecutableProcess();

        $this->instance = $builder
            ->startEvent(BpmnTestConstants::START_EVENT_ID)
            ->sequenceFlowId("s1")
            ->parallelGateway("gateway")
            ->sequenceFlowId("s2")
            ->endEvent("e1")
            ->moveToLastGateway()
            ->sequenceFlowId("s3")
            ->endEvent("e2")
            ->done();

        $allEdges = $this->instance->getModelElementsByType(BpmnEdgeInterface::class);
        $this->assertCount(3, $allEdges);

        $this->assertBpmnEdgeExists("s1");
        $this->assertBpmnEdgeExists("s2");
        $this->assertBpmnEdgeExists("s3");
    }

    public function testShouldGenerateEdgesWhenUsingMoveToActivity(): void
    {
        $builder = Bpmn::getInstance()->createExecutableProcess();

        $this->instance = $builder
            ->startEvent(BpmnTestConstants::START_EVENT_ID)
            ->sequenceFlowId("s1")
            ->exclusiveGateway()
            ->sequenceFlowId("s2")
            ->userTask(BpmnTestConstants::USER_TASK_ID)
            ->sequenceFlowId("s3")
            ->endEvent("e1")
            ->moveToActivity(BpmnTestConstants::USER_TASK_ID)
            ->sequenceFlowId("s4")
            ->endEvent("e2")
            ->done();

        $allEdges = $this->instance->getModelElementsByType(BpmnEdgeInterface::class);
        $this->assertCount(4, $allEdges);

        $this->assertBpmnEdgeExists("s1");
        $this->assertBpmnEdgeExists("s2");
        $this->assertBpmnEdgeExists("s3");
        $this->assertBpmnEdgeExists("s4");
    }

    public function testShouldGenerateEdgesWhenUsingMoveToNode(): void
    {
        $builder = Bpmn::getInstance()->createExecutableProcess();

        $this->instance = $builder
            ->startEvent(BpmnTestConstants::START_EVENT_ID)
            ->sequenceFlowId("s1")
            ->exclusiveGateway()
            ->sequenceFlowId("s2")
            ->userTask(BpmnTestConstants::USER_TASK_ID)
            ->sequenceFlowId("s3")
            ->endEvent("e1")
            ->moveToNode(BpmnTestConstants::USER_TASK_ID)
            ->sequenceFlowId("s4")
            ->endEvent("e2")
            ->done();

        $allEdges = $this->instance->getModelElementsByType(BpmnEdgeInterface::class);
        $this->assertCount(4, $allEdges);

        $this->assertBpmnEdgeExists("s1");
        $this->assertBpmnEdgeExists("s2");
        $this->assertBpmnEdgeExists("s3");
        $this->assertBpmnEdgeExists("s4");
    }

    public function testShouldGenerateEdgesWhenUsingConnectTo(): void
    {
        $builder = Bpmn::getInstance()->createExecutableProcess();

        $this->instance = $builder
            ->startEvent(BpmnTestConstants::START_EVENT_ID)
            ->sequenceFlowId("s1")
            ->exclusiveGateway("gateway")
            ->sequenceFlowId("s2")
            ->userTask(BpmnTestConstants::USER_TASK_ID)
            ->sequenceFlowId("s3")
            ->endEvent(BpmnTestConstants::END_EVENT_ID)
            ->moveToNode(BpmnTestConstants::USER_TASK_ID)
            ->sequenceFlowId("s4")
            ->connectTo("gateway")
            ->done();

        $allEdges = $this->instance->getModelElementsByType(BpmnEdgeInterface::class);
        $this->assertCount(4, $allEdges);

        $this->assertBpmnEdgeExists("s1");
        $this->assertBpmnEdgeExists("s2");
        $this->assertBpmnEdgeExists("s3");
        $this->assertBpmnEdgeExists("s4");
    }

    protected function findBpmnEdge(string $sequenceFlowId): ?BpmnEdgeInterface
    {
        $allEdges = $this->instance->getModelElementsByType(BpmnEdgeInterface::class);
        foreach ($allEdges as $edge) {
            if ($edge->getBpmnElement()->getId() == $sequenceFlowId) {
                return $edge;
            }
        }
        return null;
    }

    protected function assertBpmnEdgeExists(string $id): void
    {
        $edge = $this->findBpmnEdge($id);
        $this->assertFalse($edge == null);
    }
}
