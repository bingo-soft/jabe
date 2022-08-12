<?php

namespace Jabe\Impl\Model\Wsdl\Impl;

use Xml\ModelInstanceInterface;
use Xml\Impl\ModelInstanceImpl;
use Xml\Impl\Parser\AbstractModelParser;
use Xml\Instance\DomDocumentInterface;

class WsdlParser extends AbstractModelParser
{
    private const SCHEMA_LOCATION = "src/Impl/Model/Wsdl/Resources/wsdl.xsd";
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
