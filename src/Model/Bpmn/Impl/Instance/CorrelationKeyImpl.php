<?php

namespace Jabe\Model\Bpmn\Impl\Instance;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Instance\{
    BaseElementInterface,
    CorrelationKeyInterface,
    CorrelationPropertyInterface
};

class CorrelationKeyImpl extends BaseElementImpl implements CorrelationKeyInterface
{
    protected static $nameAttribute;
    protected static $correlationPropertyRefCollection;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            CorrelationKeyInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_CORRELATION_KEY
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(BaseElementInterface::class)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new CorrelationKeyImpl($instanceContext);
                }
            }
        );

        self::$nameAttribute = $typeBuilder->stringAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_NAME)
        ->build();

        $sequenceBuilder = $typeBuilder->sequence();

        self::$correlationPropertyRefCollection = $sequenceBuilder->elementCollection(CorrelationPropertyRef::class)
        ->qNameElementReferenceCollection(CorrelationPropertyInterface::class)
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

    public function getCorrelationProperties(): array
    {
        return self::$correlationPropertyRefCollection->getReferenceTargetElements($this);
    }
}
