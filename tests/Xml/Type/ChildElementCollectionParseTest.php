<?php

namespace Tests\Xml\Type;

use Tests\Xml\TestModel\Instance\FlyingAnimal;

class ChildElementCollectionParseTest extends ChildElementCollectionCreateTest
{
    protected function setUp(): void
    {
        parent::parseModel("ChildElementCollectionTest");
        $this->copyModelInstance();
    }

    private function copyModelInstance(): void
    {
        $this->tweety = $this->modelInstance->getModelElementById("tweety");
        $this->daffy = $this->modelInstance->getModelElementById("daffy");
        $this->daisy = $this->modelInstance->getModelElementById("daisy");
        $this->plucky = $this->modelInstance->getModelElementById("plucky");
        $this->birdo = $this->modelInstance->getModelElementById("birdo");

        $this->flightInstructorChild = FlyingAnimal::$flightInstructorChild->getReferenceSourceCollection();
        $this->flightPartnerRefCollection = FlyingAnimal::$flightPartnerRefsColl->getReferenceSourceCollection();
    }
}
