<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Impl\Util\ModelUtil;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\QueryInterface;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\{
    ExtensionElementsInterface
};

class ExtensionElementsImpl extends BpmnModelElementInstanceImpl implements ExtensionElementsInterface
{
    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            ExtensionElementsInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_EXTENSION_ELEMENTS
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new ExtensionElementsImpl($instanceContext);
                }
            }
        );

        $typeBuilder->build();
    }

    public function getElements(): array
    {
        return ModelUtil::getModelElementCollection($this->getDomElement()->getChildElements(), $this->modelInstance);
    }

    public function getElementsQuery(): QueryInterface
    {
        return new QueryImpl($this->getElements());
    }

    public function addExtensionElementNs(string $namespaceUri, string $localName): ModelElementInstanceInterface
    {
        $extensionElementType = $this->modelInstance->registerGenericType($namespaceUri, $localName);
        $extensionElement = $extensionElementType->newInstance($this->modelInstance);
        $this->addChildElement($extensionElement);
        return $extensionElement;
    }

    public function addExtensionElement(string $extensionElementClass): ModelElementInstanceInterface
    {
        $extensionElement = $this->modelInstance->newInstance($extensionElementClass);
        $this->addChildElement($extensionElement);
        return $extensionElement;
    }

    public function addChildElement(ModelElementInstanceInterface $extensionElement): void
    {
        $this->getDomElement()->appendChild($extensionElement->getDomElement());
    }
}
