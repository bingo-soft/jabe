<?php

namespace Jabe\Model\Bpmn\Impl\Instance\Extension;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Impl\Instance\BpmnModelElementInstanceImpl;
use Jabe\Model\Bpmn\Instance\Extension\StringInterface;

class StringImpl extends BpmnModelElementInstanceImpl implements StringInterface
{
    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            StringInterface::class,
            BpmnModelConstants::EXTENSION_ELEMENT_STRING
        )
        ->namespaceUri(BpmnModelConstants::EXTENSION_NS)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new StringImpl($instanceContext);
                }
            }
        );

        $typeBuilder->build();
    }
}
