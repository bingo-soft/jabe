<?php

namespace Tests\Xml\Type;

class AttributeParseTest extends AttributeCreateTest
{
    protected function setUp(): void
    {
        parent::parseModel("AnimalTest");
        $this->copyModelInstance();
    }

    private function copyModelInstance(): void
    {
        $this->tweety = $this->modelInstance->getModelElementById("tweety");
        $this->idAttribute = $this->tweety->getElementType()->getAttribute("id");
        $this->nameAttribute = $this->tweety->getElementType()->getAttribute("name");
        $this->fatherAttribute = $this->tweety->getElementType()->getAttribute("father");
    }
}
