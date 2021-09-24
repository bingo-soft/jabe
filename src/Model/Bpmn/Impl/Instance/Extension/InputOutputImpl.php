<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance\Extension;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Impl\Instance\BpmnModelElementInstanceImpl;
use BpmPlatform\Model\Bpmn\Instance\Extension\{
    InputOutputInterface,
    InputParameterInterface,
    OutputParameterInterface
};

class InputOutputImpl extends BpmnModelElementInstanceImpl implements InputOutputInterface
{
    protected static $inputParameterCollection;
    protected static $outputParameterCollection;

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            InputOutputInterface::class,
            BpmnModelConstants::EXTENSION_ELEMENT_INPUT_OUTPUT
        )
        ->namespaceUri(BpmnModelConstants::EXTENSION_NS)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new InputOutputImpl($instanceContext);
                }
            }
        );

        $sequenceBuilder = $typeBuilder->sequence();

        self::$inputParameterCollection = $sequenceBuilder->elementCollection(InputParameterInterface::class)
        ->build();

        self::$outputParameterCollection = $sequenceBuilder->elementCollection(OutputParameterInterface::class)
        ->build();

        $typeBuilder->build();
    }

    public function getInputParameters(): array
    {
        return self::$inputParameterCollection->get($this);
    }

    public function getOutputParameters(): array
    {
        return self::$outputParameterCollection->get($this);
    }
}
