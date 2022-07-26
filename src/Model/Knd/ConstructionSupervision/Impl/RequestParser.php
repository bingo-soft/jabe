<?php

namespace Jabe\Model\Knd\ConstructionSupervision\Impl;

use Jabe\Model\Xml\ModelInstanceInterface;
use Jabe\Model\Xml\Impl\ModelInstanceImpl;
use Jabe\Model\Xml\Impl\Parser\AbstractModelParser;
use Jabe\Model\Xml\Instance\DomDocumentInterface;

class RequestParser extends AbstractModelParser
{
    private const SCHEMA_LOCATION = "src/Model/Knd/ConstructionSupervision/Resources/ConstructionSupervision.xsd";
    private const KND_NS = "urn://rostelekom.ru/ConstructionSupervision/1.0.2";

    public function __construct()
    {
        //$this->addSchema(self::KND_NS, self::SCHEMA_LOCATION);
    }

    protected function createModelInstance(DomDocumentInterface $document): ModelInstanceInterface
    {
        return new ModelInstanceImpl(
            RequestModelInstanceImpl::getModel(),
            RequestModelInstanceImpl::getModelBuilder(),
            $document
        );
    }
}
