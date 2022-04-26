<?php

namespace Jabe\Model\Bpmn\Impl\Instance\Extension;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Impl\Instance\BpmnModelElementInstanceImpl;
use Jabe\Model\Bpmn\Instance\Extension\{
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
