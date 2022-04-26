<?php

namespace Jabe\Model\Wsdl\Impl;

use Jabe\Model\Xml\ModelInstanceInterface;
use Jabe\Model\Xml\Impl\ModelInstanceImpl;
use Jabe\Model\Xml\Impl\Parser\AbstractModelParser;
use Jabe\Model\Xml\Instance\DomDocumentInterface;

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
