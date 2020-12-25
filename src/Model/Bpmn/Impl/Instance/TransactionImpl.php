<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\TransactionMethod;
use BpmPlatform\Model\Bpmn\Instance\{
    SubProcessInterface,
    TransactionInterface
};

class TransactionImpl extends SubProcessImpl implements TransactionInterface
{
    protected static $methodAttribute;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            TransactionInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_TRANSACTION
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(SubProcessInterface::class)
        ->instanceProvider(
            new class extends ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new TransactionImpl($instanceContext);
                }
            }
        );

        self::$methodAttribute = $typeBuilder->namedEnumAttribute(
            BpmnModelConstants::BPMN_ATTRIBUTE_METHOD,
            TransactionMethod::class
        )
        ->defaultValue(TransactionMethod::COMPENSATE)
        ->build();

        $typeBuilder->build();
    }

    public function getMethod(): string
    {
        return self::$methodAttribute->getValue($this);
    }

    public function setMethod(string $method): void
    {
        self::$methodAttribute->setValue($this, $method);
    }
}
