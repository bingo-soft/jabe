<?php

namespace Jabe\Model\Knd\ConstructionSupervision\Impl\Instance\Request;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Impl\Instance\ModelElementInstanceImpl;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Model\Knd\ConstructionSupervision\Impl\RequestModelConstants;
use Jabe\Model\Knd\ConstructionSupervision\Instance\Request\{
    ConstructionPermitInterface,
    DataInterface,
    EndDate1Interface,
    FIASObjectAddressInterface,
    IsAddressManuallyRequiredInterface,
    LandPlotCadastralNumberBlockInterface,
    ObjectNameInterface,
    ShortProjectParametersInterface,
    StageDescriptionInterface,
    StartDate1Interface
};

class DataImpl extends ModelElementInstanceImpl implements DataInterface
{
    private static $constructionPermit;
    private static $endDate1;
    private static $fiasObjectAddress;
    private static $isAddressManuallyRequired;
    private static $landPlotCadastralNumberBlock;
    private static $objectName;
    private static $shortProjectParameters;
    private static $stageDescription;
    private static $startDate1;

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            DataInterface::class,
            RequestModelConstants::ELEMENT_NAME_DATA
        )
        ->namespaceUri(RequestModelConstants::MODEL_NAMESPACE)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): DataInterface
                {
                    return new DataImpl($instanceContext);
                }
            }
        );

        $sequenceBuilder = $typeBuilder->sequence();

        self::$constructionPermit = $sequenceBuilder->element(ConstructionPermitInterface::class)
        ->build();
        self::$endDate1 = $sequenceBuilder->element(EndDate1Interface::class)
        ->build();
        self::$fiasObjectAddress = $sequenceBuilder->element(FIASObjectAddressInterface::class)
        ->build();
        self::$isAddressManuallyRequired = $sequenceBuilder->element(IsAddressManuallyRequiredInterface::class)
        ->build();
        self::$landPlotCadastralNumberBlock = $sequenceBuilder->element(LandPlotCadastralNumberBlockInterface::class)
        ->build();
        self::$objectName = $sequenceBuilder->element(ObjectNameInterface::class)
        ->build();
        self::$shortProjectParameters = $sequenceBuilder->element(ShortProjectParametersInterface::class)
        ->build();
        self::$stageDescription = $sequenceBuilder->element(StageDescriptionInterface::class)
        ->build();
        self::$startDate1 = $sequenceBuilder->element(StartDate1Interface::class)
        ->build();

        $typeBuilder->build();
    }

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public function getConstructionPermit(): ConstructionPermitInterface
    {
        return self::$constructionPermit->getChild($this);
    }

    public function getEndDate1(): EndDate1Interface
    {
        return self::$endDate1->getChild($this);
    }

    public function getFIASObjectAddress(): FIASObjectAddressInterface
    {
        return self::$fiasObjectAddress->getChild($this);
    }

    public function getIsAddressManuallyRequired(): IsAddressManuallyRequiredInterface
    {
        return self::$isAddressManuallyRequired->getChild($this);
    }

    public function getLandPlotCadastralNumberBlock(): LandPlotCadastralNumberBlockInterface
    {
        return self::$landPlotCadastralNumberBlock->getChild($this);
    }

    public function getObjectName(): ObjectNameInterface
    {
        return self::$objectName->getChild($this);
    }

    public function getShortProjectParameters(): ShortProjectParametersInterface
    {
        return self::$shortProjectParameters->getChild($this);
    }

    public function getStageDescription(): StageDescriptionInterface
    {
        return self::$stageDescription->getChild($this);
    }

    public function getStartDate1(): StartDate1Interface
    {
        return self::$startDate1->getChild($this);
    }

    public function asArray(): array
    {
        return [
            "ObjectName" => self::$objectName->getChild($this)->getTextContent(),
            "ShortProjectParameters" => self::$shortProjectParameters->getChild($this)->getTextContent(),
            "StageDescription" => self::$stageDescription->getChild($this)->getTextContent(),
            "IsAddressManuallyRequired" => self::$isAddressManuallyRequired->getChild($this)->getTextContent(),
            "FIASObjectAddress" => self::$fiasObjectAddress->getChild($this)->getTextContent(),
            "StartDate1" => self::$startDate1->getChild($this)->getTextContent(),
            "EndDate1" => self::$endDate1->getChild($this)->getTextContent(),
            "ConstructionPermit" => self::$constructionPermit->getChild($this)->asArray(),
            "LandPlotCadastralNumberBlock" => self::$landPlotCadastralNumberBlock->getChild($this)->asArray()
        ];
    }
}
