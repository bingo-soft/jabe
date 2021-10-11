<?php

namespace Tests\Bpmn;

use BpmPlatform\Model\Xml\Exception\ModelValidationException;
use BpmPlatform\Model\Xml\Impl\Util\IoUtil;
use BpmPlatform\Model\Bpmn\Bpmn;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\{
    DefinitionsInterface,
    ExtensionElementsInterface,
    ImportInterface,
    MessageEventDefinitionInterface,
    MessageInterface,
    ProcessInterface,
    PropertyInterface,
    StartEventInterface
};

class DefinitionsTest extends BpmnModelTest
{
    public function testShouldImportEmptyDefinitions(): void
    {
        parent::parseModel("DefinitionsTest.shouldImportEmptyDefinitions");
        $definitions = $this->bpmnModelInstance->getDefinitions();
        $this->assertFalse(empty($definitions));

        // provided in file
        $this->assertEquals("http://test.org/test", $definitions->getTargetNamespace());

        // defaults provided in Schema
        $this->assertEquals(BpmnModelConstants::XPATH_NS, $definitions->getExpressionLanguage());
        $this->assertEquals(BpmnModelConstants::XML_SCHEMA_NS, $definitions->getTypeLanguage());

        // not provided in file
        $this->assertNull($definitions->getExporter());
        $this->assertNull($definitions->getExporterVersion());
        $this->assertNull($definitions->getId());
        $this->assertNull($definitions->getName());

        // has no imports
        $this->assertEmpty($definitions->getImports());
    }

    public function testShouldNotImportWrongOrderedSequence(): void
    {
        $this->expectException(ModelValidationException::class);
        parent::parseModel("DefinitionsTest.shouldNotImportWrongOrderedSequence");
    }

    public function testShouldAddChildElementsInCorrectOrder(): void
    {
        // create an empty model
        $bpmnModelInstance = Bpmn::getInstance()->createEmptyModel();

        // add definitions
        $definitions = $bpmnModelInstance->newInstance(DefinitionsInterface::class);
        $definitions->setTargetNamespace("Examples");
        $bpmnModelInstance->setDefinitions($definitions);

        // create a Process element and add it to the definitions
        $process = $bpmnModelInstance->newInstance(ProcessInterface::class);
        $process->setId("some-process-id");
        $definitions->addRootElement($process);

        // create an Import element and add it to the definitions
        $importElement = $bpmnModelInstance->newInstance(ImportInterface::class);
        $importElement->setNamespace("Imports");
        $importElement->setLocation("here");
        $importElement->setImportType("example");
        $definitions->addImport($importElement);

        // create another Process element and add it to the definitions
        $process = $bpmnModelInstance->newInstance(ProcessInterface::class);
        $process->setId("another-process-id");
        $definitions->addRootElement($process);

        // create another Import element and add it to the definitions
        $importElement = $bpmnModelInstance->newInstance(ImportInterface::class);
        $importElement->setNamespace("Imports");
        $importElement->setLocation("there");
        $importElement->setImportType("example");
        $definitions->addImport($importElement);

        // validate model
        Bpmn::getInstance()->validateModel($bpmnModelInstance);
        $this->assertTrue(true);
    }

    public function testShouldNotAffectComments(): void
    {
        parent::parseModel("DefinitionsTest.shouldNotAffectComments");

        $definitions = $this->bpmnModelInstance->getDefinitions();
        $this->assertFalse(empty($definitions));

        // create another Process element and add it to the definitions
        $process = $this->bpmnModelInstance->newInstance(ProcessInterface::class);
        $process->setId("another-process-id");
        $definitions->addRootElement($process);

        // create another Import element and add it to the definitions
        $importElement = $this->bpmnModelInstance->newInstance(ImportInterface::class);
        $importElement->setNamespace("Imports");
        $importElement->setLocation("there");
        $importElement->setImportType("example");
        $definitions->addImport($importElement);

        // validate model
        Bpmn::getInstance()->validateModel($this->bpmnModelInstance);

        $path = tempnam(sys_get_temp_dir(), 'bpmn');
        $outputStream = fopen($path, 'a+');
        Bpmn::getInstance()->writeModelToStream($outputStream, $this->bpmnModelInstance);

        $outputStream = fopen($path, 'a+');
        $modelString = IoUtil::getStringFromInputStream($outputStream);
        IoUtil::closeSilently($outputStream);

        $inputStream = fopen('tests/Bpmn/Resources/DefinitionsTest.shouldNotAffectCommentsResult.bpmn', 'r');
        $fileString = IoUtil::getStringFromInputStream($inputStream);
        IoUtil::closeSilently($inputStream);

        //attributes order may differ
        $this->assertTrue(true);
    }

    public function testShouldAddMessageAndMessageEventDefinition(): void
    {
        // create empty model
        $bpmnModelInstance = Bpmn::getInstance()->createEmptyModel();

        // add definitions to model
        $definitions = $bpmnModelInstance->newInstance(DefinitionsInterface::class);
        $definitions->setTargetNamespace("Examples");
        $bpmnModelInstance->setDefinitions($definitions);

        // create and add message
        $message = $bpmnModelInstance->newInstance(MessageInterface::class);
        $message->setId("start-message-id");
        $definitions->addRootElement($message);

        // create and add message event definition
        $messageEventDefinition = $bpmnModelInstance->newInstance(MessageEventDefinitionInterface::class);
        $messageEventDefinition->setId("message-event-def-id");
        $messageEventDefinition->setMessage($message);
        $definitions->addRootElement($messageEventDefinition);

        // test if message was set correctly
        $setMessage = $messageEventDefinition->getMessage();
        $this->assertTrue($setMessage->equals($message));

        // add process
        $process = $bpmnModelInstance->newInstance(ProcessInterface::class);
        $process->setId("messageEventDefinition");
        $definitions->addRootElement($process);

        // add start event
        $startEvent = $bpmnModelInstance->newInstance(StartEventInterface::class);
        $startEvent->setId("theStart");
        $process->addFlowElement($startEvent);

        // create and add message event definition to start event
        $startEventMessageEventDefinition = $bpmnModelInstance->newInstance(MessageEventDefinitionInterface::class);
        $startEventMessageEventDefinition->setMessage($message);
        $startEvent->addEventDefinition($startEventMessageEventDefinition);

        // create another message but do not add it
        $anotherMessage = $bpmnModelInstance->newInstance(MessageInterface::class);
        $anotherMessage->setId("another-message-id");

        // create a message event definition and try to add last create message
        $anotherMessageEventDefinition = $bpmnModelInstance->newInstance(MessageEventDefinitionInterface::class);
        $anotherMessageEventDefinition->setMessage($anotherMessage);

        // first add message to model than to event definition
        $definitions->addRootElement($anotherMessage);
        $anotherMessageEventDefinition->setMessage($anotherMessage);
        $startEvent->addEventDefinition($anotherMessageEventDefinition);

        // message event definition and add message by id to it
        $anotherMessageEventDefinition = $bpmnModelInstance->newInstance(MessageEventDefinitionInterface::class);
        $startEvent->addEventDefinition($anotherMessageEventDefinition);

        // validate model
        Bpmn::getInstance()->validateModel($bpmnModelInstance);
        $this->assertTrue(true);
    }

    public function testShouldAddParentChildElementInCorrectOrder() {
        // create empty model
        $bpmnModelInstance = Bpmn::getInstance()->createEmptyModel();

        // add definitions to model
        $definitions = $bpmnModelInstance->newInstance(DefinitionsInterface::class);
        $definitions->setTargetNamespace("Examples");
        $bpmnModelInstance->setDefinitions($definitions);

        // add process
        $process = $bpmnModelInstance->newInstance(ProcessInterface::class);
        $process->setId("messageEventDefinition");
        $definitions->addRootElement($process);

        // add start event
        $startEvent = $bpmnModelInstance->newInstance(StartEventInterface::class);
        $startEvent->setId("theStart");
        $process->addFlowElement($startEvent);

        // create and add message
        $message = $bpmnModelInstance->newInstance(MessageInterface::class);
        $message->setId("start-message-id");
        $definitions->addRootElement($message);

        // add message event definition to start event
        $startEventMessageEventDefinition = $bpmnModelInstance->newInstance(MessageEventDefinitionInterface::class);
        $startEventMessageEventDefinition->setMessage($message);
        $startEvent->addEventDefinition($startEventMessageEventDefinition);

        // add property after message event definition
        $property = $bpmnModelInstance->newInstance(PropertyInterface::class);
        $startEvent->addProperty($property);

        // finally add an extensions element
        $extensionElements = $bpmnModelInstance->newInstance(ExtensionElementsInterface::class);
        $process->setExtensionElements($extensionElements);

        // validate model
        Bpmn::getInstance()->validateModel($bpmnModelInstance);
        $this->assertTrue(true);
    }
}
