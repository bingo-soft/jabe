<?php

namespace Jabe\Model\Knd\ConstructionSupervision\Impl\Instance\Request;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Impl\Instance\ModelElementInstanceImpl;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Model\Knd\ConstructionSupervision\Impl\RequestModelConstants;
use Jabe\Model\Knd\ConstructionSupervision\Instance\Request\{
    LandPlotCadastralNumberBlockInterface,
    LandPlotCadastralNumberInterface
};

class LandPlotCadastralNumberBlockImpl extends ModelElementInstanceImpl implements LandPlotCadastralNumberBlockInterface
{
    private $landPlotCadastralNumber;

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            LandPlotCadastralNumberBlockInterface::class,
            RequestModelConstants::ELEMENT_NAME_LAND_PLOT_CADASTRAL_NUMBER_BLOCK
        )
        ->namespaceUri(RequestModelConstants::MODEL_NAMESPACE)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): LandPlotCadastralNumberBlockInterface
                {
                    return new LandPlotCadastralNumberBlockImpl($instanceContext);
                }
            }
        );

        self::$landPlotCadastralNumber = $sequenceBuilder->element(LandPlotCadastralNumberInterface::class)
        ->build();

        $typeBuilder->build();
    }

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public function getLandPlotCadastralNumber(): LandPlotCadastralNumberInterface
    {
        return self::$landPlotCadastralNumber->getChild($this);
    }
}
