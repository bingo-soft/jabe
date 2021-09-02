<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use BpmPlatform\Model\Xml\ModelBuilder;
use BpmPlatform\Model\Xml\TestModel\{
    TestModel,
    TestModelConstants
};
use BpmPlatform\Model\Xml\TestModel\Instance\{
    Animals,
    Animal,
    Bird,
    FlyingAnimal,
    RelationshipDefinition
};

class ModelTest extends TestCase
{
    private $model;

    protected function setUp(): void
    {
        $this->model = TestModel::getTestModel();
    }

    public function testGetTypes(): void
    {
        $types = $this->model->getTypes();
        $this->assertFalse(empty($types));
        $this->assertContains($this->model->getType(Animals::class), $types);
        $this->assertContains($this->model->getType(Animal::class), $types);
        $this->assertContains($this->model->getType(FlyingAnimal::class), $types);
        $this->assertContains($this->model->getType(Bird::class), $types);
        $this->assertContains($this->model->getType(RelationshipDefinition::class), $types);
    }

    public function testGetType(): void
    {
        $flyingAnimalType = $this->model->getType(FlyingAnimal::class);
        $this->assertEquals($flyingAnimalType->getInstanceType(), FlyingAnimal::class);
    }

    public function testGetTypeForName(): void
    {
        $birdType = $this->model->getTypeForName(null, TestModelConstants::ELEMENT_NAME_BIRD);
        $this->assertNull($birdType);

        $birdType = $this->model->getTypeForName(
            TestModelConstants::MODEL_NAMESPACE,
            TestModelConstants::ELEMENT_NAME_BIRD
        );
        $this->assertEquals($birdType->getInstanceType(), Bird::class);
    }

    public function testGetModelName(): void
    {
        $this->assertEquals(TestModelConstants::MODEL_NAME, $this->model->getModelName());
    }
}
