<?php

namespace Jabe\Model\Bpmn\Impl;

use Jabe\Model\Bpmn\Bpmn;
use Jabe\Model\Xml\Impl\Parser\AbstractModelParser;
use Jabe\Model\Xml\Instance\DomDocumentInterface;
use Jabe\Model\Xml\ModelInstanceInterface;

class BpmnParser extends AbstractModelParser
{
    public function __construct()
    {
        $this->addSchema(BpmnModelConstants::BPMN20_NS, BpmnModelConstants::BPMN_20_SCHEMA_LOCATION);
    }

    protected function createModelInstance(DomDocumentInterface $document): ModelInstanceInterface
    {
        return new BpmnModelInstanceImpl(
            Bpmn::getInstance()->getBpmnModel(),
            Bpmn::getInstance()->getBpmnModelBuilder(),
            $document
        );
    }
}
