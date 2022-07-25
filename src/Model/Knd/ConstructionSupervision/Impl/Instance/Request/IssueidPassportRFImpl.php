<?php

namespace Jabe\Model\Knd\ConstructionSupervision\Impl\Instance\Request;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Impl\Instance\ModelElementInstanceImpl;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Model\Knd\ConstructionSupervision\Impl\RequestModelConstants;
use Jabe\Model\Knd\ConstructionSupervision\Instance\Request\IssueidPassportRFInterface;

class IssueidPassportRFImpl extends ModelElementInstanceImpl implements IssueidPassportRFInterface
{
    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            IssueidPassportRFInterface::class,
            RequestModelConstants::ELEMENT_NAME_ISSUEID_PASSPORT_RF
        )
        ->namespaceUri(RequestModelConstants::MODEL_NAMESPACE)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): IssueidPassportRFInterface
                {
                    return new IssueidPassportRFImpl($instanceContext);
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
