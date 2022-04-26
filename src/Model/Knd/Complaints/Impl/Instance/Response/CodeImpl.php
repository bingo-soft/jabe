<?php

namespace Jabe\Model\Knd\Complaints\Impl\Instance\Response;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Impl\Instance\ModelElementInstanceImpl;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Model\Knd\Complaints\Impl\KndResponseModelConstants;
use Jabe\Model\Knd\Complaints\Instance\Response\CodeInterface;

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
