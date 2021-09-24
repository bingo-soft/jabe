<?php

namespace BpmPlatform\Model\Bpmn\Impl;

use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\DefinitionsInterface;
use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\Impl\{
    ModelImpl,
    ModelInstanceImpl
};
use BpmPlatform\Model\Xml\Instance\DomDocumentInterface;

class BpmnModelInstanceImpl extends ModelInstanceImpl implements BpmnModelInstanceInterface
{
    public function __construct(ModelImpl $model, ModelBuilder $modelBuilder, DomDocumentInterface $document)
    {
        parent::__construct($model, $modelBuilder, $document);
    }

    public function getDefinitions(): DefinitionsInterface
    {
        return $this->getDocumentElement();
    }

    public function clone(): BpmnModelInstanceInterface
    {
        return new BpmnModelInstanceImpl($this->model, $this->modelBuilder, $this->document->clone());
    }
}
