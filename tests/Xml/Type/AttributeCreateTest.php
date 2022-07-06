<?php

namespace Tests\Xml\Type;

use PHPUnit\Framework\TestCase;
use Jabe\Model\Xml\ModelBuilder;
use Tests\Xml\TestModel\{
    Gender,
    TestModelConstants,
    TestModelParser
};
use Tests\Xml\TestModel\TestModelTest;
use Tests\Xml\TestModel\Instance\{
    Animal,
    Animals
};

class AttributeCreateTest extends TestModelTest
{
    protected $tweety;
    protected $idAttribute;
    protected $nameAttribute;
    protected $fatherAttribute;

    protected function setUp(): void
    {
        $this->modelParser = new TestModelParser();
        $this->modelInstance = $this->modelParser->getEmptyModel();

        $animals = $this->modelInstance->newInstance(Animals::class);
        $this->modelInstance->setDocumentElement($animals);

        $this->tweety = $this->createBird($this->modelInstance, "tweety", Gender::FEMALE);

        $this->idAttribute = $this->tweety->getElementType()->getAttribute("id");
        $this->nameAttribute = $this->tweety->getElementType()->getAttribute("name");
        $this->fatherAttribute = $this->tweety->getElementType()->getAttribute("father");
    }

    public function testOwningElementType(): void
    {
        $animalType = $this->modelInstance->getModel()->getType(Animal::class);
        $this->assertTrue($this->idAttribute->getOwningElementType() instanceof $animalType);
        $this->assertTrue($this->nameAttribute->getOwningElementType() instanceof $animalType);
        $this->assertTrue($this->fatherAttribute->getOwningElementType() instanceof $animalType);
    }

    public function testSetAttributeValue(): void
    {
        $identifier = "new-" . $this->tweety->getId();
        $this->idAttribute->setValue($this->tweety, $identifier);
        $this->assertEquals($identifier, $this->idAttribute->getValue($this->tweety));
    }

    public function testSetAttributeValueWithoutUpdateReference(): void
    {
        $identifier = "new-" . $this->tweety->getId();
        $this->idAttribute->setValue($this->tweety, $identifier, false);
        $this->assertEquals($identifier, $this->idAttribute->getValue($this->tweety));
    }

    public function testSetDefaultValue(): void
    {
        $defaultName = "default-name";
        $this->assertNull($this->tweety->getName());
        $this->assertNull($this->nameAttribute->getDefaultValue());

        $this->nameAttribute->setDefaultValue($defaultName);
        $this->assertFalse($this->tweety->getName() === null);
        $this->assertEquals($this->tweety->getName(), $this->nameAttribute->getDefaultValue());


        $this->tweety->setName("not-" . $defaultName);
        $this->assertFalse($this->tweety->getName() == $defaultName);

        $this->tweety->removeAttribute("name");
        $this->assertTrue($this->tweety->getName() == $defaultName);
        $this->nameAttribute->setDefaultValue(null);
        $this->assertNull($this->nameAttribute->getDefaultValue());
    }

    public function testRequired(): void
    {
        $this->tweety->removeAttribute("name");
        $this->assertFalse($this->nameAttribute->isRequired());

        $this->nameAttribute->setRequired(true);
        $this->assertTrue($this->nameAttribute->isRequired());

        $this->nameAttribute->setRequired(false);
    }

    public function testSetNamespaceUri(): void
    {
        $testNamespace = "http://test.org/test";

        $this->idAttribute->setNamespaceUri($testNamespace);
        $this->assertEquals($testNamespace, $this->idAttribute->getNamespaceUri());

        $this->idAttribute->setNamespaceUri(null);
        $this->assertNull($this->idAttribute->getNamespaceUri());
    }

    public function testIdAttribute(): void
    {
        $this->assertTrue($this->idAttribute->isIdAttribute());
        $this->assertFalse($this->nameAttribute->isIdAttribute());
        $this->assertFalse($this->fatherAttribute->isIdAttribute());
    }

    public function testAttributeName(): void
    {
        $this->assertEquals("id", $this->idAttribute->getAttributeName());
        $this->assertEquals("name", $this->nameAttribute->getAttributeName());
        $this->assertEquals("father", $this->fatherAttribute->getAttributeName());
    }

    public function testRemoveAttribute(): void
    {
        $this->tweety->setName("test");
        $this->assertFalse($this->tweety->getName() === null);
        $this->assertEquals($this->tweety->getName(), $this->nameAttribute->getValue($this->tweety));

        $this->nameAttribute->removeAttribute($this->tweety);
        $this->assertNull($this->tweety->getName());
        $this->assertEquals(null, $this->nameAttribute->getValue($this->tweety));
    }

    public function testIncomingReferences(): void
    {
        $this->assertFalse(empty($this->idAttribute->getIncomingReferences()));
        $this->assertTrue(empty($this->nameAttribute->getIncomingReferences()));
        $this->assertTrue(empty($this->fatherAttribute->getIncomingReferences()));
    }

    public function testOutgoingReferences(): void
    {
        $this->assertTrue(empty($this->idAttribute->getOutgoingReferences()));
        $this->assertTrue(empty($this->nameAttribute->getOutgoingReferences()));
        $this->assertFalse(empty($this->fatherAttribute->getOutgoingReferences()));
    }
}
