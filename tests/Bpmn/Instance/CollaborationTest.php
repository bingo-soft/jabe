<?php

namespace Tests\Bpmn\Instance;

use Tests\Xml\Test\{
    AbstractTypeAssumption,
    AttributeAssumption
};
use BpmPlatform\Model\Bpmn\Impl\Instance\SupportedInterfaceRef;
use BpmPlatform\Model\Bpmn\Instance\{
    ArtifactInterface,
    ConversationAssociationInterface,
    ConversationLinkInterface,
    ConversationNodeInterface,
    CorrelationKeyInterface,
    MessageFlowAssociationInterface,
    MessageFlowInterface,
    ParticipantAssociationInterface,
    ParticipantInterface,
    RootElementInterface
};

class CollaborationTest extends BpmnModelElementInstanceTest
{
    public function getTypeAssumption(): AbstractTypeAssumption
    {
        return new BpmnTypeAssumption($this->model, false, null, RootElementInterface::class);
    }

    public function getChildElementAssumptions(): array
    {
        return [
            new BpmnChildElementAssumption($this->model, ParticipantInterface::class),
            new BpmnChildElementAssumption($this->model, MessageFlowInterface::class),
            new BpmnChildElementAssumption($this->model, ArtifactInterface::class),
            new BpmnChildElementAssumption($this->model, ConversationNodeInterface::class),
            new BpmnChildElementAssumption($this->model, ConversationAssociationInterface::class),
            new BpmnChildElementAssumption($this->model, ParticipantAssociationInterface::class),
            new BpmnChildElementAssumption($this->model, MessageFlowAssociationInterface::class),
            new BpmnChildElementAssumption($this->model, CorrelationKeyInterface::class),
            new BpmnChildElementAssumption($this->model, ConversationLinkInterface::class),
        ];
    }

    public function getAttributesAssumptions(): array
    {
        return [
            new AttributeAssumption(null, "name"),
            new AttributeAssumption(null, "isClosed", false, false, false)
        ];
    }
}
