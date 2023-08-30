<?php

namespace Jabe\Impl\Form\Engine;

class HtmlWriteContext
{
    private $writer;
    private $elements;

    public function __construct(StringWriter $writer, Elements $elements)
    {
        $this->writer = $writer;
        $this->elements = $elements;
    }

    public function getWriter(): ?StringWriter
    {
        return $this->writer;
    }

    public function getElementStackSize(): int
    {
        return $this->elements->size();
    }
}
