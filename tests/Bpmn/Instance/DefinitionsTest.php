<?php

namespace Tests\Bpmn\Instance;

use Tests\Xml\Test\{
    AbstractTypeAssumption,
    AttributeAssumption
};
use Jabe\Model\Bpmn\Impl\BpmnModelConstants;
use Jabe\Model\Bpmn\Instance\Bpmndi\BpmnDiagramInterface;
use Jabe\Model\Bpmn\Instance\{
    ExtensionInterface,
    ImportInterface,
    RootElementInterface,
    RelationshipInterface
};

class DefinitionsTest extends BpmnModelElementInstanceTest
{
    public function getTypeAssumption(): AbstractTypeAssumption
    {
        return new BpmnTypeAssumption($this->model, false);
    }

    public function getChildElementAssumptions(): array
    {
        return [
            new BpmnChildElementAssumption($this->model, ImportInterface::class),
            new BpmnChildElementAssumption($this->model, ExtensionInterface::class),
            new BpmnChildElementAssumption($this->model, RootElementInterface::class),
            new BpmnChildElementAssumption(
                $this->model,
                BpmnDiagramInterface::class,
                null,
                null,
                BpmnModelConstants::BPMNDI_NS
            ),
            new BpmnChildElementAssumption($this->model, RelationshipInterface::class),
        ];
    }

    public function getAttributesAssumptions(): array
    {
        return [
            new AttributeAssumption(null, "id", true),
            new AttributeAssumption(null, "name"),
            new AttributeAssumption(null, "targetNamespace", false, true),
            new AttributeAssumption(null, "expressionLanguage", false, false, "http://www.w3.org/1999/XPath"),
            new AttributeAssumption(null, "typeLanguage", false, false, "http://www.w3.org/2001/XMLSchema"),
            new AttributeAssumption(null, "exporter"),
            new AttributeAssumption(null, "exporterVersion")
        ];
    }
}
