<?php

namespace BpmPlatform\Model\Knd\Complaints\Impl\Instance\Response;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Impl\Instance\ModelElementInstanceImpl;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Knd\Complaints\Impl\KndResponseModelConstants;
use BpmPlatform\Model\Knd\Complaints\Instance\Response\{
    InspectionInterface,
    IdInterface
};

class InspectionImpl extends ModelElementInstanceImpl implements InspectionInterface
{
    protected static $idChild;

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            InspectionInterface::class,
            KndResponseModelConstants::ELEMENT_NAME_INSPECTION
        )
        ->namespaceUri(KndResponseModelConstants::MODEL_NAMESPACE)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): InspectionInterface
                {
                    return new InspectionImpl($instanceContext);
                }
            }
        );

        $sequenceBuilder = $typeBuilder->sequence();

        self::$idChild = $sequenceBuilder->element(IdInterface::class)
        ->build();

        $typeBuilder->build();
    }

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public function getId(): IdInterface
    {
        return self::$idChild->getChild($this);
    }

    public function setId(IdInterface $id): void
    {
        self::$idChild->setChild($this, $id);
    }
}
