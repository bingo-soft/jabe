<?php

namespace Tests\Bpmn;

use Jabe\Model\Bpmn\Bpmn;
use Jabe\Model\Bpmn\Instance\{
    DefinitionsInterface,
    MessageEventDefinitionInterface,
    MessageInterface,
    ProcessInterface,
    StartEventInterface
};

class ReferenceTest extends BpmnModelTest
{
    private $testBpmnModelInstance;
    private $message;
    private $messageEventDefinition;
    private $startEvent;

    protected function setUp(): void
    {
        parent::parseModel("ReferenceTest.shouldFindReferenceWithNamespace");

        $this->testBpmnModelInstance = Bpmn::getInstance()->createEmptyModel();
        $definitions = $this->testBpmnModelInstance->newInstance(DefinitionsInterface::class);
        $this->testBpmnModelInstance->setDefinitions($definitions);

        $this->message = $this->testBpmnModelInstance->newInstance(MessageInterface::class);
        $this->message->setId("message-id");
        $definitions->addRootElement($this->message);

        $process = $this->testBpmnModelInstance->newInstance(ProcessInterface::class);
        $process->setId("process-id");
        $definitions->addRootElement($process);

        $this->startEvent = $this->testBpmnModelInstance->newInstance(StartEventInterface::class);
        $this->startEvent->setId("start-event-id");
        $process->addFlowElement($this->startEvent);

        $this->messageEventDefinition = $this->testBpmnModelInstance->newInstance(
            MessageEventDefinitionInterface::class
        );
        $this->messageEventDefinition->setId("msg-def-id");
        $this->messageEventDefinition->setMessage($this->message);
        $this->startEvent->addEventDefinition($this->messageEventDefinition);

        $this->startEvent->addEventDefinitionRef($this->messageEventDefinition);
    }

    public function testShouldUpdateReferenceOnIdChange(): void
    {
        $this->assertTrue($this->messageEventDefinition->getMessage()->equals($this->message));
        $this->message->setId("changed-message-id");
        $this->assertEquals("changed-message-id", $this->message->getId());
        $this->assertTrue($this->messageEventDefinition->getMessage()->equals($this->message));

        $this->message->setAttributeValue("id", "another-message-id", true);
        $this->assertEquals("another-message-id", $this->message->getId());
        $this->assertTrue($this->messageEventDefinition->getMessage()->equals($this->message));
    }

    public function testShouldRemoveReferenceIfReferencingElementIsRemoved(): void
    {
        $this->assertTrue($this->messageEventDefinition->getMessage()->equals($this->message));

        $definitions = $this->testBpmnModelInstance->getDefinitions();
        $definitions->removeRootElement($this->message);

        $this->assertEquals("msg-def-id", $this->messageEventDefinition->getId());
        $this->assertNull($this->messageEventDefinition->getMessage());
    }

    public function testShouldRemoveReferenceIfReferencingAttributeIsRemoved(): void
    {
        $this->assertTrue($this->messageEventDefinition->getMessage()->equals($this->message));

        $this->message->removeAttribute("id");

        $this->assertEquals("msg-def-id", $this->messageEventDefinition->getId());
        $this->assertNull($this->messageEventDefinition->getMessage());
    }

    public function testShouldUpdateReferenceIfReferencingElementIsReplaced(): void
    {
        $this->assertTrue($this->messageEventDefinition->getMessage()->equals($this->message));
        $newMessage = $this->testBpmnModelInstance->newInstance(MessageInterface::class);
        $newMessage->setId("new-message-id");

        $this->message->replaceWithElement($newMessage);

        $this->assertTrue($this->messageEventDefinition->getMessage()->equals($newMessage));
    }

    public function testShouldAddMessageEventDefinitionRef(): void
    {
        $eventDefinitionRefs = $this->startEvent->getEventDefinitionRefs();
        $this->assertFalse(empty($eventDefinitionRefs));
        $exists = false;
        foreach ($eventDefinitionRefs as $ref) {
            if ($ref->equals($this->messageEventDefinition)) {
                $exists = true;
            }
        }
        $this->assertTrue($exists);
    }

    public function testShouldUpdateMessageEventDefinitionRefOnIdChange(): void
    {
        $this->messageEventDefinition->setId("changed-message-event-definition-id");
        $eventDefinitionRefs = $this->startEvent->getEventDefinitionRefs();
        $exists = false;
        foreach ($eventDefinitionRefs as $ref) {
            if ($ref->equals($this->messageEventDefinition)) {
                $exists = true;
            }
        }
        $this->assertTrue($exists);
    }

    public function testShouldRemoveMessageEventDefinitionRefIfMessageEventDefinitionIsRemoved(): void
    {
        $this->startEvent->removeEventDefinition($this->messageEventDefinition);
        $eventDefinitionRefs = $this->startEvent->getEventDefinitionRefs();
        $this->assertEmpty($eventDefinitionRefs);
    }

    public function testShouldReplaceMessageEventDefinitionRefIfMessageEventDefinitionIsReplaced(): void
    {
        $otherMessageEventDefinition = $this->testBpmnModelInstance->newInstance(MessageEventDefinitionInterface::class);
        $otherMessageEventDefinition->setId("other-message-event-definition-id");
        $eventDefinitionRefs = $this->startEvent->getEventDefinitionRefs();
        $exists = false;
        foreach ($eventDefinitionRefs as $ref) {
            if ($ref->equals($this->messageEventDefinition)) {
                $exists = true;
            }
        }
        $this->assertTrue($exists);
        $this->messageEventDefinition->replaceWithElement($otherMessageEventDefinition);

        $eventDefinitionRefs = $this->startEvent->getEventDefinitionRefs();
        $exists = false;
        foreach ($eventDefinitionRefs as $ref) {
            if ($ref->equals($otherMessageEventDefinition)) {
                $exists = true;
            }
        }
        $this->assertTrue($exists);
    }

    public function testShouldRemoveMessageEventDefinitionRefIfIdIsRemovedOfMessageEventDefinition(): void
    {
        $this->messageEventDefinition->removeAttribute("id");
        $eventDefinitionRefs = $this->startEvent->getEventDefinitionRefs();
        $this->assertEmpty($eventDefinitionRefs);
    }

    public function testShouldFindReferenceWithNamespace(): void
    {
        $messageEventDefinition = $this->bpmnModelInstance->getModelElementById("message-event-definition");
        $message = $this->bpmnModelInstance->getModelElementById("message-id");
        $this->assertFalse($messageEventDefinition->getMessage() == null);
        $this->assertTrue($messageEventDefinition->getMessage()->equals($message));
        $message->setId("changed-message");
        $this->assertFalse($messageEventDefinition->getMessage() == null);
        $this->assertTrue($messageEventDefinition->getMessage()->equals($message));
        $message->setAttributeValue("id", "again-changed-message", true);
        $this->assertFalse($messageEventDefinition->getMessage() == null);
        $this->assertTrue($messageEventDefinition->getMessage()->equals($message));

        $startEvent = $this->bpmnModelInstance->getModelElementById("start-event");
        $eventDefinitionRefs = $startEvent->getEventDefinitionRefs();
        $this->assertFalse(empty($eventDefinitionRefs));
        $exists = false;
        foreach ($eventDefinitionRefs as $ref) {
            if ($ref->equals($messageEventDefinition)) {
                $exists = true;
            }
        }
        $this->assertTrue($exists);

        $messageEventDefinition->setAttributeValue("id", "again-changed-message-event", true);
        $exists = false;
        foreach ($eventDefinitionRefs as $ref) {
            if ($ref->equals($messageEventDefinition)) {
                $exists = true;
            }
        }
        $this->assertTrue($exists);

        $message->removeAttribute("id");
        $this->assertNull($messageEventDefinition->getMessage());
        $messageEventDefinition->removeAttribute("id");
        $this->assertEmpty($startEvent->getEventDefinitionRefs());
    }
}
