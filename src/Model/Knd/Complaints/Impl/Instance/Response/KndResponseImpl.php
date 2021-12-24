<?php

namespace BpmPlatform\Model\Knd\Complaints\Impl\Instance\Response;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Impl\Instance\{
    ModelElementInstanceImpl,
    ModelTypeInstanceContext
};
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Knd\Complaints\Impl\KndResponseModelConstants;
use BpmPlatform\Model\Knd\Complaints\Instance\Response\{
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
