<?php

namespace Tests\Knd;

use PHPUnit\Framework\TestCase;
use Jabe\Model\Xml\ModelInstanceInterface;
use Jabe\Model\Knd\Complaints\Impl\KndResponseParser;

class KndResponseParseTest extends TestCase
{
    protected $modelParser;

    protected $modelInstance;

    /**
     * @param string $test
     */
    protected function parseModel(string $test)
    {
        $this->modelParser = new KndResponseParser();
        $xml = fopen('tests/Knd/Resources/Examples/' . $test . '.xml', 'r+');
        $this->modelInstance = $this->modelParser->parseModelFromStream($xml);
    }

    public function testResponse0(): void
    {
        $this->parseModel("Response0");
        $kndResponse = $this->modelInstance->getDocumentElement();
        $inspectionResult = $kndResponse->getInspectionResult();
        $inspectionStatus = $inspectionResult->getStatus();
        $inspectionStatusCode = $inspectionStatus->getCode();
        $this->assertEquals(101, $inspectionStatusCode->getTextContent());
    }
}
