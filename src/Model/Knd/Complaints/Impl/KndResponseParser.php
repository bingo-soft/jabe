<?php

namespace Jabe\Model\Knd\Complaints\Impl;

use Jabe\Model\Xml\ModelInstanceInterface;
use Jabe\Model\Xml\Impl\ModelInstanceImpl;
use Jabe\Model\Xml\Impl\Parser\AbstractModelParser;
use Jabe\Model\Xml\Instance\DomDocumentInterface;

class KndResponseParser extends AbstractModelParser
{
    private const SCHEMA_LOCATION = "src/Model/Knd/Complaints/Resources/Complaints.xsd";
    private const KND_NS = "http://tor.knd.evolenta.ru/do_knd/1.0.0";

    public function __construct()
    {
        $this->addSchema(self::KND_NS, self::SCHEMA_LOCATION);
    }

    protected function createModelInstance(DomDocumentInterface $document): ModelInstanceInterface
    {
        return new ModelInstanceImpl(
            KndResponseModelInstanceImpl::getModel(),
            KndResponseModelInstanceImpl::getModelBuilder(),
            $document
        );
    }
}
