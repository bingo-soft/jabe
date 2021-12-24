<?php

namespace BpmPlatform\Model\Knd\Complaints\Impl\Instance\Response;

use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Impl\Instance\ModelElementInstanceImpl;
use BpmPlatform\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use BpmPlatform\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use BpmPlatform\Model\Knd\Complaints\Impl\KndResponseModelConstants;
use BpmPlatform\Model\Knd\Complaints\Instance\Response\{
    CodeInterface,
    StatusInterface
};

class StatusImpl extends ModelElementInstanceImpl implements StatusInterface
{
    protected static $codeChild;

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            StatusInterface::class,
            KndResponseModelConstants::ELEMENT_NAME_STATUS
        )
        ->namespaceUri(KndResponseModelConstants::MODEL_NAMESPACE)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): StatusInterface
                {
                    return new StatusImpl($instanceContext);
                }
            }
        );

        $sequenceBuilder = $typeBuilder->sequence();

        self::$codeChild = $sequenceBuilder->element(CodeInterface::class)
        ->build();

        $typeBuilder->build();
    }

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public function getCode(): CodeInterface
    {
        return self::$codeChild->getChild($this);
    }

    public function setCode(CodeInterface $code): void
    {
        self::$codeChild->setChild($this, $code);
    }
}
