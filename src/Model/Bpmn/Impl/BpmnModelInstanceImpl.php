<?php

namespace Jabe\Model\Bpmn\Impl;

use Jabe\Model\Bpmn\BpmnModelInstanceInterface;
use Jabe\Model\Bpmn\Instance\DefinitionsInterface;
use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Impl\{
    ModelImpl,
    ModelInstanceImpl
};
use Jabe\Model\Xml\Instance\DomDocumentInterface;

class BpmnModelInstanceImpl extends ModelInstanceImpl implements BpmnModelInstanceInterface
{
    public function __construct(ModelImpl $model, ModelBuilder $modelBuilder, DomDocumentInterface $document)
    {
        parent::__construct($model, $modelBuilder, $document);
    }

    public function getDefinitions(): ?DefinitionsInterface
    {
        return $this->getDocumentElement();
    }

    public function setDefinitions(DefinitionsInterface $definitions): void
    {
        $this->setDocumentElement($definitions);
    }

    public function clone(): BpmnModelInstanceInterface
    {
        return new BpmnModelInstanceImpl($this->model, $this->modelBuilder, $this->document->clone());
    }
}
