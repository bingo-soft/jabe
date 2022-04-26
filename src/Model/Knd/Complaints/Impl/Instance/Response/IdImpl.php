<?php

namespace Jabe\Model\Knd\Complaints\Impl\Instance\Response;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Impl\Instance\ModelElementInstanceImpl;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Model\Knd\Complaints\Impl\KndResponseModelConstants;
use Jabe\Model\Knd\Complaints\Instance\Response\IdInterface;

class IdImpl extends ModelElementInstanceImpl implements IdInterface
{
    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            IdInterface::class,
            KndResponseModelConstants::ELEMENT_NAME_ID
        )
        ->namespaceUri(KndResponseModelConstants::MODEL_NAMESPACE)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): IdInterface
                {
                    return new IdImpl($instanceContext);
                }
            }
        );

        $typeBuilder->build();
    }

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }
}
