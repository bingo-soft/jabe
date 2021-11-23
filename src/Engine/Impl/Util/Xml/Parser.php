<?php

namespace BpmPlatform\Engine\Impl\Util\Xml;

class Parser
{
    private static $INSTANCE;

    private function __construct()
    {
    }

    public function getInstance(): Parser
    {
        if (self::$INSTANCE == null) {
            self::$INSTANCE = new Parser();
        }
        return self::$INSTANCE;
    }

    public function createParse(): Parse
    {
        return new Parse($this);
    }

    public function parse($streamSource, DefaultHandlerInterface $handler): void
    {
        if (SaxParser::getInstance()->isFree()) {
            SaxParser::getInstance()->init();
        }

        SaxParser::getInstance()->parse($streamSource, $handler);

        SaxParser::getInstance()->free();
    }

    public function getXmlParser()
    {
        return SaxParser::getInstance()->getXmlParser();
    }
}
