<?php

namespace Tests\Xml\TestModel;

use BpmPlatform\Model\Xml\ModelInstanceInterface;
use BpmPlatform\Model\Xml\Impl\ModelInstanceImpl;
use BpmPlatform\Model\Xml\Impl\Parser\AbstractModelParser;
use BpmPlatform\Model\Xml\Instance\DomDocumentInterface;

class TestModelParser extends AbstractModelParser
{
    private const SCHEMA_LOCATION = "tests/Xml/TestModel/Resources/TestModel/Testmodel.xsd";
    private const TEST_NS = "http://test.org/animals";

    public function __construct()
    {
        $this->addSchema(self::TEST_NS, file_get_contents(self::SCHEMA_LOCATION));
    }

    protected function createModelInstance(DomDocumentInterface $document): ModelInstanceInterface
    {
        return new ModelInstanceImpl(TestModel::getTestModel(), TestModel::getModelBuilder(), $document);
    }
}
