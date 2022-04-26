<?php

namespace Jabe\Model\Xml\Impl;

use Jabe\Model\Xml\{
    ModelInterface,
    ModelBuilder
};
use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;
use Jabe\Model\Xml\Type\{
    ModelElementTypeInterface,
    ModelElementTypeBuilderInterface,
    ModelTypeInstanceProviderInterface
};
use Jabe\Model\Xml\Impl\Instance\{
    ModelElementInstanceImpl,
    ModelTypeInstanceContext
};
use Jabe\Model\Xml\Impl\Type\{
    ModelElementTypeBuilderImpl
};

class ModelBuilderImpl extends ModelBuilder
{
    private $typeBuilders = [];
    private $model;

    public function __construct(string $modelName)
    {
        $this->model = new ModelImpl($modelName);
    }

    public function alternativeNamespace(string $alternativeNs, string $actualNs): ModelBuilder
    {
        $this->model->declareAlternativeNamespace($alternativeNs, $actualNs);
        return $this;
    }

    public function defineType(
        string $modelInstanceType,
        string $typeName
    ): ModelElementTypeBuilderInterface {
        $typeBuilder = new ModelElementTypeBuilderImpl($modelInstanceType, $typeName, $this->model);
        $this->typeBuilders[] = $typeBuilder;
        return $typeBuilder;
    }

    public function defineGenericType(string $typeName, string $typeNamespaceUri): ModelElementTypeInterface
    {
        $typeBuilder = $this->defineType(ModelElementInstanceInterface::class, $typeName)
            ->namespaceUri($typeNamespaceUri)
            ->instanceProvider(
                new class implements ModelTypeInstanceProviderInterface {
                    public function newInstance(
                        ModelTypeInstanceContext $instanceContext
                    ): ModelElementInstanceInterface {
                        return new ModelElementInstanceImpl($instanceContext);
                    }
                }
            );
        return $typeBuilder->build();
    }

    public function build(): ModelInterface
    {
        foreach ($this->typeBuilders as $typeBuilder) {
            $typeBuilder->buildTypeHierarchy($this->model);
        }
        foreach ($this->typeBuilders as $typeBuilder) {
            $typeBuilder->performModelBuild($this->model);
        }
        return $this->model;
    }
}
