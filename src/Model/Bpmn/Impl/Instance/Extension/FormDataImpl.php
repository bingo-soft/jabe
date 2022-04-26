<?php

namespace Jabe\Model\Bpmn\Impl\Instance\Extension;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Impl\Instance\BpmnModelElementInstanceImpl;
use Jabe\Model\Bpmn\Instance\Extension\{
    FormDataInterface,
    FormFieldInterface
};

class FormDataImpl extends BpmnModelElementInstanceImpl implements FormDataInterface
{
    protected static $formFieldCollection;

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            FormDataInterface::class,
            BpmnModelConstants::EXTENSION_ELEMENT_FORM_DATA
        )
        ->namespaceUri(BpmnModelConstants::EXTENSION_NS)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new FormDataImpl($instanceContext);
                }
            }
        );

        $sequenceBuilder = $typeBuilder->sequence();

        self::$formFieldCollection = $sequenceBuilder->elementCollection(FormFieldInterface::class)
        ->build();

        $typeBuilder->build();
    }

    public function getFormFields(): array
    {
        return self::$formFieldCollection->get($this);
    }
}
