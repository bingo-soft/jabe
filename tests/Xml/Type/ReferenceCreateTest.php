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
    Animals,
    FlyingAnimal,
    FlightInstructor,
    FlightPartnerRef
};

class ReferenceCreateTest extends TestModelTest
{
    protected $tweety;
    protected $daffy;
    protected $daisy;
    protected $plucky;
    protected $birdo;
    protected $flightPartnerRef;

    protected $animalType;
    protected $fatherReference;
    protected $motherReference;
    protected $flightPartnerRefsColl;

    protected function setUp(): void
    {
        $this->modelParser = new TestModelParser();
        $this->modelInstance = $this->modelParser->getEmptyModel();

        $animals = $this->modelInstance->newInstance(Animals::class);
        $this->modelInstance->setDocumentElement($animals);

        $this->tweety = $this->createBird($this->modelInstance, "tweety", Gender::FEMALE);
        $this->daffy = $this->createBird($this->modelInstance, "daffy", Gender::MALE);
        $this->daisy = $this->createBird($this->modelInstance, "daisy", Gender::FEMALE);
        $this->plucky = $this->createBird($this->modelInstance, "plucky", Gender::MALE);
        $this->birdo = $this->createBird($this->modelInstance, "birdo", Gender::FEMALE);

        $this->tweety->setFather($this->daffy);
        $this->tweety->setMother($this->daisy);

        // ID attribute reference
        $this->animalType = $this->modelInstance->getModel()->getType(Animal::class);
        $this->fatherReference = $this->animalType->getAttribute("father")->getOutgoingReferences()[0];
        $this->motherReference = $this->animalType->getAttribute("mother")->getOutgoingReferences()[0];
        $this->flightPartnerRefsColl = FlyingAnimal::$flightPartnerRefsColl;

        $this->tweety->addFlightPartnerRef($this->daffy);

        $flightPartnerRefType = $this->modelInstance->getModel()->getType(FlightPartnerRef::class);
        $this->flightPartnerRef = $this->modelInstance->getModelElementsByType($flightPartnerRefType)[0];
    }

    public function testReferenceIdentifier(): void
    {
        $this->assertEquals($this->daffy->getId(), $this->fatherReference->getReferenceIdentifier($this->tweety));
        $this->assertEquals($this->daisy->getId(), $this->motherReference->getReferenceIdentifier($this->tweety));
        $this->assertEquals($this->daffy->getId(), $this->flightPartnerRefsColl->getReferenceIdentifier($this->tweety));
    }

    public function testReferenceTargetElement(): void
    {
        $this->assertTrue($this->fatherReference->getReferenceTargetElement($this->tweety)->equals($this->daffy));
        $this->assertTrue($this->motherReference->getReferenceTargetElement($this->tweety)->equals($this->daisy));
        $this->assertTrue($this->flightPartnerRefsColl->getReferenceTargetElement($this->tweety)->equals($this->daffy));

        $this->fatherReference->setReferenceTargetElement($this->tweety, $this->plucky);
        $this->motherReference->setReferenceTargetElement($this->tweety, $this->birdo);
        $this->flightPartnerRefsColl->setReferenceTargetElement($this->flightPartnerRef, $this->daisy);

        $this->assertTrue($this->fatherReference->getReferenceTargetElement($this->tweety)->equals($this->plucky));
        $this->assertTrue($this->motherReference->getReferenceTargetElement($this->tweety)->equals($this->birdo));
        $this->assertTrue($this->flightPartnerRefsColl->getReferenceTargetElement($this->tweety)->equals($this->daisy));
    }

    public function testReferenceSourceAttribute(): void
    {
        $fatherAttribute = $this->animalType->getAttribute("father");
        $motherAttribute = $this->animalType->getAttribute("mother");

        $this->assertTrue($this->fatherReference->getReferenceSourceAttribute() == $fatherAttribute);
        $this->assertTrue($this->motherReference->getReferenceSourceAttribute() == $motherAttribute);
    }

    public function testRemoveReference(): void
    {
        $this->fatherReference->referencedElementRemoved($this->daffy, $this->daffy->getId());
        $this->assertNull($this->fatherReference->getReferenceTargetElement($this->tweety));

        $this->motherReference->referencedElementRemoved($this->daisy, $this->daisy->getId());
        $this->assertNull($this->motherReference->getReferenceTargetElement($this->tweety));
    }

    public function testTargetElementsCollection(): void
    {
        $referenceTargetElements = $this->flightPartnerRefsColl->getReferenceTargetElements($this->tweety);
        $flightPartners = [$this->birdo, $this->daffy, $this->daisy, $this->plucky];

        $this->assertCount(1, $referenceTargetElements);
        $exists = false;
        foreach ($referenceTargetElements as $ref) {
            if ($ref->equals($this->daffy)) {
                $exists = true;
                break;
            }
        }
        $this->assertTrue($exists);

        $this->flightPartnerRefsColl->add($this->tweety, $this->daisy);
        $referenceTargetElements = $this->flightPartnerRefsColl->getReferenceTargetElements($this->tweety);
        $this->assertCount(2, $referenceTargetElements);

        $exists = false;
        foreach ($referenceTargetElements as $ref) {
            if ($ref->equals($this->daisy)) {
                $exists = true;
                break;
            }
        }
        $this->assertTrue($exists);

        $this->flightPartnerRefsColl->remove($this->tweety, $this->daisy);
        $referenceTargetElements = $this->flightPartnerRefsColl->getReferenceTargetElements($this->tweety);
        $this->assertCount(1, $referenceTargetElements);
        $exists = false;
        foreach ($referenceTargetElements as $ref) {
            if ($ref->equals($this->daffy)) {
                $exists = true;
                break;
            }
        }
        $this->assertTrue($exists);

        $this->flightPartnerRefsColl->addAll($this->tweety, $flightPartners);
        $referenceTargetElements = $this->flightPartnerRefsColl->getReferenceTargetElements($this->tweety);
        $this->assertCount(4, $referenceTargetElements);

        foreach ($flightPartners as $partner) {
            $exists = false;
            foreach ($referenceTargetElements as $ref) {
                if ($ref->equals($partner)) {
                    $exists = true;
                    break;
                }
            }
            $this->assertTrue($exists);
        }

        $this->flightPartnerRefsColl->removeAll($this->tweety, $flightPartners);
        $referenceTargetElements = $this->flightPartnerRefsColl->getReferenceTargetElements($this->tweety);
        $this->assertCount(0, $referenceTargetElements);

        $this->flightPartnerRefsColl->addAll($this->tweety, $flightPartners);
        $referenceTargetElements = $this->flightPartnerRefsColl->getReferenceTargetElements($this->tweety);
        $this->assertFalse(empty($referenceTargetElements));

        $this->flightPartnerRefsColl->clear($this->tweety);
        $referenceTargetElements = $this->flightPartnerRefsColl->getReferenceTargetElements($this->tweety);
        $this->assertTrue(empty($referenceTargetElements));
    }
}
