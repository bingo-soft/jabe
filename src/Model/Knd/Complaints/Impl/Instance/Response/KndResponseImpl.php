<?php

namespace Jabe\Model\Knd\Complaints\Impl\Instance\Response;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Impl\Instance\{
    ModelElementInstanceImpl,
    ModelTypeInstanceContext
};
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Model\Knd\Complaints\Impl\KndResponseModelConstants;
use Jabe\Model\Knd\Complaints\Instance\Response\{
    InspectionResultInterface,
    KndResponseInterface,
};

class KndResponseImpl extends ModelElementInstanceImpl implements KndResponseInterface
{
    public static $inspectionResultChild;

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            KndResponseInterface::class,
            KndResponseModelConstants::ELEMENT_NAME_KND_RESPONSE
        )
        ->namespaceUri(KndResponseModelConstants::MODEL_NAMESPACE)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): KndResponseInterface
                {
                    return new KndResponseImpl($instanceContext);
                }
            }
        );

        $sequence = $typeBuilder->sequence();

        self::$inspectionResultChild = $sequence->element(InspectionResultInterface::class)
        ->build();

        $typeBuilder->build();
    }

    public function getInspectionResult(): InspectionResultInterface
    {
        return self::$inspectionResultChild->getChild($this);
    }

    public function setInspectionResult(InspectionResultInterface $result): void
    {
        self::$inspectionResultChild->setChild($this, $result);
    }
}
