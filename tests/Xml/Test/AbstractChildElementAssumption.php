<?php

namespace Tests\Xml\Test;

use Jabe\Model\Xml\ModelInterface;

abstract class AbstractChildElementAssumption
{
    public $namespaceUri;
    public $childElementType;
    public $minOccurs;
    public $maxOccurs;
    private $model;

    public function __construct(
        ModelInterface $model,
        string $childElementType,
        int $minOccurs = null,
        int $maxOccurs = null,
        string $namespaceUri = null
    ) {
        $this->model = $model;
        $this->childElementType = $this->model->getType($childElementType);
        $this->minOccurs = $minOccurs ?? 0;
        $this->maxOccurs = $maxOccurs ?? -1;
        $this->namespaceUri = $namespaceUri ?? $this->getDefaultNamespace();
    }

    abstract public function getDefaultNamespace(): string;
}
