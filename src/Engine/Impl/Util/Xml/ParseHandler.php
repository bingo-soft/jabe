<?php

namespace BpmPlatform\Engine\Impl\Util\Xml;

class ParseHandler implements DefaultHandlerInterface
{
    protected $parse;

    protected $elementStack = [];

    public function __construct(Parse $parse)
    {
        $this->parse = $parse;
    }

    public function startElement(string $name, array $attributes): void
    {
        $element = new Element(
            $name,
            $attributes,
            xml_get_current_line_number($this->parse->getXmlParser()),
            xml_get_current_column_number($this->parse->getXmlParser())
        );
        if (empty($this->elementStack)) {
            $this->parse->setRootElement($element);
        } else {
            $this->elementStack[0]->add($element);
        }
        array_unshift($this->elementStack, $element);
    }

    public function endElement(string $name): void
    {
        array_shift($this->elementStack);
    }
}
