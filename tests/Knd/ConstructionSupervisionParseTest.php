<?php

namespace Tests\Knd;

use PHPUnit\Framework\TestCase;
use Jabe\Model\Xml\ModelInstanceInterface;
use Jabe\Model\Knd\ConstructionSupervision\Impl\RequestParser;

class ConstructionSupervisionParseTest extends TestCase
{
    protected $modelParser;

    protected $modelInstance;

    /**
     * @param string $test
     */
    protected function parseModel(string $test)
    {
        $this->modelParser = new RequestParser();
        $xml = fopen('tests/Knd/Resources/Examples/' . $test . '.xml', 'r+');
        $this->modelInstance = $this->modelParser->parseModelFromStream($xml);
    }

    public function testRequest0(): void
    {
        $this->parseModel("Request0");
        $request = $this->modelInstance->getDocumentElement();
        $arr = $request->asArray();
        $this->assertCount(7, $arr);
        $this->assertEquals("01.01.2021", $arr['Service']['currentDate']);
    }
}
