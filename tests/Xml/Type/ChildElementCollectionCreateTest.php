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

class ChildElementCollectionCreateTest extends TestModelTest
{
    protected $tweety;
    protected $daffy;
    protected $daisy;
    protected $plucky;
    protected $birdo;
    protected $flightInstructorChild;
    protected $flightPartnerRefCollection;

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

        $this->tweety->setFlightInstructor($this->daffy);
        $this->tweety->addFlightPartnerRef($this->daisy);
        $this->tweety->addFlightPartnerRef($this->plucky);

        $this->flightInstructorChild = FlyingAnimal::$flightInstructorChild->getReferenceSourceCollection();
        $this->flightPartnerRefCollection = FlyingAnimal::$flightPartnerRefsColl->getReferenceSourceCollection();
    }

    public function testImmutable(): void
    {
        $this->assertFalse($this->flightInstructorChild->isImmutable());
        $this->assertFalse($this->flightPartnerRefCollection->isImmutable());

        $this->flightInstructorChild->setImmutable();
        $this->flightPartnerRefCollection->setImmutable();
        $this->assertTrue($this->flightInstructorChild->isImmutable());
        $this->assertTrue($this->flightPartnerRefCollection->isImmutable());

        $this->flightInstructorChild->setMutable(true);
        $this->flightPartnerRefCollection->setMutable(true);
        $this->assertFalse($this->flightInstructorChild->isImmutable());
        $this->assertFalse($this->flightPartnerRefCollection->isImmutable());
    }

    public function testMinOccurs(): void
    {
        $this->assertFalse($this->flightInstructorChild->getMinOccurs() != 0);
        $this->assertFalse($this->flightPartnerRefCollection->getMinOccurs() != 0);
    }

    public function testMaxOccurs(): void
    {
        $this->assertEquals(1, $this->flightInstructorChild->getMaxOccurs());
        $this->assertEquals(-1, $this->flightPartnerRefCollection->getMaxOccurs());
    }

    public function testChildElementType(): void
    {
        $this->assertEquals(FlightInstructor::class, $this->flightInstructorChild->getChildElementTypeClass());
        $this->assertEquals(FlightPartnerRef::class, $this->flightPartnerRefCollection->getChildElementTypeClass());
    }

    public function testParentElementType(): void
    {
        $flyingAnimalType = $this->modelInstance->getModel()->getType(FlyingAnimal::class);
        $this->assertEquals(get_class($flyingAnimalType), get_class($this->flightInstructorChild->getParentElementType()));
        $this->assertEquals(get_class($flyingAnimalType), get_class($this->flightPartnerRefCollection->getParentElementType()));
    }

    public function testGetChildElements(): void
    {
        $this->assertCount(1, $this->flightInstructorChild->get($this->tweety));
        $this->assertCount(2, $this->flightPartnerRefCollection->get($this->tweety));

        $flightInstructor = $this->flightInstructorChild->getChild($this->tweety);
        $this->assertEquals($this->daffy->getId(), $flightInstructor->getTextContent());

        $collection = $this->flightPartnerRefCollection->get($this->tweety);
        foreach ($collection as $flightPartnerRef) {
            $this->assertContains($flightPartnerRef->getTextContent(), [$this->daisy->getId(), $this->plucky->getId()]);
        }
    }

    public function testRemoveChildElements(): void
    {
        $this->assertFalse(empty($this->flightInstructorChild->get($this->tweety)));
        $this->assertFalse(empty($this->flightPartnerRefCollection->get($this->tweety)));

        $this->flightInstructorChild->removeChild($this->tweety);
        $this->flightPartnerRefCollection->clear($this->tweety);

        $this->assertTrue(empty($this->flightInstructorChild->get($this->tweety)));
        $this->assertTrue(empty($this->flightPartnerRefCollection->get($this->tweety)));
    }

    public function testChildElementsCollection(): void
    {
        $flightPartnerRefs = $this->flightPartnerRefCollection->get($this->tweety);

        $daisyRef = $flightPartnerRefs[0];
        $pluckyRef = $flightPartnerRefs[1];

        $this->assertEquals($this->daisy->getId(), $daisyRef->getTextContent());
        $this->assertEquals($this->plucky->getId(), $pluckyRef->getTextContent());

        $birdoRef = $this->modelInstance->newInstance(FlightPartnerRef::class);
        $birdoRef->setTextContent($this->birdo->getId());

        $flightPartners = [$birdoRef, $daisyRef, $pluckyRef];

        // directly test collection methods and not use the appropriate assertion methods
        $this->assertCount(2, $flightPartnerRefs);
        $this->flightPartnerRefCollection->add($this->tweety, $birdoRef);

        $flightPartnerRefs = $this->flightPartnerRefCollection->get($this->tweety);
        $this->assertCount(3, $flightPartnerRefs);

        foreach ($flightPartners as $partner) {
            $exists = false;
            foreach ($this->flightPartnerRefCollection->get($this->tweety) as $ref) {
                if ($ref->equals($partner)) {
                    $exists = true;
                }
            }
            $this->assertTrue($exists);
        }

        $this->flightPartnerRefCollection->remove($this->tweety, $daisyRef);
        $flightPartnerRefs = $this->flightPartnerRefCollection->get($this->tweety);
        $this->assertCount(2, $flightPartnerRefs);
        foreach ([$birdoRef, $pluckyRef] as $partner) {
            $exists = false;
            foreach ($this->flightPartnerRefCollection->get($this->tweety) as $ref) {
                if ($ref->equals($partner)) {
                    $exists = true;
                }
            }
            $this->assertTrue($exists);
        }

        $this->flightPartnerRefCollection->addAll($this->tweety, $flightPartners);
        foreach ([$birdoRef, $daisyRef, $pluckyRef] as $partner) {
            $exists = false;
            foreach ($this->flightPartnerRefCollection->get($this->tweety) as $ref) {
                if ($ref->equals($partner)) {
                    $exists = true;
                }
            }
            $this->assertTrue($exists);
        }

        $this->flightPartnerRefCollection->clear($this->tweety);
        $this->assertEmpty($this->flightPartnerRefCollection->get($this->tweety));
    }
}
