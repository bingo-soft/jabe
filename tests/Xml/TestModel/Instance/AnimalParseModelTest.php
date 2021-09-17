<?php

namespace Tests\Xml\TestModel\Instance;

class AnimalParseModelTest extends AnimalCreateModelTest
{
    protected function setUp(): void
    {
        parent::parseModel("AnimalTest");
        $this->copyModelInstance();
    }

    private function copyModelInstance(): void
    {
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
}
