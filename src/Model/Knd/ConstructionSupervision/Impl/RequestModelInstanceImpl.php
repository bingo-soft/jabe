<?php

namespace Jabe\Model\Knd\ConstructionSupervision\Impl;

use Jabe\Model\Xml\{
    ModelBuilder,
    ModelInterface
};
use Jabe\Model\Knd\ConstructionSupervision\Impl\Instance\Request\{
    CitizenshipImpl,
    CompetentOrganizationImpl,
    ConstructionPermitImpl,
    CurrentDateImpl,
    DataImpl,
    DateBirthImpl,
    DateImpl,
    DelegateInfoImpl,
    DepartmentIdImpl,
    DepartmentNameImpl,
    DocnumberImpl,
    DocseriesImpl,
    DocumentPersonalImpl,
    EmailImpl,
    EndDate1Impl,
    FactAddressImpl,
    FIASObjectAddressImpl,
    FirstnameImpl,
    FullfioImpl,
    GenderImpl,
    GoalImpl,
    IsAddressManuallyRequiredImpl,
    IsPaperDocumentRequiredImpl,
    IssuedateImpl,
    IssueidPassportRFImpl,
    IssueorgImpl,
    IssuerImpl,
    IssuerTypeImpl,
    LandPlotCadastralNumberBlockImpl,
    LandPlotCadastralNumberImpl,
    LastnameImpl,
    MethodGettingResultsImpl,
    MiddlenameImpl,
    NameDocImpl,
    NameImpl,
    NumberImpl,
    ObjectNameImpl,
    OkatoImpl,
    OrderIdImpl,
    OrderStatusCodeImpl,
    OrganizationIDImpl,
    PhoneImpl,
    RecipientPersonalDataImpl,
    RegAddressImpl,
    RequestImpl,
    ServiceImpl,
    ShortProjectParametersImpl,
    SnilsImpl,
    StageDescriptionImpl,
    StartDate1Impl,
    TargetIdImpl,
    TargetNameImpl,
    TermImpl,
    TypeDocImpl
};

class RequestModelInstanceImpl
{
    private static $model;
    private static $modelBuilder;

    public static function getModel(): ModelInterface
    {
        if (self::$model === null) {
            $modelBuilder = self::getModelBuilder();

            CitizenshipImpl::registerType($modelBuilder);
            CompetentOrganizationImpl::registerType($modelBuilder);
            ConstructionPermitImpl::registerType($modelBuilder);
            CurrentDateImpl::registerType($modelBuilder);
            DataImpl::registerType($modelBuilder);
            DateBirthImpl::registerType($modelBuilder);
            DateImpl::registerType($modelBuilder);
            DelegateInfoImpl::registerType($modelBuilder);
            DepartmentIdImpl::registerType($modelBuilder);
            DepartmentNameImpl::registerType($modelBuilder);
            DocnumberImpl::registerType($modelBuilder);
            DocseriesImpl::registerType($modelBuilder);
            DocumentPersonalImpl::registerType($modelBuilder);
            EmailImpl::registerType($modelBuilder);
            EndDate1Impl::registerType($modelBuilder);
            FactAddressImpl::registerType($modelBuilder);
            FIASObjectAddressImpl::registerType($modelBuilder);
            FirstnameImpl::registerType($modelBuilder);
            FullfioImpl::registerType($modelBuilder);
            GenderImpl::registerType($modelBuilder);
            GoalImpl::registerType($modelBuilder);
            IsAddressManuallyRequiredImpl::registerType($modelBuilder);
            IsPaperDocumentRequiredImpl::registerType($modelBuilder);
            IssuedateImpl::registerType($modelBuilder);
            IssueidPassportRFImpl::registerType($modelBuilder);
            IssueorgImpl::registerType($modelBuilder);
            IssuerImpl::registerType($modelBuilder);
            IssuerTypeImpl::registerType($modelBuilder);
            LandPlotCadastralNumberBlockImpl::registerType($modelBuilder);
            LandPlotCadastralNumberImpl::registerType($modelBuilder);
            LastnameImpl::registerType($modelBuilder);
            MethodGettingResultsImpl::registerType($modelBuilder);
            MiddlenameImpl::registerType($modelBuilder);
            NameDocImpl::registerType($modelBuilder);
            NameImpl::registerType($modelBuilder);
            NumberImpl::registerType($modelBuilder);
            ObjectNameImpl::registerType($modelBuilder);
            OkatoImpl::registerType($modelBuilder);
            OrderIdImpl::registerType($modelBuilder);
            OrderStatusCodeImpl::registerType($modelBuilder);
            OrganizationIDImpl::registerType($modelBuilder);
            PhoneImpl::registerType($modelBuilder);
            RecipientPersonalDataImpl::registerType($modelBuilder);
            RegAddressImpl::registerType($modelBuilder);
            RequestImpl::registerType($modelBuilder);
            ServiceImpl::registerType($modelBuilder);
            ShortProjectParametersImpl::registerType($modelBuilder);
            SnilsImpl::registerType($modelBuilder);
            StageDescriptionImpl::registerType($modelBuilder);
            StartDate1Impl::registerType($modelBuilder);
            TargetIdImpl::registerType($modelBuilder);
            TargetNameImpl::registerType($modelBuilder);
            TermImpl::registerType($modelBuilder);
            TypeDocImpl::registerType($modelBuilder);

            self::$model = $modelBuilder->build();
        }

        return self::$model;
    }

    public static function getModelBuilder(): ModelBuilder
    {
        if (self::$modelBuilder === null) {
            self::$modelBuilder = ModelBuilder::createInstance(RequestModelConstants::MODEL_NAME);
        }
        return self::$modelBuilder;
    }
}
