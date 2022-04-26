<?php

namespace Jabe\Model\Bpmn\Impl\Instance\Dc;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Impl\Instance\BpmnModelElementInstanceImpl;
use Jabe\Model\Bpmn\Instance\Dc\PointInterface;

class PointImpl extends BpmnModelElementInstanceImpl implements PointInterface
{
    protected static $xAttribute;
    protected static $yAttribute;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            PointInterface::class,
            BpmnModelConstants::DC_ELEMENT_POINT
        )
        ->namespaceUri(BpmnModelConstants::DC_NS)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ModelElementInstanceInterface
                {
                    return new PointImpl($instanceContext);
                }
            }
        );

        self::$xAttribute = $typeBuilder->doubleAttribute(BpmnModelConstants::DC_ATTRIBUTE_X)
        ->required()
        ->build();

        self::$yAttribute = $typeBuilder->doubleAttribute(BpmnModelConstants::DC_ATTRIBUTE_Y)
        ->required()
        ->build();

        $typeBuilder->build();
    }

    public function getX(): float
    {
        return self::$xAttribute->getValue($this);
    }

    public function setX(float $x): void
    {
        self::$xAttribute->setValue($this, $x);
    }

    public function getY(): float
    {
        return self::$yAttribute->getValue($this);
    }

    public function setY(float $y): void
    {
        self::$yAttribute->setValue($this, $y);
    }
}
