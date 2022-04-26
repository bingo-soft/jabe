<?php

namespace Jabe\Engine\Impl\Webservice;

use Jabe\Model\Wsdl\Impl\WsdlParser;
use Jabe\Model\Wsdl\Instance\DefinitionsInterface;

class WSDLManager
{
    private $parser;

    public function __construct()
    {
        $this->parser = new WsdlParser();
    }

    public function getDefinition(string $url): DefinitionsInterface
    {
        $modelInstance = $this->parser->parseModelFromStream(fopen($url, "r"));
        return $modelInstance->getDocumentElement();
    }
}
