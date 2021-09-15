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
        $this->modelParser = new TestModelParser();
        $this->modelInstance = $this->modelParser->getEmptyModel();

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

        $this->tweety->addRelationshipDefinition($this->hedwigRelationship);
        $this->tweety->addRelationshipDefinition($this->birdoRelationship);
        $this->tweety->addRelationshipDefinition($this->pluckyRelationship);
        $this->tweety->addRelationshipDefinition($this->fiffyRelationship);

        $this->tweety->addFriend($this->birdo);
        $this->tweety->addFriend($this->plucky);

        $this->timmyRelationship = $this->createRelationshipDefinition(
            $this->modelInstance,
            $this->timmy,
            FriendRelationshipDefinition::class
        );
        $this->daisyRelationship = $this->createRelationshipDefinition(
            $this->modelInstance,
            $this->daisy,
            ChildRelationshipDefinition::class
        );
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

        $this->timmyRelationship = $this->createRelationshipDefinition(
            $this->modelInstance,
            $this->timmy,
            FriendRelationshipDefinition::class
        );
        $this->daisyRelationship = $this->createRelationshipDefinition(
            $this->modelInstance,
            $this->daisy,
            ChildRelationshipDefinition::class
        );
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
        $animalWithRelationship->addRelationship($relationshipDefinition);
    }

    private function createEgg(ModelInstance $modelInstance, string $id): Egg
    {
        $egg = $modelInstance->newInstance(Egg::class, $id);
        return $egg;
    }

    public function testGetElementById(): void
    {
        $this->assertFalse(
            $this->tweety->getModelInstance()->getModelElementById($this->tweety->getId()) == null
        );
        $this->tweety->setId("new-" . $this->tweety->getId());
        $this->assertFalse(
            $this->tweety->getModelInstance()->getModelElementById($this->tweety->getId()) == null
        );
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

    public function testChangeIdAttributeOfMotherReference() {
        $this->tweety->setMother($this->daisy);
        $this->assertTrue($this->tweety->getMother()->equals($this->daisy));
        $this->daisy->setId("new-" . $this->daisy->getId());
        $this->assertTrue($this->tweety->getMother()->equals($this->daisy));
    }

    public function testSetIsEndangeredAttributeByHelper(): void
    {
        $this->tweety->setIsEndangered(true);
        $this->assertTrue($this->tweety->isEndangered());
    }

    public function testSetIsEndangeredAttributeByAttributeName(): void
    {
        $this->tweety->setAttributeValue("isEndangered", "false");
        $this->assertFalse($this->tweety->isEndangered());
    }

    public function testRemoveIsEndangeredAttribute(): void
    {
        $this->tweety->removeAttribute("isEndangered");
        // default value of isEndangered: false
        $this->assertFalse($this->tweety->isEndangered());
    }

    public function testSetGenderAttributeByHelper(): void
    {
        $this->tweety->setGender(Gender::MALE);
        $this->assertEquals(Gender::MALE, $this->tweety->getGender());
    }

    public function testSetGenderAttributeByAttributeName(): void
    {
        $this->tweety->setAttributeValue("gender", Gender::UNKNOWN);
        $this->assertEquals(Gender::UNKNOWN, $this->tweety->getGender());
    }

    public function testRemoveGenderAttribute(): void
    {
        $this->tweety->removeAttribute("gender");
        $this->assertNull($this->tweety->getGender());

        try {
            $this->expectException(\BpmPlatform\Model\Xml\Exception\ModelValidationException::class);
            $this->validateModel();
        } finally {
            $this->tweety->setGender(Gender::FEMALE);
        }
    }

    public function testSetAgeAttributeByHelper(): void
    {
        $this->tweety->setAge(13);
        $this->assertEquals(13, $this->tweety->getAge());
    }

    public function testSetAgeAttributeByAttributeName(): void
    {
        $this->tweety->setAttributeValue("age", "23");
        $this->assertEquals(23, $this->tweety->getAge());
    }

    public function testRemoveAgeAttribute(): void
    {
        $this->tweety->removeAttribute("age");
        $this->assertNull($this->tweety->getAge());
    }

    private function validateModel(): void
    {
        $this->modelParser->validateModel($this->modelInstance->getDocument());
    }

    public function testAddRelationshipDefinitionsByHelper(): void
    {
        $this->assertCount(4, $this->tweety->getRelationshipDefinitions());
        $rels = [
            $this->hedwigRelationship,
            $this->birdoRelationship,
            $this->pluckyRelationship,
            $this->fiffyRelationship
        ];
        foreach ($rels as $rel) {
            $exists = false;
            foreach ($this->tweety->getRelationshipDefinitions() as $rel2) {
                if ($rel2->equals($rel)) {
                    $exists = true;
                }
            }
            $this->assertTrue($exists);
        }
        $this->tweety->addRelationship($this->timmyRelationship);
        $this->tweety->addRelationship($this->daisyRelationship);
        $rels[] = $this->timmyRelationship;
        $rels[] = $this->daisyRelationship;
        $this->assertCount(6, $this->tweety->getRelationshipDefinitions());
        foreach ($rels as $rel) {
            $exists = false;
            foreach ($this->tweety->getRelationshipDefinitions() as $rel2) {
                if ($rel2->equals($rel)) {
                    $exists = true;
                }
            }
            $this->assertTrue($exists);
        }
    }

    public function testUpdateRelationshipDefinitionsByIdByHelper(): void
    {
        $this->hedwigRelationship->setId("new-" . $this->hedwigRelationship->getId());
        $this->pluckyRelationship->setId("new-" . $this->pluckyRelationship->getId());
        $this->assertCount(4, $this->tweety->getRelationshipDefinitions());
        $rels = [
            $this->hedwigRelationship,
            $this->birdoRelationship,
            $this->pluckyRelationship,
            $this->fiffyRelationship
        ];
        foreach ($rels as $rel) {
            $exists = false;
            foreach ($this->tweety->getRelationshipDefinitions() as $rel2) {
                if ($rel2->equals($rel)) {
                    $exists = true;
                }
            }
            $this->assertTrue($exists);
        }
    }

    public function testUpdateRelationshipDefinitionsByIdByAttributeName(): void
    {
        $this->birdoRelationship->setAttributeValue("id", "new-" . $this->birdoRelationship->getId(), true);
        $this->fiffyRelationship->setAttributeValue("id", "new-" . $this->fiffyRelationship->getId(), true);
        $this->assertCount(4, $this->tweety->getRelationshipDefinitions());
        $rels = [
            $this->hedwigRelationship,
            $this->birdoRelationship,
            $this->pluckyRelationship,
            $this->fiffyRelationship
        ];
        foreach ($rels as $rel) {
            $exists = false;
            foreach ($this->tweety->getRelationshipDefinitions() as $rel2) {
                if ($rel2->equals($rel)) {
                    $exists = true;
                }
            }
            $this->assertTrue($exists);
        }
    }

    public function testUpdateRelationshipDefinitionsByReplaceElements(): void
    {
        $this->hedwigRelationship->replaceWithElement($this->timmyRelationship);
        $this->pluckyRelationship->replaceWithElement($this->daisyRelationship);
        $rels = [
            $this->timmyRelationship,
            $this->birdoRelationship,
            $this->daisyRelationship,
            $this->fiffyRelationship
        ];
        foreach ($rels as $rel) {
            $exists = false;
            foreach ($this->tweety->getRelationshipDefinitions() as $rel2) {
                if ($rel2->equals($rel)) {
                    $exists = true;
                }
            }
            $this->assertTrue($exists);
        }
    }

    public function testClearRelationshipDefinitions(): void
    {
        $this->tweety->clearRelationships();
        $this->assertEmpty($this->tweety->getRelationshipDefinitions());
    }

    public function testAddRelationsDefinitionRefsByHelper(): void
    {
        $this->addRelationshipDefinition($this->tweety, $this->timmyRelationship);
        $this->addRelationshipDefinition($this->tweety, $this->daisyRelationship);

        $this->tweety->addRelationshipDefinition($this->timmyRelationship);
        $this->tweety->addRelationshipDefinition($this->daisyRelationship);

        $this->assertCount(6, $this->tweety->getRelationshipDefinitions());

        $rels = [
            $this->hedwigRelationship,
            $this->pluckyRelationship,
            $this->timmyRelationship,
            $this->birdoRelationship,
            $this->daisyRelationship,
            $this->fiffyRelationship
        ];
        foreach ($rels as $rel) {
            $exists = false;
            foreach ($this->tweety->getRelationshipDefinitions() as $rel2) {
                if ($rel2->equals($rel)) {
                    $exists = true;
                }
            }
            $this->assertTrue($exists);
        }
    }

    public function testUpdateRelationshipDefinitionRefsByIdByHelper(): void
    {
        $this->hedwigRelationship->setId("child-relationship");
        $this->pluckyRelationship->setId("friend-relationship");
        $this->assertCount(4, $this->tweety->getRelationshipDefinitions());
        $rels = [
            $this->hedwigRelationship,
            $this->pluckyRelationship,
            $this->birdoRelationship,
            $this->fiffyRelationship
        ];
        foreach ($rels as $rel) {
            $exists = false;
            foreach ($this->tweety->getRelationshipDefinitions() as $rel2) {
                if ($rel2->equals($rel)) {
                    $exists = true;
                }
            }
            $this->assertTrue($exists);
        }
    }

    public function testUpdateRelationshipDefinitionRefsByIdByAttributeName(): void
    {
        $this->birdoRelationship->setAttributeValue("id", "birdo-relationship", true);
        $this->fiffyRelationship->setAttributeValue("id", "fiffy-relationship", true);
        $this->assertCount(4, $this->tweety->getRelationshipDefinitionRefs());
        $rels = [
            $this->hedwigRelationship,
            $this->pluckyRelationship,
            $this->birdoRelationship,
            $this->fiffyRelationship
        ];
        foreach ($rels as $rel) {
            $exists = false;
            foreach ($this->tweety->getRelationshipDefinitionRefs() as $rel2) {
                if ($rel2->equals($rel)) {
                    $exists = true;
                }
            }
            $this->assertTrue($exists);
        }
    }

    public function testUpdateRelationshipDefinitionRefsByReplaceElements(): void
    {
        $this->hedwigRelationship->replaceWithElement($this->timmyRelationship);
        $this->pluckyRelationship->replaceWithElement($this->daisyRelationship);
        $this->assertCount(4, $this->tweety->getRelationshipDefinitionRefs());
        $rels = [
            $this->timmyRelationship,
            $this->daisyRelationship,
            $this->birdoRelationship,
            $this->fiffyRelationship
        ];
        foreach ($rels as $rel) {
            $exists = false;
            foreach ($this->tweety->getRelationshipDefinitionRefs() as $rel2) {
                if ($rel2->equals($rel)) {
                    $exists = true;
                }
            }
            $this->assertTrue($exists);
        }
    }

    public function testUpdateRelationshipDefinitionRefsByRemoveElements(): void
    {
        $this->tweety->removeRelationship($this->birdoRelationship);
        $this->tweety->removeRelationship($this->fiffyRelationship);
        $this->assertCount(2, $this->tweety->getRelationshipDefinitionRefs());
        $rels = [
            $this->hedwigRelationship,
            $this->pluckyRelationship
        ];
        foreach ($rels as $rel) {
            $exists = false;
            foreach ($this->tweety->getRelationshipDefinitionRefs() as $rel2) {
                if ($rel2->equals($rel)) {
                    $exists = true;
                }
            }
            $this->assertTrue($exists);
        }
    }

    public function testUpdateRelationshipDefinitionRefsByRemoveIdAttribute(): void
    {
        $this->birdoRelationship->removeAttribute("id");
        $this->pluckyRelationship->removeAttribute("id");
        $this->assertCount(2, $this->tweety->getRelationshipDefinitionRefs());
        $rels = [
            $this->hedwigRelationship,
            $this->fiffyRelationship
        ];
        foreach ($rels as $rel) {
            $exists = false;
            foreach ($this->tweety->getRelationshipDefinitionRefs() as $rel2) {
                if ($rel2->equals($rel)) {
                    $exists = true;
                }
            }
            $this->assertTrue($exists);
        }
    }

    public function testClearRelationshipDefinitionsRefs(): void
    {
        $this->tweety->clearRelationshipDefinitions();
        $this->assertEmpty($this->tweety->getRelationshipDefinitionRefs());
        // should not affect animal relationship definitions
        $this->assertCount(4, $this->tweety->getRelationshipDefinitions());
    }

    public function testClearRelationshipDefinitionRefsByClearRelationshipDefinitions(): void
    {
        $this->assertTrue(count($this->tweety->getRelationshipDefinitionRefs()) > 0);
        $this->tweety->clearRelationships();
        $this->assertEmpty($this->tweety->getRelationshipDefinitions());
        // should affect animal relationship definition refs
        $this->assertEmpty($this->tweety->getRelationshipDefinitionRefs());
    }

    public function testAddRelationshipDefinitionRefElementsByHelper(): void
    {
        $this->assertCount(4, $this->tweety->getRelationshipDefinitionRefElements());

        $this->addRelationshipDefinition($this->tweety, $this->timmyRelationship);
        $timmyRelationshipDefinitionRef = $this->modelInstance->newInstance(RelationshipDefinitionRef::class);
        $timmyRelationshipDefinitionRef->setTextContent($this->timmyRelationship->getId());
        $this->tweety->addRelationshipDefinitionRefElement($timmyRelationshipDefinitionRef);

        $this->addRelationshipDefinition($this->tweety, $this->daisyRelationship);
        $daisyRelationshipDefinitionRef = $this->modelInstance->newInstance(RelationshipDefinitionRef::class);
        $daisyRelationshipDefinitionRef->setTextContent($this->daisyRelationship->getId());
        $this->tweety->addRelationshipDefinitionRefElement($daisyRelationshipDefinitionRef);

        $this->assertCount(6, $this->tweety->getRelationshipDefinitionRefElements());

        $rels = [
            $timmyRelationshipDefinitionRef,
            $daisyRelationshipDefinitionRef
        ];
        foreach ($rels as $rel) {
            $exists = false;
            foreach ($this->tweety->getRelationshipDefinitionRefElements() as $rel2) {
                if ($rel2->equals($rel)) {
                    $exists = true;
                }
            }
            $this->assertTrue($exists);
        }
    }

    public function testRelationshipDefinitionRefElementsByTextContent(): void
    {
        $relationshipDefinitionRefElements = $this->tweety->getRelationshipDefinitionRefElements();
        $textContents = [];
        foreach ($relationshipDefinitionRefElements as $relationshipDefinitionRef) {
            $textContent = $relationshipDefinitionRef->getTextContent();
            $this->assertTrue(!empty($textContent));
            $textContents[] = $textContent;
        }
        $this->assertCount(4, $textContents);
        $this->assertContains($this->hedwigRelationship->getId(), $textContents);
        $this->assertContains($this->birdoRelationship->getId(), $textContents);
        $this->assertContains($this->pluckyRelationship->getId(), $textContents);
        $this->assertContains($this->fiffyRelationship->getId(), $textContents);
    }

    public function testUpdateRelationshipDefinitionRefElementsByTextContent(): void
    {
        $relationshipDefinitionRefs = $this->tweety->getRelationshipDefinitionRefElements();

        $this->addRelationshipDefinition($this->tweety, $this->timmyRelationship);
        $relationshipDefinitionRefs[0]->setTextContent($this->timmyRelationship->getId());

        $this->addRelationshipDefinition($this->daisy, $this->daisyRelationship);
        $relationshipDefinitionRefs[2]->setTextContent($this->daisyRelationship->getId());

        $this->assertCount(4, $this->tweety->getRelationshipDefinitionRefs());

        $rels = [
            $this->birdoRelationship,
            $this->fiffyRelationship,
            $this->timmyRelationship,
            $this->daisyRelationship
        ];
        foreach ($rels as $rel) {
            $exists = false;
            foreach ($this->tweety->getRelationshipDefinitionRefs() as $rel2) {
                if ($rel2->equals($rel)) {
                    $exists = true;
                }
            }
            $this->assertTrue($exists);
        }
    }

    public function testUpdateRelationshipDefinitionRefElementsByTextContentWithNamespace(): void
    {
        $relationshipDefinitionRefs = $this->tweety->getRelationshipDefinitionRefElements();

        $this->addRelationshipDefinition($this->tweety, $this->timmyRelationship);
        $relationshipDefinitionRefs[0]->setTextContent("tns:" . $this->timmyRelationship->getId());

        $this->addRelationshipDefinition($this->daisy, $this->daisyRelationship);
        $relationshipDefinitionRefs[2]->setTextContent("tns:" . $this->daisyRelationship->getId());

        $rels = [
            $this->birdoRelationship,
            $this->fiffyRelationship,
            $this->timmyRelationship,
            $this->daisyRelationship
        ];
        foreach ($rels as $rel) {
            $exists = false;
            foreach ($this->tweety->getRelationshipDefinitionRefs() as $rel2) {
                if ($rel2->equals($rel)) {
                    $exists = true;
                }
            }
            $this->assertTrue($exists);
        }
    }

    public function testUpdateRelationshipDefinitionRefElementsByRemoveElements(): void
    {
        $relationshipDefinitionRefs = $this->tweety->getRelationshipDefinitionRefElements();
        $this->tweety->removeRelationshipDefinitionRefElement($relationshipDefinitionRefs[1]);
        $this->tweety->removeRelationshipDefinitionRefElement($relationshipDefinitionRefs[3]);

        $rels = [$this->hedwigRelationship, $this->pluckyRelationship];
        foreach ($rels as $rel) {
            $exists = false;
            foreach ($this->tweety->getRelationshipDefinitionRefs() as $rel2) {
                if ($rel2->equals($rel)) {
                    $exists = true;
                }
            }
            $this->assertTrue($exists);
        }
    }

    public function testClearRelationshipDefinitionRefElements(): void
    {
        $this->tweety->clearRelationshipDefinitionRefElements();
        $this->assertEmpty($this->tweety->getRelationshipDefinitionRefElements());
        $this->assertEmpty($this->tweety->getRelationshipDefinitionRefs());

        // should not affect animal relationship definitions
        $this->assertCount(4, $this->tweety->getRelationshipDefinitions());
    }

    public function testClearRelationshipDefinitionRefElementsByClearRelationshipDefinitionRefs(): void
    {
        $this->tweety->clearRelationshipDefinitions();
        $this->assertEmpty($this->tweety->getRelationshipDefinitionRefs());
        $this->assertEmpty($this->tweety->getRelationshipDefinitionRefElements());
        // should not affect animal relationship definitions
        $this->assertCount(4, $this->tweety->getRelationshipDefinitions());
    }

    public function testClearRelationshipDefinitionRefElementsByClearRelationshipDefinitions(): void
    {
        $this->tweety->clearRelationships();
        $this->assertEmpty($this->tweety->getRelationshipDefinitionRefs());
        $this->assertEmpty($this->tweety->getRelationshipDefinitionRefElements());
        // should affect animal relationship definitions
        $this->assertEmpty($this->tweety->getRelationshipDefinitions());
    }

    public function testGetBestFriends(): void
    {
        $bestFriends = $this->tweety->getBestFriends();
        $this->assertCount(2, $bestFriends);

        $birds = [$this->birdo, $this->plucky];

        foreach ($birds as $bird) {
            $exists = false;
            foreach ($bestFriends as $friend) {
                if ($friend->equals($bird)) {
                    $exists = true;
                }
            }
            $this->assertTrue($exists);
        }
    }

    public function testAddBestFriend(): void
    {
        $this->tweety->addFriend($this->daisy);

        $bestFriends = $this->tweety->getBestFriends();

        $birds = [$this->birdo, $this->plucky, $this->daisy];

        foreach ($birds as $bird) {
            $exists = false;
            foreach ($bestFriends as $friend) {
                if ($friend->equals($bird)) {
                    $exists = true;
                }
            }
            $this->assertTrue($exists);
        }
    }

    public function testRemoveBestFriendRef(): void
    {
        $this->tweety->removeFriend($this->plucky);

        $bestFriends = $this->tweety->getBestFriends();

        $this->assertCount(1, $bestFriends);
        $this->birdo->equals($bestFriends[0]);
    }

    public function testClearBestFriendRef(): void
    {
        $this->tweety->clearFriends();

        $bestFriends = $this->tweety->getBestFriends();

        $this->assertEmpty($bestFriends);
    }

    public function testClearAndAddBestFriendRef(): void
    {
        $this->tweety->clearFriends();

        $this->tweety->addFriend($this->daisy);

        $this->assertCount(1, $this->tweety->getBestFriends());
    }
}
