<?php

namespace Jabe\Model\Wsdl\Impl;

use Jabe\Model\Xml\{
    ModelBuilder,
    ModelInterface
};
use Jabe\Model\Xml\Impl\Instance\ModelElementInstanceImpl;
use Jabe\Model\Wsdl\Impl\Instance\{
    AddressImpl,
    BaseElementImpl,
    BindingImpl,
    ComplexTypeImpl,
    DefinitionsImpl,
    ElementImpl,
    OperationImpl,
    PortImpl,
    RootElementImpl,
    SchemaImpl,
    SequenceImpl,
    ServiceImpl,
    TypesImpl
};

class WsdlModelInstanceImpl
{
    private static $model;
    private static $modelBuilder;

    public static function getModel(): ModelInterface
    {
        if (self::$model === null) {
            $modelBuilder = self::getModelBuilder();
            AddressImpl::registerType($modelBuilder);
            BaseElementImpl::registerType($modelBuilder);
            BindingImpl::registerType($modelBuilder);
            ComplexTypeImpl::registerType($modelBuilder);
            DefinitionsImpl::registerType($modelBuilder);
            ElementImpl::registerType($modelBuilder);
            ModelElementInstanceImpl::registerType($modelBuilder);
            OperationImpl::registerType($modelBuilder);
            PortImpl::registerType($modelBuilder);
            RootElementImpl::registerType($modelBuilder);
            SchemaImpl::registerType($modelBuilder);
            SequenceImpl::registerType($modelBuilder);
            ServiceImpl::registerType($modelBuilder);
            TypesImpl::registerType($modelBuilder);
            self::$model = $modelBuilder->build();
        }

        return self::$model;
    }

    public static function getModelBuilder(): ModelBuilder
    {
        if (self::$modelBuilder === null) {
            self::$modelBuilder = ModelBuilder::createInstance(WsdlModelConstants::MODEL_NAME);
        }
        return self::$modelBuilder;
    }
}
