<?php

namespace Tests\Bpmn\Instance;

use Tests\Xml\Test\{
    AbstractTypeAssumption,
    AttributeAssumption
};
use Jabe\Model\Bpmn\Bpmn;
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Instance\{
    ArtifactInterface,
    TextAnnotationInterface,
    TextInterface
};

class TextAnnotationTest extends BpmnModelElementInstanceTest
{
    protected function setUp(): void
    {
        $ref = new \ReflectionClass(static::class);
        $className = str_replace('Test', 'Interface', $ref->getShortName());
        $instanceClass = sprintf("%s\%s", str_replace('Tests', 'Jabe\Model', __NAMESPACE__), $className);
        $this->modelInstance = Bpmn::getInstance()->readModelFromStream(
            fopen(realpath(".") . "/tests/Bpmn/Resources/TextAnnotationTest.bpmn", "r+")
        );
        $this->model = $this->modelInstance->getModel();
        $this->modelElementType = $this->model->getType($instanceClass);
    }

    public function getTypeAssumption(): AbstractTypeAssumption
    {
        return new BpmnTypeAssumption($this->model, false, null, ArtifactInterface::class);
    }

    public function getChildElementAssumptions(): array
    {
        return [
            new BpmnChildElementAssumption($this->model, TextInterface::class, 0, 1)
        ];
    }

    public function getAttributesAssumptions(): array
    {
        return [
            new AttributeAssumption(null, "textFormat", false, false, "text/plain")
        ];
    }

    public function testGetTextAnnotationsByType(): void
    {
        $textAnnotations = $this->modelInstance->getModelElementsByType(TextAnnotationInterface::class);
        $this->assertCount(2, $textAnnotations);
    }

    public function testGetTextAnnotationById(): void
    {
        $textAnnotation = $this->modelInstance->getModelElementById("textAnnotation2");
        $this->assertFalse($textAnnotation === null);
        $this->assertEquals("text/plain", $textAnnotation->getTextFormat());
        $text = $textAnnotation->getText();
        $this->assertEquals("Attached text annotation", $text->getTextContent());
    }

    public function testTextAnnotationAsAssociationSource(): void
    {
        $association = $this->modelInstance->getModelElementById("Association_1");
        $source = $association->getSource();
        $this->assertTrue($source instanceof TextAnnotationInterface);
        $this->assertEquals("textAnnotation2", $source->getId());
    }

    public function testTextAnnotationAsAssociationTarget(): void
    {
        $association = $this->modelInstance->getModelElementById("Association_2");
        $source = $association->getTarget();
        $this->assertTrue($source instanceof TextAnnotationInterface);
        $this->assertEquals("textAnnotation1", $source->getId());
    }
}
