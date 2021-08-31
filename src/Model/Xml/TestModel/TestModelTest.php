<?php

namespace BpmPlatform\Model\Xml\TestModel;

use BpmPlatform\Model\Xml\ModelInstanceInterface;
use BpmPlatform\Model\Xml\Impl\Parser\AbstractModelParser;
use BpmPlatform\Model\Xml\TestModel\Instance\{
    Animal,
    Bird,
    RelationshipDefinition,
    Egg
};

abstract class TestModelTest
{
    protected $testName;

    private $testModelInstance;

    private $modelParser;

    protected $modelInstance;

    public function __construct(
        string $testName,
        ModelInstanceInterface $testModelInstance,
        AbstractModelParser $modelParser
    ) {
        $this->testName = $testName;
        $this->testModelInstance = $testModelInstance;
        $this->modelParser = $modelParser;
    }

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
        $testXml = $test->getSimpleName() + ".xml";
        $testXmlAsStream = $test->getResourceAsStream($testXml);
        $modelInstance = $modelParser->parseModelFromStream($testXmlAsStream);
        return ["parsed", $modelInstance, $modelParser];
    }

    public static function createBird(ModelInstanceInterface $modelInstance, string $id, string $gender): Bird
    {
        $bird = $modelInstance->newInstance(Bird::class, $id);
        $bird->setGender($gender);
        $animals = $modelInstance->getDocumentElement();
        $animals->getAnimals()->add($bird);
        return $bird;
    }

    protected static function createRelationshipDefinition(
        ModelInstance $modelInstance,
        Animal $animalInRelationshipWith,
        string $relationshipDefinitionClass
    ): RelationshipDefinition {
        $relationshipDefinition = $modelInstance->newInstance(
            $relationshipDefinitionClass,
            "relationship-" . $animalInRelationshipWith->getId()
        );
        $relationshipDefinition->setAnimal($animalInRelationshipWith);
        return $relationshipDefinition;
    }

    public static function addRelationshipDefinition(
        Animal $animalWithRelationship,
        RelationshipDefinition $relationshipDefinition
    ): void {
        $animalInRelationshipWith = $relationshipDefinition->getAnimal();
        $relationshipDefinition->setId($animalWithRelationship->getId() . "-" . $animalInRelationshipWith->getId());
        $animalWithRelationship->getRelationshipDefinitions()->add($relationshipDefinition);
    }

    public static function createEgg(ModelInstance $modelInstance, string $id): Egg
    {
        $egg = $modelInstance->newInstance(Egg::class, $id);
        return $egg;
    }
}
