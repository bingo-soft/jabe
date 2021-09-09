<?php

namespace Tests\Xml\Type;

use PHPUnit\Framework\TestCase;
use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\TestModel\{
    TestModelConstants,
    TestModelParser
};
use BpmPlatform\Model\Xml\TestModel\Instance\{
    Animals,
    Animal,
    Bird,
    FlyingAnimal,
    RelationshipDefinition,
    RelationshipDefinitionRef,
    FlightPartnerRef,
    Egg,
    SpouseRef
};

class ModelElementTypeTest extends TestCase
{
    private $modelInstance;
    private $model;
    private $animalsType;
    private $animalType;
    private $flyingAnimalType;
    private $birdType;

    protected function setUp(): void
    {
        $modelParser = new TestModelParser();
        $this->modelInstance = $modelParser->getEmptyModel();
        $this->model = $this->modelInstance->getModel();
        $this->animalsType = $this->model->getType(Animals::class);
        $this->animalType = $this->model->getType(Animal::class);
        $this->flyingAnimalType = $this->model->getType(FlyingAnimal::class);
        $this->birdType = $this->model->getType(Bird::class);
    }

    public function testTypeName(): void
    {
        $this->assertEquals('animals', $this->animalsType->getTypeName());
        $this->assertEquals('animal', $this->animalType->getTypeName());
        $this->assertEquals('flyingAnimal', $this->flyingAnimalType->getTypeName());
        $this->assertEquals('bird', $this->birdType->getTypeName());
    }

    public function testTypeNamespace(): void
    {
        $this->assertEquals(TestModelConstants::MODEL_NAMESPACE, $this->animalsType->getTypeNamespace());
        $this->assertEquals(TestModelConstants::MODEL_NAMESPACE, $this->animalType->getTypeNamespace());
        $this->assertEquals(TestModelConstants::MODEL_NAMESPACE, $this->flyingAnimalType->getTypeNamespace());
        $this->assertEquals(TestModelConstants::MODEL_NAMESPACE, $this->birdType->getTypeNamespace());
    }

    public function testInstanceType(): void
    {
        $this->assertEquals(Animals::class, $this->animalsType->getInstanceType());
        $this->assertEquals(Animal::class, $this->animalType->getInstanceType());
        $this->assertEquals(FlyingAnimal::class, $this->flyingAnimalType->getInstanceType());
        $this->assertEquals(Bird::class, $this->birdType->getInstanceType());
    }

    public function testAttributes(): void
    {
        $this->assertEmpty($this->animalsType->getAttributes());
        $attrs = ['id', 'name', 'father', 'mother', 'isEndangered', 'gender', 'age', 'bestFriendRefs'];
        $animalAttributes = $this->animalType->getAttributes();
        foreach ($animalAttributes as $attribute) {
            $this->assertContains($attribute->getAttributeName(), $attrs);
        }

        $flyingAnimalAttributes = array_map(function ($attr) {
            return $attr->getAttributeName();
        }, $this->flyingAnimalType->getAttributes());
        $this->assertContains('wingspan', $flyingAnimalAttributes);

        $birdAttributes = array_map(function ($attr) {
            return $attr->getAttributeName();
        }, $this->birdType->getAttributes());
        $this->assertContains('canHaveExtendedWings', $birdAttributes);
    }

    public function testBaseType(): void
    {
        $this->assertNull($this->animalsType->getBaseType());
        $this->assertNull($this->animalType->getBaseType());
        $this->assertEquals($this->animalType, $this->flyingAnimalType->getBaseType());
        $this->assertEquals($this->flyingAnimalType, $this->birdType->getBaseType());
    }

    public function testAbstractType(): void
    {
        $this->assertFalse($this->animalsType->isAbstract());
        $this->assertTrue($this->animalType->isAbstract());
        $this->assertTrue($this->flyingAnimalType->isAbstract());
        $this->assertFalse($this->birdType->isAbstract());
    }

    public function testModel(): void
    {
        $this->assertEquals($this->animalsType->getModel(), $this->model);
        $this->assertEquals($this->animalType->getModel(), $this->model);
        $this->assertEquals($this->flyingAnimalType->getModel(), $this->model);
        $this->assertEquals($this->birdType->getModel(), $this->model);
    }

    public function testInstances(): void
    {
        $this->assertEmpty($this->animalsType->getInstances($this->modelInstance));
        $this->assertEmpty($this->animalType->getInstances($this->modelInstance));
        $this->assertEmpty($this->flyingAnimalType->getInstances($this->modelInstance));
        $this->assertEmpty($this->birdType->getInstances($this->modelInstance));

        $animals = $this->animalsType->newInstance($this->modelInstance);
        $this->modelInstance->setDocumentElement($animals);

        $animals->addAnimal($this->birdType->newInstance($this->modelInstance));
        $animals->addAnimal($this->birdType->newInstance($this->modelInstance));
        $animals->addAnimal($this->birdType->newInstance($this->modelInstance));

        $this->assertCount(1, $this->animalsType->getInstances($this->modelInstance));
        $this->assertEmpty($this->animalType->getInstances($this->modelInstance));
        $this->assertEmpty($this->flyingAnimalType->getInstances($this->modelInstance));
        $this->assertCount(3, $this->birdType->getInstances($this->modelInstance));

        $this->expectException(\BpmPlatform\Model\Xml\Exception\ModelTypeException::class);
        $this->animalType->newInstance($this->modelInstance);
    }

    public function testChildElementTypes(): void
    {
        $relationshipDefinitionType = $this->model->getType(RelationshipDefinition::class);
        $relationshipDefinitionRefType = $this->model->getType(RelationshipDefinitionRef::class);
        $flightPartnerRefType = $this->model->getType(FlightPartnerRef::class);
        $eggType = $this->model->getType(Egg::class);
        $spouseRefType = $this->model->getType(SpouseRef::class);
        $this->assertContains($this->animalType, $this->animalsType->getChildElementTypes());
        $this->assertContains($relationshipDefinitionType, $this->animalType->getChildElementTypes());
        $this->assertContains($relationshipDefinitionRefType, $this->animalType->getChildElementTypes());
        $this->assertContains($flightPartnerRefType, $this->flyingAnimalType->getChildElementTypes());
        $this->assertContains($eggType, $this->birdType->getChildElementTypes());
        $this->assertContains($spouseRefType, $this->birdType->getChildElementTypes());
    }
}
