<?php

namespace Tests\Bpmn;

use PHPUnit\Framework\TestCase;
use BpmPlatform\Model\Bpmn\Bpmn;
use BpmPlatform\Model\Bpmn\Instance\{
    BpmnModelElementInstanceInterface,
    DefinitionsInterface,
    EndEventInterface,
    FlowNodeInterface,
    ParallelGatewayInterface,
    ProcessInterface,
    StartEventInterface,
    SequenceFlowInterface,
    ServiceTaskInterface,
    UserTaskInterface
};

class CreateModelTest extends TestCase
{
    public $modelInstance;
    public $definitions;
    public $process;

    protected function setUp(): void
    {
        $this->modelInstance = Bpmn::getInstance()->createEmptyModel();
        $this->definitions = $this->modelInstance->newInstance(DefinitionsInterface::class);
        $this->definitions->setTargetNamespace("http://test.org/examples");
        $this->modelInstance->setDefinitions($this->definitions);
    }

    protected function tearDown(): void
    {
        Bpmn::getInstance()->validateModel($this->modelInstance);
    }

    protected function createElement(
        BpmnModelElementInstanceInterface $parentElement,
        string $id,
        string $elementClass
    ): BpmnModelElementInstanceInterface {
        $element = $this->modelInstance->newInstance($elementClass);
        $element->setAttributeValue("id", $id, true);
        $parentElement->addChildElement($element);
        return $element;
    }

    public function createSequenceFlow(
        ProcessInterface $process,
        FlowNodeInterface $from,
        FlowNodeInterface $to
    ): SequenceFlowInterface {
        $sequenceFlow = $this->createElement($process, $from->getId() . "-" . $to->getId(), SequenceFlowInterface::class);
        $process->addChildElement($sequenceFlow);
        $sequenceFlow->setSource($from);
        $from->addOutgoing($sequenceFlow);
        $sequenceFlow->setTarget($to);
        $to->addIncoming($sequenceFlow);
        return $sequenceFlow;
    }

    public function testCreateProcessWithOneTask()
    {
        // create process
        $process = $this->createElement($this->definitions, "process-with-one-task", ProcessInterface::class);

        // create elements
        $startEvent = $this->createElement($process, "start", StartEventInterface::class);
        $task1 = $this->createElement($process, "task1", UserTaskInterface::class);
        $endEvent = $this->createElement($process, "end", EndEventInterface::class);

        // create flows
        $this->createSequenceFlow($process, $startEvent, $task1);
        $this->createSequenceFlow($process, $task1, $endEvent);
        $this->assertTrue(true);
    }

    public function testCreateProcessWithParallelGateway(): void
    {
        // create process
        $process = $this->createElement($this->definitions, "process-with-parallel-gateway", ProcessInterface::class);

        // create elements
        $startEvent = $this->createElement($process, "start", StartEventInterface::class);
        $fork = $this->createElement($process, "fork", ParallelGatewayInterface::class);
        $task1 = $this->createElement($process, "task1", UserTaskInterface::class);
        $task2 = $this->createElement($process, "task2", ServiceTaskInterface::class);
        $join = $this->createElement($process, "join", ParallelGatewayInterface::class);
        $endEvent = $this->createElement($process, "end", EndEventInterface::class);

        // create flows
        $this->createSequenceFlow($process, $startEvent, $fork);
        $this->createSequenceFlow($process, $fork, $task1);
        $this->createSequenceFlow($process, $fork, $task2);
        $this->createSequenceFlow($process, $task1, $join);
        $this->createSequenceFlow($process, $task2, $join);
        $this->createSequenceFlow($process, $join, $endEvent);
        $this->assertTrue(true);
    }
}
