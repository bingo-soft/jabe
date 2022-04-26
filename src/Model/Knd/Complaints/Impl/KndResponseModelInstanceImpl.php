<?php

namespace Jabe\Model\Knd\Complaints\Impl;

use Jabe\Model\Xml\{
    ModelBuilder,
    ModelInterface
};
use Jabe\Model\Knd\Complaints\Impl\Instance\Response\{
    CodeImpl,
    IdImpl,
    InspectionImpl,
    InspectionResultImpl,
    KndResponseImpl,
    StatusImpl
};

class KndResponseModelInstanceImpl
{
    private static $model;
    private static $modelBuilder;

    public static function getModel(): ModelInterface
    {
        if (self::$model == null) {
            $modelBuilder = self::getModelBuilder();

            CodeImpl::registerType($modelBuilder);
            IdImpl::registerType($modelBuilder);
            InspectionImpl::registerType($modelBuilder);
            InspectionResultImpl::registerType($modelBuilder);
            KndResponseImpl::registerType($modelBuilder);
            StatusImpl::registerType($modelBuilder);

            self::$model = $modelBuilder->build();
        }

        return self::$model;
    }

    public static function getModelBuilder(): ModelBuilder
    {
        if (self::$modelBuilder == null) {
            self::$modelBuilder = ModelBuilder::createInstance(KndResponseModelConstants::MODEL_NAME);
        }
        return self::$modelBuilder;
    }
}
