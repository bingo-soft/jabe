<?php

namespace Tests\Bpmn;

use PHPUnit\Framework\TestCase;
use Tests\Bpmn\BpmnTestConstants;
use Jabe\Model\Bpmn\Bpmn;
use Jabe\Model\Bpmn\Instance\{
    ConversationInterface,
    EventInterface,
    ParticipantInterface,
    ServiceTaskInterface
};

class CollaborationParserTest extends TestCase
{
    private $modelInstance;
    private $collaboration;

    protected function setUp(): void
    {
        $stream = fopen('tests/Bpmn/Resources/CollaborationParserTest.bpmn', 'r+');
        $this->modelInstance = Bpmn::getInstance()->readModelFromStream($stream);
        $this->collaboration = $this->modelInstance->getModelElementById("collaboration1");
    }

    protected function tearDown(): void
    {
        Bpmn::getInstance()->validateModel($this->modelInstance);
    }

    public function testConversations(): void
    {
        $this->assertCount(1, $this->collaboration->getConversationNodes());
        $conversationNode = $this->collaboration->getConversationNodes()[0];
        $this->assertTrue($conversationNode instanceof ConversationInterface);
        $this->assertEmpty($conversationNode->getParticipants());
        $this->assertEmpty($conversationNode->getCorrelationKeys());
        $this->assertEmpty($conversationNode->getMessageFlows());
    }

    public function testConversationLink(): void
    {
        $conversationLinks = $this->collaboration->getConversationLinks();
        foreach ($conversationLinks as $conversationLink) {
            $this->assertStringStartsWith("conversationLink", $conversationLink->getId());
            $this->assertTrue($conversationLink->getSource() instanceof ParticipantInterface);
            $source = $conversationLink->getSource();
            $this->assertEquals("Pool", $source->getName());
            $this->assertStringStartsWith("participant", $source->getId());

            $this->assertTrue($conversationLink->getTarget() instanceof ConversationInterface);
            $target = $conversationLink->getTarget();
            $this->assertEquals("conversation1", $target->getId());
        }
    }

    public function testMessageFlow(): void
    {
        $messageFlows = $this->collaboration->getMessageFlows();
        foreach ($messageFlows as $messageFlow) {
            $this->assertStringStartsWith("messageFlow", $messageFlow->getId());
            $this->assertTrue($messageFlow->getSource() instanceof ServiceTaskInterface);
            $this->assertTrue($messageFlow->getTarget() instanceof EventInterface);
        }
    }

    public function testParticipant(): void
    {
        $participants = $this->collaboration->getParticipants();
        foreach ($participants as $participant) {
            $this->assertStringStartsWith("process", $participant->getProcess()->getId());
        }
    }

    public function testUnused(): void
    {
        $this->assertEmpty($this->collaboration->getCorrelationKeys());
        $this->assertEmpty($this->collaboration->getArtifacts());
        $this->assertEmpty($this->collaboration->getConversationAssociations());
        $this->assertEmpty($this->collaboration->getMessageFlowAssociations());
        $this->assertEmpty($this->collaboration->getParticipantAssociations());
    }
}
