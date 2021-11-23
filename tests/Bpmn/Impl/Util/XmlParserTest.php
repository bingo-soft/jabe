<?php

namespace Tests\Bpmn\Engine\Util;

use PHPUnit\Framework\TestCase;
use BpmPlatform\Model\Xml\Impl\Util\ReflectUtil;
use BpmPlatform\Engine\Impl\Util\Xml\{
    Parse,
    Parser
};

class XmlParserTest extends TestCase
{
    private $parse;

    public function testParsing(): void
    {
        $inputStream = ReflectUtil::getResourceAsFile("tests/Bpmn/Resources/EventDefinitionsTest.xml");
        $parse = new Parse(Parser::getInstance());
        $parse->sourceInputStream($inputStream);
        $parse->execute();
        $this->assertEquals("DEFINITIONS", $parse->getRootElement()->getTagName());
        $this->assertCount(6, $parse->getRootElement()->elements());
        $this->assertFalse($parse->getRootElement()->element("PROCESS") == null);
        $this->assertTrue($parse->getRootElement()->element("FAKE") == null);
        $this->assertCount(2, $parse->getRootElement()->element("PROCESS")->elements());
        $this->assertFalse($parse->getRootElement()->element("PROCESS")->element("USERTASK") == null);
    }
}
