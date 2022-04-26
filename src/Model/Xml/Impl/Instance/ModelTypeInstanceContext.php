<?php

namespace Jabe\Model\Xml\Impl\Instance;

use Jabe\Model\Xml\Impl\ModelInstanceImpl;
use Jabe\Model\Xml\Impl\Type\ModelElementTypeImpl;
use Jabe\Model\Xml\Instance\DomElementInterface;

class ModelTypeInstanceContext
{
    private $model;
    private $domElement;
    private $modelType;

    public function __construct(
        DomElementInterface $domElement,
        ModelInstanceImpl $model,
        ModelElementTypeImpl $modelType
    ) {
        $this->model = $model;
        $this->domElement = $domElement;
        $this->modelType = $modelType;
    }

    public function getDomElement(): DomElementInterface
    {
        return $this->domElement;
    }

    public function getModel(): ModelInstanceImpl
    {
        return $this->model;
    }

    public function getModelType(): ModelElementTypeImpl
    {
        return $this->modelType;
    }
}
