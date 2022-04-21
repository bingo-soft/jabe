<?php

namespace BpmPlatform\Model\Wsdl\Impl;

use BpmPlatform\Model\Xml\ModelInstanceInterface;
use BpmPlatform\Model\Xml\Impl\ModelInstanceImpl;
use BpmPlatform\Model\Xml\Impl\Parser\AbstractModelParser;
use BpmPlatform\Model\Xml\Instance\DomDocumentInterface;

class WsdlParser extends AbstractModelParser
{
    private const SCHEMA_LOCATION = "src/Model/Wsdl/Resources/wsdl.xsd";
    private const WSDL_NS = "http://schemas.xmlsoap.org/wsdl/";

    public function __construct()
    {
        $this->addSchema(self::WSDL_NS, self::SCHEMA_LOCATION);
    }

    protected function createModelInstance(DomDocumentInterface $document): ModelInstanceInterface
    {
        return new ModelInstanceImpl(
            WsdlModelInstanceImpl::getModel(),
            WsdlModelInstanceImpl::getModelBuilder(),
            $document
        );
    }
}
