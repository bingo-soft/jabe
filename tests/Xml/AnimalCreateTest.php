<?php

namespace Tests\Xml;

use PHPUnit\Framework\TestCase;
use BpmPlatform\Model\Xml\ModelInstanceInterface;
use BpmPlatform\Model\Xml\Impl\Parser\AbstractModelParser;
use BpmPlatform\Model\Xml\TestModel\{
    Gender,
    TestModelConstants,
    TestModelParser
};
use BpmPlatform\Model\Xml\TestModel\Instance\{
    Animal,
    Animals,
    Bird,
    ChildRelationshipDefinition,
    FriendRelationshipDefinition,
    RelationshipDefinition,
    RelationshipDefinitionRef
};

class AnimalCreateTest extends TestCase
{
    private $tweety;
    private $hedwig;
    private $birdo;
    private $plucky;
    private $fiffy;
    private $timmy;
    private $daisy;
    private $hedwigRelationship;
    private $birdoRelationship;
    private $pluckyRelationship;
    private $fiffyRelationship;
    private $timmyRelationship;
    private $daisyRelationship;
    protected $modelInstance;
    protected $modelParser;

    protected function setUp(): void
    {
        $modelParser = new TestModelParser();
        $this->modelInstance = $modelParser->getEmptyModel();

        $animals = $this->modelInstance->newInstance(Animals::class);
        $this->modelInstance->setDocumentElement($animals);

        $animals->getDomElement()->registerNamespace("tns", TestModelConstants::MODEL_NAMESPACE);

        $this->tweety = $this->createBird($this->modelInstance, "tweety", Gender::FEMALE);
        $this->hedwig = $this->createBird($this->modelInstance, "hedwig", Gender::MALE);
        $this->birdo = $this->createBird($this->modelInstance, "birdo", Gender::FEMALE);
        $this->plucky = $this->createBird($this->modelInstance, "plucky", Gender::UNKNOWN);
        $this->fiffy = $this->createBird($this->modelInstance, "fiffy", Gender::FEMALE);
        $this->timmy = $this->createBird($this->modelInstance, "timmy", Gender::MALE);
        $this->daisy = $this->createBird($this->modelInstance, "daisy", Gender::FEMALE);

        $this->hedwigRelationship = $this->createRelationshipDefinition(
            $this->modelInstance,
            $this->hedwig,
            ChildRelationshipDefinition::class
        );
        $this->addRelationshipDefinition($this->tweety, $this->hedwigRelationship);
        $this->birdoRelationship = $this->createRelationshipDefinition(
            $this->modelInstance,
            $this->birdo,
            ChildRelationshipDefinition::class
        );
        $this->addRelationshipDefinition($this->tweety, $this->birdoRelationship);
        $this->pluckyRelationship = $this->createRelationshipDefinition(
            $this->modelInstance,
            $this->plucky,
            FriendRelationshipDefinition::class
        );
        $this->addRelationshipDefinition($this->tweety, $this->pluckyRelationship);
        $this->fiffyRelationship = $this->createRelationshipDefinition(
            $this->modelInstance,
            $this->fiffy,
            FriendRelationshipDefinition::class
        );
        $this->addRelationshipDefinition($this->tweety, $this->fiffyRelationship);

        $this->tweety->getRelationshipDefinitionRefs()[] = $this->hedwigRelationship;
        $this->tweety->getRelationshipDefinitionRefs()[] = $this->birdoRelationship;
        $this->tweety->getRelationshipDefinitionRefs()[] = $this->pluckyRelationship;
        $this->tweety->getRelationshipDefinitionRefs()[] = $this->fiffyRelationship;

        $this->tweety->getBestFriends()[] = $this->birdo;
        $this->tweety->getBestFriends()[] = $this->plucky;
    }

    private function copyModelInstance(): void
    {
        $this->modelInstance = $this->cloneModelInstance();

        $this->tweety = $this->modelInstance->getModelElementById("tweety");
        $this->hedwig = $this->modelInstance->getModelElementById("hedwig");
        $this->birdo = $this->modelInstance->getModelElementById("birdo");
        $this->plucky = $this->modelInstance->getModelElementById("plucky");
        $this->fiffy = $this->modelInstance->getModelElementById("fiffy");
        $this->timmy = $this->modelInstance->getModelElementById("timmy");
        $this->daisy = $this->modelInstance->getModelElementById("daisy");

        $this->hedwigRelationship = $this->modelInstance->getModelElementById("tweety-hedwig");
        $this->birdoRelationship = $this->modelInstance->getModelElementById("tweety-birdo");
        $this->pluckyRelationship = $this->modelInstance->getModelElementById("tweety-plucky");
        $this->fiffyRelationship = $this->modelInstance->getModelElementById("tweety-fiffy");
    }

    private function createBird(ModelInstanceInterface $modelInstance, string $id, string $gender): Bird
    {
        $bird = $modelInstance->newInstance(Bird::class, $id);
        $bird->setGender($gender);
        $animals = $modelInstance->getDocumentElement();
        $animals->addAnimal($bird);
        return $bird;
    }

    private function createRelationshipDefinition(
        ModelInstanceInterface $modelInstance,
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

    private function addRelationshipDefinition(
        Animal $animalWithRelationship,
        RelationshipDefinition $relationshipDefinition
    ): void {
        $animalInRelationshipWith = $relationshipDefinition->getAnimal();
        $relationshipDefinition->setId($animalWithRelationship->getId() . "-" . $animalInRelationshipWith->getId());
        $animalWithRelationship->getRelationshipDefinitions()[] = $relationshipDefinition;
    }

    private function createEgg(ModelInstance $modelInstance, string $id): Egg
    {
        $egg = $modelInstance->newInstance(Egg::class, $id);
        return $egg;
    }

    public function testSetIdAttributeByHelper(): void
    {
        $newId = "new-" . $this->tweety->getId();
        $this->tweety->setId($newId);
        $this->assertEquals($newId, $this->tweety->getId());
    }

    public function testSetIdAttributeByAttributeName(): void
    {
        $this->tweety->setAttributeValue("id", "duffy", true);
        $this->assertEquals("duffy", $this->tweety->getId());
    }

    public function testRemoveIdAttribute(): void
    {
        $this->tweety->removeAttribute("id");
        $this->assertNull($this->tweety->getId());
    }

    public function testSetNameAttributeByHelper(): void
    {
        $this->tweety->setName("tweety");
        $this->assertEquals("tweety", $this->tweety->getName());
    }

    public function testSetNameAttributeByAttributeName(): void
    {
        $this->tweety->setAttributeValue("name", "daisy");
        $this->assertEquals("daisy", $this->tweety->getName());
    }

    public function testRemoveNameAttribute(): void
    {
        $this->tweety->removeAttribute("name");
        $this->assertNull($this->tweety->getName());
    }

    public function testSetFatherAttributeByHelper(): void
    {
        $this->tweety->setFather($this->timmy);
        $this->assertTrue($this->tweety->getFather()->equals($this->timmy));
    }

    public function testSetFatherAttributeByAttributeName(): void
    {
        $this->tweety->setAttributeValue("father", $this->timmy->getId());
        $this->assertTrue($this->tweety->getFather()->equals($this->timmy));
    }

    public function testSetFatherAttributeByAttributeNameWithNamespace(): void
    {
        $this->tweety->setAttributeValue("father", "tns:hedwig");
        $this->assertTrue($this->tweety->getFather()->equals($this->hedwig));
    }

    public function testRemoveFatherAttribute(): void
    {
        $this->tweety->setFather($this->timmy);
        $this->assertTrue($this->tweety->getFather()->equals($this->timmy));
        $this->tweety->removeAttribute("father");
        $this->assertNull($this->tweety->getFather());
    }

    public function testChangeIdAttributeOfFatherReference(): void
    {
        $this->tweety->setFather($this->timmy);
        $this->assertTrue($this->tweety->getFather()->equals($this->timmy));
        $this->tweety->setId("new-" . $this->tweety->getId());
        $this->assertTrue($this->tweety->getFather()->equals($this->timmy));
    }

    public function testReplaceFatherReferenceWithNewAnimal(): void
    {
        $this->tweety->setFather($this->timmy);
        $this->assertTrue($this->tweety->getFather()->equals($this->timmy));
        $this->timmy->replaceWithElement($this->plucky);
        $this->assertTrue($this->tweety->getFather()->equals($this->plucky));
    }

    public function testSetMotherAttributeByHelper(): void
    {
        $this->tweety->setMother($this->daisy);
        $this->assertTrue($this->tweety->getMother()->equals($this->daisy));
    }

    public function testSetMotherAttributeByAttributeName(): void
    {
        $this->tweety->setAttributeValue("mother", $this->fiffy->getId());
        $this->assertTrue($this->tweety->getMother()->equals($this->fiffy));
    }

    public function testRemoveMotherAttribute(): void
    {
        $this->tweety->setMother($this->daisy);
        $this->assertTrue($this->tweety->getMother()->equals($this->daisy));
        $this->tweety->removeAttribute("mother");
        $this->assertNull($this->tweety->getMother());
    }

    public function testReplaceMotherReferenceWithNewAnimal(): void
    {
        $this->tweety->setMother($this->daisy);
        $this->assertTrue($this->tweety->getMother()->equals($this->daisy));
        $this->daisy->replaceWithElement($this->birdo);
        $this->assertTrue($this->tweety->getMother()->equals($this->birdo));
    }
}
