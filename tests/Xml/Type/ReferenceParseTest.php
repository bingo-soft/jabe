<?php

namespace Tests\Xml\Type;

use Tests\Xml\TestModel\Instance\{
    Animal,
    FlyingAnimal,
    FlightPartnerRef
};

class ReferenceParseTest extends ReferenceCreateTest
{
    protected function setUp(): void
    {
        parent::parseModel("ReferenceTest");
        $this->copyModelInstance();
    }

    private function copyModelInstance(): void
    {
        $this->tweety = $this->modelInstance->getModelElementById("tweety");
        $this->daffy = $this->modelInstance->getModelElementById("daffy");
        $this->daisy = $this->modelInstance->getModelElementById("daisy");
        $this->plucky = $this->modelInstance->getModelElementById("plucky");
        $this->birdo = $this->modelInstance->getModelElementById("birdo");

        $this->animalType = $this->modelInstance->getModel()->getType(Animal::class);

        // QName attribute reference
        $this->fatherReference = $this->animalType->getAttribute("father")->getOutgoingReferences()[0];

        // ID attribute reference
        $this->motherReference = $this->animalType->getAttribute("mother")->getOutgoingReferences()[0];

        // ID element reference
        $this->flightPartnerRefsColl = FlyingAnimal::$flightPartnerRefsColl;

        $flightPartnerRefType = $this->modelInstance->getModel()->getType(FlightPartnerRef::class);
        $this->flightPartnerRef = $this->modelInstance->getModelElementsByType($flightPartnerRefType)[0];
    }
}
