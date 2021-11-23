<?php

namespace BpmPlatform\Engine\Impl\Util\Xml;

use BpmPlatform\Model\Xml\Impl\Util\ReflectUtil;

class Parse
{
    protected $parser;
    protected $name;
    protected $streamSource;
    protected $rootElement = null;
    protected $errors = [];
    protected $warnings = [];
    protected $schemaResource;
    protected $enableXxeProcessing = true;
    protected $saxParser;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    public function getParser(): Parser
    {
        return $this->parser;
    }

    public function getXmlParser()
    {
        return $this->parser->getXmlParser();
    }

    public function name(string $name): Parse
    {
        $this->name = $name;
        return $this;
    }

    public function sourceInputStream($inputStream): Parse
    {
        if ($this->name == null) {
            $this->name("inputStream");
        }
        $this->streamSource = $inputStream;
        return $this;
    }

    public function sourceResource(string $resource): Parse
    {
        return $this->sourceInputStream(ReflectUtil::getResourceAsFile($resource));
    }

    public function setRootElement(Element $rootElement): void
    {
        $this->rootElement = $rootElement;
    }

    public function getRootElement(): ?Element
    {
        return $this->rootElement;
    }

    public function execute(): Parse
    {
        $this->parser->parse($this->streamSource, new ParseHandler($this));
        return $this;
    }
}
