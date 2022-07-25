<?php

namespace Jabe\Model\Knd\ConstructionSupervision\Impl\Instance\Request;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Impl\Instance\ModelElementInstanceImpl;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Model\Knd\ConstructionSupervision\Impl\RequestModelConstants;
use Jabe\Model\Knd\ConstructionSupervision\Instance\Request\{
    CompetentOrganizationInterface,
    NameInterface,
    OrganizationIDInterface
};

class CompetentOrganizationImpl extends ModelElementInstanceImpl implements CompetentOrganizationInterface
{
    private $name;
    private $organizationID;

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            CompetentOrganizationInterface::class,
            RequestModelConstants::ELEMENT_NAME_COMPETENT_ORGANIZATION
        )
        ->namespaceUri(RequestModelConstants::MODEL_NAMESPACE)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): CompetentOrganizationInterface
                {
                    return new CompetentOrganizationImpl($instanceContext);
                }
            }
        );

        self::$name = $sequenceBuilder->element(NameInterface::class)
        ->build();
        self::$organizationID = $sequenceBuilder->element(OrganizationIDInterface::class)
        ->build();

        $typeBuilder->build();
    }

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public function getName(): NameInterface
    {
        return self::$name->getChild($this);
    }

    public function getOrganizationID(): OrganizationIDInterface
    {
        return self::$organizationID->getChild($this);
    }
}
