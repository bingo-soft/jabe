<?php

namespace Jabe\Model\Bpmn\Impl\Instance;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Instance\{
    EscalationInterface,
    ItemDefinitionInterface,
    RootElementInterface
};

class EscalationImpl extends RootElementImpl implements EscalationInterface
{
    protected static $nameAttribute;
    protected static $escalationCodeAttribute;
    protected static $structureRefAttribute;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            EscalationInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_ESCALATION
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(RootElementInterface::class)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new EscalationImpl($instanceContext);
                }
            }
        );

        self::$nameAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_NAME)
        ->build();

        self::$escalationCodeAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_ESCALATION_CODE)
        ->build();

        self::$structureRefAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_STRUCTURE_REF)
        ->qNameAttributeReference(ItemDefinitionInterface::class)
        ->build();

        $typeBuilder->build();
    }

    public function getName(): string
    {
        return self::$nameAttribute->getValue($this);
    }

    public function setName(string $name): void
    {
        self::$nameAttribute->setValue($this, $name);
    }

    public function getEscalationCode(): ?string
    {
        return self::$escalationCodeAttribute->getValue($this);
    }

    public function setEscalationCode(string $escalationCode): void
    {
        self::$escalationCodeAttribute->setValue($this, $escalationCode);
    }

    public function getStructure(): ItemDefinitionInterface
    {
        return self::$structureRefAttribute->getReferenceTargetElement($this);
    }

    public function setStructure(ItemDefinitionInterface $structure): void
    {
        self::$structureRefAttribute->setReferenceTargetElement($this, $structure);
    }
}
