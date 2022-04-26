<?php

namespace Jabe\Model\Bpmn\Impl\Instance\Extension;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Impl\Instance\BpmnModelElementInstanceImpl;
use Jabe\Model\Bpmn\Instance\Extension\{
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
