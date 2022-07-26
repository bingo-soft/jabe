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
    private static $competentOrganization;
    private static $data;
    private static $delegateInfo;
    private static $goal;
    private static $methodGettingResults;
    private static $recipientPersonalData;
    private static $service;

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

        $sequenceBuilder = $typeBuilder->sequence();

        self::$competentOrganization = $sequenceBuilder->element(CompetentOrganizationInterface::class)
        ->build();
        self::$data = $sequenceBuilder->element(DataInterface::class)
        ->build();
        self::$delegateInfo = $sequenceBuilder->element(DelegateInfoInterface::class)
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

    public function getDelegateInfo(): DelegateInfoInterface
    {
        return self::$delegateInfo->getChild($this);
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

    public function asArray(): array
    {
        return [
            "Service" => self::$service->getChild($this)->asArray(),
            "Goal" => self::$goal->getChild($this)->getTextContent(),
            "DelegateInfo" => self::$delegateInfo->getChild($this)->getTextContent(),
            "RecipientPersonalData" => self::$recipientPersonalData->getChild($this)->asArray(),
            "CompetentOrganization" => self::$competentOrganization->getChild($this)->asArray(),
            "Data" => self::$data->getChild($this)->asArray(),
            "MethodGettingResults" => self::$methodGettingResults->getChild($this)->asArray()
        ];
    }
}
