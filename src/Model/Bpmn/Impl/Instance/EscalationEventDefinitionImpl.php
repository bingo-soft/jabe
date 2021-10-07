<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\{
    EscalationEventDefinitionInterface,
    EscalationInterface,
    EventDefinitionInterface
};

class EscalationEventDefinitionImpl extends EventDefinitionImpl implements EscalationEventDefinitionInterface
{
    protected static $escalationRefAttribute;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            EscalationEventDefinitionInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_ESCALATION_EVENT_DEFINITION
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(EventDefinitionInterface::class)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new EscalationEventDefinitionImpl($instanceContext);
                }
            }
        );

        self::$escalationRefAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_ESCALATION_REF)
        ->qNameAttributeReference(EscalationInterface::class)
        ->build();

        $typeBuilder->build();
    }

    public function getEscalation(): ?EscalationInterface
    {
        return self::$escalationRefAttribute->getReferenceTargetElement($this);
    }

    public function setEscalation(EscalationInterface $escalation): void
    {
        self::$escalationRefAttribute->setReferenceTargetElement($this, $escalation);
    }
}
