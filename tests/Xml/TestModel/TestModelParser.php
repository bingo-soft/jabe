<?php

namespace Tests\Xml\TestModel;

use Jabe\Model\Xml\ModelInstanceInterface;
use Jabe\Model\Xml\Impl\ModelInstanceImpl;
use Jabe\Model\Xml\Impl\Parser\AbstractModelParser;
use Jabe\Model\Xml\Instance\DomDocumentInterface;

class TestModelParser extends AbstractModelParser
{
    private const SCHEMA_LOCATION = "tests/Xml/TestModel/Resources/TestModel/Testmodel.xsd";
    private const TEST_NS = "http://test.org/animals";

    public function __construct()
    {
        $this->addSchema(self::TEST_NS, self::SCHEMA_LOCATION);
    }

    protected function createModelInstance(DomDocumentInterface $document): ModelInstanceInterface
    {
        return new ModelInstanceImpl(TestModel::getTestModel(), TestModel::getModelBuilder(), $document);
    }
}
