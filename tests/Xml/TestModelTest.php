<?php

namespace Tests\Xml;

use PHPUnit\Framework\TestCase;
use BpmPlatform\Model\Xml\ModelInstanceInterface;
use BpmPlatform\Model\Xml\Impl\Parser\AbstractModelParser;
use BpmPlatform\Model\Xml\TestModel\TestModelParser;
use BpmPlatform\Model\Xml\TestModel\Instance\{
    Animal,
    Bird,
    RelationshipDefinition,
    Egg
};

abstract class TestModelTest extends TestCase
{
    protected $testName;

    protected $testModelInstance;

    protected $modelParser;

    protected $modelInstance;

    public function cloneModelInstance(): ModelInstanceInterface
    {
        return $this->testModelInstance->clone();
    }

    /**
     * @param mixed $test
     *
     * @return array
     */
    protected static function parseModel($test): array
    {
        $modelParser = new TestModelParser();

        $ref = new \ReflectionClass($test);
        $testXml = $ref->getShortName() + ".xml";
        echo $ref->getShortName() + ".xml" + "\n";
        $testXmlAsStream = $test->getResourceAsStream($testXml);
        $modelInstance = $modelParser->parseModelFromStream($testXmlAsStream);
        return ["parsed", $modelInstance, $modelParser];
    }
}
