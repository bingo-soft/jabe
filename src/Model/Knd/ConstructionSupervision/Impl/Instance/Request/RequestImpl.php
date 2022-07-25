<?php

namespace Jabe\Model\Knd\ConstructionSupervision\Impl\Instance\Request;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Impl\Instance\ModelElementInstanceImpl;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Model\Knd\ConstructionSupervision\Impl\RequestModelConstants;
use Jabe\Model\Knd\ConstructionSupervision\Instance\Request\{
    CompetentOrganizationInterface,
    DataInterface,
    DelegateInfoInterface,
    GoalInterface,
    MethodGettingResultsInterface,
    RecipientPersonalDataInterface,
    RequestInterface,
    ServiceInterface
};

class RequestImpl extends ModelElementInstanceImpl implements RequestInterface
{
    private $competentOrganization;
    private $data;
    private $goal;
    private $methodGettingResults;
    private $recipientPersonalData;
    private $service;

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            RequestInterface::class,
            RequestModelConstants::ELEMENT_NAME_REQUEST
        )
        ->namespaceUri(RequestModelConstants::MODEL_NAMESPACE)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): RequestInterface
                {
                    return new RequestImpl($instanceContext);
                }
            }
        );

        self::$competentOrganization = $sequenceBuilder->element(CompetentOrganizationInterface::class)
        ->build();
        self::$data = $sequenceBuilder->element(DataInterface::class)
        ->build();
        self::$goal = $sequenceBuilder->element(GoalInterface::class)
        ->build();
        self::$methodGettingResults = $sequenceBuilder->element(MethodGettingResultsInterface::class)
        ->build();
        self::$recipientPersonalData = $sequenceBuilder->element(RecipientPersonalDataInterface::class)
        ->build();
        self::$service = $sequenceBuilder->element(ServiceInterface::class)
        ->build();

        $typeBuilder->build();
    }

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public function getCompetentOrganization(): CompetentOrganizationInterface
    {
        return self::$competentOrganization->getChild($this);
    }

    public function getData(): DataInterface
    {
        return self::$data->getChild($this);
    }

    public function getGoal(): GoalInterface
    {
        return self::$goal->getChild($this);
    }

    public function getMethodGettingResults(): MethodGettingResultsInterface
    {
        return self::$methodGettingResults->getChild($this);
    }

    public function getRecipientPersonalData(): RecipientPersonalDataInterface
    {
        return self::$recipientPersonalData->getChild($this);
    }

    public function getService(): ServiceInterface
    {
        return self::$service->getChild($this);
    }
}
