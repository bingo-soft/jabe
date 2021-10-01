<?php

namespace Tests\Xml\Test;

use BpmPlatform\Model\Xml\ModelInterface;

abstract class AbstractTypeAssumption
{
    public $namespaceUri;
    public $extendsType;
    public $extendsTypeClassName;
    public $isAbstract;
    private $model;

    public function __construct(
        ModelInterface $model,
        bool $isAbstract,
        string $namespaceUri = null,
        string $extendsType = null
    ) {
        $this->model = $model;
        $this->isAbstract = $isAbstract;
        $this->namespaceUri = $namespaceUri ?? $this->getDefaultNamespace();
        $this->extendsType = $this->model->getType($extendsType);
        $this->extendsTypeClassName = $extendsType;
    }

    abstract public function getDefaultNamespace(): string;
}
