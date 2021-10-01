<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Xml\Impl\Util\StringUtil;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\{
    ConditionInterface,
    ConditionalEventDefinitionInterface,
    EventDefinitionInterface
};

class ConditionalEventDefinitionImpl extends EventDefinitionImpl implements ConditionalEventDefinitionInterface
{
    protected static $conditionChild;
    protected static $variableName;
    protected static $variableEvents;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            ConditionalEventDefinitionInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_CONDITIONAL_EVENT_DEFINITION
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(EventDefinitionInterface::class)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new ConditionalEventDefinitionImpl($instanceContext);
                }
            }
        );

        $sequenceBuilder = $typeBuilder->sequence();

        self::$conditionChild = $sequenceBuilder->element(ConditionInterface::class)
        ->required()
        ->build();

        /** extensions */

        self::$variableName = $typeBuilder->stringAttribute(BpmnModelConstants::EXTENSION_ATTRIBUTE_VARIABLE_NAME)
        ->namespace(BpmnModelConstants::EXTENSION_NS)
        ->build();

        self::$variableEvents = $typeBuilder->stringAttribute(BpmnModelConstants::EXTENSION_ATTRIBUTE_VARIABLE_EVENTS)
        ->namespace(BpmnModelConstants::EXTENSION_NS)
        ->build();

        $typeBuilder->build();
    }

    public function getCondition(): ConditionInterface
    {
        return self::$conditionChild->getChild($this);
    }

    public function setCondition(ConditionInterface $condition): void
    {
        self::$conditionChild->setChild($this, $condition);
    }

    public function getVariableName(): ?string
    {
        return self::$variableName->getValue($this);
    }

    public function setVariableName(?string $variableName): void
    {
        self::$variableName->setValue($this, $variableName);
    }

    public function getVariableEvents(): ?string
    {
        return self::$variableEvents->getValue($this);
    }

    public function setVariableEvents(?string $variableEvents): void
    {
        self::$variableEvents->setValue($this, $variableEvents);
    }

    public function getVariableEventsList(): array
    {
        $variableEvents = self::$variableEvents->getValue($this);
        return StringUtil::splitCommaSeparatedList($variableEvents);
    }

    public function setVariableEventsList(array $variableEventsList): void
    {
        $variableEvents = StringUtil::joinCommaSeparatedList($variableEventsList);
        self::$variableEvents->setValue($this, $variableEvents);
    }
}
