<?php

namespace Tests\Bpmn\Builder;

use PHPUnit\Framework\TestCase;
use Tests\Bpmn\BpmnTestConstants;
use BpmPlatform\Model\Bpmn\Bpmn;
use BpmPlatform\Model\Bpmn\Instance\Dc\BoundsInterface;
use BpmPlatform\Model\Bpmn\Instance\Di\WaypointInterface;
use BpmPlatform\Model\Bpmn\Instance\Bpmndi\{
    BpmnEdgeInterface,
    BpmnShapeInterface
};

class CoordinatesGenerationTest extends TestCase
{
    private $instance;

    public function testShouldPlaceStartEvent(): void
    {
        $builder = Bpmn::getInstance()->createExecutableProcess();

        $this->instance = $builder
            ->startEvent(BpmnTestConstants::START_EVENT_ID)
            ->done();

        $startBounds = $this->findBpmnShape(BpmnTestConstants::START_EVENT_ID)->getBounds();
        $this->assertShapeCoordinates($startBounds, 100, 100);
    }

    public function testShouldPlaceUserTask(): void
    {
        $builder = Bpmn::getInstance()->createExecutableProcess();

        $this->instance = $builder
            ->startEvent(BpmnTestConstants::START_EVENT_ID)
            ->sequenceFlowId(BpmnTestConstants::SEQUENCE_FLOW_ID)
            ->userTask(BpmnTestConstants::USER_TASK_ID)
            ->done();

        $userTaskBounds = $this->findBpmnShape(BpmnTestConstants::USER_TASK_ID)->getBounds();
        $this->assertShapeCoordinates($userTaskBounds, 186, 78);

        $sequenceFlowWaypoints = $this->findBpmnEdge(BpmnTestConstants::SEQUENCE_FLOW_ID)->getWaypoints();

        $waypoint = $sequenceFlowWaypoints[0];
        $this->assertWaypointCoordinates($waypoint, 136, 118);

        $waypoint = $sequenceFlowWaypoints[count($sequenceFlowWaypoints) - 1];

        $this->assertWaypointCoordinates($waypoint, 186, 118);
    }

    public function testShouldPlaceSendTask(): void
    {
        $builder = Bpmn::getInstance()->createExecutableProcess();

        $this->instance = $builder
            ->startEvent(BpmnTestConstants::START_EVENT_ID)
            ->sequenceFlowId(BpmnTestConstants::SEQUENCE_FLOW_ID)
            ->sendTask(BpmnTestConstants::SEND_TASK_ID)
            ->done();

        $sendTaskBounds = $this->findBpmnShape(BpmnTestConstants::SEND_TASK_ID)->getBounds();
        $this->assertShapeCoordinates($sendTaskBounds, 186, 78);

        $sequenceFlowWaypoints = $this->findBpmnEdge(BpmnTestConstants::SEQUENCE_FLOW_ID)->getWaypoints();

        $waypoint = $sequenceFlowWaypoints[0];
        $this->assertWaypointCoordinates($waypoint, 136, 118);

        $waypoint = $sequenceFlowWaypoints[count($sequenceFlowWaypoints) - 1];
        $this->assertWaypointCoordinates($waypoint, 186, 118);
    }

    public function testShouldPlaceServiceTask(): void
    {
        $builder = Bpmn::getInstance()->createExecutableProcess();

        $this->instance = $builder
            ->startEvent(BpmnTestConstants::START_EVENT_ID)
            ->sequenceFlowId(BpmnTestConstants::SEQUENCE_FLOW_ID)
            ->serviceTask(BpmnTestConstants::SERVICE_TASK_ID)
            ->done();

        $serviceTaskBounds = $this->findBpmnShape(BpmnTestConstants::SERVICE_TASK_ID)->getBounds();
        $this->assertShapeCoordinates($serviceTaskBounds, 186, 78);

        $sequenceFlowWaypoints = $this->findBpmnEdge(BpmnTestConstants::SEQUENCE_FLOW_ID)->getWaypoints();

        $waypoint = $sequenceFlowWaypoints[0];
        $this->assertWaypointCoordinates($waypoint, 136, 118);

        $waypoint = $sequenceFlowWaypoints[count($sequenceFlowWaypoints) - 1];
        $this->assertWaypointCoordinates($waypoint, 186, 118);
    }

    public function testShouldPlaceReceiveTask(): void
    {
        $builder = Bpmn::getInstance()->createExecutableProcess();

        $this->instance = $builder
            ->startEvent(BpmnTestConstants::START_EVENT_ID)
            ->sequenceFlowId(BpmnTestConstants::SEQUENCE_FLOW_ID)
            ->receiveTask(BpmnTestConstants::TASK_ID)
            ->done();

        $receiveTaskBounds = $this->findBpmnShape(BpmnTestConstants::TASK_ID)->getBounds();
        $this->assertShapeCoordinates($receiveTaskBounds, 186, 78);

        $sequenceFlowWaypoints = $this->findBpmnEdge(BpmnTestConstants::SEQUENCE_FLOW_ID)->getWaypoints();

        $waypoint = $sequenceFlowWaypoints[0];
        $this->assertWaypointCoordinates($waypoint, 136, 118);

        $waypoint = $sequenceFlowWaypoints[count($sequenceFlowWaypoints) - 1];
        $this->assertWaypointCoordinates($waypoint, 186, 118);
    }

    public function testShouldPlaceManualTask(): void
    {
        $builder = Bpmn::getInstance()->createExecutableProcess();

        $this->instance = $builder
            ->startEvent(BpmnTestConstants::START_EVENT_ID)
            ->sequenceFlowId(BpmnTestConstants::SEQUENCE_FLOW_ID)
            ->manualTask(BpmnTestConstants::TASK_ID)
            ->done();

        $manualTaskBounds = $this->findBpmnShape(BpmnTestConstants::TASK_ID)->getBounds();
        $this->assertShapeCoordinates($manualTaskBounds, 186, 78);

        $sequenceFlowWaypoints = $this->findBpmnEdge(BpmnTestConstants::SEQUENCE_FLOW_ID)->getWaypoints();

        $waypoint = $sequenceFlowWaypoints[0];
        $this->assertWaypointCoordinates($waypoint, 136, 118);

        $waypoint = $sequenceFlowWaypoints[count($sequenceFlowWaypoints) - 1];
        $this->assertWaypointCoordinates($waypoint, 186, 118);
    }

    public function testShouldPlaceBusinessRuleTask(): void
    {
        $builder = Bpmn::getInstance()->createExecutableProcess();

        $this->instance = $builder
            ->startEvent(BpmnTestConstants::START_EVENT_ID)
            ->sequenceFlowId(BpmnTestConstants::SEQUENCE_FLOW_ID)
            ->businessRuleTask(BpmnTestConstants::TASK_ID)
            ->done();

        $businessRuleTaskBounds = $this->findBpmnShape(BpmnTestConstants::TASK_ID)->getBounds();
        $this->assertShapeCoordinates($businessRuleTaskBounds, 186, 78);

        $sequenceFlowWaypoints = $this->findBpmnEdge(BpmnTestConstants::SEQUENCE_FLOW_ID)->getWaypoints();

        $waypoint = $sequenceFlowWaypoints[0];
        $this->assertWaypointCoordinates($waypoint, 136, 118);

        $waypoint = $sequenceFlowWaypoints[count($sequenceFlowWaypoints) - 1];
        $this->assertWaypointCoordinates($waypoint, 186, 118);
    }

    public function testShouldPlaceScriptTask(): void
    {
        $builder = Bpmn::getInstance()->createExecutableProcess();

        $this->instance = $builder
            ->startEvent(BpmnTestConstants::START_EVENT_ID)
            ->sequenceFlowId(BpmnTestConstants::SEQUENCE_FLOW_ID)
            ->scriptTask(BpmnTestConstants::TASK_ID)
            ->done();

        $scriptTaskBounds = $this->findBpmnShape(BpmnTestConstants::TASK_ID)->getBounds();
        $this->assertShapeCoordinates($scriptTaskBounds, 186, 78);

        $sequenceFlowWaypoints = $this->findBpmnEdge(BpmnTestConstants::SEQUENCE_FLOW_ID)->getWaypoints();

        $waypoint = $sequenceFlowWaypoints[0];
        $this->assertWaypointCoordinates($waypoint, 136, 118);

        $waypoint = $sequenceFlowWaypoints[count($sequenceFlowWaypoints) - 1];
        $this->assertWaypointCoordinates($waypoint, 186, 118);
    }

    public function testShouldPlaceCatchingIntermediateEvent(): void
    {
        $builder = Bpmn::getInstance()->createExecutableProcess();

        $this->instance = $builder
            ->startEvent(BpmnTestConstants::START_EVENT_ID)
            ->sequenceFlowId(BpmnTestConstants::SEQUENCE_FLOW_ID)
            ->intermediateCatchEvent("id")
            ->done();

        $catchEventBounds = $this->findBpmnShape("id")->getBounds();
        $this->assertShapeCoordinates($catchEventBounds, 186, 100);

        $sequenceFlowWaypoints = $this->findBpmnEdge(BpmnTestConstants::SEQUENCE_FLOW_ID)->getWaypoints();

        $waypoint = $sequenceFlowWaypoints[0];
        $this->assertWaypointCoordinates($waypoint, 136, 118);

        $waypoint = $sequenceFlowWaypoints[count($sequenceFlowWaypoints) - 1];
        $this->assertWaypointCoordinates($waypoint, 186, 118);
    }

    public function testShouldPlaceThrowingIntermediateEvent(): void
    {
        $builder = Bpmn::getInstance()->createExecutableProcess();

        $this->instance = $builder
            ->startEvent(BpmnTestConstants::START_EVENT_ID)
            ->sequenceFlowId(BpmnTestConstants::SEQUENCE_FLOW_ID)
            ->intermediateThrowEvent("id")
            ->done();

        $throwEventBounds = $this->findBpmnShape("id")->getBounds();
        $this->assertShapeCoordinates($throwEventBounds, 186, 100);

        $sequenceFlowWaypoints = $this->findBpmnEdge(BpmnTestConstants::SEQUENCE_FLOW_ID)->getWaypoints();

        $waypoint = $sequenceFlowWaypoints[0];
        $this->assertWaypointCoordinates($waypoint, 136, 118);

        $waypoint = $sequenceFlowWaypoints[count($sequenceFlowWaypoints) - 1];
        $this->assertWaypointCoordinates($waypoint, 186, 118);
    }

    public function testShouldPlaceEndEvent(): void
    {
        $builder = Bpmn::getInstance()->createExecutableProcess();

        $this->instance = $builder
            ->startEvent(BpmnTestConstants::START_EVENT_ID)
            ->sequenceFlowId(BpmnTestConstants::SEQUENCE_FLOW_ID)
            ->endEvent("id")
            ->done();

        $endEventBounds = $this->findBpmnShape("id")->getBounds();
        $this->assertShapeCoordinates($endEventBounds, 186, 100);

        $sequenceFlowWaypoints = $this->findBpmnEdge(BpmnTestConstants::SEQUENCE_FLOW_ID)->getWaypoints();

        $waypoint = $sequenceFlowWaypoints[0];
        $this->assertWaypointCoordinates($waypoint, 136, 118);

        $waypoint = $sequenceFlowWaypoints[count($sequenceFlowWaypoints) - 1];
        $this->assertWaypointCoordinates($waypoint, 186, 118);
    }

    public function testShouldPlaceCallActivity(): void
    {
        $builder = Bpmn::getInstance()->createExecutableProcess();

        $this->instance = $builder
            ->startEvent(BpmnTestConstants::START_EVENT_ID)
            ->sequenceFlowId(BpmnTestConstants::SEQUENCE_FLOW_ID)
            ->callActivity("id")
            ->done();

        $callActivity = $this->findBpmnShape("id")->getBounds();
        $this->assertShapeCoordinates($callActivity, 186, 78);

        $sequenceFlowWaypoints = $this->findBpmnEdge(BpmnTestConstants::SEQUENCE_FLOW_ID)->getWaypoints();

        $waypoint = $sequenceFlowWaypoints[0];
        $this->assertWaypointCoordinates($waypoint, 136, 118);

        $waypoint = $sequenceFlowWaypoints[count($sequenceFlowWaypoints) - 1];
        $this->assertWaypointCoordinates($waypoint, 186, 118);
    }

    public function testShouldPlaceExclusiveGateway(): void
    {
        $builder = Bpmn::getInstance()->createExecutableProcess();

        $this->instance = $builder
            ->startEvent(BpmnTestConstants::START_EVENT_ID)
            ->sequenceFlowId(BpmnTestConstants::SEQUENCE_FLOW_ID)
            ->exclusiveGateway("id")
            ->done();

        $exclusiveGateway = $this->findBpmnShape("id")->getBounds();
        $this->assertShapeCoordinates($exclusiveGateway, 186, 93);

        $sequenceFlowWaypoints = $this->findBpmnEdge(BpmnTestConstants::SEQUENCE_FLOW_ID)->getWaypoints();

        $waypoint = $sequenceFlowWaypoints[0];
        $this->assertWaypointCoordinates($waypoint, 136, 118);

        $waypoint = $sequenceFlowWaypoints[count($sequenceFlowWaypoints) - 1];
        $this->assertWaypointCoordinates($waypoint, 186, 118);
    }

    public function testShouldPlaceInclusiveGateway(): void
    {
        $builder = Bpmn::getInstance()->createExecutableProcess();

        $this->instance = $builder
            ->startEvent(BpmnTestConstants::START_EVENT_ID)
            ->sequenceFlowId(BpmnTestConstants::SEQUENCE_FLOW_ID)
            ->inclusiveGateway("id")
            ->done();

        $inclusiveGateway = $this->findBpmnShape("id")->getBounds();
        $this->assertShapeCoordinates($inclusiveGateway, 186, 93);

        $sequenceFlowWaypoints = $this->findBpmnEdge(BpmnTestConstants::SEQUENCE_FLOW_ID)->getWaypoints();

        $waypoint = $sequenceFlowWaypoints[0];
        $this->assertWaypointCoordinates($waypoint, 136, 118);

        $waypoint = $sequenceFlowWaypoints[count($sequenceFlowWaypoints) - 1];
        $this->assertWaypointCoordinates($waypoint, 186, 118);
    }

    public function testShouldPlaceParallelGateway(): void
    {
        $builder = Bpmn::getInstance()->createExecutableProcess();

        $this->instance = $builder
            ->startEvent(BpmnTestConstants::START_EVENT_ID)
            ->sequenceFlowId(BpmnTestConstants::SEQUENCE_FLOW_ID)
            ->parallelGateway("id")
            ->done();

        $parallelGateway = $this->findBpmnShape("id")->getBounds();
        $this->assertShapeCoordinates($parallelGateway, 186, 93);

        $sequenceFlowWaypoints = $this->findBpmnEdge(BpmnTestConstants::SEQUENCE_FLOW_ID)->getWaypoints();

        $waypoint = $sequenceFlowWaypoints[0];
        $this->assertWaypointCoordinates($waypoint, 136, 118);

        $waypoint = $sequenceFlowWaypoints[count($sequenceFlowWaypoints) - 1];
        $this->assertWaypointCoordinates($waypoint, 186, 118);
    }

    public function testShouldPlaceEventBasedGateway(): void
    {
        $builder = Bpmn::getInstance()->createExecutableProcess();

        $this->instance = $builder
            ->startEvent(BpmnTestConstants::START_EVENT_ID)
            ->sequenceFlowId(BpmnTestConstants::SEQUENCE_FLOW_ID)
            ->eventBasedGateway()
            ->id("id")
            ->done();

        $eventBasedGateway = $this->findBpmnShape("id")->getBounds();
        $this->assertShapeCoordinates($eventBasedGateway, 186, 93);

        $sequenceFlowWaypoints = $this->findBpmnEdge(BpmnTestConstants::SEQUENCE_FLOW_ID)->getWaypoints();

        $waypoint = $sequenceFlowWaypoints[0];
        $this->assertWaypointCoordinates($waypoint, 136, 118);

        $waypoint = $sequenceFlowWaypoints[count($sequenceFlowWaypoints) - 1];
        $this->assertWaypointCoordinates($waypoint, 186, 118);
    }

    public function testShouldPlaceSubProcess(): void
    {
        $builder = Bpmn::getInstance()->createExecutableProcess();

        $this->instance = $builder
            ->startEvent(BpmnTestConstants::START_EVENT_ID)
            ->sequenceFlowId(BpmnTestConstants::SEQUENCE_FLOW_ID)
            ->subProcess(BpmnTestConstants::SUB_PROCESS_ID)
            ->done();

        $subProcessBounds = $this->findBpmnShape(BpmnTestConstants::SUB_PROCESS_ID)->getBounds();
        $this->assertShapeCoordinates($subProcessBounds, 186, 18);

        $sequenceFlowWaypoints = $this->findBpmnEdge(BpmnTestConstants::SEQUENCE_FLOW_ID)->getWaypoints();

        $waypoint = $sequenceFlowWaypoints[0];
        $this->assertWaypointCoordinates($waypoint, 136, 118);

        $waypoint = $sequenceFlowWaypoints[count($sequenceFlowWaypoints) - 1];
        $this->assertWaypointCoordinates($waypoint, 186, 118);
    }

    public function testShouldPlaceBoundaryEventForTask(): void
    {
        $builder = Bpmn::getInstance()->createExecutableProcess();

        $this->instance = $builder
            ->startEvent(BpmnTestConstants::START_EVENT_ID)
            ->userTask(BpmnTestConstants::USER_TASK_ID)
            ->boundaryEvent("boundary")
            ->sequenceFlowId(BpmnTestConstants::SEQUENCE_FLOW_ID)
            ->endEvent(BpmnTestConstants::END_EVENT_ID)
            ->moveToActivity(BpmnTestConstants::USER_TASK_ID)
            ->endEvent()
            ->done();

        $boundaryEventBounds = $this->findBpmnShape("boundary")->getBounds();
        $this->assertShapeCoordinates($boundaryEventBounds, 218, 140);
    }

    public function testShouldPlaceFollowingFlowNodeProperlyForTask(): void
    {
        $builder = Bpmn::getInstance()->createExecutableProcess();

        $this->instance = $builder
            ->startEvent(BpmnTestConstants::START_EVENT_ID)
            ->userTask(BpmnTestConstants::USER_TASK_ID)
            ->boundaryEvent("boundary")
            ->sequenceFlowId(BpmnTestConstants::SEQUENCE_FLOW_ID)
            ->endEvent(BpmnTestConstants::END_EVENT_ID)
            ->moveToActivity(BpmnTestConstants::USER_TASK_ID)
            ->endEvent()
            ->done();

        $endEventBounds = $this->findBpmnShape(BpmnTestConstants::END_EVENT_ID)->getBounds();
        $this->assertShapeCoordinates($endEventBounds, 266.5, 208);

        $sequenceFlowWaypoints = $this->findBpmnEdge(BpmnTestConstants::SEQUENCE_FLOW_ID)->getWaypoints();
        $waypoint = $sequenceFlowWaypoints[0];
        $this->assertWaypointCoordinates($waypoint, 236, 176);

        $waypoint = $sequenceFlowWaypoints[count($sequenceFlowWaypoints) - 1];

        $this->assertWaypointCoordinates($waypoint, 266.5, 226);
    }

    public function testShouldPlaceTwoBoundaryEventsForTask(): void
    {
        $builder = Bpmn::getInstance()->createExecutableProcess();

        $this->instance = $builder
            ->startEvent(BpmnTestConstants::START_EVENT_ID)
            ->userTask(BpmnTestConstants::USER_TASK_ID)
            ->boundaryEvent("boundary1")
            ->sequenceFlowId(BpmnTestConstants::SEQUENCE_FLOW_ID)
            ->endEvent(BpmnTestConstants::END_EVENT_ID)
            ->moveToActivity(BpmnTestConstants::USER_TASK_ID)
            ->endEvent()
            ->moveToActivity(BpmnTestConstants::USER_TASK_ID)
            ->boundaryEvent("boundary2")
            ->done();

        $boundaryEvent1Bounds = $this->findBpmnShape("boundary1")->getBounds();
        $this->assertShapeCoordinates($boundaryEvent1Bounds, 218, 140);

        $boundaryEvent2Bounds = $this->findBpmnShape("boundary2")->getBounds();
        $this->assertShapeCoordinates($boundaryEvent2Bounds, 254, 140);
    }

    public function testShouldPlaceThreeBoundaryEventsForTask(): void
    {
        $builder = Bpmn::getInstance()->createExecutableProcess();

        $this->instance = $builder
            ->startEvent(BpmnTestConstants::START_EVENT_ID)
            ->userTask(BpmnTestConstants::USER_TASK_ID)
            ->boundaryEvent("boundary1")
            ->sequenceFlowId(BpmnTestConstants::SEQUENCE_FLOW_ID)
            ->endEvent(BpmnTestConstants::END_EVENT_ID)
            ->moveToActivity(BpmnTestConstants::USER_TASK_ID)
            ->endEvent()
            ->moveToActivity(BpmnTestConstants::USER_TASK_ID)
            ->boundaryEvent("boundary2")
            ->moveToActivity(BpmnTestConstants::USER_TASK_ID)
            ->boundaryEvent("boundary3")
            ->done();

        $boundaryEvent1Bounds = $this->findBpmnShape("boundary1")->getBounds();
        $this->assertShapeCoordinates($boundaryEvent1Bounds, 218, 140);

        $boundaryEvent2Bounds = $this->findBpmnShape("boundary2")->getBounds();
        $this->assertShapeCoordinates($boundaryEvent2Bounds, 254, 140);

        $boundaryEvent3Bounds = $this->findBpmnShape("boundary3")->getBounds();
        $this->assertShapeCoordinates($boundaryEvent3Bounds, 182, 140);
    }

    public function testShouldPlaceManyBoundaryEventsForTask(): void
    {
        $builder = Bpmn::getInstance()->createExecutableProcess();

        $this->instance = $builder
            ->startEvent(BpmnTestConstants::START_EVENT_ID)
            ->userTask(BpmnTestConstants::USER_TASK_ID)
            ->boundaryEvent("boundary1")
            ->sequenceFlowId(BpmnTestConstants::SEQUENCE_FLOW_ID)
            ->endEvent(BpmnTestConstants::END_EVENT_ID)
            ->moveToActivity(BpmnTestConstants::USER_TASK_ID)
            ->endEvent()
            ->moveToActivity(BpmnTestConstants::USER_TASK_ID)
            ->boundaryEvent("boundary2")
            ->moveToActivity(BpmnTestConstants::USER_TASK_ID)
            ->boundaryEvent("boundary3")
            ->moveToActivity(BpmnTestConstants::USER_TASK_ID)
            ->boundaryEvent("boundary4")
            ->done();

        $boundaryEvent1Bounds = $this->findBpmnShape("boundary1")->getBounds();
        $this->assertShapeCoordinates($boundaryEvent1Bounds, 218, 140);

        $boundaryEvent2Bounds = $this->findBpmnShape("boundary2")->getBounds();
        $this->assertShapeCoordinates($boundaryEvent2Bounds, 254, 140);

        $boundaryEvent3Bounds = $this->findBpmnShape("boundary3")->getBounds();
        $this->assertShapeCoordinates($boundaryEvent3Bounds, 182, 140);

        $boundaryEvent4Bounds = $this->findBpmnShape("boundary4")->getBounds();
        $this->assertShapeCoordinates($boundaryEvent4Bounds, 218, 140);
    }

    public function testShouldPlaceBoundaryEventForSubProcess(): void
    {
        $builder = Bpmn::getInstance()->createExecutableProcess();

        $this->instance = $builder
            ->startEvent(BpmnTestConstants::START_EVENT_ID)
            ->subProcess(BpmnTestConstants::SUB_PROCESS_ID)
            ->boundaryEvent("boundary")
            ->sequenceFlowId(BpmnTestConstants::SEQUENCE_FLOW_ID)
            ->endEvent(BpmnTestConstants::END_EVENT_ID)
            ->moveToActivity(BpmnTestConstants::SUB_PROCESS_ID)
            ->endEvent()
            ->done();

        $boundaryEventBounds = $this->findBpmnShape("boundary")->getBounds();
        $this->assertShapeCoordinates($boundaryEventBounds, 343, 200);
    }

    public function testShouldPlaceFollowingFlowNodeForSubProcess(): void
    {
        $builder = Bpmn::getInstance()->createExecutableProcess();

        $this->instance = $builder
            ->startEvent(BpmnTestConstants::START_EVENT_ID)
            ->subProcess(BpmnTestConstants::SUB_PROCESS_ID)
            ->boundaryEvent("boundary")
            ->sequenceFlowId(BpmnTestConstants::SEQUENCE_FLOW_ID)
            ->endEvent(BpmnTestConstants::END_EVENT_ID)
            ->moveToActivity(BpmnTestConstants::SUB_PROCESS_ID)
            ->endEvent()
            ->done();

        $endEventBounds = $this->findBpmnShape(BpmnTestConstants::END_EVENT_ID)->getBounds();
        $this->assertShapeCoordinates($endEventBounds, 391.5, 268);

        $sequenceFlowWaypoints = $this->findBpmnEdge(BpmnTestConstants::SEQUENCE_FLOW_ID)->getWaypoints();
        $waypoint = $sequenceFlowWaypoints[0];
        $this->assertWaypointCoordinates($waypoint, 361, 236);

        $waypoint = $sequenceFlowWaypoints[count($sequenceFlowWaypoints) - 1];

        $this->assertWaypointCoordinates($waypoint, 391.5, 286);
    }

    public function testShouldPlaceTwoBoundaryEventsForSubProcess(): void
    {
        $builder = Bpmn::getInstance()->createExecutableProcess();

        $this->instance = $builder
            ->startEvent(BpmnTestConstants::START_EVENT_ID)
            ->subProcess(BpmnTestConstants::SUB_PROCESS_ID)
            ->boundaryEvent("boundary1")
            ->moveToActivity(BpmnTestConstants::SUB_PROCESS_ID)
            ->boundaryEvent("boundary2")
            ->moveToActivity(BpmnTestConstants::SUB_PROCESS_ID)
            ->endEvent()
            ->done();

        $boundaryEvent1Bounds = $this->findBpmnShape("boundary1")->getBounds();
        $this->assertShapeCoordinates($boundaryEvent1Bounds, 343, 200);

        $boundaryEvent2Bounds = $this->findBpmnShape("boundary2")->getBounds();
        $this->assertShapeCoordinates($boundaryEvent2Bounds, 379, 200);
    }

    public function testShouldPlaceThreeBoundaryEventsForSubProcess(): void
    {
        $builder = Bpmn::getInstance()->createExecutableProcess();

        $this->instance = $builder
            ->startEvent(BpmnTestConstants::START_EVENT_ID)
            ->subProcess(BpmnTestConstants::SUB_PROCESS_ID)
            ->boundaryEvent("boundary1")
            ->moveToActivity(BpmnTestConstants::SUB_PROCESS_ID)
            ->boundaryEvent("boundary2")
            ->moveToActivity(BpmnTestConstants::SUB_PROCESS_ID)
            ->boundaryEvent("boundary3")
            ->moveToActivity(BpmnTestConstants::SUB_PROCESS_ID)
            ->endEvent()
            ->done();

        $boundaryEvent1Bounds = $this->findBpmnShape("boundary1")->getBounds();
        $this->assertShapeCoordinates($boundaryEvent1Bounds, 343, 200);

        $boundaryEvent2Bounds = $this->findBpmnShape("boundary2")->getBounds();
        $this->assertShapeCoordinates($boundaryEvent2Bounds, 379, 200);

        $boundaryEvent3Bounds = $this->findBpmnShape("boundary3")->getBounds();
        $this->assertShapeCoordinates($boundaryEvent3Bounds, 307, 200);
    }

    public function testShouldPlaceManyBoundaryEventsForSubProcess(): void
    {
        $builder = Bpmn::getInstance()->createExecutableProcess();

        $this->instance = $builder
            ->startEvent(BpmnTestConstants::START_EVENT_ID)
            ->subProcess(BpmnTestConstants::SUB_PROCESS_ID)
            ->boundaryEvent("boundary1")
            ->moveToActivity(BpmnTestConstants::SUB_PROCESS_ID)
            ->boundaryEvent("boundary2")
            ->moveToActivity(BpmnTestConstants::SUB_PROCESS_ID)
            ->boundaryEvent("boundary3")
            ->moveToActivity(BpmnTestConstants::SUB_PROCESS_ID)
            ->boundaryEvent("boundary4")
            ->moveToActivity(BpmnTestConstants::SUB_PROCESS_ID)
            ->endEvent()
            ->done();

        $boundaryEvent1Bounds = $this->findBpmnShape("boundary1")->getBounds();
        $this->assertShapeCoordinates($boundaryEvent1Bounds, 343, 200);

        $boundaryEvent2Bounds = $this->findBpmnShape("boundary2")->getBounds();
        $this->assertShapeCoordinates($boundaryEvent2Bounds, 379, 200);

        $boundaryEvent3Bounds = $this->findBpmnShape("boundary3")->getBounds();
        $this->assertShapeCoordinates($boundaryEvent3Bounds, 307, 200);

        $boundaryEvent4Bounds = $this->findBpmnShape("boundary4")->getBounds();
        $this->assertShapeCoordinates($boundaryEvent4Bounds, 343, 200);
    }

    public function testShouldPlaceTwoBranchesForParallelGateway(): void
    {
        $builder = Bpmn::getInstance()->createExecutableProcess();

        $this->instance = $builder
            ->startEvent(BpmnTestConstants::START_EVENT_ID)
            ->parallelGateway("id")
            ->sequenceFlowId("s1")
            ->userTask(BpmnTestConstants::USER_TASK_ID)
            ->moveToNode("id")
            ->sequenceFlowId("s2")
            ->endEvent(BpmnTestConstants::END_EVENT_ID)
            ->done();

        $userTaskBounds = $this->findBpmnShape(BpmnTestConstants::USER_TASK_ID)->getBounds();
        $this->assertShapeCoordinates($userTaskBounds, 286, 78);

        $endEventBounds = $this->findBpmnShape(BpmnTestConstants::END_EVENT_ID)->getBounds();
        $this->assertShapeCoordinates($endEventBounds, 286, 208);

        $sequenceFlowWaypoints = $this->findBpmnEdge("s2")->getWaypoints();
        $waypoint = $sequenceFlowWaypoints[0];
        $this->assertWaypointCoordinates($waypoint, 211, 143);

        $waypoint = $sequenceFlowWaypoints[count($sequenceFlowWaypoints) - 1];

        $this->assertWaypointCoordinates($waypoint, 286, 226);
    }

    public function testShouldPlaceThreeBranchesForParallelGateway(): void
    {
        $builder = Bpmn::getInstance()->createExecutableProcess();

        $this->instance = $builder
            ->startEvent(BpmnTestConstants::START_EVENT_ID)
            ->parallelGateway("id")
            ->userTask(BpmnTestConstants::USER_TASK_ID)
            ->moveToNode("id")
            ->endEvent(BpmnTestConstants::END_EVENT_ID)
            ->moveToNode("id")
            ->sequenceFlowId("s1")
            ->serviceTask(BpmnTestConstants::SERVICE_TASK_ID)
            ->done();

        $userTaskBounds = $this->findBpmnShape(BpmnTestConstants::USER_TASK_ID)->getBounds();
        $this->assertShapeCoordinates($userTaskBounds, 286, 78);

        $endEventBounds = $this->findBpmnShape(BpmnTestConstants::END_EVENT_ID)->getBounds();
        $this->assertShapeCoordinates($endEventBounds, 286, 208);

        $serviceTaskBounds = $this->findBpmnShape(BpmnTestConstants::SERVICE_TASK_ID)->getBounds();
        $this->assertShapeCoordinates($serviceTaskBounds, 286, 294);

        $sequenceFlowWaypoints = $this->findBpmnEdge("s1")->getWaypoints();
        $waypoint = $sequenceFlowWaypoints[0];
        $this->assertWaypointCoordinates($waypoint, 211, 143);

        $waypoint = $sequenceFlowWaypoints[count($sequenceFlowWaypoints) - 1];

        $this->assertWaypointCoordinates($waypoint, 286, 334);
    }

    public function testShouldPlaceManyBranchesForParallelGateway(): void
    {
        $builder = Bpmn::getInstance()->createExecutableProcess();

        $this->instance = $builder
            ->startEvent(BpmnTestConstants::START_EVENT_ID)
            ->parallelGateway("id")
            ->userTask(BpmnTestConstants::USER_TASK_ID)
            ->moveToNode("id")
            ->endEvent(BpmnTestConstants::END_EVENT_ID)
            ->moveToNode("id")
            ->serviceTask(BpmnTestConstants::SERVICE_TASK_ID)
            ->moveToNode("id")
            ->sequenceFlowId("s1")
            ->sendTask(BpmnTestConstants::SEND_TASK_ID)
            ->done();

        $userTaskBounds = $this->findBpmnShape(BpmnTestConstants::USER_TASK_ID)->getBounds();
        $this->assertShapeCoordinates($userTaskBounds, 286, 78);

        $endEventBounds = $this->findBpmnShape(BpmnTestConstants::END_EVENT_ID)->getBounds();
        $this->assertShapeCoordinates($endEventBounds, 286, 208);

        $serviceTaskBounds = $this->findBpmnShape(BpmnTestConstants::SERVICE_TASK_ID)->getBounds();
        $this->assertShapeCoordinates($serviceTaskBounds, 286, 294);

        $sendTaskBounds = $this->findBpmnShape(BpmnTestConstants::SEND_TASK_ID)->getBounds();
        $this->assertShapeCoordinates($sendTaskBounds, 286, 424);

        $sequenceFlowWaypoints = $this->findBpmnEdge("s1")->getWaypoints();
        $waypoint = $sequenceFlowWaypoints[0];
        $this->assertWaypointCoordinates($waypoint, 211, 143);

        $waypoint = $sequenceFlowWaypoints[count($sequenceFlowWaypoints) - 1];

        $this->assertWaypointCoordinates($waypoint, 286, 464);
    }

    public function testShouldPlaceTwoBranchesForExclusiveGateway(): void
    {
        $builder = Bpmn::getInstance()->createExecutableProcess();

        $this->instance = $builder
            ->startEvent(BpmnTestConstants::START_EVENT_ID)
            ->exclusiveGateway("id")
            ->sequenceFlowId("s1")
            ->userTask(BpmnTestConstants::USER_TASK_ID)
            ->moveToNode("id")
            ->sequenceFlowId("s2")
            ->endEvent(BpmnTestConstants::END_EVENT_ID)
            ->done();

        $userTaskBounds = $this->findBpmnShape(BpmnTestConstants::USER_TASK_ID)->getBounds();
        $this->assertShapeCoordinates($userTaskBounds, 286, 78);

        $endEventBounds = $this->findBpmnShape(BpmnTestConstants::END_EVENT_ID)->getBounds();
        $this->assertShapeCoordinates($endEventBounds, 286, 208);

        $sequenceFlowWaypoints = $this->findBpmnEdge("s2")->getWaypoints();
        $waypoint = $sequenceFlowWaypoints[0];
        $this->assertWaypointCoordinates($waypoint, 211, 143);

        $waypoint = $sequenceFlowWaypoints[count($sequenceFlowWaypoints) - 1];

        $this->assertWaypointCoordinates($waypoint, 286, 226);
    }

    public function testShouldPlaceThreeBranchesForExclusiveGateway(): void
    {
        $builder = Bpmn::getInstance()->createExecutableProcess();

        $this->instance = $builder
            ->startEvent(BpmnTestConstants::START_EVENT_ID)
            ->exclusiveGateway("id")
            ->userTask(BpmnTestConstants::USER_TASK_ID)
            ->moveToNode("id")
            ->endEvent(BpmnTestConstants::END_EVENT_ID)
            ->moveToNode("id")
            ->sequenceFlowId("s1")
            ->serviceTask(BpmnTestConstants::SERVICE_TASK_ID)
            ->done();

        $userTaskBounds = $this->findBpmnShape(BpmnTestConstants::USER_TASK_ID)->getBounds();
        $this->assertShapeCoordinates($userTaskBounds, 286, 78);

        $endEventBounds = $this->findBpmnShape(BpmnTestConstants::END_EVENT_ID)->getBounds();
        $this->assertShapeCoordinates($endEventBounds, 286, 208);

        $serviceTaskBounds = $this->findBpmnShape(BpmnTestConstants::SERVICE_TASK_ID)->getBounds();
        $this->assertShapeCoordinates($serviceTaskBounds, 286, 294);

        $sequenceFlowWaypoints = $this->findBpmnEdge("s1")->getWaypoints();
        $waypoint = $sequenceFlowWaypoints[0];
        $this->assertWaypointCoordinates($waypoint, 211, 143);

        $waypoint = $sequenceFlowWaypoints[count($sequenceFlowWaypoints) - 1];

        $this->assertWaypointCoordinates($waypoint, 286, 334);
    }

    public function testShouldPlaceManyBranchesForExclusiveGateway(): void
    {
        $builder = Bpmn::getInstance()->createExecutableProcess();

        $this->instance = $builder
            ->startEvent(BpmnTestConstants::START_EVENT_ID)
            ->exclusiveGateway("id")
            ->userTask(BpmnTestConstants::USER_TASK_ID)
            ->moveToNode("id")
            ->endEvent(BpmnTestConstants::END_EVENT_ID)
            ->moveToNode("id")
            ->serviceTask(BpmnTestConstants::SERVICE_TASK_ID)
            ->moveToNode("id")
            ->sequenceFlowId("s1")
            ->sendTask(BpmnTestConstants::SEND_TASK_ID)
            ->done();

        $userTaskBounds = $this->findBpmnShape(BpmnTestConstants::USER_TASK_ID)->getBounds();
        $this->assertShapeCoordinates($userTaskBounds, 286, 78);

        $endEventBounds = $this->findBpmnShape(BpmnTestConstants::END_EVENT_ID)->getBounds();
        $this->assertShapeCoordinates($endEventBounds, 286, 208);

        $serviceTaskBounds = $this->findBpmnShape(BpmnTestConstants::SERVICE_TASK_ID)->getBounds();
        $this->assertShapeCoordinates($serviceTaskBounds, 286, 294);

        $sendTaskBounds = $this->findBpmnShape(BpmnTestConstants::SEND_TASK_ID)->getBounds();
        $this->assertShapeCoordinates($sendTaskBounds, 286, 424);

        $sequenceFlowWaypoints = $this->findBpmnEdge("s1")->getWaypoints();
        $waypoint = $sequenceFlowWaypoints[0];
        $this->assertWaypointCoordinates($waypoint, 211, 143);

        $waypoint = $sequenceFlowWaypoints[count($sequenceFlowWaypoints) - 1];

        $this->assertWaypointCoordinates($waypoint, 286, 464);
    }

    public function testShouldPlaceTwoBranchesForEventBasedGateway(): void
    {
        $builder = Bpmn::getInstance()->createExecutableProcess();

        $this->instance = $builder
            ->startEvent(BpmnTestConstants::START_EVENT_ID)
            ->eventBasedGateway()
              ->id("id")
            ->sequenceFlowId("s1")
            ->userTask(BpmnTestConstants::USER_TASK_ID)
            ->moveToNode("id")
            ->sequenceFlowId("s2")
            ->endEvent(BpmnTestConstants::END_EVENT_ID)
            ->done();

        $userTaskBounds = $this->findBpmnShape(BpmnTestConstants::USER_TASK_ID)->getBounds();
        $this->assertShapeCoordinates($userTaskBounds, 286, 78);

        $endEventBounds = $this->findBpmnShape(BpmnTestConstants::END_EVENT_ID)->getBounds();
        $this->assertShapeCoordinates($endEventBounds, 286, 208);

        $sequenceFlowWaypoints = $this->findBpmnEdge("s2")->getWaypoints();
        $waypoint = $sequenceFlowWaypoints[0];
        $this->assertWaypointCoordinates($waypoint, 211, 143);

        $waypoint = $sequenceFlowWaypoints[count($sequenceFlowWaypoints) - 1];

        $this->assertWaypointCoordinates($waypoint, 286, 226);
    }

    public function testShouldPlaceThreeBranchesForEventBasedGateway(): void
    {
        $builder = Bpmn::getInstance()->createExecutableProcess();

        $this->instance = $builder
            ->startEvent(BpmnTestConstants::START_EVENT_ID)
            ->eventBasedGateway()
            ->id("id")
            ->userTask(BpmnTestConstants::USER_TASK_ID)
            ->moveToNode("id")
            ->endEvent(BpmnTestConstants::END_EVENT_ID)
            ->moveToNode("id")
            ->sequenceFlowId("s1")
            ->serviceTask(BpmnTestConstants::SERVICE_TASK_ID)
            ->done();

        $userTaskBounds = $this->findBpmnShape(BpmnTestConstants::USER_TASK_ID)->getBounds();
        $this->assertShapeCoordinates($userTaskBounds, 286, 78);

        $endEventBounds = $this->findBpmnShape(BpmnTestConstants::END_EVENT_ID)->getBounds();
        $this->assertShapeCoordinates($endEventBounds, 286, 208);

        $serviceTaskBounds = $this->findBpmnShape(BpmnTestConstants::SERVICE_TASK_ID)->getBounds();
        $this->assertShapeCoordinates($serviceTaskBounds, 286, 294);

        $sequenceFlowWaypoints = $this->findBpmnEdge("s1")->getWaypoints();
        $waypoint = $sequenceFlowWaypoints[0];
        $this->assertWaypointCoordinates($waypoint, 211, 143);

        $waypoint = $sequenceFlowWaypoints[count($sequenceFlowWaypoints) - 1];

        $this->assertWaypointCoordinates($waypoint, 286, 334);
    }

    public function testShouldPlaceManyBranchesForEventBasedGateway(): void
    {
        $builder = Bpmn::getInstance()->createExecutableProcess();

        $this->instance = $builder
            ->startEvent(BpmnTestConstants::START_EVENT_ID)
            ->eventBasedGateway()
              ->id("id")
            ->userTask(BpmnTestConstants::USER_TASK_ID)
            ->moveToNode("id")
            ->endEvent(BpmnTestConstants::END_EVENT_ID)
            ->moveToNode("id")
            ->serviceTask(BpmnTestConstants::SERVICE_TASK_ID)
            ->moveToNode("id")
            ->sequenceFlowId("s1")
            ->sendTask(BpmnTestConstants::SEND_TASK_ID)
            ->done();

        $userTaskBounds = $this->findBpmnShape(BpmnTestConstants::USER_TASK_ID)->getBounds();
        $this->assertShapeCoordinates($userTaskBounds, 286, 78);

        $endEventBounds = $this->findBpmnShape(BpmnTestConstants::END_EVENT_ID)->getBounds();
        $this->assertShapeCoordinates($endEventBounds, 286, 208);

        $serviceTaskBounds = $this->findBpmnShape(BpmnTestConstants::SERVICE_TASK_ID)->getBounds();
        $this->assertShapeCoordinates($serviceTaskBounds, 286, 294);

        $sendTaskBounds = $this->findBpmnShape(BpmnTestConstants::SEND_TASK_ID)->getBounds();
        $this->assertShapeCoordinates($sendTaskBounds, 286, 424);

        $sequenceFlowWaypoints = $this->findBpmnEdge("s1")->getWaypoints();
        $waypoint = $sequenceFlowWaypoints[0];
        $this->assertWaypointCoordinates($waypoint, 211, 143);

        $waypoint = $sequenceFlowWaypoints[count($sequenceFlowWaypoints) - 1];

        $this->assertWaypointCoordinates($waypoint, 286, 464);
    }

    public function testShouldPlaceTwoBranchesForInclusiveGateway(): void
    {
        $builder = Bpmn::getInstance()->createExecutableProcess();

        $this->instance = $builder
            ->startEvent(BpmnTestConstants::START_EVENT_ID)
            ->inclusiveGateway("id")
            ->sequenceFlowId("s1")
            ->userTask(BpmnTestConstants::USER_TASK_ID)
            ->moveToNode("id")
            ->sequenceFlowId("s2")
            ->endEvent(BpmnTestConstants::END_EVENT_ID)
            ->done();

        $userTaskBounds = $this->findBpmnShape(BpmnTestConstants::USER_TASK_ID)->getBounds();
        $this->assertShapeCoordinates($userTaskBounds, 286, 78);

        $endEventBounds = $this->findBpmnShape(BpmnTestConstants::END_EVENT_ID)->getBounds();
        $this->assertShapeCoordinates($endEventBounds, 286, 208);

        $sequenceFlowWaypoints = $this->findBpmnEdge("s2")->getWaypoints();
        $waypoint = $sequenceFlowWaypoints[0];
        $this->assertWaypointCoordinates($waypoint, 211, 143);

        $waypoint = $sequenceFlowWaypoints[count($sequenceFlowWaypoints) - 1];

        $this->assertWaypointCoordinates($waypoint, 286, 226);
    }

    public function testShouldPlaceThreeBranchesForInclusiveGateway(): void
    {
        $builder = Bpmn::getInstance()->createExecutableProcess();

        $this->instance = $builder
            ->startEvent(BpmnTestConstants::START_EVENT_ID)
            ->inclusiveGateway("id")
            ->userTask(BpmnTestConstants::USER_TASK_ID)
            ->moveToNode("id")
            ->endEvent(BpmnTestConstants::END_EVENT_ID)
            ->moveToNode("id")
            ->sequenceFlowId("s1")
            ->serviceTask(BpmnTestConstants::SERVICE_TASK_ID)
            ->done();

        $userTaskBounds = $this->findBpmnShape(BpmnTestConstants::USER_TASK_ID)->getBounds();
        $this->assertShapeCoordinates($userTaskBounds, 286, 78);

        $endEventBounds = $this->findBpmnShape(BpmnTestConstants::END_EVENT_ID)->getBounds();
        $this->assertShapeCoordinates($endEventBounds, 286, 208);

        $serviceTaskBounds = $this->findBpmnShape(BpmnTestConstants::SERVICE_TASK_ID)->getBounds();
        $this->assertShapeCoordinates($serviceTaskBounds, 286, 294);

        $sequenceFlowWaypoints = $this->findBpmnEdge("s1")->getWaypoints();
        $waypoint = $sequenceFlowWaypoints[0];
        $this->assertWaypointCoordinates($waypoint, 211, 143);

        $waypoint = $sequenceFlowWaypoints[count($sequenceFlowWaypoints) - 1];

        $this->assertWaypointCoordinates($waypoint, 286, 334);
    }

    public function testShouldPlaceManyBranchesForInclusiveGateway(): void
    {
        $builder = Bpmn::getInstance()->createExecutableProcess();

        $this->instance = $builder
            ->startEvent(BpmnTestConstants::START_EVENT_ID)
            ->inclusiveGateway("id")
            ->userTask(BpmnTestConstants::USER_TASK_ID)
            ->moveToNode("id")
            ->endEvent(BpmnTestConstants::END_EVENT_ID)
            ->moveToNode("id")
            ->serviceTask(BpmnTestConstants::SERVICE_TASK_ID)
            ->moveToNode("id")
            ->sequenceFlowId("s1")
            ->sendTask(BpmnTestConstants::SEND_TASK_ID)
            ->done();

        $userTaskBounds = $this->findBpmnShape(BpmnTestConstants::USER_TASK_ID)->getBounds();
        $this->assertShapeCoordinates($userTaskBounds, 286, 78);

        $endEventBounds = $this->findBpmnShape(BpmnTestConstants::END_EVENT_ID)->getBounds();
        $this->assertShapeCoordinates($endEventBounds, 286, 208);

        $serviceTaskBounds = $this->findBpmnShape(BpmnTestConstants::SERVICE_TASK_ID)->getBounds();
        $this->assertShapeCoordinates($serviceTaskBounds, 286, 294);

        $sendTaskBounds = $this->findBpmnShape(BpmnTestConstants::SEND_TASK_ID)->getBounds();
        $this->assertShapeCoordinates($sendTaskBounds, 286, 424);

        $sequenceFlowWaypoints = $this->findBpmnEdge("s1")->getWaypoints();
        $waypoint = $sequenceFlowWaypoints[0];
        $this->assertWaypointCoordinates($waypoint, 211, 143);

        $waypoint = $sequenceFlowWaypoints[count($sequenceFlowWaypoints) - 1];

        $this->assertWaypointCoordinates($waypoint, 286, 464);
    }

    public function testShouldPlaceStartEventWithinSubProcess(): void
    {

        $builder = Bpmn::getInstance()->createExecutableProcess();

        $this->instance = $builder
            ->startEvent(BpmnTestConstants::START_EVENT_ID)
            ->subProcess(BpmnTestConstants::SUB_PROCESS_ID)
              ->embeddedSubProcess()
              ->startEvent("innerStartEvent")
              ->done();

        $startEventBounds = $this->findBpmnShape("innerStartEvent")->getBounds();
        $this->assertShapeCoordinates($startEventBounds, 236, 100);
    }

    public function testShouldAdjustSubProcessWidth(): void
    {
        $builder = Bpmn::getInstance()->createExecutableProcess();

        $this->instance = $builder
            ->startEvent(BpmnTestConstants::START_EVENT_ID)
            ->subProcess(BpmnTestConstants::SUB_PROCESS_ID)
              ->embeddedSubProcess()
              ->startEvent("innerStartEvent")
              ->parallelGateway("innerParallelGateway")
              ->userTask("innerUserTask")
              ->endEvent("innerEndEvent")
            ->subProcessDone()
            ->done();

        $subProcessBounds = $this->findBpmnShape(BpmnTestConstants::SUB_PROCESS_ID)->getBounds();
        $this->assertEquals(472, $subProcessBounds->getWidth());
    }

    public function testShouldAdjustSubProcessWidthWithEmbeddedSubProcess(): void
    {
        $builder = Bpmn::getInstance()->createExecutableProcess();

        $this->instance = $builder
            ->startEvent(BpmnTestConstants::START_EVENT_ID)
            ->subProcess(BpmnTestConstants::SUB_PROCESS_ID)
              ->embeddedSubProcess()
              ->startEvent("innerStartEvent")
              ->subProcess("innerSubProcess")
                ->embeddedSubProcess()
                ->startEvent()
                ->userTask()
                ->userTask()
                ->endEvent()
              ->subProcessDone()
              ->endEvent("innerEndEvent")
            ->subProcessDone()
            ->done();

        $subProcessBounds = $this->findBpmnShape(BpmnTestConstants::SUB_PROCESS_ID)->getBounds();
        $this->assertEquals(794, $subProcessBounds->getWidth());
    }

    public function testShouldAdjustSubProcessHeight(): void
    {
        $builder = Bpmn::getInstance()->createExecutableProcess();

        $this->instance = $builder
            ->startEvent(BpmnTestConstants::START_EVENT_ID)
            ->subProcess(BpmnTestConstants::SUB_PROCESS_ID)
              ->embeddedSubProcess()
              ->startEvent("innerStartEvent")
              ->parallelGateway("innerParallelGateway")
              ->endEvent("innerEndEvent")
              ->moveToNode("innerParallelGateway")
              ->userTask("innerUserTask")
            ->subProcessDone()
            ->done();

        $subProcessBounds = $this->findBpmnShape(BpmnTestConstants::SUB_PROCESS_ID)->getBounds();
        $this->assertEquals(298, $subProcessBounds->getHeight());
    }

    public function testShouldAdjustSubProcessHeightWithEmbeddedProcess(): void
    {
        $builder = Bpmn::getInstance()->createExecutableProcess();

        $this->instance = $builder
            ->startEvent(BpmnTestConstants::START_EVENT_ID)
            ->subProcess(BpmnTestConstants::SUB_PROCESS_ID)
              ->embeddedSubProcess()
              ->startEvent("innerStartEvent")
              ->subProcess()
                ->embeddedSubProcess()
                  ->startEvent()
                  ->exclusiveGateway("id")
                  ->userTask()
                  ->moveToNode("id")
                  ->endEvent()
              ->subProcessDone()
              ->endEvent("innerEndEvent")
            ->subProcessDone()
            ->endEvent()
            ->done();

        $subProcessBounds = $this->findBpmnShape(BpmnTestConstants::SUB_PROCESS_ID)->getBounds();
        $this->assertEquals(-32, $subProcessBounds->getY());
        $this->assertEquals(376, $subProcessBounds->getHeight());
    }

    public function testShouldPlaceCompensation(): void
    {
        $builder = Bpmn::getInstance()->createExecutableProcess();

        $this->instance = $builder
            ->startEvent()
            ->userTask("task")
            ->boundaryEvent("boundary")
              ->compensateEventDefinition()->compensateEventDefinitionDone()
              ->compensationStart()
              ->userTask("compensate")->name("compensate")
              ->compensationDone()
            ->userTask("task2")
              ->boundaryEvent("boundary2")
                ->compensateEventDefinition()->compensateEventDefinitionDone()
                ->compensationStart()
                ->userTask("compensate2")->name("compensate2")
                ->compensationDone()
            ->endEvent("theend")
            ->done();

        $compensationBounds = $this->findBpmnShape("compensate")->getBounds();
        $this->assertShapeCoordinates($compensationBounds, 266.5, 186);
        $compensation2Bounds = $this->findBpmnShape("compensate2")->getBounds();
        $this->assertShapeCoordinates($compensation2Bounds, 416.5, 186);
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

    protected function assertShapeCoordinates(BoundsInterface $bounds, float $x, float $y): void
    {
        $this->assertEquals($x, $bounds->getX());
        $this->assertEquals($y, $bounds->getY());
    }

    protected function assertWaypointCoordinates(WaypointInterface $waypoint, float $x, float $y): void
    {
        $this->assertEquals($x, $waypoint->getX());
        $this->assertEquals($y, $waypoint->getY());
    }
}
