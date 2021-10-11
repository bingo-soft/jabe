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

class DataObjectsTest extends TestCase
{
    public $modelInstance;

    protected function setUp(): void
    {
        $stream = fopen('tests/Bpmn/Resources/DataObjectTest.bpmn', 'r+');
        $this->modelInstance = Bpmn::getInstance()->readModelFromStream($stream);
    }

    protected function tearDown(): void
    {
        Bpmn::getInstance()->validateModel($this->modelInstance);
    }

    public function testGetDataObject(): void
    {
        $dataObject = $this->modelInstance->getModelElementById("_21");
        $itemDefinition = $this->modelInstance->getModelElementById("_100");
        $this->assertFalse($dataObject == null);
        $this->assertEquals("DataObject _21", $dataObject->getName());
        $this->assertFalse($dataObject->isCollection());
        $this->assertTrue($dataObject->getItemSubject()->equals($itemDefinition));
    }

    public function testGetDataObjectReference(): void
    {
        $dataObjectReference = $this->modelInstance->getModelElementById("_dataRef_7");
        $dataObject = $this->modelInstance->getModelElementById("_7");
        $this->assertFalse($dataObjectReference == null);
        $this->assertNull($dataObjectReference->getName());
        $this->assertTrue($dataObjectReference->getDataObject()->equals($dataObject));
    }

    public function testDataObjectReferenceAsDataAssociationSource(): void
    {
        $scriptTask = $this->modelInstance->getModelElementById("_3");
        $dataObjectReference = $this->modelInstance->getModelElementById("_dataRef_11");
        $dataInputAssociation = $scriptTask->getDataInputAssociations()[0];
        $sources = $dataInputAssociation->getSources();
        $this->assertCount(1, $sources);
        $this->assertTrue($sources[0]->equals($dataObjectReference));
    }

    public function testDataObjectReferenceAsDataAssociationTarget(): void
    {
        $scriptTask = $this->modelInstance->getModelElementById("_3");
        $dataObjectReference = $this->modelInstance->getModelElementById("_dataRef_7");
        $dataOutputAssociation = $scriptTask->getDataOutputAssociations()[0];
        $this->assertTrue($dataOutputAssociation->getTarget()->equals($dataObjectReference));
    }
}
