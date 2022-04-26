<?php

namespace Tests\Xml\TestModel\Instance;

use Jabe\Model\Xml\Impl\Util\StringUtil;
use Jabe\Model\Xml\ModelInstanceInterface;
use Jabe\Model\Xml\Impl\Parser\AbstractModelParser;
use Tests\Xml\TestModel\{
    Gender,
    TestModelConstants,
    TestModelParser
};
use Tests\Xml\TestModel\TestModelTest;

class BirdCreateModelTest extends TestModelTest
{
    protected $tweety;
    protected $hedwig;
    protected $timmy;
    protected $egg1;
    protected $egg2;
    protected $egg3;

    protected function setUp(): void
    {
        $this->modelParser = new TestModelParser();
        $this->modelInstance = $this->modelParser->getEmptyModel();

        $animals = $this->modelInstance->newInstance(Animals::class);
        $this->modelInstance->setDocumentElement($animals);

        // add a tns namespace prefix for QName testing
        $animals->getDomElement()->registerNamespace("tns", TestModelConstants::MODEL_NAMESPACE);

        $this->tweety = $this->createBird($this->modelInstance, "tweety", Gender::FEMALE);
        $this->hedwig = $this->createBird($this->modelInstance, "hedwig", Gender::FEMALE);
        $this->timmy = $this->createBird($this->modelInstance, "timmy", Gender::FEMALE);
        $this->egg1 = $this->createEgg($this->modelInstance, "egg1");
        $this->egg1->setMother($this->tweety);
        $this->egg1->addGuardian($this->hedwig);
        $this->egg1->addGuardian($this->timmy);

        $this->egg2 = $this->createEgg($this->modelInstance, "egg2");
        $this->egg2->setMother($this->tweety);
        $this->egg2->addGuardian($this->hedwig);
        $this->egg2->addGuardian($this->timmy);

        $this->egg3 = $this->createEgg($this->modelInstance, "egg3");
        $this->egg3->addGuardian($this->timmy);

        $this->tweety->setSpouse($this->hedwig);
        $this->tweety->addEgg($this->egg1);
        $this->tweety->addEgg($this->egg2);
        $this->tweety->addEgg($this->egg3);

        $this->hedwig->addGuardedEgg($this->egg1);
        $this->hedwig->addGuardedEgg($this->egg2);

        $guardEgg = $this->modelInstance->newInstance(GuardEgg::class);
        $guardEgg->setTextContent($this->egg1->getId() . " " . $this->egg2->getId());
        $this->timmy->addGuardedEggRef($guardEgg);
        $this->timmy->addGuardedEgg($this->egg3);
    }

    public function testAddEggsByHelper(): void
    {
        $this->assertCount(3, $this->tweety->getEggs());
        $eggs = [$this->egg1, $this->egg2, $this->egg3];
        foreach ($eggs as $egg) {
            $exists = false;
            foreach ($this->tweety->getEggs() as $egg2) {
                if ($egg2->equals($egg)) {
                    $exists = true;
                }
            }
            $this->assertTrue($exists);
        }

        $egg4 = $this->createEgg($this->modelInstance, "egg4");
        $this->tweety->addEgg($egg4);
        $egg5 = $this->createEgg($this->modelInstance, "egg5");
        $this->tweety->addEgg($egg5);

        $this->assertCount(5, $this->tweety->getEggs());

        $eggs = [$this->egg1, $this->egg2, $this->egg3, $egg4, $egg5];
        foreach ($eggs as $egg) {
            $exists = false;
            foreach ($this->tweety->getEggs() as $egg2) {
                if ($egg2->equals($egg)) {
                    $exists = true;
                }
            }
            $this->assertTrue($exists);
        }
    }

    public function testUpdateEggsByIdByHelper(): void
    {
        $this->egg1->setId("new-" . $this->egg1->getId());
        $this->egg2->setId("new-" . $this->egg2->getId());

        $this->assertCount(3, $this->tweety->getEggs());
        $eggs = [$this->egg1, $this->egg2, $this->egg3];
        foreach ($eggs as $egg) {
            $exists = false;
            foreach ($this->tweety->getEggs() as $egg2) {
                if ($egg2->equals($egg)) {
                    $exists = true;
                }
            }
            $this->assertTrue($exists);
        }
    }

    public function testUpdateEggsByIdByAttributeName(): void
    {
        $this->egg1->setAttributeValue("id", "new-" . $this->egg1->getId(), true);
        $this->egg2->setAttributeValue("id", "new-" . $this->egg2->getId(), true);

        $this->assertCount(3, $this->tweety->getEggs());
        $eggs = [$this->egg1, $this->egg2, $this->egg3];
        foreach ($eggs as $egg) {
            $exists = false;
            foreach ($this->tweety->getEggs() as $egg2) {
                if ($egg2->equals($egg)) {
                    $exists = true;
                }
            }
            $this->assertTrue($exists);
        }
    }

    public function testUpdateEggsByReplaceElements(): void
    {
        $egg4 = $this->createEgg($this->modelInstance, "egg4");
        $egg5 = $this->createEgg($this->modelInstance, "egg5");
        $this->egg1->replaceWithElement($egg4);
        $this->egg2->replaceWithElement($egg5);
        $this->assertCount(3, $this->tweety->getEggs());
        $eggs = [$this->egg3, $egg4, $egg5];
        foreach ($eggs as $egg) {
            $exists = false;
            foreach ($this->tweety->getEggs() as $egg2) {
                if ($egg2->equals($egg)) {
                    $exists = true;
                }
            }
            $this->assertTrue($exists);
        }
    }

    public function testUpdateEggsByRemoveElement(): void
    {
        $this->tweety->removeEgg($this->egg1);
        $this->assertCount(2, $this->tweety->getEggs());
        $eggs = [$this->egg2, $this->egg3];
        foreach ($eggs as $egg) {
            $exists = false;
            foreach ($this->tweety->getEggs() as $egg2) {
                if ($egg2->equals($egg)) {
                    $exists = true;
                }
            }
            $this->assertTrue($exists);
        }
    }

    public function testClearEggs(): void
    {
        $this->tweety->clearEggs();
        $this->assertEmpty($this->tweety->getEggs());
    }

    public function testSetSpouseRefByHelper(): void
    {
        $this->tweety->setSpouse($this->timmy);
        $this->assertTrue($this->tweety->getSpouse()->equals($this->timmy));
    }

    public function testUpdateSpouseByIdHelper(): void
    {
        $this->hedwig->setId("new-" . $this->hedwig->getId());
        $this->assertTrue($this->tweety->getSpouse()->equals($this->hedwig));
    }

    public function testUpdateSpouseByIdByAttributeName(): void
    {
        $this->hedwig->setAttributeValue("id", "new-" . $this->hedwig->getId(), true);
        $this->assertTrue($this->tweety->getSpouse()->equals($this->hedwig));
    }

    public function testUpdateSpouseByReplaceElement(): void
    {
        $this->hedwig->replaceWithElement($this->timmy);
        $this->assertTrue($this->tweety->getSpouse()->equals($this->timmy));
    }

    public function testUpdateSpouseByRemoveElement(): void
    {
        $animals = $this->modelInstance->getDocumentElement();
        $animals->removeAnimal($this->hedwig);
        $this->assertNull($this->tweety->getSpouse());
    }

    public function testClearSpouse(): void
    {
        $this->tweety->removeSpouse();
        $this->assertNull($this->tweety->getSpouse());
    }

    public function testSetSpouseRefsByHelper(): void
    {
        $spouseRef = $this->modelInstance->newInstance(SpouseRef::class);
        $spouseRef->setTextContent($this->timmy->getId());
        $this->tweety->getSpouseRef()->replaceWithElement($spouseRef);
        $this->assertTrue($this->tweety->getSpouse()->equals($this->timmy));
    }

    public function testSpouseRefsByTextContent(): void
    {
        $spouseRef = $this->tweety->getSpouseRef();
        $this->assertEquals($spouseRef->getTextContent(), $this->hedwig->getId());
    }

    public function testUpdateSpouseRefsByTextContent(): void
    {
        $spouseRef = $this->tweety->getSpouseRef();
        $spouseRef->setTextContent($this->timmy->getId());
        $this->assertTrue($this->tweety->getSpouse()->equals($this->timmy));
    }

    public function testUpdateSpouseRefsByTextContentWithNamespace(): void
    {
        $spouseRef = $this->tweety->getSpouseRef();
        $spouseRef->setTextContent("tns:" . $this->timmy->getId());
        $this->assertTrue($this->tweety->getSpouse()->equals($this->timmy));
    }

    public function testGetMother(): void
    {
        $mother = $this->egg1->getMother();
        $this->assertTrue($mother->equals($this->tweety));

        $mother = $this->egg2->getMother();
        $this->assertTrue($mother->equals($this->tweety));
    }

    public function testSetMotherRefByHelper(): void
    {
        $this->egg1->setMother($this->timmy);
        $this->assertTrue($this->egg1->getMother()->equals($this->timmy));
    }

    public function testUpdateMotherByIdHelper(): void
    {
        $this->tweety->setId("new-" . $this->tweety->getId());
        $this->assertTrue($this->egg1->getMother()->equals($this->tweety));
    }

    public function testUpdateMotherByIdByAttributeName(): void
    {
        $this->tweety->setAttributeValue("id", "new-" . $this->tweety->getId(), true);
        $this->assertTrue($this->egg1->getMother()->equals($this->tweety));
    }

    public function testUpdateMotherByReplaceElement(): void
    {
        $this->tweety->replaceWithElement($this->timmy);
        $this->assertTrue($this->egg1->getMother()->equals($this->timmy));
    }

    public function testUpdateMotherByRemoveElement(): void
    {
        $this->egg1->setMother($this->hedwig);
        $animals = $this->modelInstance->getDocumentElement();
        $animals->removeAnimal($this->hedwig);
        $this->assertNull($this->egg1->getMother());
    }

    public function testClearMother(): void
    {
        $this->egg1->removeMother();
        $this->assertNull($this->egg1->getMother());
    }

    public function testSetMotherRefsByHelper(): void
    {
        $mother = $this->modelInstance->newInstance(Mother::class);
        $mother->setHref("#" . $this->timmy->getId());
        $this->egg1->getMotherRef()->replaceWithElement($mother);
        $this->assertTrue($this->egg1->getMother()->equals($this->timmy));
    }

    public function testMotherRefsByTextContent(): void
    {
        $mother = $this->egg1->getMotherRef();
        $this->assertEquals("#" . $this->tweety->getId(), $mother->getHref());
    }

    public function testUpdateMotherRefsByTextContent(): void
    {
        $mother = $this->egg1->getMotherRef();
        $mother->setHref("#" . $this->timmy->getId());
        $this->assertTrue($this->egg1->getMother()->equals($this->timmy));
    }

    public function testGetGuards(): void
    {
        $guards = $this->egg1->getGuardians();
        $this->assertCount(2, $guards);
        $birds = [$this->hedwig, $this->timmy];
        foreach ($birds as $bird) {
            $exists = false;
            foreach ($guards as $guard) {
                if ($bird->equals($guard)) {
                    $exists = true;
                }
            }
            $this->assertTrue($exists);
        }

        $guards = $this->egg2->getGuardians();
        $this->assertCount(2, $guards);
        $birds = [$this->hedwig, $this->timmy];
        foreach ($birds as $bird) {
            $exists = false;
            foreach ($guards as $guard) {
                if ($bird->equals($guard)) {
                    $exists = true;
                }
            }
            $this->assertTrue($exists);
        }
    }

    public function testAddGuardianRefsByHelper(): void
    {
        $this->assertCount(2, $this->egg1->getGuardianRefs());

        $tweetyGuardian = $this->modelInstance->newInstance(Guardian::class);
        $tweetyGuardian->setHref("#" . $this->tweety->getId());
        $this->egg1->addGuardianRef($tweetyGuardian);

        $this->assertCount(3, $this->egg1->getGuardianRefs());
        $exists = false;
        foreach ($this->egg1->getGuardianRefs() as $ref) {
            if ($ref->equals($tweetyGuardian)) {
                $exists = true;
            }
        }
        $this->assertTrue($exists);
    }

    public function testGuardianRefsByTextContent(): void
    {
        $guardianRefs = $this->egg1->getGuardianRefs();
        $hrefs = [];
        foreach ($guardianRefs as $guardianRef) {
            $href = $guardianRef->getHref();
            $this->assertFalse(empty($href));
            $hrefs[] = $href;
        }
        $this->assertFalse(empty($hrefs));
        $this->assertContains("#" . $this->hedwig->getId(), $hrefs);
        $this->assertContains("#" . $this->timmy->getId(), $hrefs);
    }

    public function testUpdateGuardianRefsByTextContent(): void
    {
        $guardianRefs = $this->egg1->getGuardianRefs();

        $guardianRefs[0]->setHref("#" . $this->tweety->getId());

        $birds = [$this->tweety, $this->timmy];
        foreach ($birds as $bird) {
            $exists = false;
            foreach ($this->egg1->getGuardians() as $guard) {
                if ($bird->equals($guard)) {
                    $exists = true;
                }
            }
            $this->assertTrue($exists);
        }
    }

    public function testUpdateGuardianRefsByRemoveElements(): void
    {
        $guardianRefs = $this->egg1->getGuardianRefs();
        $this->egg1->removeGuardianRef($guardianRefs[1]);
        $this->assertCount(1, $this->egg1->getGuardians());
    }

    public function testClearGuardianRefs(): void
    {
        $this->egg1->clearGuardianRefs();
        $this->assertEmpty($this->egg1->getGuardianRefs());

        // should not affect animals collection
        $animals = $this->modelInstance->getDocumentElement();
        $this->assertCount(3, $animals->getAnimals());
    }

    public function testGetGuardedEggs(): void
    {
        $guardedEggs = $this->hedwig->getGuardedEggs();
        $this->assertCount(2, $guardedEggs);
        $eggs = [$this->egg1, $this->egg2];
        foreach ($eggs as $egg) {
            $exists = false;
            foreach ($guardedEggs as $egg2) {
                if ($egg2->equals($egg)) {
                    $exists = true;
                }
            }
            $this->assertTrue($exists);
        }
    }

    public function testAddGuardedEggRefsByHelper(): void
    {
        $this->assertCount(2, $this->hedwig->getGuardedEggRefs());

        $egg3GuardedEgg = $this->modelInstance->newInstance(GuardEgg::class);
        $egg3GuardedEgg->setTextContent($this->egg3->getId());
        $this->hedwig->addGuardedEggRef($egg3GuardedEgg);

        $this->assertCount(3, $this->hedwig->getGuardedEggRefs());

        $exists = false;
        foreach ($this->hedwig->getGuardedEggRefs() as $ref) {
            if ($ref->equals($egg3GuardedEgg)) {
                $exists = true;
            }
        }
        $this->assertTrue($exists);
    }

    public function testGuardedEggRefsByTextContent(): void
    {
        $guardianRefs = $this->timmy->getGuardedEggRefs();
        $textContents = [];
        foreach ($guardianRefs as $guardianRef) {
            $textContent = $guardianRef->getTextContent();
            $this->assertFalse(empty($textContent));
            $textContents = array_merge($textContents, StringUtil::splitListBySeparator($textContent, " "));
        }
        $this->assertCount(3, $textContents);
        $this->assertContains($this->egg1->getId(), $textContents);
        $this->assertContains($this->egg2->getId(), $textContents);
        $this->assertContains($this->egg3->getId(), $textContents);
    }

    public function testUpdateGuardedEggRefsByTextContent(): void
    {
        $guardianRefs = $this->hedwig->getGuardedEggRefs();

        $guardianRefs[0]->setTextContent($this->egg1->getId() . " " . $this->egg3->getId());

        $this->assertCount(3, $this->hedwig->getGuardedEggs());

        $eggs = [$this->egg1, $this->egg2, $this->egg3];
        foreach ($eggs as $egg) {
            $exists = false;
            foreach ($this->hedwig->getGuardedEggs() as $egg2) {
                if ($egg2->equals($egg)) {
                    $exists = true;
                }
            }
            $this->assertTrue($exists);
        }
    }

    public function testUpdateGuardedEggRefsByRemoveElements(): void
    {
        $guardianRefs = $this->timmy->getGuardedEggRefs();
        $this->timmy->removeGuardedEggRef($guardianRefs[0]);

        $this->assertCount(1, $this->timmy->getGuardedEggs());
        $exists = false;
        foreach ($this->timmy->getGuardedEggs() as $egg) {
            if ($egg->equals($this->egg3)) {
                $exists = true;
            }
        }
        $this->assertTrue($exists);
    }

    public function testClearGuardedEggRefs(): void
    {
        $this->timmy->clearGuardedEggRefs();
        $this->assertEmpty($this->timmy->getGuardedEggRefs());

        // should not affect animals collection
        $animals = $this->modelInstance->getDocumentElement();
        $this->assertCount(3, $animals->getAnimals());
    }
}
