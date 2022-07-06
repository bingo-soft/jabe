<?php

namespace Jabe\Engine\Impl\Form\Engine;

class HtmlElementWriter
{
    protected $tagName;

    /** selfClosing means that the element should not be rendered as a
     * start + end tag pair but as a single tag using "/" to close the tag
     * inline */
    protected $isSelfClosing;
    protected $textContent;
    protected $attributes = [];

    public function __construct(string $tagName, ?bool $isSelfClosing = false)
    {
        $this->tagName = $tagName;
        $this->isSelfClosing = $isSelfClosing;
    }

    public function writeStartTag(HtmlWriteContext $context): void
    {
        $this->writeLeadingWhitespace($context);
        $this->writeStartTagOpen($context);
        $this->writeAttributes($context);
        $this->writeStartTagClose($context);
        $this-> writeEndLine($context);
    }

    public function writeContent(HtmlWriteContext $context): void
    {
        if ($this->textContent !== null) {
            $this->writeLeadingWhitespace($context);
            $this->writeTextContent($context);
            $this->writeEndLine($context);
        }
    }

    public function writeEndTag(HtmlWriteContext $context): void
    {
        if (!$this->isSelfClosing) {
            $this->writeLeadingWhitespace($context);
            $this->writeEndTagElement($context);
            $this->writeEndLine($context);
        }
    }

    protected function writeEndTagElement(HtmlWriteContext $context): void
    {
        $writer = $context->getWriter();
        $writer->write("</");
        $writer->write($this->tagName);
        $writer->write(">");
    }

    protected function writeTextContent(HtmlWriteContext $context): void
    {
        $writer = $context->getWriter();
        $writer->write("  "); // add additional whitespace
        $writer->write($this->textContent);
    }

    protected function writeStartTagOpen(HtmlWriteContext $context): void
    {
        $writer = $context->getWriter();
        $writer->write("<");
        $writer->write($this->tagName);
    }

    protected function writeAttributes(HtmlWriteContext $context): void
    {
        $writer = $context->getWriter();
        foreach ($attributes as $key => $attribute) {
            $writer->write(" ");
            $writer->write($key);
            if ($attribute !== null) {
                $writer->write("=\"");
                $attributeValue = $this->escapeQuotes($attribute->getValue());
                $writer->write($attributeValue);
                $writer->write("\"");
            }
        }
    }

    protected function escapeQuotes(string $attributeValue): string
    {
        $escapedHtmlQuote = "&quot;";
        $escapedJavaQuote = "\"";
        return str_replace($escapedJavaQuote, $escapedHtmlQuote, $attributeValue);
    }

    protected function writeEndLine(HtmlWriteContext $context): void
    {
        $writer = $context->getWriter();
        $writer->write("\n");
    }

    protected function writeStartTagClose(HtmlWriteContext $context): void
    {
        $writer = $context->getWriter();
        if ($this->isSelfClosing) {
            $writer->write(" /");
        }
        $writer->write(">");
    }

    protected function writeLeadingWhitespace(HtmlWriteContext $context): void
    {
        $stackSize = $context->getElementStackSize();
        $writer = $context->getWriter();
        for ($i = 0; $i < $stackSize; $i += 1) {
            $writer->write("  ");
        }
    }

    // builder /////////////////////////////////////

    public function attribute(string $name, string $value): HtmlElementWriter
    {
        $this->attributes[$name] = $value;
        return $this;
    }

    public function textContent(string $text): HtmlElementWriter
    {
        if ($this->isSelfClosing) {
            throw new \Exception("Self-closing element cannot have text content.");
        }
        $this->textContent = $text;
        return $this;
    }
}
