<?php

namespace Tests\Xml\TestModel\Instance;

use BpmPlatform\Model\Xml\Exception\ModelException;
use BpmPlatform\Model\Xml\Impl\Util\StringUtil;
use BpmPlatform\Model\Xml\ModelInstanceInterface;
use BpmPlatform\Model\Xml\Impl\Parser\AbstractModelParser;
use Tests\Xml\TestModel\{
    Gender,
    TestModelConstants,
    TestModelParser
};
use Tests\Xml\TestModel\TestModelTest;

class UnknownAnimalTest extends TestModelTest
{
    private $wanda;
    private $flipper;

    protected function setUp(): void
    {
        parent::parseModel("UnknownAnimalTest");
        $this->copyModelInstance();
    }

    private function copyModelInstance(): void
    {
        $this->wanda = $this->modelInstance->getModelElementById("wanda");
        $this->flipper = $this->modelInstance->getModelElementById("flipper");
    }

    public function testGetUnknownAnimalById(): void
    {
        $this->assertFalse($this->wanda == null);
        $this->assertEquals("wanda", $this->wanda->getAttributeValue("id"));
        $this->assertEquals("Female", $this->wanda->getAttributeValue("gender"));
        $this->assertEquals("fish", $this->wanda->getAttributeValue("species"));

        $this->assertFalse($this->flipper == null);
        $this->assertEquals("flipper", $this->flipper->getAttributeValue("id"));
        $this->assertEquals("Male", $this->flipper->getAttributeValue("gender"));
        $this->assertEquals("dolphin", $this->flipper->getAttributeValue("species"));
    }

    public function testGetUnknownAnimalByType(): void
    {
        $unknownAnimalType = $this->modelInstance->registerGenericType(TestModelConstants::MODEL_NAMESPACE, "unknownAnimal");
        $unknownAnimals = $this->modelInstance->getModelElementsByType($unknownAnimalType);
        $this->assertCount(2, $unknownAnimals);

        $wanda = $unknownAnimals[0];
        $this->assertEquals("wanda", $wanda->getAttributeValue("id"));
        $this->assertEquals("Female", $wanda->getAttributeValue("gender"));
        $this->assertEquals("fish", $wanda->getAttributeValue("species"));

        $flipper = $unknownAnimals[1];
        $this->assertEquals("flipper", $flipper->getAttributeValue("id"));
        $this->assertEquals("Male", $flipper->getAttributeValue("gender"));
        $this->assertEquals("dolphin", $flipper->getAttributeValue("species"));
    }

    public function testAddUnknownAnimal(): void
    {
        $unknownAnimalType = $this->modelInstance->registerGenericType(TestModelConstants::MODEL_NAMESPACE, "unknownAnimal");
        $animalsType = $this->modelInstance->getModel()->getType(Animals::class);
        $animalType = $this->modelInstance->getModel()->getType(Animal::class);

        $unknownAnimal = $this->modelInstance->newInstance($unknownAnimalType);
        $this->assertFalse($unknownAnimal == null);
        $unknownAnimal->setAttributeValue("id", "new-animal", true);
        $unknownAnimal->setAttributeValue("gender", "Unknown");
        $unknownAnimal->setAttributeValue("species", "unknown");

        $animals = $this->modelInstance->getModelElementsByType($animalsType)[0];
        $childElementsByType = $animals->getChildElementsByType($animalType);
        $animals->insertElementAfter($unknownAnimal, $childElementsByType[2]);
        $this->assertCount(3, $animals->getChildElementsByType($unknownAnimalType));
    }

    public function testGetUnknownAttribute(): void
    {
        $this->assertEquals("true", $this->flipper->getAttributeValue("famous"));
        $this->assertFalse("true" == $this->wanda->getAttributeValue("famous"));

        $this->wanda->setAttributeValue("famous", "true");
        $this->assertEquals("true", $this->wanda->getAttributeValue("famous"));
    }

    public function testAddRelationshipDefinitionToUnknownAnimal(): void
    {
        $friendRelationshipDefinition = $this->modelInstance->newInstance(FriendRelationshipDefinition::class);
        $friendRelationshipDefinition->setId("friend-relationship");
        $friendRelationshipDefinition->setAttributeValue("animalRef", $this->flipper->getAttributeValue("id"));

        try {
            $this->wanda->addChildElement($friendRelationshipDefinition);
        } catch (\Exception $e) {
            $this->assertEquals(ModelException::class, get_class($e));
        }

        $this->wanda->insertElementAfter($friendRelationshipDefinition, null);

        $tweety = $this->modelInstance->getModelElementById("tweety");
        $childRelationshipDefinition = $this->modelInstance->newInstance(ChildRelationshipDefinition::class);
        $childRelationshipDefinition->setId("child-relationship");
        $childRelationshipDefinition->setAnimal($tweety);

        $this->wanda->insertElementAfter($childRelationshipDefinition, $friendRelationshipDefinition);
    }

    public function testAddChildToUnknownAnimal(): void
    {
        $this->assertEmpty($this->wanda->getChildElementsByType($this->flipper->getElementType()));
        $this->wanda->insertElementAfter($this->flipper, null);
        $this->assertCount(1, $this->wanda->getChildElementsByType($this->flipper->getElementType()));
    }

    public function testRemoveChildOfUnknownAnimal(): void
    {
        $this->assertFalse($this->wanda->removeChildElement($this->flipper));
        $this->wanda->insertElementAfter($this->flipper, null);
        $this->assertTrue($this->wanda->removeChildElement($this->flipper));
        $this->assertEmpty($this->wanda->getChildElementsByType($this->flipper->getElementType()));
    }

    public function testReplaceChildOfUnknownAnimal(): void
    {
        $yogi = $this->modelInstance->newInstance($this->flipper->getElementType());
        $yogi->setAttributeValue("id", "yogi-bear", true);
        $yogi->setAttributeValue("gender", "Male");
        $yogi->setAttributeValue("species", "bear");

        $this->assertEmpty($this->wanda->getChildElementsByType($this->flipper->getElementType()));
        $this->wanda->insertElementAfter($this->flipper, null);
        $this->assertCount(1, $this->wanda->getChildElementsByType($this->flipper->getElementType()));
        $this->wanda->replaceChildElement($this->flipper, $yogi);
        $this->assertCount(1, $this->wanda->getChildElementsByType($this->flipper->getElementType()));
        $this->assertTrue($this->wanda->getChildElementsByType($this->flipper->getElementType())[0]->equals($yogi));
    }
}
