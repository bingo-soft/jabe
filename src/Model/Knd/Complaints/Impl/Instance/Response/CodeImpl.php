<?php

namespace BpmPlatform\Model\Knd\Complaints\Impl\Instance\Response;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Impl\Instance\ModelElementInstanceImpl;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Knd\Complaints\Impl\KndResponseModelConstants;
use BpmPlatform\Model\Knd\Complaints\Instance\Response\CodeInterface;

class CodeImpl extends ModelElementInstanceImpl implements CodeInterface
{
    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            CodeInterface::class,
            KndResponseModelConstants::ELEMENT_NAME_CODE
        )
        ->namespaceUri(KndResponseModelConstants::MODEL_NAMESPACE)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): CodeInterface
                {
                    return new CodeImpl($instanceContext);
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
