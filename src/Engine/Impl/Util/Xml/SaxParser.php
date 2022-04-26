<?php

namespace Jabe\Engine\Impl\Util\Xml;

class SaxParser
{
    private static $INSTANCE;

    private $xmlParser;

    private $handler;

    private $isFree;

    private function __construct($xmlParser)
    {
        $this->xmlParser = $xmlParser;
    }

    public static function getInstance()
    {
        if (self::$INSTANCE == null) {
            self::$INSTANCE = new SaxParser(xml_parser_create());
            self::$INSTANCE->free(false);
        }
        return self::$INSTANCE;
    }

    public function isFree(): bool
    {
        return $this->isFree;
    }

    public function free(bool $flag = true): void
    {
        if ($flag && $this->xmlParser != null) {
            xml_parser_free($this->xmlParser);
            $this->xmlParser = null;
            $this->isFree = $flag;
        } elseif (!$flag) {
            $this->isFree = false;
        }
    }

    public function init(): void
    {
        $this->xmlParser = xml_parser_create();
        $this->isFree = false;
    }

    public function getXmlParser()
    {
        if ($this->xmlParser == null) {
            $this->init();
        }
        return $this->xmlParser;
    }

    public function parse($streamSource, DefaultHandlerInterface $handler): void
    {
        $meta = stream_get_meta_data($streamSource);
        $data = fread($streamSource, filesize($meta['uri']));

        xml_set_element_handler($this->getXmlParser(), function ($parser, $name, $attribs) use ($handler) {
            $handler->startElement($name, $attribs);
        }, function ($parser, $name) use ($handler) {
            $handler->endElement($name);
        });

        xml_parse($this->getXmlParser(), $data);

        fclose($streamSource);
    }
}
