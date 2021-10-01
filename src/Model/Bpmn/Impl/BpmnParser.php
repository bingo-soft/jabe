<?php

namespace BpmPlatform\Model\Bpmn\Impl;

use BpmPlatform\Model\Bpmn\Bpmn;
use BpmPlatform\Model\Xml\Impl\Parser\AbstractModelParser;
use BpmPlatform\Model\Xml\Instance\DomDocumentInterface;
use BpmPlatform\Model\Xml\ModelInstanceInterface;

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
