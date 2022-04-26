<?php

namespace Tests\WSDL;

use PHPUnit\Framework\TestCase;
use Jabe\Model\Wsdl\Impl\WsdlParser;
use Jabe\Model\Wsdl\Instance\{
    BindingInterface,
    ServiceInterface
};

class WsdlModelTest extends TestCase
{
    protected $modelParser;

    protected $modelInstance;

    protected function parseModel(string $test)
    {
        $this->modelParser = new WsdlParser();
        $xml = fopen("tests/Wsdl/Resources/$test.wsdl", 'r+');
        $this->modelInstance = $this->modelParser->parseModelFromStream($xml);
    }

    public function testCounter(): void
    {
        $this->parseModel("counter");
        $defs = $this->modelInstance->getDocumentElement();
        $types = $defs->getTypes();
        $this->assertFalse($types == null);
        $schema = $defs->getTypes()->getSchema();
        $this->assertFalse($schema == null);
        $services = $this->modelInstance->getModelElementsByType(ServiceInterface::class);
        $bindings = $this->modelInstance->getModelElementsByType(BindingInterface::class);
        $this->assertCount(1, $bindings);

        $this->assertEquals("tns:Counter", $bindings[0]->getType());

        $services = $this->modelInstance->getModelElementsByType(ServiceInterface::class);
        $this->assertCount(1, $services);

        $service = $services[0];
        $this->assertEquals("http://localhost:63081/webservicemock", $service->getPort()->getAddress()->getLocation());
        $this->assertEquals("http://localhost:63081/webservicemock", $service->getEndpoint());
    }
}
