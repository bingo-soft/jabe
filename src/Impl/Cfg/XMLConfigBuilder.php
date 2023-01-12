<?php

namespace Jabe\Impl\Cfg;

use MyBatis\Parsing\{
    XNode,
    XPathParser
};
use Jabe\ProcessEngineConfiguration;
use Util\Reflection\MetaObject;

class XMLConfigBuilder
{
    private $parser;

    public function __construct($resource)
    {
        $this->parser = new XPathParser($resource, false);
    }

    public function build(): ProcessEngineConfiguration
    {
        $this->parsed = true;
        $this->parseConfiguration($this->parser->evalNode("/configuration"));
        return $this->configuration;
    }

    private function parseConfiguration(XNode $root): void
    {
        $this->propertiesElement($root->evalNode("properties"));
    }

    private function propertiesElement(XNode $properties): void
    {
        $class = $properties->getStringAttribute("class");
        if (class_exists($class)) {
            $this->configuration = new $class();
            $meta = new MetaObject($this->configuration);
            foreach ($properties->getChildren() as $child) {
                if ("property" == $child->getName()) {
                    $name = $child->getStringAttribute("name");
                    if ($meta->hasSetter($name)) {
                        //check if evaluation string
                        //if it is evaluate, if has no bound properties, then use default property
                    }
                }
            }
        }
    }
}
