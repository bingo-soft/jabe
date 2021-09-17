<?php

namespace Tests\Xml\TestModel\Instance;

class BirdParseModelTest extends BirdCreateModelTest
{
    protected function setUp(): void
    {
        parent::parseModel("BirdTest");
        $this->copyModelInstance();
    }

    private function copyModelInstance(): void
    {
        $this->tweety = $this->modelInstance->getModelElementById("tweety");
        $this->hedwig = $this->modelInstance->getModelElementById("hedwig");
        $this->timmy = $this->modelInstance->getModelElementById("timmy");
        $this->egg1 = $this->modelInstance->getModelElementById("egg1");
        $this->egg2 = $this->modelInstance->getModelElementById("egg2");
        $this->egg3 = $this->modelInstance->getModelElementById("egg3");
    }
}
