<?php

namespace Jabe\Model\Knd\Complaints\Impl\Instance\Response;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Impl\Instance\ModelElementInstanceImpl;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Model\Knd\Complaints\Impl\KndResponseModelConstants;
use Jabe\Model\Knd\Complaints\Instance\Response\{
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
