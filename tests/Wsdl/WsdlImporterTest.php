<?php

namespace Tests\WSDL;

use PHPUnit\Framework\TestCase;
use Jabe\Impl\Cxf\Webservice\CxfWSDLImporter;
use Jabe\Impl\Webservice\{
    WSOperation,
    WSService
};
use Jabe\Impl\Bpmn\Data\{
    SimpleStructureDefinition,
    StructureDefinitionInterface
};

class WsdlImporterTest extends TestCase
{
    private $importer;

    protected function setUp(): void
    {
        $this->importer = new CxfWSDLImporter();
    }

    public function testImportCounter(): void
    {
        $url = "tests/Wsdl/Resources/counter.wsdl";
        $this->importer->importFrom($url);

        $services = $this->importer->getServices();
        $this->assertCount(1, $services);
        $service = array_values($services)[0];

        $this->assertEquals("Counter", $service->getName());
        $this->assertEquals("http://localhost:63081/webservicemock", $service->getLocation());

        $operations = $this->importer->getOperations();
        ksort($operations);

        $this->assertCount(7, $operations);
        $this->assertOperation(array_values($operations)[0], "getCount", $service);
        $this->assertOperation(array_values($operations)[1], "inc", $service);
        $this->assertOperation(array_values($operations)[2], "noNameResult", $service);
        $this->assertOperation(array_values($operations)[3], "prettyPrintCount", $service);
        $this->assertOperation(array_values($operations)[4], "reservedWordAsName", $service);
        $this->assertOperation(array_values($operations)[5], "reset", $service);
        $this->assertOperation(array_values($operations)[6], "setTo", $service);

        $structures = $this->importer->getStructures();
        ksort($structures);

        $this->assertCount(14, $structures);
        $this->assertStructure(array_values($structures)[0], "getCount", [], []);
        $this->assertStructure(array_values($structures)[1], "getCountResponse", ["count"], ["int"]);
        $this->assertStructure(array_values($structures)[2], "inc", [], []);
        $this->assertStructure(array_values($structures)[3], "incResponse", [], []);
        $this->assertStructure(array_values($structures)[4], "noNameResult", ["prefix", "suffix"], ["string", "string"]);
        $this->assertStructure(array_values($structures)[5], "noNameResultResponse", ["return"], ["string"]);
        $this->assertStructure(array_values($structures)[6], "prettyPrintCount", ["prefix", "suffix"], ["string", "string"]);
        $this->assertStructure(array_values($structures)[13], "setToResponse", [], []);
    }

    private function assertOperation(WSOperation $wsOperation, ?string $name, WSService $service): void
    {
        $this->assertEquals($name, $wsOperation->getName());
        $this->assertEquals($service, $wsOperation->getService());
    }

    private function assertStructure(StructureDefinitionInterface $structure, ?string $structureId, array $parameters, array $classes): void
    {
        $this->assertEquals($structureId, $structure->getId());
        for ($i = 0; $i < $structure->getFieldSize(); $i += 1) {
            $this->assertEquals($parameters[$i], $structure->getFieldNameAt($i));
            $this->assertEquals($classes[$i], $structure->getFieldTypeAt($i));
        }
    }
}
