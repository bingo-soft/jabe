<?php

namespace Jabe\Impl\Form\Engine;

class HtmlDocumentBuilder
{
    protected $context;
    protected $elements;
    protected $writer;

    public function __construct(HtmlElementWriter $documentElement)
    {
        $this->writer = new StringWriter();
        $this->elements = new Elements();
        $this->context = new HtmlWriteContext($this->writer, $this->elements);
        $this->startElement($documentElement);
    }

    public function startElement(HtmlElementWriter $renderer): HtmlDocumentBuilder
    {
        $renderer->writeStartTag($this->context);
        $this->elements->push($renderer);
        return $this;
    }

    public function endElement(): HtmlDocumentBuilder
    {
        $renderer = $this->elements->pop();
        $renderer->writeContent($this->context);
        $renderer->writeEndTag($this->context);
        return $this;
    }

    public function getHtmlString(): string
    {
        return strval($this->writer);
    }
}
