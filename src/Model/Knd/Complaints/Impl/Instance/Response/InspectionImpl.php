<?php

namespace Jabe\Model\Knd\Complaints\Impl\Instance\Response;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Impl\Instance\ModelElementInstanceImpl;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Model\Knd\Complaints\Impl\KndResponseModelConstants;
use Jabe\Model\Knd\Complaints\Instance\Response\{
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
