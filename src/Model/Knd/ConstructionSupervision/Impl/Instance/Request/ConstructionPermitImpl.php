<?php

namespace Jabe\Model\Knd\ConstructionSupervision\Impl\Instance\Request;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Impl\Instance\ModelElementInstanceImpl;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Model\Knd\ConstructionSupervision\Impl\RequestModelConstants;
use Jabe\Model\Knd\ConstructionSupervision\Instance\Request\{
    ConstructionPermitInterface,
    DateInterface,
    IssuerInterface,
    NumberInterface,
    TermInterface
};

class ConstructionPermitImpl extends ModelElementInstanceImpl implements ConstructionPermitInterface
{
    private $date;
    private $issuer;
    private $number;
    private $term;

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            ConstructionPermitInterface::class,
            RequestModelConstants::ELEMENT_NAME_CONSTRUCTION_PERMIT
        )
        ->namespaceUri(RequestModelConstants::MODEL_NAMESPACE)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): ConstructionPermitInterface
                {
                    return new ConstructionPermitImpl($instanceContext);
                }
            }
        );

        self::$date = $sequenceBuilder->element(DateInterface::class)
        ->build();
        self::$issuer = $sequenceBuilder->element(IssuerInterface::class)
        ->build();
        self::$number = $sequenceBuilder->element(NumberInterface::class)
        ->build();
        self::$term = $sequenceBuilder->element(TermInterface::class)
        ->build();

        $typeBuilder->build();
    }

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public function getDate(): DateInterface
    {
        return self::$date->getChild($this);
    }

    public function getIssuer(): IssuerInterface
    {
        return self::$issuer->getChild($this);
    }

    public function getNumber(): NumberInterface
    {
        return self::$number->getChild($this);
    }

    public function getTerm(): TermInterface
    {
        return self::$term->getChild($this);
    }
}
