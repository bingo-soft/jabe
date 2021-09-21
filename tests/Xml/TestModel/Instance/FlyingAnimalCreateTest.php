<?php

namespace Tests\Xml\TestModel\Instance;

use BpmPlatform\Model\Xml\Impl\Util\StringUtil;
use BpmPlatform\Model\Xml\ModelInstanceInterface;
use BpmPlatform\Model\Xml\Impl\Parser\AbstractModelParser;
use Tests\Xml\TestModel\{
    Gender,
    TestModelConstants,
    TestModelParser
};
use Tests\Xml\TestModel\TestModelTest;

class FlyingAnimalCreateTest extends TestModelTest
{
    protected $tweety;
    protected $hedwig;
    protected $birdo;
    protected $plucky;
    protected $fiffy;
    protected $timmy;
    protected $daisy;

    protected function setUp(): void
    {
        $this->modelParser = new TestModelParser();
        $this->modelInstance = $this->modelParser->getEmptyModel();

        $animals = $this->modelInstance->newInstance(Animals::class);
        $this->modelInstance->setDocumentElement($animals);

        // add a tns namespace prefix for QName testing
        $animals->getDomElement()->registerNamespace("tns", TestModelConstants::MODEL_NAMESPACE);

        $this->tweety = $this->createBird($this->modelInstance, "tweety", Gender::FEMALE);
        $this->hedwig = $this->createBird($this->modelInstance, "hedwig", Gender::MALE);
        $this->birdo = $this->createBird($this->modelInstance, "birdo", Gender::FEMALE);
        $this->plucky = $this->createBird($this->modelInstance, "plucky", Gender::UNKNOWN);
        $this->fiffy = $this->createBird($this->modelInstance, "fiffy", Gender::FEMALE);
        $this->timmy = $this->createBird($this->modelInstance, "timmy", Gender::MALE);
        $this->daisy = $this->createBird($this->modelInstance, "daisy", Gender::FEMALE);

        $this->tweety->setFlightInstructor($this->hedwig);

        $this->tweety->addFlightPartnerRef($this->hedwig);
        $this->tweety->addFlightPartnerRef($this->birdo);
        $this->tweety->addFlightPartnerRef($this->plucky);
        $this->tweety->addFlightPartnerRef($this->fiffy);
    }

    public function testSetWingspanAttributeByHelper(): void
    {
        $wingspan = 2.123;
        $this->tweety->setWingspan($wingspan);
        $this->assertEquals($wingspan, $this->tweety->getWingspan());
    }

    public function testSetWingspanAttributeByAttributeName(): void
    {
        $wingspan = 2.123;
        $this->tweety->setAttributeValue("wingspan", $wingspan, false);
        $this->assertEquals($wingspan, $this->tweety->getWingspan());
    }

    public function testRemoveWingspanAttribute(): void
    {
        $wingspan = 2.123;
        $this->tweety->setWingspan($wingspan);
        $this->assertEquals($wingspan, $this->tweety->getWingspan());

        $this->tweety->removeAttribute("wingspan");

        $this->assertNull($this->tweety->getWingspan());
    }

    public function testSetFlightInstructorByHelper(): void
    {
        $this->tweety->setFlightInstructor($this->timmy);
        $this->assertTrue($this->tweety->getFlightInstructor()->equals($this->timmy));
    }

    public function testUpdateFlightInstructorByIdHelper(): void
    {
        $this->hedwig->setId("new-" . $this->hedwig->getId());
        $this->assertTrue($this->tweety->getFlightInstructor()->equals($this->hedwig));
    }

    public function testUpdateFlightInstructorByIdAttributeName(): void
    {
        $this->hedwig->setAttributeValue("id", "new-" . $this->hedwig->getId(), true);
        $this->assertTrue($this->tweety->getFlightInstructor()->equals($this->hedwig));
    }

    public function testUpdateFlightInstructorByReplaceElement(): void
    {
        $this->hedwig->replaceWithElement($this->timmy);
        $this->assertTrue($this->tweety->getFlightInstructor()->equals($this->timmy));
    }

    public function testUpdateFlightInstructorByRemoveElement(): void
    {
        $animals = $this->modelInstance->getDocumentElement();
        $animals->removeAnimal($this->hedwig);
        $this->assertNull($this->tweety->getFlightInstructor());
    }

    public function testClearFlightInstructor(): void
    {
        $this->tweety->removeFlightInstructor();
        $this->assertNull($this->tweety->getFlightInstructor());
    }

    public function testAddFlightPartnerRefsByHelper(): void
    {
        $this->assertCount(4, $this->tweety->getFlightPartnerRefs());
        $birds = [$this->hedwig, $this->birdo, $this->plucky, $this->fiffy];
        foreach ($birds as $bird) {
            $exists = false;
            foreach ($this->tweety->getFlightPartnerRefs() as $ref) {
                if ($ref->equals($bird)) {
                    $exists = true;
                    break;
                }
            }
            $this->assertTrue($exists);
        }
        $this->tweety->addFlightPartnerRef($this->timmy);
        $this->tweety->addFlightPartnerRef($this->daisy);

        $birds[] = $this->timmy;
        $birds[] = $this->daisy;

        foreach ($birds as $bird) {
            $exists = false;
            foreach ($this->tweety->getFlightPartnerRefs() as $ref) {
                if ($ref->equals($bird)) {
                    $exists = true;
                    break;
                }
            }
            $this->assertTrue($exists);
        }
    }

    public function testUpdateFlightPartnerRefsByIdByHelper(): void
    {
        $this->hedwig->setId("new-" . $this->hedwig->getId());
        $this->plucky->setId("new-" . $this->plucky->getId());
        $this->assertCount(4, $this->tweety->getFlightPartnerRefs());
        $birds = [$this->hedwig, $this->birdo, $this->plucky, $this->fiffy];
        foreach ($birds as $bird) {
            $exists = false;
            foreach ($this->tweety->getFlightPartnerRefs() as $ref) {
                if ($ref->equals($bird)) {
                    $exists = true;
                    break;
                }
            }
            $this->assertTrue($exists);
        }
    }

    public function testUpdateFlightPartnerRefsByIdByAttributeName(): void
    {
        $this->birdo->setAttributeValue("id", "new-" . $this->birdo->getId(), true);
        $this->fiffy->setAttributeValue("id", "new-" . $this->fiffy->getId(), true);
        $birds = [$this->hedwig, $this->birdo, $this->plucky, $this->fiffy];
        foreach ($birds as $bird) {
            $exists = false;
            foreach ($this->tweety->getFlightPartnerRefs() as $ref) {
                if ($ref->equals($bird)) {
                    $exists = true;
                    break;
                }
            }
            $this->assertTrue($exists);
        }
    }

    public function testUpdateFlightPartnerRefsByReplaceElements(): void
    {
        $this->hedwig->replaceWithElement($this->timmy);
        $this->plucky->replaceWithElement($this->daisy);
        $birds = [$this->timmy, $this->birdo, $this->daisy, $this->fiffy];
        foreach ($birds as $bird) {
            $exists = false;
            foreach ($this->tweety->getFlightPartnerRefs() as $ref) {
                if ($ref->equals($bird)) {
                    $exists = true;
                    break;
                }
            }
            $this->assertTrue($exists);
        }
    }

    public function testUpdateFlightPartnerRefsByRemoveElements(): void
    {
        $this->tweety->removeFlightPartnerRef($this->birdo);
        $this->tweety->removeFlightPartnerRef($this->fiffy);
        $this->assertCount(2, $this->tweety->getFlightPartnerRefs());
        $birds = [$this->hedwig, $this->plucky];
        foreach ($birds as $bird) {
            $exists = false;
            foreach ($this->tweety->getFlightPartnerRefs() as $ref) {
                if ($ref->equals($bird)) {
                    $exists = true;
                    break;
                }
            }
            $this->assertTrue($exists);
        }
    }

    public function testClearFlightPartnerRefs(): void
    {
        $this->tweety->clearFlightPartnerRefs();
        $this->assertEmpty($this->tweety->getFlightPartnerRefs());
    }

    public function testAddFlightPartnerRefElementsByHelper(): void
    {
        $this->assertCount(4, $this->tweety->getFlightPartnerRefElements());

        $timmyFlightPartnerRef = $this->modelInstance->newInstance(FlightPartnerRef::class);
        $timmyFlightPartnerRef->setTextContent($this->timmy->getId());
        $this->tweety->addFlightPartnerRefElement($timmyFlightPartnerRef);

        $daisyFlightPartnerRef = $this->modelInstance->newInstance(FlightPartnerRef::class);
        $daisyFlightPartnerRef->setTextContent($this->daisy->getId());
        $this->tweety->addFlightPartnerRefElement($daisyFlightPartnerRef);

        $this->assertCount(6, $this->tweety->getFlightPartnerRefElements());
    }

    public function testFlightPartnerRefElementsByTextContent(): void
    {
        $flightPartnerRefElements = $this->tweety->getFlightPartnerRefElements();
        $textContents = [];
        foreach ($flightPartnerRefElements as $flightPartnerRefElement) {
            $textContent = $flightPartnerRefElement->getTextContent();
            $this->assertFalse(empty($textContent));
            $textContents[] = $textContent;
        }
        $this->assertCount(4, $textContents);
        $this->assertContains($this->hedwig->getId(), $textContents);
        $this->assertContains($this->birdo->getId(), $textContents);
        $this->assertContains($this->plucky->getId(), $textContents);
        $this->assertContains($this->fiffy->getId(), $textContents);
    }

    public function testUpdateFlightPartnerRefElementsByTextContent(): void
    {
        $flightPartnerRefs = $this->tweety->getFlightPartnerRefElements();

        $flightPartnerRefs[0]->setTextContent($this->timmy->getId());
        $flightPartnerRefs[2]->setTextContent($this->daisy->getId());

        $this->assertCount(4, $this->tweety->getFlightPartnerRefs());

        $birds = [$this->birdo, $this->fiffy, $this->timmy, $this->daisy];
        foreach ($birds as $bird) {
            $exists = false;
            foreach ($this->tweety->getFlightPartnerRefs() as $ref) {
                if ($ref->equals($bird)) {
                    $exists = true;
                    break;
                }
            }
            $this->assertTrue($exists);
        }
    }

    public function testUpdateFlightPartnerRefElementsByRemoveElements(): void
    {
        $flightPartnerRefs = $this->tweety->getFlightPartnerRefElements();
        $this->tweety->removeFlightPartnerRefElement($flightPartnerRefs[1]);
        $this->tweety->removeFlightPartnerRefElement($flightPartnerRefs[3]);

        $this->assertCount(2, $this->tweety->getFlightPartnerRefs());
        $birds = [$this->hedwig, $this->plucky];
        foreach ($birds as $bird) {
            $exists = false;
            foreach ($this->tweety->getFlightPartnerRefs() as $ref) {
                if ($ref->equals($bird)) {
                    $exists = true;
                    break;
                }
            }
            $this->assertTrue($exists);
        }
    }

    public function testClearFlightPartnerRefElements(): void
    {
        $this->tweety->clearFlightPartnerRefElements();
        $this->assertEmpty($this->tweety->getFlightPartnerRefElements());

        // should not affect animals collection
        $animals = $this->modelInstance->getDocumentElement();
        $this->assertCount(7, $animals->getAnimals());
    }
}
