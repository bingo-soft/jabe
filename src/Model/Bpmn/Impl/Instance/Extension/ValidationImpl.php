<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance\Extension;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Impl\Instance\BpmnModelElementInstanceImpl;
use BpmPlatform\Model\Bpmn\Instance\Extension\{
    ConstraintInterface,
    ValidationInterface
};

class ValidationImpl extends BpmnModelElementInstanceImpl implements ValidationInterface
{
    protected static $constraintCollection;

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            ValidationInterface::class,
            BpmnModelConstants::EXTENSION_ELEMENT_VALIDATION
        )
        ->namespaceUri(BpmnModelConstants::EXTENSION_NS)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new ValidationImpl($instanceContext);
                }
            }
        );

        $sequenceBuilder = $typeBuilder->sequence();

        self::$constraintCollection = $sequenceBuilder->elementCollection(ConstraintInterface::class)
        ->build();

        $typeBuilder->build();
    }

    public function getConstraints(): array
    {
        return self::$constraintCollection->get($this);
    }
}
