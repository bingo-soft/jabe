<?php

namespace BpmPlatform\Model\Bpmn\Impl\Instance;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Bpmn\Impl\BpmnModelConstants;
use BpmPlatform\Model\Bpmn\Instance\{
    ArtifactInterface,
    CategoryValueInterface,
    GroupInterface
};
use BpmPlatform\Model\Bpmn\Instance\Bpmndi\BpmnEdgeInterface;

class GroupImpl extends ArtifactImpl implements GroupInterface
{
    protected static $categoryValueRefAttribute;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            GroupInterface::class,
            BpmnModelConstants::BPMN_ELEMENT_GROUP
        )
        ->namespaceUri(BpmnModelConstants::BPMN20_NS)
        ->extendsType(ArtifactInterface::class)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new GroupImpl($instanceContext);
                }
            }
        );

        self::$categoryValueRefAttribute =
        $typeBuilder
            ->stringAttribute(BpmnModelConstants::BPMN_ATTRIBUTE_CATEGORY_VALUE_REF)
            ->qNameAttributeReference(CategoryValueInterface::class)
            ->build();

        $typeBuilder->build();
    }

    public function getCategory(): CategoryValueInterface
    {
        return self::$categoryValueRefAttribute->getReferenceTargetElement($this);
    }

    public function setCategory(CategoryValueInterface $categoryValue): void
    {
        self::$categoryValueRefAttribute->setReferenceTargetElement($this, $categoryValue);
    }

    public function getDiagramElement(): BpmnEdgeInterface
    {
        return parent::getDiagramElement();
    }
}
