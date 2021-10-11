<?php

namespace Tests\Bpmn\Builder;

use PHPUnit\Framework\TestCase;
use Tests\Bpmn\BpmnTestConstants;
use BpmPlatform\Model\Bpmn\Bpmn;
use BpmPlatform\Model\Bpmn\Instance\Bpmndi\{
    BpmnDiagramInterface,
    BpmnShapeInterface
};

class DiGeneratorForFlowNodesTest extends TestCase
{
    private $instance;

    protected function tearDown(): void
    {
        if ($this->instance != null) {
            Bpmn::getInstance()->validateModel($this->instance);
        }
    }

    public function testShouldGeneratePlaneForProcess(): void
    {
        // when
        $this->instance = Bpmn::getInstance()->createExecutableProcess("process")->done();

        // then
        $bpmnDiagrams = $this->instance->getModelElementsByType(BpmnDiagramInterface::class);
        $this->assertCount(1, $bpmnDiagrams);

        $diagram = $bpmnDiagrams[0];
        $this->assertFalse($diagram->getId() == null);

        $this->assertFalse($diagram->getBpmnPlane() == null);
        $this->assertTrue($diagram->getBpmnPlane()->getBpmnElement()->equals($this->instance->getModelElementById("process")));
    }

    public function testShouldGenerateShapeForStartEvent(): void
    {
        // given
        $processBuilder = Bpmn::getInstance()->createExecutableProcess();
        // when
        $this->instance = $processBuilder
                      ->startEvent(BpmnTestConstants::START_EVENT_ID)
                      ->endEvent(BpmnTestConstants::END_EVENT_ID)
                      ->done();

        // then
        $allShapes = $this->instance->getModelElementsByType(BpmnShapeInterface::class);
        $this->assertCount(2, $allShapes);

        $this->assertEventShapeProperties(BpmnTestConstants::START_EVENT_ID);
    }

    public function testShouldGenerateShapeForUserTask(): void
    {
        // given
        $processBuilder = Bpmn::getInstance()->createExecutableProcess();

        // when
        $this->instance = $processBuilder
                      ->startEvent(BpmnTestConstants::START_EVENT_ID)
                      ->userTask(BpmnTestConstants::USER_TASK_ID)
                      ->done();

        // then
        $allShapes = $this->instance->getModelElementsByType(BpmnShapeInterface::class);
        $this->assertCount(2, $allShapes);

        $this->assertTaskShapeProperties(BpmnTestConstants::USER_TASK_ID);
    }

    public function testShouldGenerateShapeForSendTask(): void
    {
        // given
        $processBuilder = Bpmn::getInstance()->createExecutableProcess();

        // when
        $this->instance = $processBuilder
                      ->startEvent(BpmnTestConstants::START_EVENT_ID)
                      ->sendTask(BpmnTestConstants::SEND_TASK_ID)
                      ->done();

        // then
        $allShapes = $this->instance->getModelElementsByType(BpmnShapeInterface::class);
        $this->assertCount(2, $allShapes);

        $this->assertTaskShapeProperties(BpmnTestConstants::SEND_TASK_ID);
    }

    public function testShouldGenerateShapeForServiceTask(): void
    {
        // given
        $processBuilder = Bpmn::getInstance()->createExecutableProcess();

        // when
        $this->instance = $processBuilder
                       ->startEvent(BpmnTestConstants::START_EVENT_ID)
                       ->serviceTask(BpmnTestConstants::SERVICE_TASK_ID)
                       ->done();

        // then
        $allShapes = $this->instance->getModelElementsByType(BpmnShapeInterface::class);
        $this->assertCount(2, $allShapes);

        $this->assertTaskShapeProperties(BpmnTestConstants::SERVICE_TASK_ID);
    }

    public function testShouldGenerateShapeForReceiveTask(): void
    {
        // given
        $processBuilder = Bpmn::getInstance()->createExecutableProcess();

        // when
        $this->instance = $processBuilder
                      ->startEvent(BpmnTestConstants::START_EVENT_ID)
                      ->receiveTask(BpmnTestConstants::TASK_ID)
                      ->done();

        // then
        $allShapes = $this->instance->getModelElementsByType(BpmnShapeInterface::class);
        $this->assertCount(2, $allShapes);

        $this->assertTaskShapeProperties(BpmnTestConstants::TASK_ID);
    }

    public function testShouldGenerateShapeForManualTask(): void
    {
        // given
        $processBuilder = Bpmn::getInstance()->createExecutableProcess();

        // when
        $this->instance = $processBuilder
                      ->startEvent(BpmnTestConstants::START_EVENT_ID)
                      ->manualTask(BpmnTestConstants::TASK_ID)
                      ->done();

        // then
        $allShapes = $this->instance->getModelElementsByType(BpmnShapeInterface::class);
        $this->assertCount(2, $allShapes);

        $this->assertTaskShapeProperties(BpmnTestConstants::TASK_ID);
    }

    public function testShouldGenerateShapeForBusinessRuleTask(): void
    {
        // given
        $processBuilder = Bpmn::getInstance()->createExecutableProcess();

        // when
        $this->instance = $processBuilder
                      ->startEvent(BpmnTestConstants::START_EVENT_ID)
                      ->businessRuleTask(BpmnTestConstants::TASK_ID)
                      ->done();

        // then
        $allShapes = $this->instance->getModelElementsByType(BpmnShapeInterface::class);
        $this->assertCount(2, $allShapes);

        $this->assertTaskShapeProperties(BpmnTestConstants::TASK_ID);
    }

    public function testShouldGenerateShapeForScriptTask(): void
    {
        // given
        $processBuilder = Bpmn::getInstance()->createExecutableProcess();

        // when
        $this->instance = $processBuilder
                      ->startEvent(BpmnTestConstants::START_EVENT_ID)
                      ->scriptTask(BpmnTestConstants::TASK_ID)
                      ->done();

        // then
        $allShapes = $this->instance->getModelElementsByType(BpmnShapeInterface::class);
        $this->assertCount(2, $allShapes);

        $this->assertTaskShapeProperties(BpmnTestConstants::TASK_ID);
    }

    public function testShouldGenerateShapeForCatchingIntermediateEvent(): void
    {
        // given
        $processBuilder = Bpmn::getInstance()->createExecutableProcess();

        // when
        $this->instance = $processBuilder
                      ->startEvent(BpmnTestConstants::START_EVENT_ID)
                      ->intermediateCatchEvent(BpmnTestConstants::CATCH_ID)
                      ->endEvent(BpmnTestConstants::END_EVENT_ID)
                      ->done();

        // then
        $allShapes = $this->instance->getModelElementsByType(BpmnShapeInterface::class);
        $this->assertCount(3, $allShapes);

        $this->assertEventShapeProperties(BpmnTestConstants::CATCH_ID);
    }

    public function testShouldGenerateShapeForBoundaryIntermediateEvent(): void
    {
        // given
        $processBuilder = Bpmn::getInstance()->createExecutableProcess();

        // when
        $this->instance = $processBuilder
                      ->startEvent(BpmnTestConstants::START_EVENT_ID)
                      ->userTask(BpmnTestConstants::USER_TASK_ID)
                      ->endEvent(BpmnTestConstants::END_EVENT_ID)
                        ->moveToActivity(BpmnTestConstants::USER_TASK_ID)
                          ->boundaryEvent(BpmnTestConstants::BOUNDARY_ID)
                            ->conditionalEventDefinition(BpmnTestConstants::CONDITION_ID)
                              ->condition(BpmnTestConstants::TEST_CONDITION)
                            ->conditionalEventDefinitionDone()
                          ->endEvent()
                     ->done();

        // then
        $allShapes = $this->instance->getModelElementsByType(BpmnShapeInterface::class);
        $this->assertCount(5, $allShapes);

        $this->assertEventShapeProperties(BpmnTestConstants::BOUNDARY_ID);
    }

    public function testShouldGenerateShapeForThrowingIntermediateEvent(): void
    {
        // given
        $processBuilder = Bpmn::getInstance()->createExecutableProcess();

        // when
        $this->instance = $processBuilder
                      ->startEvent(BpmnTestConstants::START_EVENT_ID)
                      ->intermediateThrowEvent("inter")
                      ->endEvent(BpmnTestConstants::END_EVENT_ID)->done();

        // then
        $allShapes = $this->instance->getModelElementsByType(BpmnShapeInterface::class);
        $this->assertCount(3, $allShapes);

        $this->assertEventShapeProperties("inter");
    }

    public function testShouldGenerateShapeForEndEvent(): void
    {
        // given
        $processBuilder = Bpmn::getInstance()->createExecutableProcess();

        // when
        $this->instance = $processBuilder
                      ->startEvent(BpmnTestConstants::START_EVENT_ID)
                      ->endEvent(BpmnTestConstants::END_EVENT_ID)
                      ->done();

        // then
        $allShapes = $this->instance->getModelElementsByType(BpmnShapeInterface::class);
        $this->assertCount(2, $allShapes);

        $this->assertEventShapeProperties(BpmnTestConstants::END_EVENT_ID);
    }

    public function testShouldGenerateShapeForBlankSubProcess(): void
    {
        // given
        $processBuilder = Bpmn::getInstance()->createExecutableProcess();

        // when
        $this->instance = $processBuilder
                      ->startEvent(BpmnTestConstants::START_EVENT_ID)
                      ->subProcess(BpmnTestConstants::SUB_PROCESS_ID)
                      ->endEvent(BpmnTestConstants::END_EVENT_ID)
                      ->done();

        // then
        $allShapes = $this->instance->getModelElementsByType(BpmnShapeInterface::class);
        $this->assertCount(3, $allShapes);

        $bpmnShapeSubProcess = $this->findBpmnShape(BpmnTestConstants::SUB_PROCESS_ID);
        $this->assertFalse($bpmnShapeSubProcess == null);
        $this->assertSubProcessSize($bpmnShapeSubProcess);
        $this->assertTrue($bpmnShapeSubProcess->isExpanded());
    }

    public function testShouldGenerateShapesForNestedFlowNodes(): void
    {
        // given
        $processBuilder = Bpmn::getInstance()->createExecutableProcess();

        // when
        $this->instance = $processBuilder
                      ->startEvent(BpmnTestConstants::START_EVENT_ID)
                      ->subProcess(BpmnTestConstants::SUB_PROCESS_ID)
                        ->embeddedSubProcess()
                          ->startEvent("innerStartEvent")
                          ->userTask("innerUserTask")
                          ->endEvent("innerEndEvent")
                        ->subProcessDone()
                      ->endEvent(BpmnTestConstants::END_EVENT_ID)
                      ->done();

        // then
        $allShapes = $this->instance->getModelElementsByType(BpmnShapeInterface::class);
        $this->assertCount(6, $allShapes);

        $this->assertEventShapeProperties("innerStartEvent");
        $this->assertTaskShapeProperties("innerUserTask");
        $this->assertEventShapeProperties("innerEndEvent");

        $bpmnShapeSubProcess = $this->findBpmnShape(BpmnTestConstants::SUB_PROCESS_ID);
        $this->assertFalse($bpmnShapeSubProcess == null);
        $this->assertTrue($bpmnShapeSubProcess->isExpanded());
    }

    public function testShouldGenerateShapeForEventSubProcess(): void
    {
        // given
        $processBuilder = Bpmn::getInstance()->createExecutableProcess();

        // when
        $this->instance = $processBuilder
                      ->startEvent(BpmnTestConstants::START_EVENT_ID)
                      ->endEvent(BpmnTestConstants::END_EVENT_ID)
                      ->subProcess(BpmnTestConstants::SUB_PROCESS_ID)
                        ->triggerByEvent()
                          ->embeddedSubProcess()
                            ->startEvent("innerStartEvent")
                            ->endEvent("innerEndEvent")
                          ->subProcessDone()
                        ->done();

        // then
        $allShapes = $this->instance->getModelElementsByType(BpmnShapeInterface::class);
        $this->assertCount(5, $allShapes);

        $this->assertEventShapeProperties("innerStartEvent");
        $this->assertEventShapeProperties("innerEndEvent");

        $bpmnShapeEventSubProcess = $this->findBpmnShape(BpmnTestConstants::SUB_PROCESS_ID);
        $this->assertFalse($bpmnShapeEventSubProcess == null);
        $this->assertTrue($bpmnShapeEventSubProcess->isExpanded());
    }

    public function testShouldGenerateShapeForCallActivity(): void
    {
        // given
        $processBuilder = Bpmn::getInstance()->createExecutableProcess();

        // when
        $this->instance = $processBuilder
                      ->startEvent(BpmnTestConstants::START_EVENT_ID)
                      ->callActivity(BpmnTestConstants::CALL_ACTIVITY_ID)
                      ->endEvent(BpmnTestConstants::END_EVENT_ID)
                      ->done();

        // then
        $allShapes = $this->instance->getModelElementsByType(BpmnShapeInterface::class);
        $this->assertCount(3, $allShapes);

        $this->assertTaskShapeProperties(BpmnTestConstants::CALL_ACTIVITY_ID);
    }

    public function testShouldGenerateShapeForTransaction(): void
    {
        // given
        $processBuilder = Bpmn::getInstance()->createExecutableProcess();

        // when
        $this->instance = $processBuilder
                      ->startEvent(BpmnTestConstants::START_EVENT_ID)
                      ->transaction(BpmnTestConstants::TRANSACTION_ID)
                      ->embeddedSubProcess()
                        ->startEvent("innerStartEvent")
                        ->userTask("innerUserTask")
                        ->endEvent("innerEndEvent")
                      ->transactionDone()
                      ->endEvent(BpmnTestConstants::END_EVENT_ID)
                      ->done();

        // then
        $allShapes = $this->instance->getModelElementsByType(BpmnShapeInterface::class);
        $this->assertCount(6, $allShapes);

        $this->assertEventShapeProperties("innerStartEvent");
        $this->assertTaskShapeProperties("innerUserTask");
        $this->assertEventShapeProperties("innerEndEvent");

        $bpmnShapeSubProcess = $this->findBpmnShape(BpmnTestConstants::TRANSACTION_ID);
        $this->assertFalse($bpmnShapeSubProcess == null);
        $this->assertTrue($bpmnShapeSubProcess->isExpanded());
    }

    public function testShouldGenerateShapeForParallelGateway(): void
    {
        // given
        $processBuilder = Bpmn::getInstance()->createExecutableProcess();

        // when
        $this->instance = $processBuilder
                      ->startEvent(BpmnTestConstants::START_EVENT_ID)
                      ->parallelGateway("and")
                      ->endEvent(BpmnTestConstants::END_EVENT_ID)
                      ->done();

        // then
        $allShapes = $this->instance->getModelElementsByType(BpmnShapeInterface::class);
        $this->assertCount(3, $allShapes);

        $this->assertGatewayShapeProperties("and");
    }

    public function testShouldGenerateShapeForInclusiveGateway(): void
    {
        // given
        $processBuilder = Bpmn::getInstance()->createExecutableProcess();

        // when
        $this->instance = $processBuilder
                      ->startEvent(BpmnTestConstants::START_EVENT_ID)
                      ->inclusiveGateway("inclusive")
                      ->endEvent(BpmnTestConstants::END_EVENT_ID)
                      ->done();

        // then
        $allShapes = $this->instance->getModelElementsByType(BpmnShapeInterface::class);
        $this->assertCount(3, $allShapes);

        $this->assertGatewayShapeProperties("inclusive");
    }

    public function testShouldGenerateShapeForEventBasedGateway(): void
    {
        // given
        $processBuilder = Bpmn::getInstance()->createExecutableProcess();

        // when
        $this->instance = $processBuilder
                      ->startEvent(BpmnTestConstants::START_EVENT_ID)
                      ->eventBasedGateway()
                        ->id("eventBased")
                      ->endEvent(BpmnTestConstants::END_EVENT_ID)
                      ->done();

        // then
        $allShapes = $this->instance->getModelElementsByType(BpmnShapeInterface::class);
        $this->assertCount(3, $allShapes);

        $this->assertGatewayShapeProperties("eventBased");
    }

    public function testShouldGenerateShapeForExclusiveGateway(): void
    {
        // given
        $processBuilder = Bpmn::getInstance()->createExecutableProcess();

        // when
        $this->instance = $processBuilder
                      ->startEvent(BpmnTestConstants::START_EVENT_ID)
                      ->exclusiveGateway("or")
                      ->endEvent(BpmnTestConstants::END_EVENT_ID)
                      ->done();

        // then
        $allShapes = $this->instance->getModelElementsByType(BpmnShapeInterface::class);
        $this->assertCount(3, $allShapes);

        $this->assertGatewayShapeProperties("or");
        $bpmnShape = $this->findBpmnShape("or");
        $this->assertTrue($bpmnShape->isMarkerVisible());
    }

    protected function assertTaskShapeProperties(string $id): void
    {
        $bpmnShapeTask = $this->findBpmnShape($id);
        $this->assertFalse($bpmnShapeTask == null);
        $this->assertActivitySize($bpmnShapeTask);
    }

    protected function assertEventShapeProperties(string $id): void
    {
        $bpmnShapeEvent = $this->findBpmnShape($id);
        $this->assertFalse($bpmnShapeEvent == null);
        $this->assertEventSize($bpmnShapeEvent);
    }

    protected function assertGatewayShapeProperties(string $id): void
    {
        $bpmnShapeGateway = $this->findBpmnShape($id);
        $this->assertFalse($bpmnShapeGateway == null);
        $this->assertGatewaySize($bpmnShapeGateway);
    }

    protected function findBpmnShape(string $id): ?BpmnShapeInterface
    {
        $allShapes = $this->instance->getModelElementsByType(BpmnShapeInterface::class);

        foreach ($allShapes as $shape) {
            if ($shape->getBpmnElement()->getId() == $id) {
                return $shape;
            }
        }
        return null;
    }

    protected function assertEventSize(BpmnShapeInterface $shape): void
    {
        $this->assertSize($shape, 36, 36);
    }

    protected function assertGatewaySize(BpmnShapeInterface $shape): void
    {
        $this->assertSize($shape, 50, 50);
    }

    protected function assertSubProcessSize(BpmnShapeInterface $shape): void
    {
        $this->assertSize($shape, 200, 350);
    }

    protected function assertActivitySize(BpmnShapeInterface $shape): void
    {
        $this->assertSize($shape, 80, 100);
    }

    protected function assertSize(BpmnShapeInterface $shape, int $height, int $width): void
    {
        $this->assertEquals($height, $shape->getBounds()->getHeight());
        $this->assertEquals($width, $shape->getBounds()->getWidth());
    }
}
