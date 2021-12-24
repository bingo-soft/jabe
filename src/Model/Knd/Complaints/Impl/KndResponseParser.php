<?php

namespace BpmPlatform\Model\Knd\Complaints\Impl;

use BpmPlatform\Model\Xml\ModelInstanceInterface;
use BpmPlatform\Model\Xml\Impl\ModelInstanceImpl;
use BpmPlatform\Model\Xml\Impl\Parser\AbstractModelParser;
use BpmPlatform\Model\Xml\Instance\DomDocumentInterface;

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
