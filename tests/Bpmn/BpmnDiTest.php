<?php

namespace Tests\Bpmn;

use PHPUnit\Framework\TestCase;
use Tests\Bpmn\BpmnTestConstants;
use Jabe\Model\Bpmn\Bpmn;
use Jabe\Model\Bpmn\Instance\Bpmndi\{
    BpmnDiagramInterface,
    BpmnLabelStyleInterface,
    BpmnEdgeInterface,
    BpmnPlaneInterface,
    BpmnShapeInterface
};
use Jabe\Model\Bpmn\Instance\Dc\BoundsInterface;
use Jabe\Model\Bpmn\Instance\Di\{
    DiagramElementInterface,
    WaypointInterface
};

class BpmnDiTest extends TestCase
{
    private $modelInstance;
    private $collaboration;
    private $participant;
    private $process;
    private $startEvent;
    private $serviceTask;
    private $exclusiveGateway;
    private $sequenceFlow;
    private $messageFlow;
    private $dataInputAssociation;
    private $association;
    private $endEvent;

    protected function setUp(): void
    {
        $stream = fopen('tests/Bpmn/Resources/BpmnDiTest.xml', 'r+');
        $this->modelInstance = Bpmn::getInstance()->readModelFromStream($stream);
        $this->collaboration = $this->modelInstance->getModelElementById(BpmnTestConstants::COLLABORATION_ID);
        $this->participant = $this->modelInstance->getModelElementById(BpmnTestConstants::PARTICIPANT_ID . '1');
        $this->process = $this->modelInstance->getModelElementById(BpmnTestConstants::PROCESS_ID . '1');
        $this->serviceTask = $this->modelInstance->getModelElementById(BpmnTestConstants::SERVICE_TASK_ID);
        $this->exclusiveGateway = $this->modelInstance->getModelElementById(BpmnTestConstants::EXCLUSIVE_GATEWAY);
        $this->startEvent = $this->modelInstance->getModelElementById(BpmnTestConstants::START_EVENT_ID . '2');
        $this->sequenceFlow = $this->modelInstance->getModelElementById(BpmnTestConstants::SEQUENCE_FLOW_ID . '3');
        $this->messageFlow = $this->modelInstance->getModelElementById(BpmnTestConstants::MESSAGE_FLOW_ID);
        $this->dataInputAssociation = $this->modelInstance->getModelElementById(BpmnTestConstants::DATA_INPUT_ASSOCIATION_ID);
        $this->association = $this->modelInstance->getModelElementById(BpmnTestConstants::ASSOCIATION_ID);
        $this->endEvent = $this->modelInstance->getModelElementById(BpmnTestConstants::END_EVENT_ID . '2');
    }

    public function testBpmnDiagram(): void
    {
        $diagrams = $this->modelInstance->getModelElementsByType(BpmnDiagramInterface::class);
        $this->assertCount(1, $diagrams);
        $diagram = $diagrams[0];
        $this->assertFalse($diagram->getBpmnPlane() == null);
        $this->assertTrue($diagram->getBpmnPlane()->getBpmnElement()->equals($this->collaboration));
        $this->assertCount(1, $diagram->getBpmnLabelStyles());
    }

    public function testBpmnPane(): void
    {
        $diagramElement = $this->collaboration->getDiagramElement();
        $this->assertTrue($diagramElement instanceof BpmnPlaneInterface);

        $this->assertTrue($diagramElement->getBpmnElement()->equals($this->collaboration));
        $this->assertFalse(empty($diagramElement->getChildElementsByType(DiagramElementInterface::class)));
    }

    public function testBpmnLabelStyle(): void
    {
        $labelStyle = $this->modelInstance->getModelElementsByType(BpmnLabelStyleInterface::class)[0];
        $font = $labelStyle->getFont();
        $this->assertEquals("Arial", $font->getName());
        $this->assertEquals(8.0, $font->getSize());
        $this->assertTrue($font->isBold());
        $this->assertFalse($font->isItalic());
        $this->assertFalse($font->isStrikeThrough());
        $this->assertFalse($font->isUnderline());
    }

    public function testBpmnShape(): void
    {
        $shape = $this->serviceTask->getDiagramElement();
        $this->assertTrue($shape->getBpmnElement()->equals($this->serviceTask));
        $this->assertNull($shape->getBpmnLabel());
        $this->assertFalse($shape->isExpanded());
        $this->assertFalse($shape->isHorizontal());
        $this->assertFalse($shape->isMarkerVisible());
        $this->assertFalse($shape->isMessageVisible());
        $this->assertNull($shape->getParticipantBandKind());
        $this->assertNull($shape->getChoreographyActivityShape());
    }

    public function testBpmnEdge(): void
    {
        $edge = $this->sequenceFlow->getDiagramElement();
        $this->assertTrue($edge->getBpmnElement()->equals($this->sequenceFlow));
        $this->assertNull($edge->getBpmnLabel());
        $this->assertNull($edge->getMessageVisibleKind());
        $this->assertTrue($edge->getSourceElement() instanceof BpmnShapeInterface);
        $this->assertTrue($edge->getSourceElement()->getBpmnElement()->equals($this->startEvent));
        $this->assertTrue($edge->getTargetElement() instanceof BpmnShapeInterface);
        $this->assertTrue($edge->getTargetElement()->getBpmnElement()->equals($this->endEvent));
    }

    public function testDiagramElementTypes(): void
    {
        $this->assertTrue($this->collaboration->getDiagramElement() instanceof BpmnPlaneInterface);
        $this->assertNull($this->process->getDiagramElement());
        $this->assertTrue($this->participant->getDiagramElement() instanceof BpmnShapeInterface);
        $this->assertTrue($this->participant->getDiagramElement() instanceof BpmnShapeInterface);
        $this->assertTrue($this->startEvent->getDiagramElement() instanceof BpmnShapeInterface);
        $this->assertTrue($this->serviceTask->getDiagramElement() instanceof BpmnShapeInterface);
        $this->assertTrue($this->exclusiveGateway->getDiagramElement() instanceof BpmnShapeInterface);
        $this->assertTrue($this->endEvent->getDiagramElement() instanceof BpmnShapeInterface);
        $this->assertTrue($this->sequenceFlow->getDiagramElement() instanceof BpmnEdgeInterface);
        $this->assertTrue($this->messageFlow->getDiagramElement() instanceof BpmnEdgeInterface);
        $this->assertTrue($this->dataInputAssociation->getDiagramElement() instanceof BpmnEdgeInterface);
        $this->assertTrue($this->association->getDiagramElement() instanceof BpmnEdgeInterface);
    }

    public function testShouldNotRemoveBpmElementReference(): void
    {
        $exists = false;
        foreach ($this->startEvent->getOutgoing() as $outgoing) {
            if ($outgoing->equals($this->sequenceFlow)) {
                $exists = true;
            }
        }
        $this->assertTrue($exists);
        $exists = false;
        foreach ($this->endEvent->getIncoming() as $incoming) {
            if ($incoming->equals($this->sequenceFlow)) {
                $exists = true;
            }
        }
        $this->assertTrue($exists);

        $edge = $this->sequenceFlow->getDiagramElement();
        $this->assertTrue($edge->getBpmnElement()->equals($this->sequenceFlow));

        $this->startEvent->removeOutgoing($this->sequenceFlow);
        $this->endEvent->removeIncoming($this->sequenceFlow);
        $exists = false;
        foreach ($this->startEvent->getOutgoing() as $outgoing) {
            if ($outgoing->equals($this->sequenceFlow)) {
                $exists = true;
            }
        }
        $this->assertFalse($exists);
        $exists = false;
        foreach ($this->endEvent->getIncoming() as $incoming) {
            if ($incoming->equals($this->sequenceFlow)) {
                $exists = true;
            }
        }
        $this->assertFalse($exists);

        $this->assertTrue($edge->getBpmnElement()->equals($this->sequenceFlow));
    }

    public function testShouldCreateValidBpmnDi(): void
    {
        $modelInstance = Bpmn::getInstance()
          ->createProcess("process")
          ->startEvent("start")
          ->sequenceFlowId("flow")
          ->endEvent("end")
          ->done();

        $process = $modelInstance->getModelElementById("process");
        $startEvent = $modelInstance->getModelElementById("start");
        $sequenceFlow = $modelInstance->getModelElementById("flow");
        $endEvent = $modelInstance->getModelElementById("end");

        // create bpmn diagram
        $bpmnDiagram = $modelInstance->newInstance(BpmnDiagramInterface::class);
        $bpmnDiagram->setId("diagram");
        $bpmnDiagram->setName("diagram");
        $bpmnDiagram->setDocumentation("bpmn diagram element");
        $bpmnDiagram->setResolution(120.0);
        $modelInstance->getDefinitions()->addChildElement($bpmnDiagram);

        // create plane for process
        $processPlane = $modelInstance->newInstance(BpmnPlaneInterface::class);
        $processPlane->setId("plane");
        $processPlane->setBpmnElement($process);
        $bpmnDiagram->setBpmnPlane($processPlane);

        // create shape for start event
        $startEventShape = $modelInstance->newInstance(BpmnShapeInterface::class);
        $startEventShape->setId("startShape");
        $startEventShape->setBpmnElement($startEvent);
        $processPlane->addDiagramElement($startEventShape);

        // create bounds for start event shape
        $startEventBounds = $modelInstance->newInstance(BoundsInterface::class);
        $startEventBounds->setHeight(36.0);
        $startEventBounds->setWidth(36.0);
        $startEventBounds->setX(632.0);
        $startEventBounds->setY(312.0);
        $startEventShape->setBounds($startEventBounds);

        // create shape for end event
        $endEventShape = $modelInstance->newInstance(BpmnShapeInterface::class);
        $endEventShape->setId("endShape");
        $endEventShape->setBpmnElement($endEvent);
        $processPlane->addDiagramElement($endEventShape);

        // create bounds for end event shape
        $endEventBounds = $modelInstance->newInstance(BoundsInterface::class);
        $endEventBounds->setHeight(36.0);
        $endEventBounds->setWidth(36.0);
        $endEventBounds->setX(718.0);
        $endEventBounds->setY(312.0);
        $endEventShape->setBounds($endEventBounds);

        // create edge for sequence flow
        $flowEdge = $modelInstance->newInstance(BpmnEdgeInterface::class);
        $flowEdge->setId("flowEdge");
        $flowEdge->setBpmnElement($sequenceFlow);
        $flowEdge->setSourceElement($startEventShape);
        $flowEdge->setTargetElement($endEventShape);
        $processPlane->addDiagramElement($flowEdge);

        // create waypoints for sequence flow edge
        $startWaypoint = $modelInstance->newInstance(WaypointInterface::class);
        $startWaypoint->setX(668.0);
        $startWaypoint->setY(330.0);
        $flowEdge->addWaypoint($startWaypoint);

        $endWaypoint = $modelInstance->newInstance(WaypointInterface::class);
        $endWaypoint->setX(718.0);
        $endWaypoint->setY(330.0);
        $flowEdge->addWaypoint($endWaypoint);

        Bpmn::getInstance()->validateModel($modelInstance);
        $this->assertTrue(true);
    }
}
