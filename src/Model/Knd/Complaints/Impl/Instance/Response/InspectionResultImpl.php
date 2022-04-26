<?php

namespace Jabe\Model\Knd\Complaints\Impl\Instance\Response;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Impl\Instance\ModelElementInstanceImpl;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Model\Knd\Complaints\Impl\KndResponseModelConstants;
use Jabe\Model\Knd\Complaints\Instance\Response\{
    InspectionInterface,
    InspectionResultInterface,
    StatusInterface
};

class InspectionResultImpl extends ModelElementInstanceImpl implements InspectionResultInterface
{
    protected static $statusChild;
    protected static $inspectionChild;

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            InspectionResultInterface::class,
            KndResponseModelConstants::ELEMENT_NAME_INSPECTION_RESULT
        )
        ->namespaceUri(KndResponseModelConstants::MODEL_NAMESPACE)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): InspectionResultInterface
                {
                    return new InspectionResultImpl($instanceContext);
                }
            }
        );

        $sequenceBuilder = $typeBuilder->sequence();

        self::$statusChild = $sequenceBuilder->element(StatusInterface::class)
        ->build();

        self::$inspectionChild = $sequenceBuilder->element(InspectionInterface::class)
        ->build();

        $typeBuilder->build();
    }

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public function getStatus(): StatusInterface
    {
        return self::$statusChild->getChild($this);
    }

    public function setStatus(StatusInterface $status): void
    {
        self::$statusChild->setChild($this, $status);
    }

    public function getInspection(): InspectionInterface
    {
        return self::$inspectionChild->getChild($this);
    }

    public function setInspection(StatusInterface $inspection): void
    {
        self::$inspectionChild->setChild($this, $inspection);
    }
}
