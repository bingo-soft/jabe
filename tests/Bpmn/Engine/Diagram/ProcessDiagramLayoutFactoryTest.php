<?php

namespace Tests\Bpmn\Engine\Diagram;

use PHPUnit\Framework\TestCase;
use BpmPlatform\Engine\Impl\Bpmn\Diagram\ProcessDiagramLayoutFactory;

class ProcessDiagramLayoutFactoryTest extends TestCase
{
    public function testGetProcessDiagramLayout(): void
    {
        $bpmnXmlStream = fopen('tests/Bpmn/Resources/Diagram/testInvoiceProcessFromBusinessProcessIncubator.bpmn', 'r+');
        $imageStream = fopen('tests/Bpmn/Resources/Diagram/testInvoiceProcessFromBusinessProcessIncubator.png', 'r+');
        $factory = new ProcessDiagramLayoutFactory();
        $layout = $factory->getProcessDiagramLayout($bpmnXmlStream, $imageStream);
        $this->assertCount(16, $layout->getElements());
        $this->assertCount(16, $layout->getNodes());
        $this->assertFalse($layout->getNode("BPMNDiagram") == null);
    }
}
